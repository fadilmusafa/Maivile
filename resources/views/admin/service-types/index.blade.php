@extends('layouts.app')

@section('title', 'Jenis Layanan')

@section('content')
@php
    $accent = ['pinjam_rapor' => 'var(--mv-green)', 'legalisir' => 'var(--mv-purple)', 'pengambilan_ijazah' => 'var(--mv-orange)'];
    $icon = ['pinjam_rapor' => 'bi-journal-bookmark-fill', 'legalisir' => 'bi-patch-check-fill', 'pengambilan_ijazah' => 'bi-mortarboard-fill'];
@endphp

<p class="text-secondary">Tiga layanan inti memiliki alur status yang tetap, sehingga kodenya tidak dapat diubah. Anda dapat mengubah nama, keterangan, dan mengaktifkan atau menonaktifkan layanan.</p>

<div class="row g-3">
    @foreach ($serviceTypes as $s)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 mv-card-hover overflow-hidden">
                <div class="d-flex h-100">
                    <div class="mv-service-accent" style="background: {{ $accent[$s->code] ?? 'var(--mv-blue)' }}"></div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi {{ $icon[$s->code] ?? 'bi-gear-fill' }} fs-4" style="color: {{ $accent[$s->code] ?? 'var(--mv-blue)' }}" aria-hidden="true"></i>
                            <h2 class="h5 mb-0">{{ $s->name }}</h2>
                        </div>
                        <p class="text-secondary flex-grow-1">{{ $s->description ?: 'Belum ada keterangan.' }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <x-badge :color="$s->is_active ? 'success' : 'secondary'">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                                <span class="small text-secondary ms-2">{{ $s->transactions_count }} transaksi</span>
                            </div>
                            <a href="{{ route('admin.service-types.edit', $s) }}" class="btn btn-sm btn-outline-light">
                                <i class="bi bi-pencil me-1" aria-hidden="true"></i>Ubah
                            </a>
                        </div>
                        <div class="small text-secondary mt-2">Kode: <code>{{ $s->code }}</code></div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
