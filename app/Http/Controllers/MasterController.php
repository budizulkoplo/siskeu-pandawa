<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coa;
use App\Models\Customer;
use App\Models\ErpTransactionCode;
use App\Models\Item;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MasterController extends Controller
{
    public function index(string $type)
    {
        $allowed = ['users', 'coa', 'kategori', 'lokasi', 'barang', 'supplier', 'customer', 'erp'];
        abort_unless(in_array($type, $allowed, true), 404);

        $data = match ($type) {
            'users' => ['rows' => User::with('roles')->latest()->get(), 'roles' => \Spatie\Permission\Models\Role::orderBy('name')->get()],
            'coa' => ['rows' => Coa::with('parent')->orderBy('code')->get()],
            'kategori' => ['rows' => Category::latest()->get()],
            'lokasi' => ['rows' => Location::latest()->get()],
            'barang' => ['rows' => Item::with(['category', 'supplier'])->latest()->get(), 'categories' => Category::orderBy('name')->get(), 'suppliers' => Supplier::orderBy('name')->get()],
            'supplier' => ['rows' => Supplier::latest()->get()],
            'customer' => ['rows' => Customer::latest()->get()],
            'erp' => ['rows' => ErpTransactionCode::with('coa')->latest()->get(), 'coas' => Coa::orderBy('code')->get()],
        };

        if ($type === 'users') {
            return view('master.users', [
                'users' => $data['rows'],
                'roles' => $data['roles'],
            ]);
        }

        return view('master.index', compact('type', 'data'));
    }

    public function store(Request $request, string $type, AuditService $audit)
    {
        $allowed = ['users', 'coa', 'kategori', 'lokasi', 'barang', 'supplier', 'customer', 'erp'];
        abort_unless(in_array($type, $allowed, true), 404);

        $payload = $request->except(['_token', 'id', 'password', 'role']);
        $id = $request->integer('id');

        if ($type === 'users') {
            $request->validate([
                'username' => ['required', 'max:80', Rule::unique('users', 'username')->ignore($id)],
                'name' => ['required', 'max:150'],
                'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($id)],
                'password' => [$id ? 'nullable' : 'required', 'min:6'],
                'status' => ['nullable', 'in:aktif,nonaktif'],
            ]);
            if ($request->filled('password')) $payload['password'] = Hash::make($request->string('password'));
            $model = $id ? User::findOrFail($id) : User::create($payload + ['status' => 'aktif']);
            if ($id) $model->update($payload);
            if ($request->filled('role')) $model->syncRoles([$request->string('role')]);
        } else {
            [$class, $rules] = match ($type) {
                'coa' => [Coa::class, ['code' => 'required|max:50', 'name' => 'required|max:150', 'type' => 'required|in:asset,liability,equity,income,expense', 'parent_id' => 'nullable|exists:coas,id']],
                'kategori' => [Category::class, ['code' => 'required|max:50', 'name' => 'required|max:150', 'description' => 'nullable']],
                'lokasi' => [Location::class, ['code' => 'required|max:50', 'name' => 'required|max:150', 'address' => 'nullable', 'is_active' => 'nullable|boolean']],
                'barang' => [Item::class, ['code' => 'required|max:50', 'name' => 'required|max:150', 'unit' => 'required|max:30', 'category_id' => 'nullable|exists:categories,id', 'supplier_id' => 'nullable|exists:suppliers,id', 'minimum_stock' => 'nullable|numeric|min:0', 'purchase_price' => 'nullable|numeric|min:0', 'sale_price' => 'nullable|numeric|min:0', 'is_active' => 'nullable|boolean']],
                'supplier' => [Supplier::class, ['code' => 'required|max:50', 'name' => 'required|max:150', 'email' => 'nullable|email', 'status' => 'nullable|max:30']],
                'customer' => [Customer::class, ['code' => 'required|max:50', 'name' => 'required|max:150', 'email' => 'nullable|email', 'status' => 'nullable|max:30']],
                'erp' => [ErpTransactionCode::class, ['code' => 'required|max:50', 'name' => 'required|max:150', 'direction' => 'required|in:in,out', 'coa_id' => 'nullable|exists:coas,id', 'is_active' => 'nullable|boolean']],
            };
            $request->validate($rules);
            $model = $id ? $class::findOrFail($id) : new $class();
            $old = $model->exists ? $model->toArray() : null;
            $model->fill($payload);
            $model->save();
            $audit->record($model, $id ? 'updated' : 'created', $old, $model->toArray(), "Master {$type} disimpan");
        }

        return back()->with('success', 'Data berhasil disimpan.');
    }

    public function assignUserRoles(Request $request)
    {
        $request->validate([
            'iduser' => ['required', 'integer', 'exists:users,id'],
            'name' => ['nullable', 'array'],
            'name.*' => ['string', 'exists:roles,name'],
        ]);

        User::findOrFail($request->integer('iduser'))->syncRoles($request->input('name', []));

        return response()->json(['success' => true, 'message' => 'Hak akses user berhasil diperbarui.']);
    }

    public function resetUserPassword(Request $request)
    {
        $request->validate([
            'userid' => ['required', 'integer', 'exists:users,id'],
            'new_password' => ['required', 'min:8'],
        ]);

        $user = User::findOrFail($request->integer('userid'));
        $user->password = Hash::make($request->string('new_password'));
        $user->save();

        return back()->with('success', 'Password user berhasil diperbarui.');
    }

    public function destroy(string $type, int $id)
    {
        $class = match ($type) {
            'users' => User::class, 'coa' => Coa::class, 'kategori' => Category::class,
            'lokasi' => Location::class, 'barang' => Item::class, 'supplier' => Supplier::class, 'customer' => Customer::class,
            'erp' => ErpTransactionCode::class, default => abort(404),
        };
        $class::findOrFail($id)->delete();
        return back()->with('success', 'Data berhasil dihapus.');
    }
}
