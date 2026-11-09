@extends('layouts.app')

@section('title', 'Detail Pengajuan')

@section('content')
@php
    $code = $transaction->serviceType->code;
    $accent = ['pinjam_rapor' => 'var(--mv-green)', 'legalisir' => 'var(--mv-purple)', 'pengambilan_ijazah' => 'var(--mv-orange)'][$code] ?? 'var(--mv-blue)';
    $canFix = $code === 'pengambilan_ijazah' && $transaction->process_status === \App\Enums\ProcessStatus::PerluPerbaikan;
    $detail = $transaction->detail;
    $represented = (bool) old('represented', filled($detail?->receiver_name));
@endphp

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card overflow-hidden mb-3" style="border-top: 4px solid {{ $accent }}">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                    <div>
                        <h2 class="h4 mb-1">{{ $transaction->transaction_code }}</h2>
                        <div class="text-secondary mb-2">{{ $transaction->serviceType->name }}</div>
                        <div class="d-flex flex-wrap gap-2">
                            <x-badge :color="$transaction->approval_status->badge()">{{ $transaction->approval_status->label() }}</x-badge>
                            <x-badge :color="$transaction->process_status->badge()">{{ $transaction->process_status->label() }}</x-badge>
                        </div>
                    </div>
                    <a href="{{ route('student.transactions.index') }}" class="btn btn-outline-light"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali</a>
                </div>

                @if ($transaction->admin_note)
                    <div class="alert alert-secondary">
                        <div class="fw-semibold mb-1"><i class="bi bi-chat-left-text me-1" aria-hidden="true"></i>Catatan petugas</div>
                        {{ $transaction->admin_note }}
                    </div>
                @endif

                @include('transactions._summary', ['showApplicant' => false])
            </div>
        </div>

        @if ($canFix)
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h3 class="h5">Perbaiki Pengajuan</h3>
                    <p class="text-secondary">Petugas meminta perbaikan. Ubah data yang diperlukan, lalu kirim ulang untuk diverifikasi.</p>

                    <form method="POST" action="{{ route('student.diploma.resubmit', $transaction) }}" enctype="multipart/form-data" data-loading novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4"><x-input name="planned_date" type="date" label="Tanggal rencana pengambilan" :value="old('planned_date', $transaction->planned_date?->toDateString())" required /></div>
                            <div class="col-md-4"><x-input name="pickup_time_start" type="time" label="Jam mulai (opsional)" :value="old('pickup_time_start', $detail?->pickup_time_start ? substr($detail->pickup_time_start, 0, 5) : null)" /></div>
                            <div class="col-md-4"><x-input name="pickup_time_end" type="time" label="Jam selesai (opsional)" :value="old('pickup_time_end', $detail?->pickup_time_end ? substr($detail->pickup_time_end, 0, 5) : null)" /></div>
                        </div>
                        <x-input name="contact_phone" label="Kontak yang dapat dihubungi" :value="old('contact_phone', $detail?->contact_phone)" required />
                        <x-textarea name="description" label="Catatan tambahan" :value="old('description', $transaction->description)" />

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="represented" value="1" id="f_represented" @checked($represented)>
                            <label class="form-check-label" for="f_represented">Ijazah akan diambil oleh orang lain (perwakilan)</label>
                        </div>
                        <div id="proxyFields" @if (! $represented) hidden @endif>
                            <div class="row">
                                <div class="col-md-6"><x-input name="receiver_name" label="Nama pengambil" :value="old('receiver_name', $detail?->receiver_name)" /></div>
                                <div class="col-md-6"><x-input name="receiver_relation" label="Hubungan dengan siswa" :value="old('receiver_relation', $detail?->receiver_relation)" /></div>
                            </div>
                            <x-file name="power_of_attorney" label="Surat kuasa" hint="Unggah ulang hanya bila perlu mengganti. Wajib bila belum ada surat kuasa." />
                        </div>
                        <x-file name="attachments" label="Dokumen pendukung tambahan (opsional)" :multiple="true" />

                        <button type="submit" class="btn btn-primary mt-2">Kirim Ulang Pengajuan</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-4">
                <h3 class="h5 mb-3">Lampiran</h3>
                @include('transactions._files')
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body p-4">
                <h3 class="h5 mb-3">Riwayat Status</h3>
                @include('transactions._timeline')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var box = document.getElementById('f_represented');
        var fields = document.getElementById('proxyFields');
        if (!box || !fields) return;
        box.addEventListener('change', function () { fields.hidden = !box.checked; });
    })();
</script>
@endpush
