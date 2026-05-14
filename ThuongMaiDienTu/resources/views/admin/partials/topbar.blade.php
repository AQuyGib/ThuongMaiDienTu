@php
    $rawTitle = $__env->getSections()['page-title'] ?? ($__env->getSections()['title'] ?? 'Dashboard');
    $props = [
        'pageTitle' => html_entity_decode($rawTitle, ENT_QUOTES, 'UTF-8'),
        'todayDate' => now()->format('d/m/Y')
    ];
@endphp

<div id="joly-admin-topbar" data-props='{!! json_encode($props, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}'>
    {{-- Static fallback or loader --}}
    <header class="h-24 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-10 z-30 shrink-0 sticky top-0">
        <div class="flex items-center gap-8">
            <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-2xl animate-pulse"></div>
            <div class="flex flex-col gap-2">
                <div class="h-6 w-32 bg-slate-100 rounded-lg animate-pulse"></div>
                <div class="h-3 w-48 bg-slate-50 rounded-lg animate-pulse"></div>
            </div>
        </div>
    </header>
</div>
