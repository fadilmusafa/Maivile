@php
    $code = $transaction->serviceType->code;
    $detail = $transaction->detail;
    $fmt = fn ($d) => $d ? $d->translatedFormat('d M Y') : '-';
    $time = fn ($t) => $t ? substr((string) $t, 0, 5) : null;

    $rows = [
        'Kode pengajuan' => $transaction->transaction_code,
        'Layanan' => $transaction->serviceType->name,
        'Pemohon' => ($transaction->user?->name ?? '-').' (NIS '.($transaction->user?->nis ?? '-').($transaction->user?->class_name ? ', '.$transaction->user->class_name : '').')',
        'Tanggal pengajuan' => $fmt($transaction->request_date),
    ];

    if ($code === 'pinjam_rapor') {
        $rows['Rapor'] = $transaction->document?->name ?? '-';
        $rows['Rencana peminjaman'] = $fmt($transaction->planned_date);
        $rows['Rencana pengembalian'] = $fmt($transaction->planned_return_date);
        $rows['Tanggal diserahkan'] = $fmt($transaction->actual_handover_date);
        $rows['Tanggal dikembalikan'] = $fmt($transaction->actual_return_date);
    } elseif ($code === 'legalisir') {
        $rows['Jenis dokumen'] = $detail?->legalization_doc_type ?? '-';
        $rows['Jumlah salinan'] = $detail?->copies ?? '-';
        $rows['Tanggal diserahkan'] = $fmt($transaction->actual_handover_date);
    } else {
        $pickup = $fmt($transaction->planned_date);
        if ($time($detail?->pickup_time_start)) {
            $pickup .= ', pukul '.$time($detail->pickup_time_start).($time($detail->pickup_time_end) ? ' - '.$time($detail->pickup_time_end) : '');
        }
        $rows['Rencana pengambilan'] = $pickup;
        $rows['Tanggal diserahkan'] = $fmt($transaction->actual_handover_date);
    }

    if ($detail?->receiver_name) {
        $rows['Penerima'] = $detail->receiver_name.($detail->receiver_relation ? ' ('.$detail->receiver_relation.')' : '');
    }

    $rows['Keperluan / catatan siswa'] = $transaction->description ?: '-';
    $rows['Status persetujuan'] = $transaction->approval_status->label();
    $rows['Status proses'] = $transaction->process_status->label();
    $rows['Catatan petugas'] = $transaction->admin_note ?: '-';
    $rows['Diproses oleh'] = $transaction->processor?->name ?? '-';
    $rows['Selesai pada'] = $transaction->completed_at ? $transaction->completed_at->translatedFormat('d M Y H:i') : '-';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Pengajuan {{ $transaction->transaction_code }}</title>
    <style>
        @page { margin: 18mm 16mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 17px; margin: 0 0 2px; }
        h2 { font-size: 12px; margin: 18px 0 6px; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; }
        .kv td { border: 1px solid #aaa; padding: 5px 7px; vertical-align: top; }
        .kv td:first-child { width: 32%; background: #f0f0f0; font-weight: bold; }
        .hist th, .hist td { border: 1px solid #aaa; padding: 4px 6px; text-align: left; vertical-align: top; font-size: 10px; }
        .hist th { background: #e9e9e9; }
        tr { page-break-inside: avoid; }
        .foot { margin-top: 14px; font-size: 9px; color: #555; }
        .sign { margin-top: 26px; }
        .sign td { border: 0; text-align: center; width: 50%; }
    </style>
</head>
<body>
    <h1>Bukti Pengajuan Layanan Dokumen</h1>
    <div class="muted">Maivile &mdash; Tata Usaha Sekolah</div>

    <h2>Data Pengajuan</h2>
    <table class="kv">
        @foreach ($rows as $label => $value)
            <tr><td>{{ $label }}</td><td>{{ $value }}</td></tr>
        @endforeach
    </table>

    <h2>Riwayat Status</h2>
    <table class="hist">
        <thead>
            <tr><th>Waktu</th><th>Status</th><th>Catatan</th><th>Oleh</th></tr>
        </thead>
        <tbody>
            @forelse ($transaction->histories as $h)
                <tr>
                    <td>{{ $h->changed_at?->translatedFormat('d M Y H:i') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $h->new_status)) }}</td>
                    <td>{{ $h->note ?: '-' }}</td>
                    <td>{{ $h->changer?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;">Belum ada riwayat.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign">
        <tr>
            <td>Pemohon<br><br><br><br>( {{ $transaction->user?->name }} )</td>
            <td>Petugas Tata Usaha<br><br><br><br>( {{ $transaction->processor?->name ?? '..............................' }} )</td>
        </tr>
    </table>

    <div class="foot">
        Dicetak oleh {{ $printedBy }} pada {{ $printedAt->translatedFormat('d M Y H:i') }}.
        Dokumen ini adalah bukti pengajuan, bukan bukti persetujuan. Status yang berlaku adalah status pada saat dokumen dicetak.
    </div>
</body>
</html>
