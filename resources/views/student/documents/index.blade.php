@extends('layouts.app')

@section('title', 'Repositori Dokumen')

@section('content')
<p class="text-secondary">Daftar dokumen akademik milik Anda yang tercatat di sekolah. Hanya dokumen digital yang dapat dibuka di sini.</p>

<div class="card">
    <div class="card-body p-3 p-lg-4">
        <form method="GET" class="d-flex flex-wrap gap-2 mb-3" role="search">
            <input type="search" name="q" value="{{ $search }}" class="form-control" style="max-width: 260px" placeholder="Cari nama atau nomor dokumen" aria-label="Cari dokumen">
            <select name="category_id" class="form-select" style="max-width: 190px" aria-label="Filter kategori">
                <option value="">Semua kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected($categoryId === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-light" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Cari</button>
            @if ($search !== '' || $categoryId) <a class="btn btn-link" href="{{ route('student.documents.index') }}">Reset</a> @endif
        </form>

        @if ($documents->isEmpty())
            <div class="text-center text-secondary py-5"><i class="bi bi-folder2-open fs-1 d-block mb-2" aria-hidden="true"></i>Belum ada dokumen yang tercatat.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 mv-cards">
                    <thead>
                        <tr>
                            <th scope="col">Dokumen</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Tahun</th>
                            <th scope="col">Jenis</th>
                            <th scope="col">Ketersediaan</th>
                            <th scope="col" class="text-end">Berkas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $d)
                            <tr>
                                <td data-label="Dokumen" class="fw-semibold">{{ $d->name }}@if ($d->document_number)<div class="small text-secondary fw-normal">{{ $d->document_number }}</div>@endif</td>
                                <td data-label="Kategori">{{ $d->category->name }}</td>
                                <td data-label="Tahun">{{ $d->academic_year ?? '-' }}</td>
                                <td data-label="Jenis"><x-badge :color="$d->document_kind->badge()">{{ $d->document_kind->label() }}</x-badge></td>
                                <td data-label="Ketersediaan"><x-badge :color="$d->availability->badge()">{{ $d->availability->label() }}</x-badge></td>
                                <td data-label="Berkas" class="text-end text-nowrap">
                                    @if ($d->hasDigitalFile())
                                        <a href="{{ route('documents.download', [$d, 'inline' => 1]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-light"><i class="bi bi-eye me-1" aria-hidden="true"></i>Lihat</a>
                                        <a href="{{ route('documents.download', $d) }}" class="btn btn-sm btn-primary"><i class="bi bi-download me-1" aria-hidden="true"></i>Unduh</a>
                                    @else
                                        <span class="text-secondary small">{{ $d->document_kind->value === 'physical' ? 'Dokumen fisik di sekolah' : 'Belum diunggah' }}</span>
                                    @endif
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
