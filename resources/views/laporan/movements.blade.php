@extends('layouts.app') @section('content')<h2>Kartu Stok</h2><form class="row g-2 mb-3"><div class="col-md-4"><select name="item_id" class="form-select"><option value="">Semua barang</option>

@foreach($items as $item)<option value="{{ $item->id }}" @selected(request('item_id')==$item->id)>{{ $item->code }} - {{ $item->name }}</option>

@endforeach</select></div><div class="col-auto"><button class="btn btn-primary">Filter</button></div></form><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Waktu</th><th>Barang</th><th>Lokasi</th><th>Referensi</th><th>Arah</th><th class="text-end">Qty</th><th class="text-end">Saldo</th><th>User</th></tr></thead><tbody>

@forelse($movements as $row)<tr><td>{{ $row->created_at->format('d/m/Y H:i') }}</td><td>{{ $row->item->name }}</td><td>{{ $row->location->name }}</td><td>{{ $row->reference_no }}</td><td>{{ $row->direction==='in'?'Masuk':'Keluar' }}</td><td class="text-end">{{ number_format($row->quantity,3,',','.') }}</td><td class="text-end">{{ number_format($row->balance_after,3,',','.') }}</td><td>{{ $row->user->name ?? '-' }}</td></tr>

@empty<tr><td colspan="8" class="text-center text-muted py-4">Belum ada mutasi stok.</td></tr>

@endforelse</tbody></table></div><div class="p-3">{{ $movements->withQueryString()->links() }}</div></div>@endsection


