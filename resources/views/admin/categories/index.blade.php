@extends('layouts.app')

@section('title', 'Kategori Dokumen')

@section('content')
<div class="card">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
            <form method="GET" class="d-flex gap-2" role="search">
                <input type="search" name="q" value="{{ $search }}" class="form-control" style="max-width: 260px" placeholder="Cari kategori" aria-label="Cari kategori">
                <button class="btn btn-outline-light" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Cari</button>
                @if ($search !== '') <a class="btn btn-link" href="{{ route('admin.categories.index') }}">Reset</a> @endif
            </form>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Kategori</a>
        </div>

        @if ($categories->isEmpty())
            <div class="text-center text-secondary py-5"><i class="bi bi-tags fs-1 d-block mb-2" aria-hidden="true"></i>Belum ada kategori.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Keterangan</th>
                            <th scope="col" class="text-center">Jumlah dokumen</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $c)
                            <tr>
                                <td class="fw-semibold">{{ $c->name }}</td>
                                <td class="text-secondary">{{ $c->description ?: '-' }}</td>
                                <td class="text-center">{{ $c->documents_count }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.categories.edit', $c) }}" class="btn btn-sm btn-outline-light btn-icon" title="Edit" aria-label="Edit {{ $c->name }}"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" class="d-inline" data-confirm="Hapus kategori {{ $c->name }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus" aria-label="Hapus {{ $c->name }}"><i class="bi bi-trash" aria-hidden="true"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $categories->links() }}</div>
        @endif
    </div>
</div>
@endsection
