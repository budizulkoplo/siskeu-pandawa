<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    @php
        $setting = \App\Models\Setting::first();
        $companyLogo = $setting?->path_logo ?: 'logo.png';
    @endphp
    <div class="sidebar-brand text-center p-0">
        <a href="{{ route('dashboard') }}" class="text-decoration-none d-flex align-items-center justify-content-center w-100 h-100">
            <img src="{{ asset($companyLogo) }}?v={{ $setting?->updated_at?->timestamp ?? 1 }}" alt="{{ $setting?->nama_perusahaan ?: 'PT Pandawa' }}" class="brand-image bg-white p-1 rounded" style="max-width:170px;max-height:50px;object-fit:contain;">
        </a>
    </div>

    <div class="sidebar-wrapper">
        @php
            $singkatNama = function ($nama) {
                $parts = explode(' ', trim($nama));
                if (count($parts) <= 1) return $nama;

                $namaDepan = array_shift($parts);
                $inisial = array_map(fn ($part) => strtoupper(substr($part, 0, 1)) . '.', $parts);

                return $namaDepan . ' ' . implode('', $inisial);
            };
            $displayName = $singkatNama(auth()->user()->name ?? 'Guest');
            $companyName = $setting?->nama_perusahaan ?: 'PT Pandawa';
            $userRoles = auth()->user()->getRoleNames()->toArray();
            $menus = \App\Models\Menu::orderBy('seq')->orderBy('name')->get();
            $menusByParent = $menus->groupBy(fn ($menu) => (int) ($menu->parent_id ?: 0));
            $canSeeMenu = function ($menu) use ($userRoles) {
                $roles = array_values(array_filter(explode(';', (string) $menu->role)));
                return $roles && array_intersect($userRoles, $roles);
            };
            $buildMenuTree = function ($parentId = 0) use (&$buildMenuTree, $menusByParent, $canSeeMenu) {
                return $menusByParent->get((int) $parentId, collect())->reduce(function ($visible, $menu) use (&$buildMenuTree, $canSeeMenu) {
                    $children = $buildMenuTree($menu->id);
                    if ($canSeeMenu($menu) || $children->isNotEmpty()) {
                        $menu->children = $children;
                        $visible->push($menu);
                    }
                    return $visible;
                }, collect());
            };
            $menuItems = $buildMenuTree();
        @endphp

        <div class="user-panel d-flex align-items-center p-3">
            <div class="image me-2">
                @if (isset(Auth::user()->foto) && Storage::disk('private')->exists("img/foto/" . Auth::user()->foto))
                    <img src="{{ url('/doc/file/foto/' . Auth::user()->foto . '?t=' . time()) }}" class="img-circle elevation-2" alt="User Image" style="width: 40px; height: 40px; object-fit: cover;">
                @else
                    <img src="{{ asset('assets/img/avatar1.jpg') }}" class="img-circle elevation-2" alt="User Image" style="width: 40px; height: 40px; object-fit: cover;">
                @endif
            </div>
            <div class="info">
                <a href="#" class="d-block text-white">{{ $displayName }}</a>
                <a href="{{ route('setting.index') }}" class="d-flex align-items-center gap-1 text-warning text-decoration-none" style="font-size:.75rem;line-height:1rem"><i class="bi bi-building fa-sm"></i><span>{{ $companyName }}</span></a>
            </div>
        </div>

        <div class="px-3 pt-2">
            <input type="text" id="menuSearch" class="form-control form-control-sm" placeholder="Cari menu...">
        </div>

        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                @foreach ($menuItems as $item)
                    @php
                        $isActiveParent = false;
                        foreach ($item->children as $child) {
                            if ($child->link && Route::is($child->link)) $isActiveParent = true;
                            foreach ($child->children as $grandchild) {
                                if ($grandchild->link && Route::is($grandchild->link)) $isActiveParent = true;
                            }
                        }
                        $itemActive = $item->link && request()->routeIs($item->link);
                    @endphp

                    <li class="nav-item menu-item {{ $isActiveParent || $itemActive ? 'menu-open' : '' }}">
                        <a href="{{ $item->children->isNotEmpty() ? '#' : ($item->link && Route::has($item->link) ? route($item->link) : '#') }}" class="nav-link menu-link {{ $itemActive || $isActiveParent ? 'active' : '' }}">
                            <i class="nav-icon {{ $item->icon ?: 'bi bi-circle' }}"></i>
                            <p>{{ $item->name }} @if($item->children->isNotEmpty())<i class="nav-arrow bi bi-chevron-right"></i>@endif</p>
                        </a>

                        @if ($item->children->isNotEmpty())
                            <ul class="nav nav-treeview submenu ps-4" style="{{ $isActiveParent ? 'display: block;' : '' }}">
                                @foreach ($item->children as $child)
                                    @php
                                        $isActiveChild = $child->link && Route::is($child->link);
                                        $isActiveGrandchild = false;
                                        foreach ($child->children as $grandchild) {
                                            if ($grandchild->link && Route::is($grandchild->link)) $isActiveGrandchild = true;
                                        }
                                    @endphp
                                    <li class="nav-item menu-item {{ $isActiveGrandchild ? 'menu-open' : '' }}">
                                        @if ($child->children->isNotEmpty())
                                            <a href="#" class="nav-link menu-link {{ $isActiveGrandchild ? 'active' : '' }}">
                                                <i class="nav-icon {{ $child->icon ?: 'bi bi-circle' }}"></i><p>{{ $child->name }}</p><i class="nav-arrow bi bi-chevron-right"></i>
                                            </a>
                                            <ul class="nav nav-treeview submenu ps-5" style="{{ $isActiveGrandchild ? 'display: block;' : '' }}">
                                                @foreach ($child->children as $grandchild)
                                                    @if ($grandchild->link && Route::has($grandchild->link))
                                                        <li class="nav-item menu-item">
                                                            <a href="{{ route($grandchild->link) }}" class="nav-link menu-link {{ request()->routeIs($grandchild->link) ? 'active' : '' }}">
                                                                <i class="nav-icon {{ $grandchild->icon ?: 'bi bi-circle' }}"></i><p>{{ $grandchild->name }}</p>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @elseif ($child->link && Route::has($child->link))
                                            <a href="{{ route($child->link) }}" class="nav-link menu-link {{ $isActiveChild ? 'active' : '' }}">
                                                <i class="nav-icon {{ $child->icon ?: 'bi bi-circle' }}"></i><p>{{ $child->name }}</p>
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>

<script>
    document.getElementById('menuSearch').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        const allItems = document.querySelectorAll('.menu-item');

        allItems.forEach(item => item.style.display = 'none');
        allItems.forEach(item => {
            const link = item.querySelector('.menu-link');
            if (!link || !link.textContent.toLowerCase().includes(keyword)) return;

            item.style.display = 'block';
            let parent = item.parentElement;
            while (parent && parent.classList.contains('submenu')) {
                parent.style.display = 'block';
                parent = parent.parentElement.closest('.menu-item');
                if (parent) parent.style.display = 'block';
            }
        });
    });
</script>

<style>
    .menu-open > .submenu { display: block !important; }
    .nav-item.menu-item .nav-link.active { background-color: #0d6efd; color: #fff; }
    .submenu { display: none; }
</style>
