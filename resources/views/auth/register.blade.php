@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <div class="mb-4">
        <a href="{{ route('home') }}" class="mv-logo">Maivile</a>
        <p class="text-secondary mb-0 mt-1">Buat akun siswa. Akun aktif setelah disetujui petugas Tata Usaha.</p>
    </div>

    @include('partials.alerts')

    <form method="POST" action="{{ route('register.store') }}" data-loading novalidate>
        @csrf

        {{-- Honeypot: disembunyikan dari pengguna, hanya diisi bot --}}
        <div style="position:absolute; left:-9999px;" aria-hidden="true">
            <label for="website">Jangan diisi</label>
            <input id="website" type="text" name="website" tabindex="-1" autocomplete="off" value="">
        </div>

        <x-input name="name" label="Nama lengkap" :value="old('name')" :required="true" autocomplete="name" />
        <x-input name="nis" label="NIS" :value="old('nis')" :required="true" autocomplete="off" />

        <div class="row g-2">
            <div class="col-6">
                <x-input name="class_name" label="Kelas" :value="old('class_name')" :required="true" placeholder="XII-IPA-1" />
            </div>
            <div class="col-6">
                <x-input name="major" label="Jurusan" :value="old('major')" :required="true" placeholder="IPA" />
            </div>
        </div>

        <x-input name="email" type="email" label="Email" :value="old('email')" :required="true" autocomplete="email" />
        <x-input name="phone" type="tel" label="Nomor telepon" :value="old('phone')" :required="true" autocomplete="tel" />
        <x-input name="password" type="password" label="Kata sandi" :required="true" autocomplete="new-password"
                 hint="Minimal 8 karakter, memuat huruf besar, huruf kecil, dan angka." />
        <x-input name="password_confirmation" type="password" label="Ulangi kata sandi" :required="true" autocomplete="new-password" />

        <button type="submit" class="btn btn-primary w-100 mt-2">Daftar</button>
    </form>

    <p class="mt-4 mb-3">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    <a href="{{ route('home') }}" class="btn btn-outline-light w-100"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke beranda</a>
@endsection
