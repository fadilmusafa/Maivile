@foreach (['success' => ['success', 'bi-check-circle-fill'], 'warning' => ['warning', 'bi-exclamation-triangle-fill'], 'error' => ['danger', 'bi-x-octagon-fill']] as $key => [$class, $icon])
    @if (session($key))
        <div class="alert alert-{{ $class }} alert-dismissible fade show" role="alert">
            <i class="bi {{ $icon }} me-2" aria-hidden="true"></i>{{ session($key) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif
@endforeach
