@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="mb-4">
        <div class="mv-logo">Maivile</div>
        <p class="text-secondary mb-0">Sistem Informasi Dokumen Akademik &amp; Layanan Administrasi Sekolah</p>
    </div>

    @include('partials.alerts')

    <form method="POST" action="{{ route('login.attempt') }}" data-loading novalidate>
        @csrf

        <div class="mb-3">
            <label for="login" class="form-label">Email atau NIS/NIP</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}"
                   class="form-control @error('login') is-invalid @enderror"
                   placeholder="contoh: 2024001 atau nama@sekolah.sch.id"
                   required autofocus autocomplete="username">
            @error('login')
                <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Kata sandi</label>
            <input id="password" name="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
            <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Masuk</button>
    </form>

    <p class="mt-4 mb-2">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    <p class="text-secondary small mb-3">Lupa kata sandi? Hubungi petugas Tata Usaha.</p>
    <a href="{{ route('home') }}" class="btn btn-outline-light w-100"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke beranda</a>
@endsection
