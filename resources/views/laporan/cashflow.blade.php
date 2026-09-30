@extends('layouts.app') @section('content')<h2>Arus Kas</h2><form class="row g-2 mb-3"><div class="col-md-3"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div><div class="col-md-3"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div><div class="col-auto"><button class="btn btn-primary">Filter</button></div></form><div class="card border-0 shadow-sm"><table class="table mb-0"><thead><tr><th>No</th><th>Tanggal</th><th>Arah</th><th>Keterangan</th><th>COA</th><th class="text-end">Nominal</th></tr></thead><tbody>

@forelse($rows as $row)<tr><td>{{ $row->transaction_no }}</td><td>{{ $row->transaction_date->format('d/m/Y') }}</td><td><span class="badge text-bg-{{ $row->direction==='in'?'success':'danger' }}">{{ $row->direction==='in'?'Penerimaan':'Pengeluaran' }}</span></td><td>{{ $row->description }}</td><td>{{ $row->coa->code ?? '-' }}</td><td class="text-end">Rp {{ number_format($row->amount,0,',','.') }}</td></tr>

@empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada transaksi kas.</td></tr>

@endforelse</tbody></table></div>@endsection


