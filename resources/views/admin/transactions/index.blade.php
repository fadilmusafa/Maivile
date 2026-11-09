@extends('layouts.app')

@section('title', 'Antrean Pengajuan')

@section('content')
<ul class="nav nav-pills mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'queue' ? 'active' : '' }}" href="{{ route('admin.transactions.index') }}">
            Perlu tindakan <span class="badge rounded-pill text-bg-secondary ms-1">{{ $queueCount }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="{{ route('admin.transactions.index', ['tab' => 'all']) }}">Semua pengajuan</a>
    </li>
</ul>

<div class="card">
    <div class="card-body p-3 p-lg-4">
        <form method="GET" class="d-flex flex-wrap gap-2 mb-3" role="search">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="search" name="q" value="{{ $search }}" class="form-control" style="max-width: 240px" placeholder="Cari kode, nama, atau NIS" aria-label="Cari pengajuan">
            <select name="service_type_id" class="form-select" style="max-width: 200px" aria-label="Filter layanan">
                <option value="">Semua layanan</option>
                @foreach ($serviceTypes as $s)
                    <option value="{{ $s->id }}" @selected($serviceId === $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="approval" class="form-select" style="max-width: 170px" aria-label="Filter persetujuan">
                <option value="">Semua persetujuan</option>
                @foreach (\App\Enums\ApprovalStatus::cases() as $a)
                    <option value="{{ $a->value }}" @selected($approval === $a->value)>{{ $a->label() }}</option>
                @endforeach
            </select>
            <select name="process" class="form-select" style="max-width: 200px" aria-label="Filter proses">
                <option value="">Semua proses</option>
                @foreach (\App\Enums\ProcessStatus::cases() as $p)
                    <option value="{{ $p->value }}" @selected($process === $p->value)>{{ $p->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ $from }}" class="form-control" style="max-width: 160px" aria-label="Dari tanggal">
            <input type="date" name="to" value="{{ $to }}" class="form-control" style="max-width: 160px" aria-label="Sampai tanggal">
            <button class="btn btn-outline-light" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Cari</button>
            @if ($search !== '' || $serviceId || $approval || $process || $from || $to)
                <a class="btn btn-link" href="{{ route('admin.transactions.index', ['tab' => $tab]) }}">Reset</a>
            @endif
        </form>

        @if ($transactions->isEmpty())
            <div class="text-center text-secondary py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2" aria-hidden="true"></i>
                {{ $tab === 'queue' ? 'Tidak ada pengajuan yang menunggu tindakan.' : 'Tidak ada pengajuan yang cocok.' }}
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 mv-cards">
                    <thead>
                        <tr>
                            <th scope="col">Kode</th>
                            <th scope="col">Siswa</th>
                            <th scope="col">Layanan</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Persetujuan</th>
                            <th scope="col">Proses</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $t)
                            <tr>
                                <td data-label="Kode" class="fw-semibold">{{ $t->transaction_code }}</td>
                                <td data-label="Siswa">{{ $t->user->name }}<div class="small text-secondary">{{ $t->user->nis }}@if ($t->user->class_name) &middot; {{ $t->user->class_name }}@endif</div></td>
                                <td data-label="Layanan">{{ $t->serviceType->name }}</td>
                                <td data-label="Tanggal">{{ $t->request_date->translatedFormat('d M Y') }}</td>
                                <td data-label="Persetujuan"><x-badge :color="$t->approval_status->badge()">{{ $t->approval_status->label() }}</x-badge></td>
                                <td data-label="Proses"><x-badge :color="$t->process_status->badge()">{{ $t->process_status->label() }}</x-badge></td>
                                <td data-label="Aksi" class="text-end">
                                    <a href="{{ route('admin.transactions.show', $t) }}" class="btn btn-sm {{ $t->process_status->value === 'menunggu_verifikasi' ? 'btn-primary' : 'btn-outline-light' }}">
                                        {{ $t->process_status->value === 'menunggu_verifikasi' ? 'Verifikasi' : 'Buka' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
@endsection
