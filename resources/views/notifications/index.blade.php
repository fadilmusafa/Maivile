@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('notifications.index') }}">Semua</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $filter === 'unread' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'unread']) }}">
                Belum dibaca <span class="badge rounded-pill text-bg-secondary ms-1">{{ $unreadCount }}</span>
            </a>
        </li>
    </ul>

    @if ($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm"><i class="bi bi-check2-all me-1" aria-hidden="true"></i>Tandai semua dibaca</button>
        </form>
    @endif
</div>

<div class="card">
    <div class="card-body p-0">
        @if ($notifications->isEmpty())
            <div class="text-center text-secondary py-5">
                <i class="bi bi-bell-slash fs-1 d-block mb-2" aria-hidden="true"></i>
                {{ $filter === 'unread' ? 'Tidak ada notifikasi yang belum dibaca.' : 'Belum ada notifikasi.' }}
            </div>
        @else
            @foreach ($notifications as $n)
                <form method="POST" action="{{ route('notifications.open', $n->id) }}">
                    @csrf
                    <button type="submit" class="mv-notif-item mv-notif-row {{ $n->read_at ? '' : 'unread' }}">
                        <span class="d-flex justify-content-between gap-2">
                            <span class="fw-semibold">
                                {{ $n->data['title'] ?? 'Notifikasi' }}
                                @unless ($n->read_at)<span class="badge rounded-pill text-bg-primary ms-1">Baru</span>@endunless
                            </span>
                            <span class="small text-secondary text-nowrap">{{ $n->created_at->translatedFormat('d M Y H:i') }}</span>
                        </span>
                        <span class="d-block">{{ $n->data['message'] ?? '' }}</span>
                        @if (! empty($n->data['note']))
                            <span class="d-block small text-secondary mt-1"><i class="bi bi-chat-left-text me-1" aria-hidden="true"></i>Catatan petugas: {{ $n->data['note'] }}</span>
                        @endif
                    </button>
                </form>
            @endforeach
        @endif
    </div>
</div>

<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
