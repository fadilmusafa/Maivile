<div class="dropdown">
    <button class="btn btn-outline-light position-relative mv-bell" type="button" data-bs-toggle="dropdown"
            data-bs-auto-close="outside" aria-expanded="false"
            aria-label="Notifikasi{{ $unread > 0 ? ', '.$unread.' belum dibaca' : '' }}">
        <i class="bi bi-bell-fill" aria-hidden="true"></i>
        @if ($unread > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                {{ $unread > 99 ? '99+' : $unread }}
            </span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-end p-0 mv-bell-menu">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <strong>Notifikasi</strong>
            @if ($unread > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        @forelse ($latest as $n)
            <form method="POST" action="{{ route('notifications.open', $n->id) }}">
                @csrf
                <button type="submit" class="mv-notif-item {{ $n->read_at ? '' : 'unread' }}">
                    <span class="fw-semibold d-block">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                    <span class="d-block small text-secondary">{{ $n->data['message'] ?? '' }}</span>
                    <span class="d-block small text-secondary">{{ $n->created_at->diffForHumans() }}</span>
                </button>
            </form>
        @empty
            <div class="text-center text-secondary small py-4">Belum ada notifikasi.</div>
        @endforelse

        <a href="{{ route('notifications.index') }}" class="d-block text-center py-2 border-top small">Lihat semua notifikasi</a>
    </div>
</div>
