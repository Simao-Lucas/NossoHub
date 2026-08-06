@props([
    'href' => null,
    'hover' => true,
])

@php
    $classes = 'nh-card block overflow-hidden '.($hover ? 'nh-card-hover' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </div>
@endif
