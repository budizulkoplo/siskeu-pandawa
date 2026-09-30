@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <h3 class="mb-0">Setting Perusahaan</h3>
</div>

<div class="card card-info card-outline">
    <div class="card-body">
        <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nama Perusahaan</label>
                <input type="text" name="nama_perusahaan" class="form-control"
                       value="{{ old('nama_perusahaan', $setting->nama_perusahaan ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $setting->alamat ?? '') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Telepon</label>
                <input type="text" name="telepon" class="form-control"
                       value="{{ old('telepon', $setting->telepon ?? '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="form-control" accept=".png,.jpg,.jpeg">
                @if(!empty($setting?->path_logo))
                    <img src="{{ asset($setting->path_logo) }}" alt="Logo" class="mt-2" height="80">
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>
@endsection
