@if ($transaction->histories->isEmpty())
    <p class="text-secondary mb-0">Belum ada riwayat.</p>
@else
    <ol class="list-unstyled mb-0">
        @foreach ($transaction->histories->sortByDesc('changed_at') as $h)
            @php $st = \App\Enums\ProcessStatus::tryFrom($h->new_status); @endphp
            <li class="d-flex gap-3 pb-3">
                <i class="bi bi-circle-fill small mt-1 text-secondary" aria-hidden="true"></i>
                <div>
                    <div>
                        <x-badge :color="$st?->badge() ?? 'secondary'">{{ $st?->label() ?? ucfirst(str_replace('_', ' ', $h->new_status)) }}</x-badge>
                        <span class="small text-secondary ms-1">{{ $h->changed_at->translatedFormat('d M Y H:i') }} &middot; {{ $h->changer?->name ?? '-' }}</span>
                    </div>
                    @if ($h->note)<div class="mt-1">{{ $h->note }}</div>@endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
