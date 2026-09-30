@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">Menu Management</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-xl-3">
                <div class="card card-info card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">Roles</div>
                        <div class="card-tools">
                            <a href="{{ route('settings.roles') }}" class="btn btn-tool" title="Kelola role">
                                <i class="bi bi-file-earmark-plus"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <ul class="list-group" id="listrole">
                            @foreach($roles as $role)
                                <li class="list-group-item d-flex justify-content-between align-items-start" data-role="{{ $role->name }}" style="cursor: pointer">
                                    <span class="rolename">{{ $role->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-xl-9">
                <div class="card card-info card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">Menu</div>
                        <div class="card-tools"><span class="text-muted small" id="activeRole"></span></div>
                    </div>
                    <div class="card-body">
                        <div id="jstree_menu"></div>
                        <div id="menuEmpty" class="text-muted py-3 d-none">Belum ada role yang tersedia.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        let treeReady = false;

        function loadMenuTree(role) {
            treeReady = false;
            $('#activeRole').text(role ? 'Role: ' + role : '');

            if ($('#jstree_menu').jstree(true)) {
                $('#jstree_menu').jstree('destroy').empty();
            }

            if (!role) {
                $('#menuEmpty').removeClass('d-none');
                return;
            }

            $('#menuEmpty').addClass('d-none');
            $('#jstree_menu')
                .on('ready.jstree', function () {
                    treeReady = true;
                })
                .on('changed.jstree', function (event, data) {
                    if (!treeReady || !data.changed || (!data.changed.selected.length && !data.changed.deselected.length)) return;

                    $.ajax({
                        method: 'PUT',
                        url: '{{ route('settings.menus.access') }}',
                        data: {
                            selected_ids: data.changed.selected,
                            deselected_ids: data.changed.deselected,
                            gp: role,
                            _token: '{{ csrf_token() }}'
                        },
                        error: function () {
                            if (window.Swal) {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Hak akses menu gagal diperbarui.' });
                            }
                        }
                    });
                })
                .jstree({
                    core: {
                        data: {
                            url: '{{ url('settings/menus/data') }}/' + encodeURIComponent(role),
                            data: function (node) {
                                return { id: node.id };
                            }
                        }
                    },
                    checkbox: {
                        three_state: true,
                        cascade: 'up+down+undetermined'
                    },
                    plugins: ['checkbox', 'changed']
                });
        }

        $('#listrole li').on('click', function () {
            $('#listrole li').removeClass('active');
            $(this).addClass('active');
            loadMenuTree($(this).data('role'));
        });

        $('#listrole li:first').trigger('click');
    });
</script>
@endpush
