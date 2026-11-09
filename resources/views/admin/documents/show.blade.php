@extends('layouts.app')

@section('title', 'Detail Dokumen')

@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                    <div>
                        <h2 class="h4 mb-1">{{ $document->name }}</h2>
                        <div class="d-flex gap-2">
                            <x-badge :color="$document->document_kind->badge()">{{ $document->document_kind->label() }}</x-badge>
                            <x-badge :color="$document->availability->badge()">{{ $document->availability->label() }}</x-badge>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($document->hasDigitalFile())
                            <a href="{{ route('documents.download', [$document, 'inline' => 1]) }}" target="_blank" rel="noopener" class="btn btn-outline-light"><i class="bi bi-eye me-1" aria-hidden="true"></i>Lihat</a>
                            <a href="{{ route('documents.download', $document) }}" class="btn btn-primary"><i class="bi bi-download me-1" aria-hidden="true"></i>Unduh</a>
                        @endif
                        <a href="{{ route('admin.documents.edit', $document) }}" class="btn btn-outline-light"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Ubah</a>
                    </div>
                </div>

                <dl class="row mv-dl mb-0">
                    <dt class="col-sm-4">Kategori</dt><dd class="col-sm-8">{{ $document->category->name }}</dd>
                    <dt class="col-sm-4">Pemilik</dt>
                    <dd class="col-sm-8">@if ($document->owner){{ $document->owner->name }} ({{ $document->owner->nis }})@else<span class="text-secondary">Dokumen umum</span>@endif</dd>
                    <dt class="col-sm-4">Nomor dokumen</dt><dd class="col-sm-8">{{ $document->document_number ?: '-' }}</dd>
                    <dt class="col-sm-4">Tahun akademik</dt><dd class="col-sm-8">{{ $document->academic_year ?: '-' }}</dd>
                    <dt class="col-sm-4">Keterangan</dt><dd class="col-sm-8">{{ $document->description ?: '-' }}</dd>
                    <dt class="col-sm-4">Lokasi fisik</dt><dd class="col-sm-8">{{ $document->physical_location ?: '-' }}</dd>
                    <dt class="col-sm-4">Berkas digital</dt>
                    <dd class="col-sm-8">
                        @if ($document->hasDigitalFile())
                            <span class="text-success"><i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>Tersedia</span>
                            <span class="text-secondary">({{ $document->file_mime }})</span>
                        @else
                            <span class="text-secondary">Belum diunggah. Dokumen belum tersedia secara digital.</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4">Dicatat pada</dt><dd class="col-sm-8">{{ $document->created_at->translatedFormat('d M Y H:i') }}</dd>
                    <dt class="col-sm-4">Dipakai transaksi</dt><dd class="col-sm-8">{{ $transactionCount }}</dd>
                </dl>

                <hr class="border-secondary-subtle">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-light"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali</a>
                    <form method="POST" action="{{ route('admin.documents.destroy', $document) }}" data-confirm="Hapus dokumen {{ $document->name }}?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i>Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
