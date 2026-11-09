@extends('layouts.app')

@section('title', 'Legalisir Dokumen')

@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card overflow-hidden" style="border-top: 4px solid var(--mv-purple)">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-patch-check-fill fs-4" style="color: var(--mv-purple)" aria-hidden="true"></i>
                    <h2 class="h5 mb-0">Ajukan Legalisir Dokumen</h2>
                </div>
                <p class="text-secondary">Lampirkan fotokopi dokumen yang akan dilegalisir. Pengajuan yang disetujui masih harus diproses petugas sebelum dokumen dapat diambil.</p>

                <form method="POST" action="{{ route('student.legalization.store') }}" enctype="multipart/form-data" data-loading novalidate>
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <x-select name="legalization_doc_type" label="Jenis dokumen" required>
                                <option value="">Pilih jenis dokumen</option>
                                @foreach ($docTypes as $type)
                                    <option value="{{ $type }}" @selected(old('legalization_doc_type') === $type)>{{ $type }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6">
                            <x-input name="copies" type="number" label="Jumlah salinan" :value="old('copies', 1)" required :hint="'Bilangan bulat 1 sampai '.$maxCopies.'.'" />
                        </div>
                    </div>

                    <x-select name="document_id" label="Dokumen tercatat (opsional)" hint="Pilih bila dokumennya sudah tercatat di repositori Anda.">
                        <option value="">Tidak memilih</option>
                        @foreach ($documents as $d)
                            <option value="{{ $d->id }}" @selected((int) old('document_id') === $d->id)>{{ $d->name }} ({{ $d->category->name }})</option>
                        @endforeach
                    </x-select>

                    <x-textarea name="description" label="Keperluan legalisir" :value="old('description')" required hint="Contoh: melamar kerja, mendaftar kuliah." />
                    <x-input name="contact_phone" label="Nomor telepon yang dapat dihubungi" :value="old('contact_phone', auth()->user()->phone)" />
                    <x-file name="attachments" label="Lampiran persyaratan" :multiple="true" :required="true" hint="Fotokopi dokumen yang dilegalisir. Maksimal 3 berkas, PDF/JPG/PNG, 2 MB per berkas." />

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                        <a href="{{ route('student.transactions.index') }}" class="btn btn-outline-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
