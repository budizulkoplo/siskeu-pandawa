@extends('layouts.app') @section('content')<h2>Laporan Stok</h2><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Barang</th><th>Kategori</th><th>Lokasi</th><th class="text-end">Stok</th><th>Satuan</th></tr></thead><tbody>

@forelse($items as $item)

@forelse($item->stocks as $stock)<tr><td>{{ $item->code }}</td><td>{{ $item->name }}</td><td>{{ $item->category->name ?? '-' }}</td><td>{{ $stock->location->name }}</td><td class="text-end">{{ number_format($stock->quantity,3,',','.') }}</td><td>{{ $item->unit }}</td></tr>

@empty<tr><td>{{ $item->code }}</td><td>{{ $item->name }}</td><td>{{ $item->category->name ?? '-' }}</td><td colspan="3" class="text-muted">Belum ada stok</td></tr>

@endforelse

@empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada barang.</td></tr>

@endforelse</tbody></table></div></div>@endsection


