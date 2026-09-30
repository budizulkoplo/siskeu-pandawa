@extends('layouts.app') @section('content')<div class="d-flex justify-content-between mb-3"><h2>{{ $title }}</h2><a href="{{ url()->previous() }}" class="btn btn-light">Kembali</a></div><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Uraian</th><th>Perubahan</th></tr></thead><tbody>

@forelse($logs as $log)<tr><td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td><td>{{ $log->user->name ?? 'System' }}<br><small class="text-muted">{{ $log->ip_address }}</small></td><td><span class="badge text-bg-primary">{{ strtoupper($log->action) }}</span></td><td>{{ $log->description }}</td><td><details><summary>Lihat detail</summary><pre class="small mb-0">{{ json_encode(['lama'=>$log->old_values,'baru'=>$log->new_values], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></details></td></tr>

@empty<tr><td colspan="5" class="text-center text-muted py-4">Belum ada log.</td></tr>

@endforelse</tbody></table></div></div>@endsection


