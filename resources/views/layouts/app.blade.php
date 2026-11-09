<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') &middot; Maivile</title>
    @include('partials.assets')
</head>
<body>
<div class="d-lg-flex">
    @include('partials.sidebar')

    <div class="mv-main flex-grow-1">
        <header class="mv-header">
            <button class="btn btn-outline-light d-lg-none" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#mvSidebar" aria-controls="mvSidebar" aria-label="Buka menu">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="ms-auto d-flex align-items-center gap-3">
                <x-notification-bell />
                <div class="text-end d-none d-sm-block">
                    <div class="fw-semibold lh-sm">{{ auth()->user()->name }}</div>
                    <small class="text-secondary">{{ auth()->user()->roleLabel() }}</small>
                </div>
            </div>
        </header>

        <main class="p-3 p-lg-4">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>
</div>

<div class="modal fade" id="mvConfirmModal" tabindex="-1" aria-labelledby="mvConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="mvConfirmTitle">Konfirmasi tindakan</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body mv-confirm-text"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger mv-confirm-ok">Ya, lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/maivile.js') }}"></script>
@stack('scripts')
</body>
</html>
