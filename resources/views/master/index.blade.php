@extends('layouts.app')

@section('content')
@php
    $labels = [
        'users' => 'User',
        'coa' => 'COA',
        'kategori' => 'Kategori',
        'lokasi' => 'Lokasi',
        'barang' => 'Barang',
        'supplier' => 'Supplier',
        'customer' => 'Customer',
        'erp' => 'ERP Master',
    ];
    $label = $labels[$type];
@endphp

<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">Master {{ $label }}</h3>
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
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" id="btnAddMaster">
                        <i class="bi bi-plus-circle"></i> Tambah {{ $label }}
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="masterTable" class="table table-sm table-hover w-100" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                @if($type === 'users')
                                    <th>Username</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th>
                                @elseif($type === 'coa')
                                    <th>Kode</th><th>Nama COA</th><th>Tipe</th><th>Induk</th><th>Status</th>
                                @elseif($type === 'kategori')
                                    <th>Kode</th><th>Nama Kategori</th><th>Deskripsi</th><th>Status</th>
                                @elseif($type === 'lokasi')
                                    <th>Kode</th><th>Nama Lokasi</th><th>Alamat</th><th>Status</th>
                                @elseif($type === 'barang')
                                    <th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Satuan</th><th>Harga Beli</th><th>Harga Jual</th><th>Status</th>
                                @elseif(in_array($type, ['supplier', 'customer']))
                                    <th>Kode</th><th>Nama {{ $label }}</th><th>Telepon</th><th>Email</th><th>Status</th>
                                @elseif($type === 'erp')
                                    <th>Kode</th><th>Nama Transaksi</th><th>Arah</th><th>COA</th><th>Status</th>
                                @endif
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['rows'] as $index => $row)
                                @php
                                    $rowJson = json_encode($row->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    @if($type === 'users')
                                        <td>{{ $row->username }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->email ?: '-' }}</td>
                                        <td>{{ $row->roles->pluck('name')->join(', ') ?: '-' }}</td>
                                        <td><span class="badge {{ $row->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($row->status ?: 'aktif') }}</span></td>
                                    @elseif($type === 'coa')
                                        <td>{{ $row->code }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ ucfirst($row->type) }}</td>
                                        <td>{{ $row->parent->code ?? '-' }}</td>
                                        <td><span class="badge bg-success">Aktif</span></td>
                                    @elseif($type === 'kategori')
                                        <td>{{ $row->code }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->description ?: '-' }}</td>
                                        <td><span class="badge bg-success">Aktif</span></td>
                                    @elseif($type === 'lokasi')
                                        <td>{{ $row->code }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->address ?: '-' }}</td>
                                        <td><span class="badge {{ $row->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $row->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    @elseif($type === 'barang')
                                        <td>{{ $row->code }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->category->name ?? '-' }}</td>
                                        <td>{{ $row->unit }}</td>
                                        <td class="text-end">Rp {{ number_format((float) $row->purchase_price, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format((float) $row->sale_price, 0, ',', '.') }}</td>
                                        <td><span class="badge {{ $row->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $row->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    @elseif(in_array($type, ['supplier', 'customer']))
                                        <td>{{ $row->code }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->phone ?: '-' }}</td>
                                        <td>{{ $row->email ?: '-' }}</td>
                                        <td><span class="badge {{ $row->status !== 'nonaktif' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($row->status ?: 'aktif') }}</span></td>
                                    @elseif($type === 'erp')
                                        <td>{{ $row->code }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->direction === 'in' ? 'Masuk' : 'Keluar' }}</td>
                                        <td>{{ $row->coa->code ?? '-' }}</td>
                                        <td><span class="badge {{ $row->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $row->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    @endif
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-warning editMaster" data-id="{{ $row->id }}" data-row="{{ $rowJson }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('master.'.$type.'.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Hapus data {{ $label }} ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
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

<div class="modal fade" id="masterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('master.'.$type.'.store') }}" id="masterForm">
                @csrf
                <input type="hidden" name="id" id="masterId">

                <div class="modal-header">
                    <h5 class="modal-title"><span id="modalTitle">Tambah</span> {{ $label }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-2">
                        @if($type === 'users')
                            <div class="col-md-6"><label class="form-label">Username <span class="text-danger">*</span></label><input name="username" class="form-control form-control-sm" required maxlength="80"></div>
                            <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input name="name" class="form-control form-control-sm" required maxlength="150"></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control form-control-sm"></div>
                            <div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select form-select-sm"><option value="">-- Pilih Role --</option>@foreach($data['roles'] as $role)<option value="{{ $role->name }}">{{ $role->name }}</option>@endforeach</select></div>
                            <div class="col-md-6"><label class="form-label">Password <span class="text-danger password-required">*</span></label><input name="password" type="password" class="form-control form-control-sm" minlength="6" id="masterPassword"><small class="text-muted">Kosongkan saat edit jika tidak diubah.</small></div>
                        @elseif($type === 'coa')
                            <div class="col-md-6"><label class="form-label">Kode <span class="text-danger">*</span></label><input name="code" class="form-control form-control-sm" required maxlength="50"></div>
                            <div class="col-md-6"><label class="form-label">Nama COA <span class="text-danger">*</span></label><input name="name" class="form-control form-control-sm" required maxlength="150"></div>
                            <div class="col-md-6"><label class="form-label">Tipe <span class="text-danger">*</span></label><select name="type" class="form-select form-select-sm" required><option value="asset">Asset</option><option value="liability">Liability</option><option value="equity">Equity</option><option value="income">Income</option><option value="expense">Expense</option></select></div>
                            <div class="col-md-6"><label class="form-label">COA Induk</label><select name="parent_id" class="form-select form-select-sm"><option value="">-- Tanpa Induk --</option>@foreach($data['rows'] as $parent)<option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name }}</option>@endforeach</select></div>
                        @elseif($type === 'kategori')
                            <div class="col-md-6"><label class="form-label">Kode <span class="text-danger">*</span></label><input name="code" class="form-control form-control-sm" required maxlength="50"></div>
                            <div class="col-md-6"><label class="form-label">Nama Kategori <span class="text-danger">*</span></label><input name="name" class="form-control form-control-sm" required maxlength="150"></div>
                            <div class="col-12"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control form-control-sm" rows="2"></textarea></div>
                        @elseif($type === 'lokasi')
                            <div class="col-md-6"><label class="form-label">Kode <span class="text-danger">*</span></label><input name="code" class="form-control form-control-sm" required maxlength="50"></div>
                            <div class="col-md-6"><label class="form-label">Nama Lokasi <span class="text-danger">*</span></label><input name="name" class="form-control form-control-sm" required maxlength="150"></div>
                            <div class="col-12"><label class="form-label">Alamat</label><textarea name="address" class="form-control form-control-sm" rows="2"></textarea></div>
                            <div class="col-md-6"><label class="form-label">Status</label><select name="is_active" class="form-select form-select-sm"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                        @elseif($type === 'barang')
                            <div class="col-md-6"><label class="form-label">Kode Barang <span class="text-danger">*</span></label><input name="code" class="form-control form-control-sm" required maxlength="50"></div>
                            <div class="col-md-6"><label class="form-label">Nama Barang <span class="text-danger">*</span></label><input name="name" class="form-control form-control-sm" required maxlength="150"></div>
                            <div class="col-md-6"><label class="form-label">Kategori</label><select name="category_id" class="form-select form-select-sm"><option value="">-- Pilih Kategori --</option>@foreach($data['categories'] as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                            <div class="col-md-6"><label class="form-label">Supplier Utama</label><select name="supplier_id" class="form-select form-select-sm"><option value="">-- Pilih Supplier --</option>@foreach($data['suppliers'] as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select></div>
                            <div class="col-md-4"><label class="form-label">Satuan <span class="text-danger">*</span></label><input name="unit" class="form-control form-control-sm" value="pcs" required maxlength="30"></div>
                            <div class="col-md-4"><label class="form-label">Stok Minimum</label><input name="minimum_stock" type="number" step=".001" min="0" class="form-control form-control-sm" value="0"></div>
                            <div class="col-md-4"><label class="form-label">Status</label><select name="is_active" class="form-select form-select-sm"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                            <div class="col-md-6"><label class="form-label">Harga Beli</label><input name="purchase_price" type="number" min="0" class="form-control form-control-sm" value="0"></div>
                            <div class="col-md-6"><label class="form-label">Harga Jual</label><input name="sale_price" type="number" min="0" class="form-control form-control-sm" value="0"></div>
                        @elseif(in_array($type, ['supplier', 'customer']))
                            <div class="col-md-6"><label class="form-label">Kode <span class="text-danger">*</span></label><input name="code" class="form-control form-control-sm" required maxlength="50"></div>
                            <div class="col-md-6"><label class="form-label">Nama {{ $label }} <span class="text-danger">*</span></label><input name="name" class="form-control form-control-sm" required maxlength="150"></div>
                            <div class="col-12"><label class="form-label">Alamat</label><textarea name="address" class="form-control form-control-sm" rows="2"></textarea></div>
                            <div class="col-md-4"><label class="form-label">Telepon</label><input name="phone" class="form-control form-control-sm"></div>
                            <div class="col-md-4"><label class="form-label">Email</label><input name="email" type="email" class="form-control form-control-sm"></div>
                            <div class="col-md-4"><label class="form-label">PIC</label><input name="pic" class="form-control form-control-sm"></div>
                            <div class="col-md-6"><label class="form-label">NPWP</label><input name="npwp" class="form-control form-control-sm"></div>
                            <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select form-select-sm"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select></div>
                            <div class="col-12"><label class="form-label">Catatan</label><textarea name="notes" class="form-control form-control-sm" rows="2"></textarea></div>
                        @elseif($type === 'erp')
                            <div class="col-md-6"><label class="form-label">Kode <span class="text-danger">*</span></label><input name="code" class="form-control form-control-sm" required maxlength="50"></div>
                            <div class="col-md-6"><label class="form-label">Nama Transaksi <span class="text-danger">*</span></label><input name="name" class="form-control form-control-sm" required maxlength="150"></div>
                            <div class="col-md-6"><label class="form-label">Arah <span class="text-danger">*</span></label><select name="direction" class="form-select form-select-sm" required><option value="in">Masuk</option><option value="out">Keluar</option></select></div>
                            <div class="col-md-6"><label class="form-label">COA</label><select name="coa_id" class="form-select form-select-sm"><option value="">-- Pilih COA --</option>@foreach($data['coas'] as $coa)<option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>@endforeach</select></div>
                            <div class="col-md-6"><label class="form-label">Status</label><select name="is_active" class="form-select form-select-sm"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-save"></i> <span id="submitText">Simpan</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        const modalElement = document.getElementById('masterModal');
        const masterModal = bootstrap.Modal.getOrCreateInstance(modalElement);
        const form = document.getElementById('masterForm');
        const table = $('#masterTable').DataTable({
            responsive: true,
            autoWidth: false,
            order: [[1, 'asc']],
            language: {
                emptyTable: 'Belum ada data {{ strtolower($label) }}',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
            },
            columnDefs: [{ targets: -1, orderable: false, searchable: false, className: 'text-center' }]
        });

        function resetForm() {
            form.reset();
            document.getElementById('masterId').value = '';
            document.getElementById('modalTitle').textContent = 'Tambah';
            document.getElementById('submitText').textContent = 'Simpan';
            const password = document.getElementById('masterPassword');
            if (password) {
                password.required = true;
                document.querySelector('.password-required').classList.remove('d-none');
            }
        }

        document.getElementById('btnAddMaster').addEventListener('click', function () {
            resetForm();
            masterModal.show();
        });

        document.querySelectorAll('.editMaster').forEach(function (button) {
            button.addEventListener('click', function () {
                const row = JSON.parse(this.dataset.row);
                resetForm();
                document.getElementById('masterId').value = row.id;

                Object.keys(row).forEach(function (key) {
                    const field = form.querySelector('[name="' + key + '"]');
                    if (!field || key === 'roles') return;
                    field.value = row[key] === true ? '1' : (row[key] === false ? '0' : (row[key] ?? ''));
                });

                @if($type === 'users')
                    const role = row.roles && row.roles.length ? row.roles[0].name : '';
                    form.querySelector('[name="role"]').value = role;
                    document.getElementById('masterPassword').required = false;
                    document.querySelector('.password-required').classList.add('d-none');
                @endif

                document.getElementById('modalTitle').textContent = 'Edit';
                document.getElementById('submitText').textContent = 'Update';
                masterModal.show();
            });
        });

        modalElement.addEventListener('hidden.bs.modal', resetForm);
    });
</script>
@endpush
