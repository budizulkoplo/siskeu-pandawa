@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-1">Laporan Piutang</h2><div class="text-muted">Daftar tagihan pelanggan yang belum lunas.</div></div>
    <a href="{{ route('invoices.index') }}" class="btn btn-primary"><i class="bi bi-receipt me-1"></i>Kelola Invoice</a>
</div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Invoice</th><th>Pelanggan</th><th>Jatuh Tempo</th><th>Status</th><th class="text-end">Nilai</th><th class="text-end">Terbayar</th><th class="text-end">Sisa</th></tr></thead>
    <tbody>
    @forelse($rows as $row)
        @php($overdue = $row->due_date && $row->due_date->isPast())
        <tr><td><a href="{{ route('invoices.show', $row->invoice) }}" class="fw-semibold text-decoration-none">{{ $row->invoice->invoice_no ?? '-' }}</a></td><td>{{ $row->customer->name ?? '-' }}</td><td>{{ $row->due_date?->format('d/m/Y') ?? '-' }}</td><td><span class="badge text-bg-{{ $overdue ? 'danger' : 'warning' }}">{{ $overdue ? 'Jatuh Tempo' : 'Belum Jatuh Tempo' }}</span></td><td class="text-end">Rp {{ number_format($row->amount, 0, ',', '.') }}</td><td class="text-end">Rp {{ number_format($row->paid_amount, 0, ',', '.') }}</td><td class="text-end fw-semibold">Rp {{ number_format($row->amount - $row->paid_amount, 0, ',', '.') }}</td></tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada piutang terbuka.</td></tr>
    @endforelse
    </tbody>
</table></div>@if($rows->hasPages())<div class="card-footer bg-white">{{ $rows->links() }}</div>@endif</div>
@endsection
