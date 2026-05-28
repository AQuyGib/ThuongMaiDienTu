@props([
    'variant' => 'secondary',
    'href' => null,
    'icon' => null,
    'title' => null,
])

@php
    $base = 'inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold transition hover:shadow-sm';

    $variants = [
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700',
        'info' => 'bg-sky-600 text-white hover:bg-sky-700',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['secondary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'title' => $title]) }}>
        @if ($icon)
            {!! $icon !!}
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'title' => $title]) }}>
        @if ($icon)
            {!! $icon !!}
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
