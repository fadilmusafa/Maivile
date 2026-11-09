@extends('layouts.app')

@section('title', 'Peminjaman Rapor')

@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card overflow-hidden" style="border-top: 4px solid var(--mv-green)">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-journal-bookmark-fill fs-4" style="color: var(--mv-green)" aria-hidden="true"></i>
                    <h2 class="h5 mb-0">Ajukan Peminjaman Rapor</h2>
                </div>
                <p class="text-secondary">Rapor fisik dipinjam untuk paling lama {{ $maxDays }} hari. Pengajuan akan diverifikasi petugas, dan peminjaman baru tercatat setelah rapor diserahkan.</p>

                @if ($documents->isEmpty())
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
                        Belum ada rapor fisik atas nama Anda yang tercatat di repositori. Hubungi petugas tata usaha.
                    </div>
                @else
                    <form method="POST" action="{{ route('student.loan.store') }}" enctype="multipart/form-data" data-loading novalidate>
                        @csrf

                        <x-select name="document_id" label="Rapor yang dipinjam" required>
                            <option value="">Pilih rapor</option>
                            @foreach ($documents as $d)
                                <option value="{{ $d->id }}" @selected((int) old('document_id') === $d->id) @if ($d->availability->value !== 'tersedia') disabled @endif>
                                    {{ $d->name }}@if ($d->availability->value !== 'tersedia') ({{ $d->availability->label() }})@endif
                                </option>
                            @endforeach
                        </x-select>

                        <div class="row">
                            <div class="col-md-6"><x-input name="planned_date" type="date" label="Tanggal rencana peminjaman" :value="old('planned_date')" required /></div>
                            <div class="col-md-6"><x-input name="planned_return_date" type="date" label="Tanggal rencana pengembalian" :value="old('planned_return_date')" required /></div>
                        </div>

                        <x-textarea name="description" label="Keperluan peminjaman" :value="old('description')" required />
                        <x-input name="contact_phone" label="Nomor telepon yang dapat dihubungi" :value="old('contact_phone', auth()->user()->phone)" />
                        <x-file name="attachments" label="Lampiran (opsional)" :multiple="true" hint="Boleh melampirkan surat permohonan. Maksimal 3 berkas, PDF/JPG/PNG, 2 MB per berkas." />

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                            <a href="{{ route('student.transactions.index') }}" class="btn btn-outline-light">Batal</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
