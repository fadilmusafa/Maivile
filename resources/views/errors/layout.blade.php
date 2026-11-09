<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') &middot; Maivile</title>
    @include('partials.assets')
</head>
<body>
<main class="mv-error">
    <div>
        <div class="mv-error-code">@yield('code')</div>
        <h1 class="h3 fw-bold mt-3">@yield('title')</h1>
        <p class="text-secondary mb-4">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke beranda</a>
    </div>
</main>
</body>
</html>
