@extends('layouts.app')

@php
    $isEdit = $document->exists;
    $kindValue = old('document_kind', $document->document_kind?->value ?? 'physical');
    $borrowed = $isEdit && $document->availability === \App\Enums\DocumentAvailability::Dipinjam;
    $availValue = old('availability', $document->availability?->value ?? 'tersedia');
@endphp

@section('title', $isEdit ? 'Ubah Dokumen' : 'Tambah Dokumen')

@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data" data-loading novalidate
                      action="{{ $isEdit ? route('admin.documents.update', $document) : route('admin.documents.store') }}">
                    @csrf
                    @if ($isEdit) @method('PUT') @endif

                    <x-input name="name" label="Nama dokumen" :value="old('name', $document->name)" required />

                    <div class="row">
                        <div class="col-md-6">
                            <x-select name="category_id" label="Kategori" required>
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected((int) old('category_id', $document->category_id) === $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6">
                            <x-select name="owner_user_id" label="Pemilik (siswa)" hint="Kosongkan untuk dokumen umum yang hanya dapat dilihat Admin.">
                                <option value="">Dokumen umum (tanpa pemilik)</option>
                                @foreach ($students as $s)
                                    <option value="{{ $s->id }}" @selected((int) old('owner_user_id', $document->owner_user_id) === $s->id)>{{ $s->name }} ({{ $s->nis }})</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><x-input name="document_number" label="Nomor dokumen" :value="old('document_number', $document->document_number)" /></div>
                        <div class="col-md-6"><x-input name="academic_year" label="Tahun akademik / penerbitan" :value="old('academic_year', $document->academic_year)" placeholder="2025/2026 atau 2026" /></div>
                    </div>

                    <x-textarea name="description" label="Keterangan" :value="old('description', $document->description)" />

                    <hr class="border-secondary-subtle my-4">

                    <x-select name="document_kind" label="Jenis dokumen" required
                              hint="Digital: ada berkas di sistem. Fisik: hanya pencatatan metadata (misalnya ijazah asli).">
                        <option value="physical" @selected($kindValue === 'physical')>Fisik (arsip di sekolah)</option>
                        <option value="digital" @selected($kindValue === 'digital')>Digital (ada berkas)</option>
                    </x-select>

                    <div id="physicalFields">
                        <x-input name="physical_location" label="Lokasi penyimpanan fisik" :value="old('physical_location', $document->physical_location)" placeholder="contoh: Lemari Arsip TU" />
                    </div>

                    <div id="digitalFields">
                        <div class="mb-3">
                            <label for="f_file" class="form-label">Berkas dokumen (PDF/JPG/PNG, maks. 2 MB)</label>
                            <input id="f_file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                   @class(['form-control', 'is-invalid' => $errors->has('file')])>
                            @error('file')
                                <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}</div>
                            @enderror
                            @if ($isEdit && $document->hasDigitalFile())
                                <div class="form-text text-secondary">Berkas saat ini sudah ada. Unggah berkas baru hanya jika ingin menggantinya.</div>
                            @endif
                        </div>
                    </div>

                    <x-select name="availability" label="Ketersediaan" :disabled="$borrowed"
                              :hint="$borrowed ? 'Sedang dipinjam. Status berubah otomatis saat pengembalian dicatat.' : null">
                        <option value="tersedia" @selected($availValue === 'tersedia')>Tersedia</option>
                        <option value="tidak_tersedia" @selected($availValue === 'tidak_tersedia')>Tidak tersedia</option>
                        @if ($borrowed) <option value="dipinjam" selected>Dipinjam</option> @endif
                    </x-select>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ $isEdit ? route('admin.documents.show', $document) : route('admin.documents.index') }}" class="btn btn-outline-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var kind = document.getElementById('f_document_kind');
        var digital = document.getElementById('digitalFields');
        var physical = document.getElementById('physicalFields');
        function sync() {
            var isDigital = kind.value === 'digital';
            digital.hidden = !isDigital;
            physical.hidden = isDigital;
        }
        kind.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
