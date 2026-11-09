@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
@if (session('generated_password'))
    <div class="alert alert-info" role="alert">
        <div class="fw-semibold mb-2"><i class="bi bi-key-fill me-1" aria-hidden="true"></i>Kata sandi awal untuk {{ session('generated_for') }}</div>
        <div class="mv-secret d-inline-block user-select-all">{{ session('generated_password') }}</div>
        <div class="small mt-2">Catat dan berikan kepada pemilik akun. Kata sandi ini <strong>tidak akan ditampilkan lagi</strong>, dan pemilik wajib menggantinya saat login pertama.</div>
    </div>
@endif

<ul class="nav nav-pills mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $role === 'student' ? 'active' : '' }}" href="{{ route('admin.users.index', ['role' => 'student']) }}">
            Siswa <span class="badge text-bg-secondary ms-1">{{ $counts['student'] }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $role === 'admin' ? 'active' : '' }}" href="{{ route('admin.users.index', ['role' => 'admin']) }}">
            Admin / Petugas <span class="badge text-bg-secondary ms-1">{{ $counts['admin'] }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $role === 'pending' ? 'active' : '' }}" href="{{ route('admin.users.index', ['role' => 'pending']) }}">
            Menunggu persetujuan <span class="badge {{ $counts['pending'] > 0 ? 'text-bg-warning' : 'text-bg-secondary' }} ms-1">{{ $counts['pending'] }}</span>
        </a>
    </li>
</ul>

@if ($role === 'pending')
    @include('admin.users._pending')
@else

<div class="card">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
            <form method="GET" class="d-flex flex-wrap gap-2" role="search">
                <input type="hidden" name="role" value="{{ $role }}">
                <input type="search" name="q" value="{{ $search }}" class="form-control" style="max-width: 260px"
                       placeholder="Cari nama, NIS/NIP, email" aria-label="Cari pengguna">
                <select name="status" class="form-select" style="max-width: 160px" aria-label="Filter status">
                    <option value="">Semua status</option>
                    <option value="active" @selected($status === 'active')>Aktif</option>
                    <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                </select>
                <button class="btn btn-outline-light" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Cari</button>
                @if ($search !== '' || $status)
                    <a class="btn btn-link" href="{{ route('admin.users.index', ['role' => $role]) }}">Reset</a>
                @endif
            </form>
            <a href="{{ route('admin.users.create', ['role' => $role]) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah {{ $role === 'student' ? 'Siswa' : 'Admin' }}
            </a>
        </div>

        @if ($users->isEmpty())
            <div class="text-center text-secondary py-5">
                <i class="bi bi-people fs-1 d-block mb-2" aria-hidden="true"></i>
                Tidak ada data yang cocok.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">{{ $role === 'student' ? 'NIS' : 'NIP' }}</th>
                            @if ($role === 'student')
                                <th scope="col">Kelas</th>
                            @endif
                            <th scope="col">Kontak</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr>
                                <td class="fw-semibold">{{ $u->name }}@if ($u->id === auth()->id()) <span class="text-secondary fw-normal">(Anda)</span>@endif</td>
                                <td>{{ $u->identifier() ?? '-' }}</td>
                                @if ($role === 'student')
                                    <td>{{ $u->class_name ?? '-' }}@if ($u->major) <span class="text-secondary">&middot; {{ $u->major }}</span>@endif</td>
                                @endif
                                <td>
                                    <div>{{ $u->email ?? '-' }}</div>
                                    <div class="small text-secondary">{{ $u->phone ?? '-' }}</div>
                                </td>
                                <td>
                                    <x-badge :color="$u->is_active ? 'success' : 'secondary'">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                                    @if ($u->must_change_password)
                                        <div class="small text-secondary mt-1">Belum ganti sandi</div>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-light btn-icon"
                                       title="Edit" aria-label="Edit {{ $u->name }}"><i class="bi bi-pencil" aria-hidden="true"></i></a>

                                    @if ($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.reset-password', $u) }}" class="d-inline"
                                              data-confirm="Reset kata sandi {{ $u->name }}? Kata sandi baru dibuat otomatis dan harus diganti saat login.">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-light btn-icon" title="Reset kata sandi"
                                                    aria-label="Reset kata sandi {{ $u->name }}"><i class="bi bi-key" aria-hidden="true"></i></button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.toggle-active', $u) }}" class="d-inline"
                                              data-confirm="{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $u->name }}?">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-light btn-icon"
                                                    title="{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                    aria-label="{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $u->name }}">
                                                <i class="bi {{ $u->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}" aria-hidden="true"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline"
                                              data-confirm="Hapus akun {{ $u->name }}? Tindakan ini tidak dapat dibatalkan dari aplikasi.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"
                                                    aria-label="Hapus {{ $u->name }}"><i class="bi bi-trash" aria-hidden="true"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endif
@endsection
