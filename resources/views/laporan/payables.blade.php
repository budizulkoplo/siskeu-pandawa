@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-1">Laporan Hutang</h2><div class="text-muted">Daftar kewajiban pembelian kepada pemasok yang belum lunas.</div></div>
    <a href="{{ route('purchases.index') }}" class="btn btn-primary"><i class="bi bi-cart-plus me-1"></i>Kelola Pembelian</a>
</div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Pembelian</th><th>Pemasok</th><th>Jatuh Tempo</th><th>Status</th><th class="text-end">Nilai</th><th class="text-end">Terbayar</th><th class="text-end">Sisa</th></tr></thead>
    <tbody>
    @forelse($rows as $row)
        @php($overdue = $row->due_date && $row->due_date->isPast())
        <tr><td class="fw-semibold">{{ $row->purchase->invoice_no ?? '-' }}</td><td>{{ $row->supplier->name ?? '-' }}</td><td>{{ $row->due_date?->format('d/m/Y') ?? '-' }}</td><td><span class="badge text-bg-{{ $overdue ? 'danger' : 'warning' }}">{{ $overdue ? 'Jatuh Tempo' : 'Belum Jatuh Tempo' }}</span></td><td class="text-end">Rp {{ number_format($row->amount, 0, ',', '.') }}</td><td class="text-end">Rp {{ number_format($row->paid_amount, 0, ',', '.') }}</td><td class="text-end fw-semibold">Rp {{ number_format($row->amount - $row->paid_amount, 0, ',', '.') }}</td></tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada hutang terbuka.</td></tr>
    @endforelse
    </tbody>
</table></div>@if($rows->hasPages())<div class="card-footer bg-white">{{ $rows->links() }}</div>@endif</div>
@endsection
