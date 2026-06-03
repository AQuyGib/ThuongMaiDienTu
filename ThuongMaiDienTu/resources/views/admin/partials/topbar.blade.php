@php
    $rawTitle = $__env->getSections()['page-title'] ?? ($__env->getSections()['title'] ?? 'Dashboard');
    $props = [
        'pageTitle' => html_entity_decode($rawTitle, ENT_QUOTES, 'UTF-8'),
        'todayDate' => now()->format('d/m/Y')
    ];
    $adminUnreadCount = \App\Models\Notification::where('user_id', auth()->id())->unread()->count();
@endphp

<div id="joly-admin-topbar" data-props='{!! json_encode($props, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}'>
    {{-- Static fallback or loader --}}
    <header class="h-28 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-10 z-30 shrink-0 sticky top-0">
        <div class="flex items-center gap-8">
            <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-2xl animate-pulse"></div>
            <div class="flex flex-col gap-2">
                <div class="h-6 w-32 bg-slate-100 rounded-lg animate-pulse"></div>
                <div class="h-3 w-48 bg-slate-50 rounded-lg animate-pulse"></div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700 font-bold text-sm hover:bg-indigo-100 transition relative">
                <i class="fa-regular fa-bell"></i>
                Thông báo
                <span id="adminUnreadBadge" class="absolute -top-2 -right-2 min-w-5 h-5 px-1 rounded-full bg-rose-600 text-white text-[10px] font-black flex items-center justify-center {{ $adminUnreadCount > 0 ? '' : 'hidden' }}">{{ $adminUnreadCount > 99 ? '99+' : $adminUnreadCount }}</span>
            </a>
            <a href="{{ route('admin.notifications.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition">
                <i class="fa-solid fa-paper-plane"></i>
                Tạo mới
            </a>
        </div>
    </header>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const badge = document.getElementById('adminUnreadBadge');
    const endpoint = '{{ route('admin.notifications.unread-count') }}';

    const refreshBadge = () => {
        fetch(endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.ok ? response.json() : null)
            .then(data => {
                if (!badge || !data) return;
                const count = Number(data.unread_count || 0);
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.toggle('hidden', count <= 0);
            })
            .catch(() => {});
    };

    refreshBadge();
    setInterval(refreshBadge, 30000);
});
</script>
@endpush
