<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
{
    private function parseMenuRoles(?string $roles): array
    {
        return array_values(array_unique(array_filter(explode(';', (string) $roles))));
    }

    private function formatMenuRoles(array $roles): string
    {
        $roles = array_values(array_unique(array_filter($roles)));

        return $roles ? ';' . implode(';', $roles) . ';' : '';
    }

    private function descendantMenuIds(int $menuId): array
    {
        $ids = [];
        $children = Menu::where('parent_id', $menuId)->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->descendantMenuIds($childId));
        }

        return $ids;
    }

    private function expandSelectedMenuIds(array $ids): array
    {
        $expanded = [];

        foreach ($ids as $id) {
            $id = (int) $id;
            $descendants = $this->descendantMenuIds($id);
            $expanded = array_merge($expanded, $descendants ?: [$id]);
        }

        return array_values(array_unique($expanded));
    }

    private function expandDeselectedMenuIds(array $ids): array
    {
        $expanded = [];

        foreach ($ids as $id) {
            $id = (int) $id;
            $expanded[] = $id;
            $expanded = array_merge($expanded, $this->descendantMenuIds($id));
        }

        return array_values(array_unique($expanded));
    }

    private function updateMenuRoles(array $ids, string $role, bool $active): void
    {
        foreach (Menu::whereIn('id', $ids)->get() as $menu) {
            $roles = $this->parseMenuRoles($menu->role);

            if ($active) {
                $roles[] = $role;
            } else {
                $roles = array_values(array_filter($roles, fn ($item) => $item !== $role));
            }

            $menu->role = $this->formatMenuRoles($roles);
            $menu->save();
        }
    }

    public function roles() { return view('settings.roles', ['roles' => Role::withCount('users')->orderBy('name')->get()]); }
    public function storeRole(Request $request) { $request->validate(['name' => 'required|max:50']); Role::firstOrCreate(['name' => $request->string('name'), 'guard_name' => 'web']); return back()->with('success', 'Role berhasil disimpan.'); }
    public function deleteRole(Role $role) { $role->delete(); return back()->with('success', 'Role berhasil dihapus.'); }
    public function menus() { return view('settings.menus', ['roles' => Role::with('permissions')->orderBy('name')->get()]); }

    public function menuData(string $role)
    {
        $menus = Menu::orderBy('seq')->orderBy('name')->get();

        return response()->json($menus->map(function (Menu $menu) use ($role) {
            $children = $this->descendantMenuIds((int) $menu->id);

            return [
                'text' => $menu->name,
                'id' => $menu->id,
                'icon' => $menu->icon ?: 'bi bi-circle',
                'parent' => $menu->parent_id ?: '#',
                'state' => [
                    'opened' => true,
                    'disabled' => false,
                    'selected' => !$children && in_array($role, $this->parseMenuRoles($menu->role), true),
                ],
            ];
        })->values());
    }

    public function updateMenuAccess(Request $request)
    {
        $request->validate([
            'gp' => ['required', 'string', 'exists:roles,name'],
            'selected_ids' => ['nullable', 'array'],
            'selected_ids.*' => ['integer', 'exists:menus,id'],
            'deselected_ids' => ['nullable', 'array'],
            'deselected_ids.*' => ['integer', 'exists:menus,id'],
        ]);

        $deselectedIds = $this->expandDeselectedMenuIds($request->input('deselected_ids', []));
        $selectedIds = $this->expandSelectedMenuIds($request->input('selected_ids', []));

        if ($deselectedIds) {
            $this->updateMenuRoles($deselectedIds, $request->string('gp')->toString(), false);
        }

        if ($selectedIds) {
            $this->updateMenuRoles($selectedIds, $request->string('gp')->toString(), true);
        }

        return response()->json([
            'success' => true,
            'selected_ids' => $selectedIds,
            'deselected_ids' => $deselectedIds,
        ]);
    }

    public function storeMenu(Request $request) { $request->validate(['name' => 'required', 'link' => 'nullable']); Menu::create($request->only(['name', 'link', 'icon', 'parent_id', 'role', 'seq'])); return back()->with('success', 'Menu berhasil disimpan.'); }
    public function deleteMenu(Menu $menu) { $menu->delete(); return back()->with('success', 'Menu berhasil dihapus.'); }
    public function company() { return view('settings.company', ['setting' => Setting::firstOrCreate([])]); }
    public function updateCompany(Request $request) { $setting = Setting::firstOrCreate([]); $setting->update($request->only(['nama_perusahaan', 'alamat', 'telepon', 'path_logo', 'npwp', 'bank_name', 'bank_account', 'bank_account_name'])); return back()->with('success', 'Pengaturan perusahaan berhasil disimpan.'); }
}
