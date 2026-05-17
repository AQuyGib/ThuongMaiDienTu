@extends('admin.layouts.master')

@section('title', 'Quản lý thông báo')
@section('page-title', 'Quản lý thông báo')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Quản lý thông báo</h1>
            <p class="text-slate-500 mt-1">Theo dõi toàn bộ thông báo hệ thống, khuyến mãi và gửi tay.</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('admin.notifications.create') }}" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition">
                <i class="fa-solid fa-paper-plane mr-2"></i> Tạo thông báo
            </a>
            <a href="{{ route('notifications.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                <i class="fa-regular fa-bell mr-2"></i> Xem trang người dùng
            </a>
            <form method="POST" action="{{ route('admin.notifications.low-stock-check') }}">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-amber-500 text-white text-sm font-bold hover:bg-amber-600 transition">
                    <i class="fa-solid fa-boxes-stacked mr-2"></i> Quét tồn kho thấp
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Tổng thông báo</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Chưa đọc</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($stats['unread']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Khuyến mãi tự động</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($stats['promo']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Gửi tay</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($stats['manual']) }}</div>
        </div>
    </div>

    @include('admin.notifications.partials.charts')

    @include('partials.notification-filters', [
        'typeOptions' => $typeOptions,
        'filters' => [
            'type' => $selectedType,
            'read' => $selectedRead,
            'recipient' => request('recipient'),
            'from' => request('from'),
            'to' => request('to'),
        ],
        'showRecipient' => true,
        'showDateRange' => true,
        'resetUrl' => route('admin.notifications.index'),
    ])

    <form method="POST" action="{{ route('admin.notifications.bulk-destroy') }}">
        @csrf
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h2 class="font-black text-slate-900 text-lg">Danh sách thông báo gần đây</h2>
                    <p class="text-xs text-slate-500">Hiển thị 20 bản ghi mới nhất</p>
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-bold hover:bg-rose-700 transition">
                    <i class="fa-solid fa-trash mr-2"></i> Xóa đã chọn
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/60 text-slate-400 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 w-10"><input type="checkbox" id="checkAll"></th>
                            <th class="px-6 py-4">Người nhận</th>
                            <th class="px-6 py-4">Tiêu đề</th>
                            <th class="px-6 py-4">Loại</th>
                            <th class="px-6 py-4">Trạng thái</th>
                            <th class="px-6 py-4">Thời gian</th>
                            <th class="px-6 py-4 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($notifications as $notification)
                            <tr class="{{ $notification->read_at ? 'hover:bg-slate-50/50' : 'bg-indigo-50/40' }}">
                                <td class="px-6 py-4"><input type="checkbox" name="notification_ids[]" value="{{ $notification->notification_id }}" class="row-check"></td>
                                <td class="px-6 py-4 font-semibold {{ $notification->read_at ? 'text-slate-700' : 'text-slate-900' }}">{{ $notification->user->full_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold {{ $notification->read_at ? 'text-slate-900' : 'text-indigo-900' }}">{{ $notification->title }}</div>
                                    <div class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $notification->content }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-indigo-700">{{ $typeOptions[$notification->type] ?? $notification->type }}</td>
                                <td class="px-6 py-4">
                                    @if($notification->read_at)
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700">Đã đọc</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-700">Chưa đọc</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $notification->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2 flex-wrap">
                                        <a href="{{ route('admin.notifications.show', $notification->notification_id) }}" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-50">Chi tiết</a>
                                        @unless($notification->read_at)
                                            <form method="POST" action="{{ route('admin.notifications.read', $notification->notification_id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700">Đã đọc</button>
                                            </form>
                                        @endunless
                                        <form method="POST" action="{{ route('admin.notifications.destroy', $notification->notification_id) }}" class="delete-single-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-2 rounded-lg bg-rose-600 text-white text-sm font-bold hover:bg-rose-700">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">Chưa có thông báo nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-100">
                {{ $notifications->links() }}
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dailyCtx = document.getElementById('dailyNotificationsChart');
    const monthlyCtx = document.getElementById('monthlyNotificationsChart');
    const dailyChart = dailyCtx ? new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: @json($dailyChart['labels']),
            datasets: [{
                label: 'Số thông báo',
                data: @json($dailyChart['values']),
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.12)',
                tension: 0.35,
                fill: true,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    }) : null;

    const monthlyChart = monthlyCtx ? new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: @json($monthlyChart['labels']),
            datasets: [{
                label: 'Số thông báo',
                data: @json($monthlyChart['values']),
                borderRadius: 10,
                backgroundColor: '#0f172a',
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    }) : null;

    const checkAll = document.getElementById('checkAll');
    const rowChecks = Array.from(document.querySelectorAll('.row-check'));
    const bulkForm = document.querySelector('form[action="{{ route('admin.notifications.bulk-destroy') }}"]');
    const bulkDeleteBtn = bulkForm?.querySelector('button[type="submit"]');

    if (checkAll) {
        checkAll.addEventListener('change', () => {
            rowChecks.forEach(cb => cb.checked = checkAll.checked);
            syncButtonState();
        });
    }

    const syncButtonState = () => {
        const checkedCount = rowChecks.filter(cb => cb.checked).length;
        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = checkedCount === 0;
            bulkDeleteBtn.classList.toggle('opacity-50', checkedCount === 0);
            bulkDeleteBtn.classList.toggle('cursor-not-allowed', checkedCount === 0);
        }
    };

    rowChecks.forEach(cb => cb.addEventListener('change', syncButtonState));
    syncButtonState();

    if (bulkForm && bulkDeleteBtn && window.Swal) {
        bulkForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const checkedCount = rowChecks.filter(cb => cb.checked).length;
            if (checkedCount === 0) return;

            Swal.fire({
                title: 'Xóa các thông báo đã chọn?',
                text: `Bạn đang chọn xóa ${checkedCount} thông báo. Hành động này không thể hoàn tác.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xóa ngay',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkForm.submit();
                }
            });
        });
    }
});
</script>
@endpush
