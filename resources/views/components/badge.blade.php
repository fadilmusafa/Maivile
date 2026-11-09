@props(['color' => 'secondary'])
@php
    // info -> primary (biru) agar sesuai palet; label teks selalu ditampilkan (tidak hanya warna).
    $map = ['info' => 'primary', 'warning' => 'warning', 'success' => 'success', 'danger' => 'danger', 'secondary' => 'secondary'];
    $bs = $map[$color] ?? 'secondary';
@endphp
<span {{ $attributes->merge(['class' => "badge rounded-pill text-bg-{$bs}"]) }}>{{ $slot }}</span>
