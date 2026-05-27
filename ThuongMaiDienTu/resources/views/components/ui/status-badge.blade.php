@props([
    'status' => 'draft',
])

@php
    $map = [
        'draft' => ['Nháp', 'bg-slate-100 text-slate-700'],
        'issued' => ['Đã phát hành', 'bg-indigo-100 text-indigo-700'],
        'paid' => ['Đã thanh toán', 'bg-emerald-100 text-emerald-700'],
        'cancelled' => ['Đã hủy', 'bg-amber-100 text-amber-700'],
    ];

    [$label, $classes] = $map[$status] ?? [ucfirst($status), 'bg-slate-100 text-slate-700'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold '.$classes]) }}>
    {{ $label }}
</span>
