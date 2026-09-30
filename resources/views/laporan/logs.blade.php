@extends('layouts.app') @section('content')<h2>Log Perubahan</h2><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Waktu</th><th>Modul</th><th>ID</th><th>Aksi</th><th>User</th><th>Uraian</th></tr></thead><tbody>

@forelse($logs as $log)<tr><td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td><td>{{ class_basename($log->auditable_type) }}</td><td>{{ $log->auditable_id }}</td><td>{{ strtoupper($log->action) }}</td><td>{{ $log->user->name ?? 'System' }}<br><small>{{ $log->ip_address }}</small></td><td>{{ $log->description }}</td></tr>

@empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada log perubahan.</td></tr>

@endforelse</tbody></table></div><div class="p-3">{{ $logs->links() }}</div></div>@endsection


