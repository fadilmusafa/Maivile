@extends('layouts.app')

@section('title', 'Riwayat Pengajuan')

@section('content')
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('student.loan.create') }}" class="btn btn-sm" style="background: var(--mv-green); color: #000"><i class="bi bi-journal-bookmark-fill me-1" aria-hidden="true"></i>Pinjam Rapor</a>
    <a href="{{ route('student.legalization.create') }}" class="btn btn-sm text-white" style="background: var(--mv-purple)"><i class="bi bi-patch-check-fill me-1" aria-hidden="true"></i>Legalisir</a>
    <a href="{{ route('student.diploma.create') }}" class="btn btn-sm" style="background: var(--mv-orange); color: #000"><i class="bi bi-mortarboard-fill me-1" aria-hidden="true"></i>Ambil Ijazah</a>
</div>

<div class="card">
    <div class="card-body p-3 p-lg-4">
        <form method="GET" class="d-flex flex-wrap gap-2 mb-3" role="search">
            <input type="search" name="q" value="{{ $search }}" class="form-control" style="max-width: 220px" placeholder="Cari kode pengajuan" aria-label="Cari kode pengajuan">
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
            <button class="btn btn-outline-light" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Cari</button>
            @if ($search !== '' || $serviceId || $approval || $process) <a class="btn btn-link" href="{{ route('student.transactions.index') }}">Reset</a> @endif
        </form>

        @if ($transactions->isEmpty())
            <div class="text-center text-secondary py-5"><i class="bi bi-inbox fs-1 d-block mb-2" aria-hidden="true"></i>Belum ada pengajuan yang cocok.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 mv-cards">
                    <thead>
                        <tr>
                            <th scope="col">Kode</th>
                            <th scope="col">Layanan</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Persetujuan</th>
                            <th scope="col">Proses</th>
                            <th scope="col" class="text-end">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $t)
                            <tr>
                                <td data-label="Kode" class="fw-semibold">{{ $t->transaction_code }}</td>
                                <td data-label="Layanan">{{ $t->serviceType->name }}</td>
                                <td data-label="Tanggal">{{ $t->request_date->translatedFormat('d M Y') }}</td>
                                <td data-label="Persetujuan"><x-badge :color="$t->approval_status->badge()">{{ $t->approval_status->label() }}</x-badge></td>
                                <td data-label="Proses"><x-badge :color="$t->process_status->badge()">{{ $t->process_status->label() }}</x-badge></td>
                                <td data-label="Detail" class="text-end"><a href="{{ route('student.transactions.show', $t) }}" class="btn btn-sm btn-outline-light">Buka</a></td>
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
