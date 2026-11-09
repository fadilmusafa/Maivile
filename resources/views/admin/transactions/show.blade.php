@extends('layouts.app')

@section('title', 'Detail Pengajuan')

@section('content')
@php
    $code = $transaction->serviceType->code;
    $accent = ['pinjam_rapor' => 'var(--mv-green)', 'legalisir' => 'var(--mv-purple)', 'pengambilan_ijazah' => 'var(--mv-orange)'][$code] ?? 'var(--mv-blue)';
    $status = $transaction->process_status->value;
    $detail = $transaction->detail;
    $isDiploma = $code === 'pengambilan_ijazah';

    $canReview = $status === 'menunggu_verifikasi';
    $canHandover = in_array('dipinjam', $next, true) || in_array('diserahkan', $next, true);
    $canReturn = in_array('dikembalikan', $next, true);
    $advanceTargets = array_values(array_intersect($next, ['sedang_diproses', 'siap_diambil', 'selesai']));
    $labels = ['sedang_diproses' => 'Tandai sedang diproses', 'siap_diambil' => 'Tandai siap diambil', 'selesai' => 'Tutup transaksi (selesai)'];
    $notSelf = old('receiver_is_self', filled($detail?->receiver_name) ? '0' : '1') === '0';
@endphp

<div class="row g-3">
    <div class="col-xl-7">
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
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-light"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali</a>
                </div>
                @include('transactions._summary', ['showApplicant' => true])
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body p-4">
                <h3 class="h5 mb-3">Lampiran</h3>
                @include('transactions._files')
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <h3 class="h5 mb-3">Riwayat Status</h3>
                @include('transactions._timeline')
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        @if ($canReview)
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h3 class="h5">Verifikasi Pengajuan</h3>
                    <p class="text-secondary">Periksa identitas, dokumen, dan lampiran terlebih dahulu. Catatan wajib diisi bila menolak atau meminta perbaikan.</p>

                    <form method="POST" action="{{ route('admin.transactions.review', $transaction) }}" data-loading novalidate>
                        @csrf
                        @if ($isDiploma)
                            <x-input name="planned_date" type="date" label="Jadwal pengambilan (opsional)"
                                     :value="old('planned_date')" hint="Isi hanya bila jadwal diubah dari yang diminta siswa. Kosongkan untuk memakai jadwal siswa." />
                        @endif
                        <x-textarea name="note" label="Catatan untuk siswa" :value="old('note')" />

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" name="decision" value="approve" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>{{ $isDiploma ? 'Konfirmasi jadwal' : 'Setujui' }}
                            </button>
                            @if ($isDiploma)
                                <button type="submit" name="decision" value="revise" class="btn btn-outline-warning">
                                    <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Minta perbaikan
                                </button>
                            @endif
                            <button type="submit" name="decision" value="reject" class="btn btn-outline-danger">
                                <i class="bi bi-x-lg me-1" aria-hidden="true"></i>Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if ($status === 'perlu_perbaikan')
            <div class="alert alert-warning">
                <i class="bi bi-hourglass-split me-1" aria-hidden="true"></i>Menunggu siswa memperbaiki dan mengirim ulang pengajuan.
            </div>
        @endif

        @if (count($advanceTargets) > 0)
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h3 class="h5">Perbarui Tahap</h3>
                    <form method="POST" action="{{ route('admin.transactions.advance', $transaction) }}" data-loading novalidate>
                        @csrf
                        <x-textarea name="advance_note" label="Catatan (opsional)" :rows="2" :value="old('advance_note')" />
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($advanceTargets as $target)
                                <button type="submit" name="status" value="{{ $target }}" class="btn btn-primary">{{ $labels[$target] }}</button>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if ($canHandover)
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h3 class="h5">{{ $code === 'pinjam_rapor' ? 'Catat Penyerahan Rapor' : 'Catat Penyerahan Dokumen' }}</h3>
                    <p class="text-secondary">Status baru berubah setelah penyerahan benar-benar dicatat di sini. Bukti serah terima wajib diunggah.</p>

                    <form method="POST" action="{{ route('admin.transactions.handover', $transaction) }}" enctype="multipart/form-data" data-loading novalidate>
                        @csrf
                        <x-input name="handover_date" type="date" label="Tanggal penyerahan" :value="old('handover_date', now()->toDateString())" required />

                        <div class="mb-3">
                            <div class="form-label">Penerima</div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="receiver_is_self" id="recv_self" value="1" @checked(! $notSelf)>
                                <label class="form-check-label" for="recv_self">Siswa yang bersangkutan ({{ $transaction->user->name }})</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="receiver_is_self" id="recv_other" value="0" @checked($notSelf)>
                                <label class="form-check-label" for="recv_other">Orang lain (perlu surat kuasa)</label>
                            </div>
                            @error('receiver_is_self')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div id="otherReceiver" @if (! $notSelf) hidden @endif>
                            <x-input name="receiver_name" label="Nama penerima" :value="old('receiver_name', $detail?->receiver_name)" />
                            <x-input name="receiver_relation" label="Hubungan dengan siswa" :value="old('receiver_relation', $detail?->receiver_relation)" />
                        </div>

                        <x-input name="receiver_id_number" :label="'Nomor identitas penerima'.($isDiploma ? '' : ' (opsional)')"
                                 :value="old('receiver_id_number')" :required="$isDiploma"
                                 hint="Catat nomor KTP/kartu pelajar yang diperiksa petugas." />
                        <x-textarea name="handover_note" label="Catatan penyerahan" :rows="2" :value="old('handover_note')" />
                        <x-file name="proof" label="Bukti serah terima" :required="true" hint="Foto atau scan tanda terima. PDF/JPG/PNG, maksimal 2 MB." />

                        <button type="submit" class="btn btn-primary">Simpan Penyerahan</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($canReturn)
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h3 class="h5">Catat Pengembalian Rapor</h3>
                    <form method="POST" action="{{ route('admin.transactions.return', $transaction) }}" data-loading novalidate>
                        @csrf
                        <x-input name="return_date" type="date" label="Tanggal pengembalian" :value="old('return_date', now()->toDateString())" required />
                        <x-textarea name="return_condition_note" label="Kondisi rapor saat dikembalikan" :rows="2" :value="old('return_condition_note')" />
                        <button type="submit" class="btn btn-primary">Simpan Pengembalian</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($transaction->process_status->isFinal())
            <div class="alert alert-secondary">
                <i class="bi bi-flag-fill me-1" aria-hidden="true"></i>Transaksi ini sudah final dan tidak dapat diubah lagi.
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var other = document.getElementById('otherReceiver');
        var self = document.getElementById('recv_self');
        var diff = document.getElementById('recv_other');
        if (!other || !self || !diff) return;
        function sync() { other.hidden = !diff.checked; }
        self.addEventListener('change', sync);
        diff.addEventListener('change', sync);
    })();
</script>
@endpush
