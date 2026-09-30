@extends('layouts.app') @section('content')<h2>Role</h2><div class="row g-4"><div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><form method="POST" action="{{ route('settings.roles.store') }}">@csrf<label>Nama Role</label><div class="input-group mt-2"><input name="name" class="form-control" required><button class="btn btn-primary">Tambah</button></div></form></div></div></div><div class="col-md-8"><div class="card border-0 shadow-sm"><table class="table mb-0"><thead><tr><th>Role</th><th></th></tr></thead><tbody>

@foreach($roles as $role)<tr><td>{{ $role->name }}</td><td class="text-end"><form method="POST" action="{{ route('settings.roles.delete',$role) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button></form></td></tr>

@endforeach</tbody></table></div></div></div>@endsection


