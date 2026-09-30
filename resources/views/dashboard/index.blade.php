@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><div><h2 class="mb-1">Dashboard</h2><div class="text-muted">Ringkasan operasional keuangan dan persediaan hari ini.</div></div><span class="badge text-bg-light">{{ now()->translatedFormat('d F Y') }}</span></div>
<div class="row g-3 mb-4">
@foreach([['items','Master Barang',$items,'bi-boxes','primary'],['stock','Total Stok',$stock,'bi-stack','success'],['purchases','Pembelian Hari Ini',$purchases,'bi-cart-plus','warning'],['sales','Penjualan Hari Ini',$sales,'bi-cart-check','info'],['cashIn','Penerimaan Hari Ini',$cashIn,'bi-arrow-down-circle','success'],['cashOut','Pengeluaran Hari Ini',$cashOut,'bi-arrow-up-circle','danger'],['receivables','Piutang Terbuka',$receivables,'bi-person-lines-fill','primary'],['payables','Hutang Terbuka',$payables,'bi-receipt-cutoff','danger']] as $card)
<div class="col-md-4 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-{{ $card[4] }}"><i class="bi {{ $card[3] }} fs-3"></i></div><div class="text-muted small mt-2">{{ $card[1] }}</div><div class="fs-5 fw-bold">{{ in_array($card[0], ['items','stock']) ? number_format($card[2], 0, ',', '.') : 'Rp '.number_format($card[2], 0, ',', '.') }}</div></div></div></div>
@endforeach
</div>
<div class="row g-3 mb-4"><div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body d-flex justify-content-between"><span><i class="bi bi-exclamation-circle text-danger me-2"></i>Piutang jatuh tempo</span><strong class="text-danger">Rp {{ number_format($overdueReceivables, 0, ',', '.') }}</strong></div></div></div><div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body d-flex justify-content-between"><span><i class="bi bi-exclamation-circle text-danger me-2"></i>Hutang jatuh tempo</span><strong class="text-danger">Rp {{ number_format($overduePayables, 0, ',', '.') }}</strong></div></div></div></div>
<div class="card border-0 shadow-sm"><div class="card-header bg-white"><h5 class="mb-0">Aktivitas Stok Terakhir</h5></div><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Waktu</th><th>Barang</th><th>Lokasi</th><th>Jenis</th><th>Arah</th><th class="text-end">Qty</th><th>Oleh</th></tr></thead><tbody>
@forelse($movements as $row)
<tr><td>{{ $row->created_at->format('d/m/Y H:i') }}</td><td>{{ $row->item->name }}</td><td>{{ $row->location->name }}</td><td>{{ ucfirst($row->movement_type) }}</td><td><span class="badge text-bg-{{ $row->direction === 'in' ? 'success' : 'danger' }}">{{ $row->direction === 'in' ? 'Masuk' : 'Keluar' }}</span></td><td class="text-end">{{ number_format($row->quantity, 3, ',', '.') }}</td><td>{{ $row->user->name ?? '-' }}</td></tr>
@empty
<tr><td colspan="7" class="text-center text-muted py-4">Belum ada aktivitas stok.</td></tr>
@endforelse
</tbody></table></div></div>
@endsection
