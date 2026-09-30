@extends('layouts.app')

@section('content')
@php($incoming = $direction === 'in')
<div class="app-content-header"><div class="container-fluid"><h3 class="mb-0">Nota {{ $incoming ? 'Penerimaan' : 'Pengeluaran' }} - Pandawa</h3></div></div>
@if(session('print_url'))<div class="alert alert-success d-flex justify-content-between align-items-center gap-3"><span>Transaksi kas berhasil disimpan. Anda dapat langsung mencetaknya.</span><a class="btn btn-sm btn-success" href="{{ session('print_url') }}" target="_blank"><i class="bi bi-printer"></i> Cetak Nota</a></div>@endif
<div class="app-content"><div class="container-fluid"><div class="card card-info card-outline mb-4">
    <div class="card-header pt-1 pb-1"><div class="card-tools"><button class="btn btn-sm btn-primary" id="btnTambahNota"><i class="bi bi-file-earmark-plus"></i> Tambah Nota</button></div></div>
    <div class="card-body"><table id="tbNotas" class="table table-sm table-striped w-100" style="font-size:small"><thead><tr><th class="text-center">No</th><th>Nota No</th><th>Nama Trans.</th><th class="text-center">Tanggal</th><th class="text-end">Total</th><th class="text-center">Payment</th><th class="text-center">Status</th><th class="text-center">User</th><th class="text-center">Aksi</th></tr></thead><tbody>
        @forelse($rows as $index => $row)
            <tr><td class="text-center">{{ $index + 1 }}</td><td>{{ $row->transaction_no }}</td><td>{{ $row->description }}</td><td class="text-center">{{ $row->transaction_date->format('d/m/Y') }}</td><td class="text-end">Rp {{ number_format($row->amount, 2, ',', '.') }}</td><td class="text-center"><span class="badge text-bg-success">Cash</span></td><td class="text-center"><span class="badge text-bg-success">Paid</span></td><td class="text-center">{{ $row->user->name ?? auth()->user()->name }}</td><td class="text-center text-nowrap"><button class="btn btn-sm btn-info text-white btn-view" data-id="{{ $row->id }}"><i class="bi bi-eye"></i></button> <a class="btn btn-sm btn-outline-primary" href="{{ route($incoming ? 'cash.in.print' : 'cash.out.print', $row) }}" target="_blank" title="Cetak nota"><i class="bi bi-printer"></i></a></td></tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted">Belum ada transaksi.</td></tr>
        @endforelse
    </tbody></table></div>
</div></div></div>

<div class="modal fade" id="modalNota" tabindex="-1"><div class="modal-dialog modal-xxl"><div class="modal-content"><div class="row g-0">
    <div class="col-md-9"><form method="POST" action="{{ route($incoming ? 'cash.in.store' : 'cash.out.store') }}" id="frmNota">@csrf
        <div class="modal-header"><h6 class="modal-title">Form Nota {{ $incoming ? 'Masuk' : 'Keluar' }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-2">
            <div class="col-md-4"><label class="form-label">No Invoice *</label><input type="text" class="form-control form-control-sm" value="{{ strtoupper($incoming ? 'RCV-' : 'PAY-') . now()->format('YmdHis') }}" readonly></div>
            <div class="col-md-4"><label class="form-label">Nama Transaksi *</label><input type="text" class="form-control form-control-sm" name="description" required></div>
            <div class="col-md-4"><label class="form-label">Perusahaan</label><input type="text" class="form-control form-control-sm" value="PT Pandawa" disabled></div>
            <div class="col-md-4"><label class="form-label">Payment Method *</label><select class="form-select form-select-sm"><option>Cash</option><option>Transfer</option></select></div>
            <div class="col-md-4"><label class="form-label">Tanggal *</label><input type="date" class="form-control form-control-sm" name="transaction_date" value="{{ now()->format('Y-m-d') }}" required></div>
            <div class="col-md-4"><label class="form-label">Kode ERP</label><select class="form-select form-select-sm" name="erp_code_id"><option value="">-- Pilih Kode ERP --</option>@foreach($codes as $code)<option value="{{ $code->id }}">{{ $code->code }} - {{ $code->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">COA *</label><select class="form-select form-select-sm" name="coa_id" required><option value="">-- Pilih COA --</option>@foreach($coas as $coa)<option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Bukti Nota</label><input type="file" class="form-control form-control-sm" disabled><small class="text-muted">Upload bukti akan diaktifkan pada modul dokumen transaksi.</small></div>
        </div><hr><div class="row mb-3"><div class="col-md-6"><h6>Detail Transaksi</h6></div><div class="col-md-6 text-end"><span class="text-primary"><i class="bi bi-wallet2"></i> Saldo tersedia: <span>Rp 0</span></span></div></div>
        <table class="table table-sm table-bordered"><thead><tr><th>Kode Transaksi</th><th>Deskripsi</th><th width="80">Qty</th><th width="140">Nominal</th><th width="140">Total</th><th width="40"><button type="button" class="btn btn-sm btn-success" id="addRow">+</button></th></tr></thead><tbody id="detailRows"><tr><td><select class="form-select form-select-sm" name="erp_code_id"><option value="">-- Pilih Kode --</option>@foreach($codes as $code)<option value="{{ $code->id }}">{{ $code->code }} - {{ $code->name }}</option>@endforeach</select></td><td><input class="form-control form-control-sm detail-description" value="" required></td><td><input type="number" class="form-control form-control-sm qty" value="1" min="1"></td><td><input type="number" class="form-control form-control-sm nominal" name="amount" value="0" min=".01" step=".01" required></td><td><input class="form-control form-control-sm total" value="0" readonly></td><td><button type="button" class="btn btn-sm btn-danger removeRow">x</button></td></tr></tbody><tfoot><tr><td colspan="3" class="text-end"><strong>Subtotal:</strong></td><td colspan="2"><input class="form-control form-control-sm text-end" id="subtotal" value="Rp 0" readonly></td><td></td></tr><tr><td colspan="3" class="text-end"><strong>Grand Total:</strong></td><td colspan="2"><input class="form-control form-control-sm text-end fw-bold" id="grandTotal" value="Rp 0" readonly></td><td></td></tr></tfoot></table></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form></div>
    <div class="col-md-3 border-start"><div class="modal-header border-bottom"><h6 class="modal-title m-0"><i class="bi bi-clock-history"></i> Riwayat Update</h6></div><div class="modal-body p-2" style="height:calc(100vh - 150px);overflow-y:auto"><p class="text-muted small">Log perubahan akan tampil setelah transaksi tersimpan.</p></div></div>
</div></div></div>
@endsection

@section('scripts')
<style>.modal-xxl{max-width:95vw}.card-info.card-outline{border-top:3px solid #0dcaf0}.btn-info{background-color:#0dcaf0;border-color:#0dcaf0}.table-striped>tbody>tr:nth-of-type(odd)>*{--bs-table-accent-bg:rgba(0,0,0,.04)}</style>
<script>
$(function(){
    $('#tbNotas').DataTable({pageLength:50,lengthMenu:[[10,25,50,100], [10,25,50,100]],responsive:true,language:{search:'Search:',lengthMenu:'_MENU_ entries per page',emptyTable:'Belum ada transaksi.',info:'Showing _START_ to _END_ of _TOTAL_ entries'}});
    $('#btnTambahNota').on('click',function(){$('#frmNota')[0].reset();$('#modalNota').modal('show');});
    function hitung(){let total=0;$('#detailRows tr').each(function(){let qty=parseFloat($(this).find('.qty').val())||0;let nominal=parseFloat($(this).find('.nominal').val())||0;let line=qty*nominal;$(this).find('.total').val(line.toLocaleString('id-ID'));total+=line;});$('#subtotal,#grandTotal').val('Rp '+total.toLocaleString('id-ID'));}
    $(document).on('input','.qty,.nominal',hitung); $(document).on('click','.removeRow',function(){if($('#detailRows tr').length>1){$(this).closest('tr').remove();hitung();}}); $('#addRow').on('click',function(){let row=$('#detailRows tr:first').clone();row.find('input').val('');row.find('.qty').val(1);row.find('.total').val(0);$('#detailRows').append(row);});
});
</script>
@endsection
