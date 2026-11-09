@extends('layouts.app')

@php
    $isEdit = $user->exists;
    $isStudent = $user->role === 'student';
    $isSelf = $isEdit && $user->id === auth()->id();
    $roleName = $isStudent ? 'Siswa' : 'Admin';
@endphp

@section('title', ($isEdit ? 'Ubah ' : 'Tambah ').$roleName)

@section('content')
<div class="row">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" data-loading novalidate>
                    @csrf
                    @if ($isEdit) @method('PUT') @else <input type="hidden" name="role" value="{{ $user->role }}"> @endif

                    <x-input name="name" label="Nama lengkap" :value="old('name', $user->name)" required autocomplete="off" />

                    @if ($isStudent)
                        <x-input name="nis" label="NIS" :value="old('nis', $user->nis)" required />
                        <div class="row">
                            <div class="col-md-6"><x-input name="class_name" label="Kelas" :value="old('class_name', $user->class_name)" required placeholder="contoh: XII-IPA-1" /></div>
                            <div class="col-md-6"><x-input name="major" label="Jurusan" :value="old('major', $user->major)" placeholder="contoh: IPA" /></div>
                        </div>
                    @else
                        <x-input name="nip" label="NIP" :value="old('nip', $user->nip)" />
                    @endif

                    <x-input name="email" type="email" label="Email" :value="old('email', $user->email)" :required="! $isStudent"
                             :hint="$isStudent ? 'Boleh dikosongkan. Siswa tetap dapat login dengan NIS.' : null" />
                    <x-input name="phone" label="Nomor telepon" :value="old('phone', $user->phone)" />

                    @unless ($isEdit)
                        <x-input name="password" type="password" label="Kata sandi awal" autocomplete="new-password"
                                 hint="Kosongkan agar dibuat otomatis. Minimal 8 karakter dengan huruf dan angka. Pemilik wajib menggantinya saat login pertama." />
                    @endunless

                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="f_is_active" value="1"
                               @checked(old('is_active', $user->is_active)) @disabled($isSelf)>
                        <label class="form-check-label" for="f_is_active">Akun aktif</label>
                        @if ($isSelf)
                            <div class="form-text text-secondary">Anda tidak dapat menonaktifkan akun sendiri.</div>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.users.index', ['role' => $user->role]) }}" class="btn btn-outline-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
