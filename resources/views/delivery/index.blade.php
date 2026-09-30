@extends('layouts.app')
@section('content')<div class="d-flex justify-content-between mb-3"><div><h2>Surat Jalan</h2><div class="text-muted">Dokumen pengiriman barang berdasarkan invoice.</div></div></div><div class="card border-0 shadow-sm"><table class="table mb-0"><thead><tr><th>Nomor</th><th>Invoice</th><th>Customer</th><th>Tanggal</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($orders as $order)<tr><td>{{ $order->delivery_no }}</td><td>{{ $order->invoice->invoice_no ?? '-' }}</td><td>{{ $order->customer->name }}</td><td>{{ $order->delivery_date->format('d/m/Y') }}</td><td>{{ ucfirst($order->status) }}</td><td><a href="{{ route('delivery.show',$order) }}" class="btn btn-sm btn-outline-primary">Detail</a></td></tr>
@empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada surat jalan.</td></tr>
@endforelse</tbody></table><div class="p-3">{{ $orders->links() }}</div></div>@endsection

