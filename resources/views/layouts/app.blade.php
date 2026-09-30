<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php($setting = \App\Models\Setting::first())
    @include('partials.head')
    <link rel="icon" type="image/png" href="{{ asset($setting?->path_logo ?: 'logo.png') }}?v={{ $setting?->updated_at?->timestamp ?? 1 }}"/>
    @if (isset($csscustom)){{ $csscustom }}@endif
    @vite(['resources/js/app.js'])
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary" id="bdy">
    <div class="app-wrapper">
        @include('layouts.navigation')
        <x-sidebar/>
        <main class="app-main">
            <div class="app-content"><div class="container-fluid">
                @if(session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @if($errors->any())<div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @yield('content')
            </div></div>
        </main>
        <footer class="app-footer d-flex justify-content-between align-items-center px-3"><div><strong>{{ date('Y') }} &copy;</strong> {{ $setting?->nama_perusahaan ?: 'PT Pandawa' }}</div><div id="jam-indonesia" class="text-muted"></div></footer>
        <script>function updateJam(){const now=new Date();document.getElementById('jam-indonesia').textContent=now.toLocaleDateString('id-ID',{weekday:'long',year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'});}updateJam();setInterval(updateJam,1000);</script>
    </div>
    @include('partials.script')
    @if (isset($jscustom)){{ $jscustom }}@endif
    @stack('scripts')
</body>
</html>
