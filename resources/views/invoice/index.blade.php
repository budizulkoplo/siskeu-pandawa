@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><div><h2>Invoice / Faktur</h2><div class="text-muted">Kelola tagihan customer dan histori pembayaran.</div></div><a class="btn btn-primary" href="{{ route('invoices.create') }}"><i class="bi bi-plus-lg"></i> Invoice Baru</a></div>
<div class="row g-3 mb-4">
@foreach([['Belum Dibayar', $metrics['unpaid'], 'warning'], ['Overdue', $metrics['overdue'], 'danger'], ['Jatuh Tempo 7 Hari', $metrics['due_soon'], 'info'], ['Lunas Bulan Ini', $metrics['paid_month'], 'success']] as $metric)<div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">{{ $metric[0] }}</div><div class="fs-5 fw-bold text-{{ $metric[2] }}">Rp {{ number_format($metric[1], 0, ',', '.') }}</div></div></div></div>
@endforeach</div>
<form class="mb-3"><div class="input-group" style="max-width:300px"><select name="status" class="form-select"><option value="">Semua Status</option>
@foreach(['draft'=>'Draft','sent'=>'Terkirim','unpaid'=>'Belum Dibayar','partially_paid'=>'Sebagian Dibayar','paid'=>'Lunas','cancelled'=>'Cancelled'] as $key => $name)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $name }}</option>
@endforeach</select><button class="btn btn-outline-primary">Filter</button></div></form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Nomor Invoice</th><th>Customer</th><th>Tanggal</th><th>Jatuh Tempo</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($invoices as $invoice)<tr><td class="fw-semibold">{{ $invoice->invoice_no }}</td><td>{{ $invoice->customer->name }}</td><td>{{ $invoice->invoice_date->format('d/m/Y') }}</td><td>{{ optional($invoice->due_date)->format('d/m/Y') ?: '-' }}</td><td class="text-end">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td><td><span class="badge text-bg-{{ $invoice->display_status === 'Lunas' ? 'success' : ($invoice->display_status === 'Overdue' ? 'danger' : 'warning') }}">{{ $invoice->display_status }}</span></td><td class="text-nowrap"><a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">Detail</a> <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak nota"><i class="bi bi-printer"></i></a></td></tr>
@empty<tr><td colspan="7" class="text-center text-muted py-5">Belum ada invoice.</td></tr>
@endforelse</tbody></table></div><div class="p-3">{{ $invoices->withQueryString()->links() }}</div></div>
@endsection

