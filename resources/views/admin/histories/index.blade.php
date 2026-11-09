@extends('layouts.app')

@section('title', 'Riwayat Status')

@section('content')
<div class="card">
    <div class="card-body p-3 p-lg-4">
        <form method="GET" class="d-flex flex-wrap gap-2 mb-3" role="search">
            <input type="search" name="q" value="{{ $search }}" class="form-control" style="max-width: 240px" placeholder="Cari kode, nama, atau NIS" aria-label="Cari riwayat">
            <select name="service_type_id" class="form-select" style="max-width: 200px" aria-label="Filter layanan">
                <option value="">Semua layanan</option>
                @foreach ($serviceTypes as $s)
                    <option value="{{ $s->id }}" @selected($serviceId === $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select" style="max-width: 210px" aria-label="Filter status tujuan">
                <option value="">Semua status tujuan</option>
                @foreach (\App\Enums\ProcessStatus::cases() as $p)
                    <option value="{{ $p->value }}" @selected($status === $p->value)>{{ $p->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ $from }}" class="form-control" style="max-width: 160px" aria-label="Dari tanggal">
            <input type="date" name="to" value="{{ $to }}" class="form-control" style="max-width: 160px" aria-label="Sampai tanggal">
            <button class="btn btn-outline-light" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Cari</button>
            @if ($search !== '' || $serviceId || $status || $from || $to)
                <a class="btn btn-link" href="{{ route('admin.histories.index') }}">Reset</a>
            @endif
        </form>

        @if ($histories->isEmpty())
            <div class="text-center text-secondary py-5">
                <i class="bi bi-clock-history fs-1 d-block mb-2" aria-hidden="true"></i>
                Tidak ada riwayat yang cocok.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Waktu</th>
                            <th scope="col">Pengajuan</th>
                            <th scope="col">Layanan</th>
                            <th scope="col">Perubahan status</th>
                            <th scope="col">Catatan</th>
                            <th scope="col">Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($histories as $h)
                            @php
                                $old = $h->old_status ? \App\Enums\ProcessStatus::tryFrom($h->old_status) : null;
                                $new = \App\Enums\ProcessStatus::tryFrom($h->new_status);
                            @endphp
                            <tr>
                                <td class="text-nowrap">{{ $h->changed_at->translatedFormat('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.transactions.show', $h->transaction) }}" class="fw-semibold">{{ $h->transaction->transaction_code }}</a>
                                    <div class="small text-secondary">{{ $h->transaction->user->name }} &middot; {{ $h->transaction->user->nis }}</div>
                                </td>
                                <td>{{ $h->transaction->serviceType->name }}</td>
                                <td class="text-nowrap">
                                    @if ($old)
                                        <x-badge :color="$old->badge()">{{ $old->label() }}</x-badge>
                                        <i class="bi bi-arrow-right mx-1 text-secondary" aria-label="menjadi"></i>
                                    @endif
                                    <x-badge :color="$new?->badge() ?? 'secondary'">{{ $new?->label() ?? ucfirst(str_replace('_', ' ', $h->new_status)) }}</x-badge>
                                </td>
                                <td class="text-secondary">{{ \Illuminate\Support\Str::limit($h->note, 80) ?: '-' }}</td>
                                <td>{{ $h->changer?->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="mt-3">{{ $histories->links() }}</div>
@endsection
