@php
    $typeLabels = ['requirement' => 'Persyaratan', 'power_of_attorney' => 'Surat kuasa', 'handover_proof' => 'Bukti serah terima'];
@endphp
@if ($transaction->files->isEmpty())
    <p class="text-secondary mb-0">Belum ada lampiran.</p>
@else
    <ul class="list-group list-group-flush">
        @foreach ($transaction->files as $f)
            <li class="list-group-item bg-transparent text-white px-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="min-w-0">
                    <i class="bi bi-paperclip me-1" aria-hidden="true"></i>{{ $f->original_name }}
                    <div class="small text-secondary">{{ $typeLabels[$f->type] ?? $f->type }} &middot; {{ number_format($f->file_size / 1024, 0) }} KB &middot; {{ $f->created_at->translatedFormat('d M Y H:i') }}</div>
                </div>
                <div class="text-nowrap">
                    <a href="{{ route('transaction-files.download', [$f, 'inline' => 1]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-light"><i class="bi bi-eye me-1" aria-hidden="true"></i>Lihat</a>
                    <a href="{{ route('transaction-files.download', $f) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-download me-1" aria-hidden="true"></i>Unduh</a>
                </div>
            </li>
        @endforeach
    </ul>
@endif
