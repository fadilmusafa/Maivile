@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body p-4">
                <h2 class="h5 mb-1">Data Pribadi</h2>
                <p class="text-secondary small mb-4">
                    @if ($user->isAdmin())
                        Anda dapat memperbarui nama dan nomor telepon.
                    @else
                        Data identitas dikelola oleh sekolah. Anda hanya dapat memperbarui nomor telepon.
                    @endif
                </p>

                <form method="POST" action="{{ route('profile.update') }}" data-loading novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        @if ($user->isAdmin())
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @else
                            <input id="name" type="text" value="{{ $user->name }}" class="form-control" disabled>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="ident" class="form-label">{{ $user->isAdmin() ? 'NIP' : 'NIS' }}</label>
                        <input id="ident" type="text" value="{{ $user->identifier() ?? '-' }}" class="form-control" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="text" value="{{ $user->email ?? '-' }}" class="form-control" disabled>
                    </div>

                    @if ($user->isStudent())
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="class_name" class="form-label">Kelas</label>
                                <input id="class_name" type="text" value="{{ $user->class_name ?? '-' }}" class="form-control" disabled>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="major" class="form-label">Jurusan</label>
                                <input id="major" type="text" value="{{ $user->major ?? '-' }}" class="form-control" disabled>
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <label for="phone" class="form-label">Nomor telepon</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                               class="form-control @error('phone') is-invalid @enderror" autocomplete="tel">
                        @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6" id="password">
        <div class="card h-100">
            <div class="card-body p-4">
                <h2 class="h5 mb-1">Ganti Kata Sandi</h2>
                <p class="text-secondary small mb-4">Minimal 8 karakter dan mengandung huruf serta angka.</p>

                <form method="POST" action="{{ route('password.update') }}" data-loading novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Kata sandi saat ini</label>
                        <input id="current_password" name="current_password" type="password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               required autocomplete="current-password">
                        @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Kata sandi baru</label>
                        <input id="new_password" name="password" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Konfirmasi kata sandi baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               class="form-control" required autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary">Ganti kata sandi</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
