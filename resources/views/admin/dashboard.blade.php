@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="mb-4">
    <h2 class="h3 fw-bold mb-1">Halo, {{ auth()->user()->name }}</h2>
    <p class="text-secondary mb-0">Ringkasan seluruh layanan administrasi dokumen akademik.</p>
</div>

<div class="row g-3 mb-4">
    @foreach ($cards as $card)
        <div class="col-6 col-xl-3">
            <div class="card mv-stat mv-card-hover h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="mv-stat-icon" style="color: {{ $card['color'] }}"><i class="bi {{ $card['icon'] }}" aria-hidden="true"></i></div>
                    <div>
                        <div class="mv-stat-value">{{ number_format($card['value']) }}</div>
                        <div class="text-secondary small">{{ $card['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<h3 class="h5 mb-3">Ringkasan per Layanan</h3>
<div class="row g-3 mb-4">
    @foreach ($serviceSummary as $row)
        <div class="col-md-4">
            <a href="{{ route('admin.transactions.index', ['tab' => 'all', 'service_type_id' => $row['service']->id]) }}"
               class="card mv-service-card h-100 text-decoration-none" style="background: {{ $row['style']['color'] }}; color: {{ $row['style']['text'] }}; border: 0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="fw-bold fs-5">{{ $row['service']->name }}</div>
                        <i class="bi {{ $row['style']['icon'] }} fs-3" aria-hidden="true"></i>
                    </div>
                    <div class="mv-service-total">{{ number_format($row['total']) }}</div>
                    <div class="small fw-semibold">total pengajuan</div>
                    <div class="d-flex flex-wrap gap-3 small mt-2">
                        <span>{{ $row['waiting'] }} menunggu verifikasi</span>
                        <span>{{ $row['active'] }} diproses</span>
                        <span>{{ $row['done'] }} selesai</span>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h5 mb-0">Perlu Tindakan Petugas</h3>
                    <a href="{{ route('admin.transactions.index') }}" class="small">Buka antrean ({{ $needsActionCount }})</a>
                </div>

                @if ($needsAction->isEmpty())
                    <div class="text-center text-secondary py-4">
                        <i class="bi bi-check2-circle fs-1 d-block mb-2" aria-hidden="true"></i>
                        Tidak ada pengajuan yang menunggu tindakan.
                    </div>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($needsAction as $t)
                            <li class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 border-bottom">
                                <div>
                                    <span class="fw-semibold">{{ $t->transaction_code }}</span>
                                    <span class="text-secondary">&middot; {{ $t->serviceType->name }}</span>
                                    <div class="small text-secondary">{{ $t->user->name }} &middot; {{ $t->user->nis }}</div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <x-badge :color="$t->process_status->badge()">{{ $t->process_status->label() }}</x-badge>
                                    <a href="{{ route('admin.transactions.show', $t) }}" class="btn btn-sm {{ $t->process_status->value === 'menunggu_verifikasi' ? 'btn-primary' : 'btn-outline-light' }}">
                                        {{ $t->process_status->value === 'menunggu_verifikasi' ? 'Verifikasi' : 'Buka' }}
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body p-4">
                <h3 class="h5 mb-1">Pengajuan per Bulan</h3>
                <p class="small text-secondary">6 bulan terakhir, berdasarkan tanggal pengajuan.</p>
                <div class="mv-bars" role="group" aria-label="Jumlah pengajuan per bulan">
                    @foreach ($monthly as $m)
                        <div class="mv-bar-col">
                            <div class="mv-bar-value">{{ $m['count'] }}</div>
                            <div class="mv-bar-track"><div class="mv-bar" style="height: {{ $m['pct'] }}%"></div></div>
                            <div class="mv-bar-label">{{ $m['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-4">
        <h3 class="h5 mb-3">Pengajuan Terbaru</h3>

        @if ($recent->isEmpty())
            <div class="text-center text-secondary py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2" aria-hidden="true"></i>
                Belum ada pengajuan.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Kode</th>
                            <th scope="col">Siswa</th>
                            <th scope="col">Layanan</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Persetujuan</th>
                            <th scope="col">Proses</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recent as $t)
                            <tr>
                                <td class="fw-semibold">{{ $t->transaction_code }}</td>
                                <td>{{ $t->user->name }}<div class="small text-secondary">{{ $t->user->nis }}</div></td>
                                <td>{{ $t->serviceType->name }}</td>
                                <td>{{ $t->request_date->translatedFormat('d M Y') }}</td>
                                <td><x-badge :color="$t->approval_status->badge()">{{ $t->approval_status->label() }}</x-badge></td>
                                <td><x-badge :color="$t->process_status->badge()">{{ $t->process_status->label() }}</x-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
