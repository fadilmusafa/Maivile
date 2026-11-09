<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') &middot; Maivile</title>
    @include('partials.assets')
    <link href="{{ asset('css/maivile-landing.css') }}" rel="stylesheet">
    <link href="{{ asset('css/maivile-public.css') }}" rel="stylesheet">
</head>
<body class="mv-auth-page">
<header class="mv-lp-nav sticky-top">
    <nav class="navbar navbar-expand-lg container" aria-label="Menu utama">
        <a class="navbar-brand mv-logo mv-logo-anim" href="{{ route('home') }}">Maivile</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#lpMenu"
                aria-controls="lpMenu" aria-expanded="false" aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="lpMenu">
            <ul class="navbar-nav mx-lg-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#fitur">Fitur</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#panduan">Panduan</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#faq">FAQ</a></li>
            </ul>
            <div class="d-flex flex-column flex-lg-row gap-2 pb-3 pb-lg-0">
                <a href="{{ route('login') }}" class="btn btn-outline-light">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
            </div>
        </div>
    </nav>
</header>

<main class="mv-auth">
    <div class="mv-auth-split">
        <aside class="mv-auth-side d-none d-lg-block" aria-label="Tentang Maivile">
            <span class="mv-lp-pill"><i class="bi bi-stars" aria-hidden="true"></i> Layanan Tata Usaha online</span>
            <p class="mv-auth-headline">Urus rapor, legalisir, dan ijazah dari satu akun</p>
            <p class="mv-lp-lead">Ajukan, unggah persyaratan, lalu pantau statusnya sampai dokumen siap diambil.</p>
            <ul class="mv-auth-points list-unstyled mb-0">
                <li style="--c: var(--mv-green)"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i> Peminjaman Rapor</li>
                <li style="--c: var(--mv-purple)"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> Legalisir Dokumen</li>
                <li style="--c: var(--mv-orange)"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i> Pengambilan Ijazah</li>
            </ul>
        </aside>

        <div class="mv-auth-card">
            @yield('content')
        </div>
    </div>
</main>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/maivile.js') }}"></script>
</body>
</html>
