<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pengajuan &middot; Maivile</title>
    <style>
        @page { margin: 12mm; }
        body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 10px; color: #111; background: #fff; margin: 0; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        h2 { font-size: 12px; margin: 14px 0 6px; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background: #e9e9e9; font-size: 9px; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .num { text-align: right; }
        .meta td { border: 0; padding: 1px 8px 1px 0; width: auto; }
        .sign { margin-top: 28px; width: 100%; }
        .sign td { border: 0; }
        .note { margin-top: 8px; padding: 6px; border: 1px solid #c90; background: #fff7e0; }
        @if ($mode === 'print')
        .toolbar { padding: 10px 12px; background: #f1f1f1; border-bottom: 1px solid #ccc; margin-bottom: 12px; }
        .toolbar button, .toolbar a { font-size: 13px; padding: 6px 12px; margin-right: 6px; cursor: pointer; }
        .wrap { padding: 0 12px 20px; }
        @media print { .toolbar { display: none; } .wrap { padding: 0; } @page { size: A4 landscape; margin: 12mm; } }
        @endif
    </style>
</head>
<body>
@if ($mode === 'print')
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak</button>
        <a href="{{ $backUrl }}">Kembali</a>
    </div>
    <div class="wrap">
@endif

<h1>Laporan Pengajuan Layanan Dokumen</h1>
<div class="muted">Maivile &mdash; Tata Usaha Sekolah</div>

<table class="meta" style="margin-top:8px; width:auto;">
    @foreach ($filters as $label => $value)
        <tr><td class="muted">{{ $label }}</td><td>: {{ $value }}</td></tr>
    @endforeach
    <tr><td class="muted">Dicetak oleh</td><td>: {{ $printedBy }}, {{ $printedAt->translatedFormat('d M Y H:i') }}</td></tr>
</table>

<h2>Ringkasan ({{ $summary['total'] }} pengajuan)</h2>
<table>
    <thead>
        <tr>
            <th>Layanan</th>
            <th class="num">Total</th>
            <th class="num">Pending</th>
            <th class="num">Approved</th>
            <th class="num">Rejected</th>
            <th class="num">Selesai</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($summary['by_service'] as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td class="num">{{ $row['total'] }}</td>
                <td class="num">{{ $row['pending'] }}</td>
                <td class="num">{{ $row['approved'] }}</td>
                <td class="num">{{ $row['rejected'] }}</td>
                <td class="num">{{ $row['selesai'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>Daftar Pengajuan</h2>
@if ($truncated)
    <div class="note">Data dipotong: hanya {{ $limit }} pengajuan terbaru yang dicetak dari total {{ $summary['total'] }}. Persempit periode, atau gunakan unduhan Excel untuk data lengkap.</div>
@endif
<table style="margin-top:6px;">
    <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Tanggal</th>
            <th>Siswa</th>
            <th>Layanan</th>
            <th>Persetujuan</th>
            <th>Proses</th>
            <th>Selesai</th>
            <th>Catatan Petugas</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($transactions as $t)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{{ $t->transaction_code }}</td>
                <td>{{ $t->request_date->translatedFormat('d M Y') }}</td>
                <td>{{ $t->user?->name }}@if ($t->user?->nis) ({{ $t->user->nis }})@endif</td>
                <td>{{ $t->serviceType?->name }}</td>
                <td>{{ $t->approval_status->label() }}</td>
                <td>{{ $t->process_status->label() }}</td>
                <td>{{ $t->completed_at?->translatedFormat('d M Y') ?? '-' }}</td>
                <td>{{ $t->admin_note ?: '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="9" style="text-align:center;">Tidak ada pengajuan pada filter ini.</td></tr>
        @endforelse
    </tbody>
</table>

<table class="sign">
    <tr>
        <td style="width:70%;"></td>
        <td style="text-align:center;">
            {{ $printedAt->translatedFormat('d F Y') }}<br>Petugas Tata Usaha<br><br><br><br>
            ( {{ $printedBy }} )
        </td>
    </tr>
</table>

@if ($mode === 'print')
    </div>
@endif
</body>
</html>
