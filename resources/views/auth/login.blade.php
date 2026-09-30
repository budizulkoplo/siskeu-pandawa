@php($setting = \App\Models\Setting::first())
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - {{ $setting?->nama_perusahaan ?: 'Pandawa' }}</title>
    <link rel="icon" type="image/png" href="{{ asset($setting?->path_logo ?: 'logo.png') }}?v={{ $setting?->updated_at?->timestamp ?? 1 }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-icons-1.13.1/bootstrap-icons.min.css') }}">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #102a43, #1976d2); display: grid; place-items: center; }
        .login-card { width: min(390px, calc(100% - 2rem)); }
        .login-logo { max-width: 220px; max-height: 90px; object-fit: contain; }
    </style>
</head>
<body>
    <div class="card login-card shadow-lg border-0">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <img class="login-logo mb-3" src="{{ asset($setting?->path_logo ?: 'logo.png') }}?v={{ $setting?->updated_at?->timestamp ?? 1 }}" alt="{{ $setting?->nama_perusahaan ?: 'Pandawa' }}">
                <h3 class="fw-bold mb-1">{{ $setting?->nama_perusahaan ?: 'PANDAWA' }}</h3>
                <div class="text-muted">Sistem Keuangan</div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">Username atau password tidak sesuai.</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control" value="{{ old('username') }}" required autofocus></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Ingat saya</label></div>
                <button class="btn btn-primary w-100">Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>
