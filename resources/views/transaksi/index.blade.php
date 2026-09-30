@extends('layouts.app')

@section('content')
@php($purchase = $type === 'pembelian')
<div class="app-content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0">{{ $purchase ? 'Nota Pembelian' : 'Nota Penjualan' }}</h3>
            <small class="text-muted">Daftar transaksi {{ $purchase ? 'penerimaan barang' : 'pengeluaran barang' }}.</small>
        </div>
        <a class="btn btn-sm btn-primary" href="{{ route($purchase ? 'purchases.create' : 'sales.create') }}">
            <i class="bi bi-file-earmark-plus"></i> Tambah Nota
        </a>
    </div>
</div>

@if(session('print_url'))
<div class="alert alert-success d-flex justify-content-between align-items-center gap-3">
    <span>Nota berhasil disimpan. Anda dapat langsung mencetaknya.</span>
    <a class="btn btn-sm btn-success" href="{{ session('print_url') }}" target="_blank"><i class="bi bi-printer"></i> Cetak Nota</a>
</div>
@endif

<div class="card card-info card-outline mb-4">
    <div class="card-header pt-1 pb-1">
        <div class="card-tools"><span class="text-muted small">{{ $rows->count() }} transaksi</span></div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="transactionTable" class="table table-sm table-striped align-middle w-100" style="font-size:small">
                <thead>
                    <tr>
                        <th class="text-center">No</th><th>No Invoice</th><th class="text-center">Tanggal</th>
                        <th>{{ $purchase ? 'Supplier' : 'Customer' }}</th><th>Lokasi</th><th class="text-end">Total</th>
                        <th class="text-center">{{ $purchase ? 'Status' : 'Payment' }}</th><th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $row->invoice_no }}</td>
                        <td class="text-center">{{ $row->transaction_date?->format('d/m/Y') }}</td>
                        <td>{{ $purchase ? ($row->supplier->name ?? '-') : ($row->customer->name ?? $row->customer_name ?? '-') }}</td>
                        <td>{{ $row->location->name ?? '-' }}</td>
                        <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($purchase)
                                <span class="badge {{ $row->payment_status === 'paid' ? 'text-bg-success' : 'text-bg-warning' }}">{{ $row->payment_status === 'paid' ? 'Lunas' : 'Belum Bayar' }}</span>
                            @else
                                <span class="badge text-bg-secondary">{{ ucfirst($row->payment_method) }}</span>
                            @endif
                        </td>
                        <td class="text-center text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route($purchase ? 'purchases.print' : 'sales.print', $row) }}" target="_blank" title="Cetak nota"><i class="bi bi-printer"></i></a> <a class="btn btn-sm btn-outline-secondary" href="{{ route($purchase ? 'purchases.logs' : 'sales.logs', $row) }}" title="Riwayat perubahan"><i class="bi bi-clock-history"></i></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.DataTable) new DataTable('#transactionTable', { order: [[2, 'desc'], [0, 'desc']], pageLength: 25 });
});
</script>
@endpush
