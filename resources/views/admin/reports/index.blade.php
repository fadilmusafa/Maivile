@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="card mb-3">
    <div class="card-body p-3 p-lg-4">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 align-items-end" role="search">
            <div class="col-12 col-md-6 col-xl-2">
                <label for="r_from" class="form-label small text-secondary mb-1">Dari tanggal</label>
                <input type="date" id="r_from" name="from" value="{{ $f['from'] }}" @class(['form-control', 'is-invalid' => $errors->has('from')])>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label for="r_to" class="form-label small text-secondary mb-1">Sampai tanggal</label>
                <input type="date" id="r_to" name="to" value="{{ $f['to'] }}" @class(['form-control', 'is-invalid' => $errors->has('to')])>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label for="r_service" class="form-label small text-secondary mb-1">Layanan</label>
                <select id="r_service" name="service_type_id" class="form-select">
                    <option value="">Semua layanan</option>
                    @foreach ($serviceTypes as $s)
                        <option value="{{ $s->id }}" @selected($f['service_type_id'] === $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label for="r_approval" class="form-label small text-secondary mb-1">Persetujuan</label>
                <select id="r_approval" name="approval" class="form-select">
                    <option value="">Semua</option>
                    @foreach (\App\Enums\ApprovalStatus::cases() as $a)
                        <option value="{{ $a->value }}" @selected($f['approval'] === $a->value)>{{ $a->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label for="r_process" class="form-label small text-secondary mb-1">Proses</label>
                <select id="r_process" name="process" class="form-select">
                    <option value="">Semua</option>
                    @foreach (\App\Enums\ProcessStatus::cases() as $p)
                        <option value="{{ $p->value }}" @selected($f['process'] === $p->value)>{{ $p->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2 d-flex gap-2">
                <button class="btn btn-outline-light flex-grow-1" type="submit"><i class="bi bi-funnel me-1" aria-hidden="true"></i>Terapkan</button>
                @if (count($query))
                    <a class="btn btn-link" href="{{ route('admin.reports.index') }}">Reset</a>
                @endif
            </div>
            @if ($errors->hasAny(['from', 'to', 'service_type_id', 'approval', 'process']))
                <div class="col-12">
                    @foreach (['from', 'to', 'service_type_id', 'approval', 'process'] as $field)
                        @error($field)
                            <div class="text-danger small"><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}</div>
                        @enderror
                    @endforeach
                </div>
            @endif
        </form>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.reports.print', $query) }}" target="_blank" rel="noopener" class="btn btn-outline-light">
        <i class="bi bi-printer me-1" aria-hidden="true"></i>Cetak
    </a>
    <a href="{{ route('admin.reports.pdf', $query) }}" class="btn btn-outline-light">
        <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>Unduh PDF
    </a>
    <a href="{{ route('admin.reports.excel', $query) }}" class="btn btn-primary">
        <i class="bi bi-file-earmark-excel me-1" aria-hidden="true"></i>Unduh Excel
    </a>
</div>

<div class="card mb-3">
    <div class="card-body p-3 p-lg-4">
        <h2 class="h6 mb-3">Ringkasan <span class="text-secondary fw-normal">&middot; {{ $summary['total'] }} pengajuan</span></h2>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Layanan</th>
                        <th scope="col" class="text-end">Total</th>
                        <th scope="col" class="text-end">Pending</th>
                        <th scope="col" class="text-end">Approved</th>
                        <th scope="col" class="text-end">Rejected</th>
                        <th scope="col" class="text-end">Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary['by_service'] as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end fw-semibold">{{ $row['total'] }}</td>
                            <td class="text-end">{{ $row['pending'] }}</td>
                            <td class="text-end">{{ $row['approved'] }}</td>
                            <td class="text-end">{{ $row['rejected'] }}</td>
                            <td class="text-end">{{ $row['selesai'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if (count($summary['by_process']))
            <div class="d-flex flex-wrap gap-2 mt-3">
                @foreach ($summary['by_process'] as $row)
                    <span class="badge rounded-pill text-bg-secondary">{{ $row['label'] }}: {{ $row['total'] }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body p-3 p-lg-4">
        @if ($transactions->isEmpty())
            <div class="text-center text-secondary py-5">
                <i class="bi bi-file-earmark-bar-graph fs-1 d-block mb-2" aria-hidden="true"></i>
                Tidak ada pengajuan pada filter ini.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Kode</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Siswa</th>
                            <th scope="col">Layanan</th>
                            <th scope="col">Persetujuan</th>
                            <th scope="col">Proses</th>
                            <th scope="col">Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $t)
                            <tr>
                                <td class="fw-semibold"><a href="{{ route('admin.transactions.show', $t) }}" class="link-light text-decoration-none">{{ $t->transaction_code }}</a></td>
                                <td>{{ $t->request_date->translatedFormat('d M Y') }}</td>
                                <td>{{ $t->user?->name }}<div class="small text-secondary">{{ $t->user?->nis }}@if ($t->user?->class_name) &middot; {{ $t->user->class_name }}@endif</div></td>
                                <td>{{ $t->serviceType?->name }}</td>
                                <td><x-badge :color="$t->approval_status->badge()">{{ $t->approval_status->label() }}</x-badge></td>
                                <td><x-badge :color="$t->process_status->badge()">{{ $t->process_status->label() }}</x-badge></td>
                                <td>{{ $t->completed_at?->translatedFormat('d M Y') ?? '-' }}</td>
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
