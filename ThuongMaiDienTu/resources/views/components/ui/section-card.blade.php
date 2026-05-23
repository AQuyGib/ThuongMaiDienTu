@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-5 shadow-sm']) }}>
    @if ($title || $subtitle)
        <div class="mb-4">
            @if ($title)
                <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            @endif
            @if ($subtitle)
                <p class="text-sm text-gray-500">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
