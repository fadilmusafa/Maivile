<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Maivile')</title>
    <meta name="description" content="Maivile: ajukan peminjaman rapor, legalisir, dan pengambilan ijazah secara online, lalu pantau statusnya.">
    @include('partials.assets')
    <link href="{{ asset('css/maivile-landing.css') }}" rel="stylesheet">
    <link href="{{ asset('css/maivile-public.css') }}" rel="stylesheet">
</head>
<body>
@yield('content')
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
