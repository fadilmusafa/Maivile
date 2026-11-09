@php
    $code = $transaction->serviceType->code;
    $detail = $transaction->detail;
    $fmt = fn ($d) => $d ? $d->translatedFormat('d M Y') : '-';
    $time = fn ($t) => $t ? substr((string) $t, 0, 5) : null;
@endphp
<dl class="row mv-dl mb-0">
    @if ($showApplicant ?? false)
        <dt class="col-sm-4">Pemohon</dt>
        <dd class="col-sm-8">{{ $transaction->user->name }} <span class="text-secondary">(NIS {{ $transaction->user->nis ?? '-' }}@if ($transaction->user->class_name), {{ $transaction->user->class_name }}@endif)</span></dd>
    @endif

    <dt class="col-sm-4">Tanggal pengajuan</dt><dd class="col-sm-8">{{ $fmt($transaction->request_date) }}</dd>

    @if ($code === 'pinjam_rapor')
        <dt class="col-sm-4">Rapor</dt><dd class="col-sm-8">{{ $transaction->document?->name ?? '-' }}</dd>
        <dt class="col-sm-4">Rencana peminjaman</dt><dd class="col-sm-8">{{ $fmt($transaction->planned_date) }}</dd>
        <dt class="col-sm-4">Rencana pengembalian</dt><dd class="col-sm-8">{{ $fmt($transaction->planned_return_date) }}</dd>
        <dt class="col-sm-4">Tanggal diserahkan</dt><dd class="col-sm-8">{{ $fmt($transaction->actual_handover_date) }}</dd>
        <dt class="col-sm-4">Tanggal dikembalikan</dt><dd class="col-sm-8">{{ $fmt($transaction->actual_return_date) }}</dd>
        @if ($detail?->return_condition_note)
            <dt class="col-sm-4">Kondisi saat kembali</dt><dd class="col-sm-8">{{ $detail->return_condition_note }}</dd>
        @endif
    @elseif ($code === 'legalisir')
        <dt class="col-sm-4">Jenis dokumen</dt><dd class="col-sm-8">{{ $detail?->legalization_doc_type ?? '-' }}</dd>
        <dt class="col-sm-4">Jumlah salinan</dt><dd class="col-sm-8">{{ $detail?->copies ?? '-' }}</dd>
        @if ($transaction->document)
            <dt class="col-sm-4">Dokumen tercatat</dt><dd class="col-sm-8">{{ $transaction->document->name }}</dd>
        @endif
        <dt class="col-sm-4">Tanggal diserahkan</dt><dd class="col-sm-8">{{ $fmt($transaction->actual_handover_date) }}</dd>
    @else
        <dt class="col-sm-4">Rencana pengambilan</dt>
        <dd class="col-sm-8">{{ $fmt($transaction->planned_date) }}@if ($time($detail?->pickup_time_start)) <span class="text-secondary">, pukul {{ $time($detail->pickup_time_start) }}@if ($time($detail->pickup_time_end)) – {{ $time($detail->pickup_time_end) }}@endif</span>@endif</dd>
        @if ($transaction->document)
            <dt class="col-sm-4">Dokumen tercatat</dt><dd class="col-sm-8">{{ $transaction->document->name }}</dd>
        @endif
        <dt class="col-sm-4">Tanggal diserahkan</dt><dd class="col-sm-8">{{ $fmt($transaction->actual_handover_date) }}</dd>
    @endif

    @if ($detail?->contact_phone)
        <dt class="col-sm-4">Kontak</dt><dd class="col-sm-8">{{ $detail->contact_phone }}</dd>
    @endif

    <dt class="col-sm-4">{{ $code === 'pengambilan_ijazah' ? 'Catatan siswa' : 'Keperluan' }}</dt>
    <dd class="col-sm-8">{{ $transaction->description ?: '-' }}</dd>

    @if ($detail?->receiver_name && $transaction->actual_handover_date)
        <dt class="col-sm-4">Penerima</dt>
        <dd class="col-sm-8">{{ $detail->receiver_name }}@if ($detail->receiver_relation) <span class="text-secondary">({{ $detail->receiver_relation }})</span>@endif</dd>
        @if ($detail->receiver_id_number)
            <dt class="col-sm-4">No. identitas penerima</dt><dd class="col-sm-8">{{ $detail->receiver_id_number }}</dd>
        @endif
        @if ($detail->handover_note)
            <dt class="col-sm-4">Catatan penyerahan</dt><dd class="col-sm-8">{{ $detail->handover_note }}</dd>
        @endif
    @elseif ($detail?->receiver_name)
        <dt class="col-sm-4">Diambil oleh</dt>
        <dd class="col-sm-8">{{ $detail->receiver_name }}@if ($detail->receiver_relation) <span class="text-secondary">({{ $detail->receiver_relation }})</span>@endif</dd>
    @endif

    <dt class="col-sm-4">Catatan petugas</dt><dd class="col-sm-8">{{ $transaction->admin_note ?: '-' }}</dd>
    <dt class="col-sm-4">Diproses oleh</dt><dd class="col-sm-8">{{ $transaction->processor?->name ?? '-' }}</dd>
    <dt class="col-sm-4">Selesai pada</dt><dd class="col-sm-8">{{ $transaction->completed_at ? $transaction->completed_at->translatedFormat('d M Y H:i') : '-' }}</dd>
</dl>
<div class="mt-3">
    <a href="{{ route('transactions.receipt', $transaction) }}" class="btn btn-sm btn-outline-light">
        <i class="bi bi-printer me-1" aria-hidden="true"></i>Cetak bukti (PDF)
    </a>
</div>
