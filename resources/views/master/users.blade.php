@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">User Management</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card card-info card-outline mb-4">
            <div class="card-header pt-1 pb-1">
                <div class="card-title">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">HasRole</span>
                        <select class="form-select form-select-sm" id="filterRole">
                            <option value="all">ALL</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" id="btnAddUser" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="bi bi-file-earmark-plus"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="tbusers" class="table table-sm table-striped table-hover w-100" style="font-size: small;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Username</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Roles / Hak Akses</th>
                                <th width="12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $index => $user)
                                @php
                                    $userRoles = $user->roles->pluck('name')->values()->all();
                                    $userJson = json_encode($user->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                                @endphp
                                <tr data-roles="{{ implode('|', $userRoles) }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email ?: '-' }}</td>
                                    <td><span class="badge {{ $user->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($user->status ?: 'aktif') }}</span></td>
                                    <td>
                                        @foreach($roles as $role)
                                            <div class="form-check form-check-inline me-2">
                                                <input class="form-check-input user-role" type="checkbox" value="{{ $role->name }}" data-id="{{ $user->id }}" @checked(in_array($role->name, $userRoles, true))>
                                                <label class="form-check-label">{{ $role->name }}</label>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-info text-white editUser" data-row="{{ $userJson }}" data-bs-toggle="modal" data-bs-target="#userModal" title="Edit user">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning resetPassword" data-id="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#passwordModal" title="Reset password">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        <form method="POST" action="{{ route('master.users.destroy', $user->id) }}" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus user"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('master.users.store') }}" id="userForm">
                @csrf
                <input type="hidden" name="id" id="userId">
                <div class="modal-header">
                    <h5 class="modal-title"><span id="userModalTitle">Tambah</span> User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Username <span class="text-danger">*</span></label><input name="username" id="userUsername" class="form-control form-control-sm" required maxlength="80"></div>
                        <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input name="name" class="form-control form-control-sm" required maxlength="150"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select form-select-sm"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select></div>
                        <div class="col-md-6"><label class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label><input name="password" type="password" id="userPassword" class="form-control form-control-sm" minlength="6"><small class="text-muted">Kosongkan saat edit jika tidak diubah.</small></div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('master.users.password') }}">
                @csrf
                <input type="hidden" name="userid" id="passwordUserId">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                    <input type="password" name="new_password" class="form-control form-control-sm" minlength="8" required>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        const table = $('#tbusers').DataTable({
            ordering: false,
            responsive: true,
            autoWidth: false,
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
            },
            columnDefs: [{ targets: -1, orderable: false, searchable: false }]
        });

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'tbusers') return true;
            const selectedRole = $('#filterRole').val();
            if (selectedRole === 'all') return true;
            const roles = $(settings.aoData[dataIndex].nTr).data('roles') || '';
            return roles.split('|').includes(selectedRole);
        });

        $('#filterRole').on('change', function () {
            table.draw();
        });

        $('.user-role').on('change', function () {
            const checkbox = this;
            const cell = $(checkbox).closest('td');
            const selectedRoles = cell.find('.user-role:checked').map(function () { return this.value; }).get();

            $.ajax({
                url: '{{ route('master.users.roles') }}',
                method: 'POST',
                data: {
                    iduser: $(checkbox).data('id'),
                    name: selectedRoles,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (!response.success) return;
                    const row = $(checkbox).closest('tr');
                    row.attr('data-roles', selectedRoles.join('|')).data('roles', selectedRoles.join('|'));
                    table.draw(false);
                },
                error: function () {
                    checkbox.checked = !checkbox.checked;
                    if (window.Swal) Swal.fire({ icon: 'error', title: 'Gagal', text: 'Hak akses user gagal diperbarui.' });
                }
            });
        });

        function resetUserForm() {
            document.getElementById('userForm').reset();
            $('#userId').val('');
            $('#userModalTitle').text('Tambah');
            $('#userPassword').prop('required', true);
            $('#passwordRequired').removeClass('d-none');
        }

        $('#btnAddUser').on('click', resetUserForm);
        $('.editUser').on('click', function () {
            const row = JSON.parse(this.dataset.row);
            resetUserForm();
            $('#userId').val(row.id);
            $('#userUsername').val(row.username);
            $('[name="name"]', '#userForm').val(row.name);
            $('[name="email"]', '#userForm').val(row.email || '');
            $('[name="status"]', '#userForm').val(row.status || 'aktif');
            $('#userModalTitle').text('Edit');
            $('#userPassword').prop('required', false);
            $('#passwordRequired').addClass('d-none');
        });

        $('.resetPassword').on('click', function () {
            $('#passwordUserId').val($(this).data('id'));
        });

        $('#userModal').on('hidden.bs.modal', resetUserForm);
        $('#passwordModal').on('hidden.bs.modal', function () { this.querySelector('form').reset(); });
    });
</script>
@endpush
