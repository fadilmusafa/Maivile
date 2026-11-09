@extends('layouts.app')

@section('title', 'Repositori Dokumen')

@section('content')
<div class="card">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
            <form method="GET" class="d-flex flex-wrap gap-2" role="search">
                <input type="search" name="q" value="{{ $search }}" class="form-control" style="max-width: 250px"
                       placeholder="Cari dokumen, nomor, pemilik" aria-label="Cari dokumen">
                <select name="category_id" class="form-select" style="max-width: 170px" aria-label="Filter kategori">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected($categoryId === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <select name="kind" class="form-select" style="max-width: 140px" aria-label="Filter jenis">
                    <option value="">Semua jenis</option>
                    <option value="digital" @selected($kind === 'digital')>Digital</option>
                    <option value="physical" @selected($kind === 'physical')>Fisik</option>
                </select>
                <select name="availability" class="form-select" style="max-width: 160px" aria-label="Filter ketersediaan">
                    <option value="">Semua status</option>
                    <option value="tersedia" @selected($availability === 'tersedia')>Tersedia</option>
                    <option value="dipinjam" @selected($availability === 'dipinjam')>Dipinjam</option>
                    <option value="tidak_tersedia" @selected($availability === 'tidak_tersedia')>Tidak tersedia</option>
                </select>
                <button class="btn btn-outline-light" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Cari</button>
                @if ($search !== '' || $kind || $availability || $categoryId)
                    <a class="btn btn-link" href="{{ route('admin.documents.index') }}">Reset</a>
                @endif
            </form>
            <a href="{{ route('admin.documents.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Dokumen</a>
        </div>

        @if ($documents->isEmpty())
            <div class="text-center text-secondary py-5"><i class="bi bi-folder2-open fs-1 d-block mb-2" aria-hidden="true"></i>Tidak ada dokumen yang cocok.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 mv-cards">
                    <thead>
                        <tr>
                            <th scope="col">Dokumen</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Pemilik</th>
                            <th scope="col">Tahun</th>
                            <th scope="col">Jenis</th>
                            <th scope="col">Ketersediaan</th>
                            <th scope="col">Berkas digital</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $d)
                            <tr>
                                <td data-label="Dokumen">
                                    <a href="{{ route('admin.documents.show', $d) }}" class="fw-semibold text-decoration-none">{{ $d->name }}</a>
                                    @if ($d->document_number)<div class="small text-secondary">{{ $d->document_number }}</div>@endif
                                </td>
                                <td data-label="Kategori">{{ $d->category->name }}</td>
                                <td data-label="Pemilik">@if ($d->owner){{ $d->owner->name }}<div class="small text-secondary">{{ $d->owner->nis }}</div>@else<span class="text-secondary">Umum</span>@endif</td>
                                <td data-label="Tahun">{{ $d->academic_year ?? '-' }}</td>
                                <td data-label="Jenis"><x-badge :color="$d->document_kind->badge()">{{ $d->document_kind->label() }}</x-badge></td>
                                <td data-label="Ketersediaan"><x-badge :color="$d->availability->badge()">{{ $d->availability->label() }}</x-badge></td>
                                <td data-label="Berkas digital">
                                    @if ($d->hasDigitalFile())
                                        <span class="text-success"><i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>Ada</span>
                                    @else
                                        <span class="text-secondary">Belum diunggah</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="text-end text-nowrap">
                                    <a href="{{ route('admin.documents.show', $d) }}" class="btn btn-sm btn-outline-light btn-icon" title="Detail" aria-label="Detail {{ $d->name }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.documents.edit', $d) }}" class="btn btn-sm btn-outline-light btn-icon" title="Edit" aria-label="Edit {{ $d->name }}"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.documents.destroy', $d) }}" class="d-inline" data-confirm="Hapus dokumen {{ $d->name }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus" aria-label="Hapus {{ $d->name }}"><i class="bi bi-trash" aria-hidden="true"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $documents->links() }}</div>
        @endif
    </div>
</div>
@endsection
