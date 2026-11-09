@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h2 class="h3 fw-bold mb-1">Halo, {{ $user->name }}</h2>
    <p class="text-secondary mb-0">
        NIS {{ $user->nis ?? '-' }}
        @if ($user->class_name) &middot; Kelas {{ $user->class_name }} @endif
    </p>
</div>

<h3 class="h5 mb-3">Ajukan Layanan</h3>
<div class="row g-3 mb-4">
    @foreach ($serviceSummary as $row)
        <div class="col-md-4">
            <div class="card mv-service-card h-100" style="background: {{ $row['style']['color'] }}; color: {{ $row['style']['text'] }}; border: 0">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-bold fs-5">{{ $row['service']->name }}</div>
                        <i class="bi {{ $row['style']['icon'] }} fs-3" aria-hidden="true"></i>
                    </div>
                    <div class="small mb-3">{{ $row['total'] }} pengajuan &middot; {{ $row['active'] }} masih berjalan</div>
                    @if ($row['service']->is_active && $row['style']['route'])
                        <a href="{{ route($row['style']['route']) }}" class="btn btn-dark mt-auto align-self-start">
                            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ $row['style']['cta'] }}
                        </a>
                    @else
                        <span class="mt-auto small fw-semibold">Layanan sedang tidak tersedia.</span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    @foreach ($cards as $card)
        <div class="col-6 col-lg">
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

<div class="card">
    <div class="card-body p-4">
        <h3 class="h5 mb-3">Pengajuan Terbaru</h3>

        @if ($recent->isEmpty())
            <div class="text-center text-secondary py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2" aria-hidden="true"></i>
                Anda belum memiliki pengajuan.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Kode</th>
                            <th scope="col">Layanan</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Persetujuan</th>
                            <th scope="col">Proses</th>
                            <th scope="col">Catatan petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recent as $t)
                            <tr>
                                <td class="fw-semibold">{{ $t->transaction_code }}</td>
                                <td>{{ $t->serviceType->name }}</td>
                                <td>{{ $t->request_date->translatedFormat('d M Y') }}</td>
                                <td><x-badge :color="$t->approval_status->badge()">{{ $t->approval_status->label() }}</x-badge></td>
                                <td><x-badge :color="$t->process_status->badge()">{{ $t->process_status->label() }}</x-badge></td>
                                <td class="text-secondary">{{ \Illuminate\Support\Str::limit($t->admin_note, 60) ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body p-4">
                <h3 class="h5 mb-3">Catatan Terbaru dari Petugas</h3>
                @if ($latestNotes->isEmpty())
                    <p class="text-secondary mb-0">Belum ada catatan dari petugas.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($latestNotes as $n)
                            <li class="py-2 border-bottom">
                                <div class="d-flex justify-content-between gap-2">
                                    <a href="{{ route('student.transactions.show', $n) }}" class="fw-semibold">{{ $n->transaction_code }}</a>
                                    <span class="small text-secondary text-nowrap">{{ $n->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="small text-secondary">{{ $n->serviceType->name }}</div>
                                <div>{{ \Illuminate\Support\Str::limit($n->admin_note, 140) }}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h5 mb-0">Notifikasi
                        @if ($unreadCount > 0)<span class="badge rounded-pill text-bg-primary ms-1">{{ $unreadCount }} baru</span>@endif
                    </h3>
                    <a href="{{ route('notifications.index') }}" class="small">Lihat semua</a>
                </div>
                @if ($notifications->isEmpty())
                    <p class="text-secondary mb-0">Belum ada notifikasi.</p>
                @else
                    <div class="mv-notif-list">
                        @foreach ($notifications as $n)
                            <form method="POST" action="{{ route('notifications.open', $n->id) }}">
                                @csrf
                                <button type="submit" class="mv-notif-item {{ $n->read_at ? '' : 'unread' }}">
                                    <span class="fw-semibold d-block">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                                    <span class="d-block small text-secondary">{{ $n->data['message'] ?? '' }}</span>
                                    <span class="d-block small text-secondary">{{ $n->created_at->diffForHumans() }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

