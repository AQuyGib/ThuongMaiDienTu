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
            <button type="button" onclick="openCreateModal()" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition">
                <i class="fa-solid fa-paper-plane mr-2"></i> Tạo thông báo
            </button>
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

<!-- Create Notification Modal -->
<div id="createNotificationModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeCreateModal()"></div>

        <!-- Trick browser to center content -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-middle bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle w-full sm:max-w-4xl border border-slate-100">
            <!-- Header -->
            <div class="px-8 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-indigo-600 text-base animate-pulse"></i>
                        Tạo chiến dịch thông báo mới
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">Gửi thông báo tới hệ thống, khách hàng hoặc cá nhân cụ thể.</p>
                </div>
                <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 transition p-2 rounded-xl hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('admin.notifications.store') }}" class="p-8 space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column: Target & Messages -->
                    <div class="space-y-5">
                        <!-- Target Section -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Đối tượng nhận</label>
                            <select name="target" id="notificationTarget" onchange="handleTargetChange()" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-bold text-slate-700">
                                <option value="all">Tất cả người dùng</option>
                                <option value="users">Khách hàng</option>
                                <option value="admins">Admin / nhân sự nội bộ</option>
                                <option value="specific">Gửi cho tài khoản cụ thể</option>
                            </select>
                        </div>

                        <!-- Specific Users Search and Tag Input -->
                        <div id="specificUsersSection" class="hidden space-y-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Tìm & chọn tài khoản nhận</label>
                            <div class="relative" id="userSearchDropdown">
                                <div class="relative">
                                    <input type="text" id="userQueryInput" placeholder="Nhập tên, email hoặc ID để tìm kiếm..." class="w-full pl-10 pr-4 py-3 text-sm rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                                    <i class="fa-solid fa-user-plus absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                                </div>

                                <!-- User Search Results Dropdown -->
                                <div id="userSearchResults" class="hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 p-2 max-h-48 overflow-y-auto space-y-0.5">
                                    <!-- Ajax result rows -->
                                </div>
                            </div>

                            <!-- Selected tags container -->
                            <div id="selectedUsersContainer" class="flex flex-wrap gap-2 p-3 bg-slate-50 rounded-2xl border border-slate-100 min-h-[60px] items-center">
                                <span class="text-xs text-slate-400" id="noUsersSelectedText">Chưa có tài khoản nào được chọn.</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tiêu đề</label>
                            <input name="title" type="text" required placeholder="Ví dụ: Flash Sale 50% hôm nay" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nội dung thông báo</label>
                            <textarea name="content" rows="5" required placeholder="Mô tả nội dung thông báo chi tiết..." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50"></textarea>
                        </div>
                    </div>

                    <!-- Right Column: Actions & Related items -->
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Đường dẫn hành động (Action URL)</label>
                            <input name="action_url" type="text" placeholder="/products hoặc URL đầy đủ" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50">
                            <span class="text-[10px] text-slate-400 mt-1 block">Khách hàng sẽ chuyển hướng đến link này khi click vào thông báo.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Sản phẩm liên quan</label>
                            <div class="relative" id="productSearchDropdown">
                                <button type="button" onclick="toggleProductDropdown()" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 text-left flex justify-between items-center text-slate-700">
                                    <span id="selectedProductText" class="truncate">-- Không chọn --</span>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
                                </button>
                                <input type="hidden" name="product_id" id="submitProductId" value="">

                                <div id="productDropdownMenu" class="hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 p-3 space-y-3">
                                    <div class="relative">
                                        <input type="text" id="productQueryInput" placeholder="Tìm sản phẩm bằng tên..." class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                    </div>

                                    <div id="productSearchResults" class="max-h-48 overflow-y-auto space-y-1 divide-y divide-slate-50">
                                        <div class="p-3 text-center text-xs text-slate-400 cursor-pointer hover:bg-slate-50 rounded-lg font-medium" onclick="selectProduct('', '-- Không chọn --')">
                                            -- Không chọn --
                                        </div>
                                        @foreach($products as $product)
                                            <div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-xl cursor-pointer transition" onclick="selectProduct('{{ $product->product_id }}', '#{{ $product->product_id }} - {{ addslashes($product->name) }}')">
                                                <img src="{{ $product->thumbnail ?: 'https://via.placeholder.com/40' }}" class="w-8 h-8 object-cover rounded-lg border border-slate-100 shrink-0">
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-800 truncate">{{ $product->name }}</div>
                                                    <div class="text-[10px] text-slate-400 mt-0.5">#{{ $product->product_id }} • {{ number_format($product->base_price) }}đ</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Mã KM / flash sale</label>
                            <div class="relative" id="promoSearchDropdown">
                                <button type="button" onclick="togglePromoDropdown()" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 text-left flex justify-between items-center text-slate-700">
                                    <span id="selectedPromoText" class="truncate">-- Không chọn --</span>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
                                </button>
                                <input type="hidden" name="promo_id" id="submitPromoId" value="">

                                <div id="promoDropdownMenu" class="hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 p-3 space-y-3">
                                    <div class="relative">
                                        <input type="text" id="promoQueryInput" placeholder="Tìm mã KM/flash sale..." class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                    </div>

                                    <div id="promoSearchResults" class="max-h-48 overflow-y-auto space-y-1 divide-y divide-slate-50">
                                        <div class="p-3 text-center text-xs text-slate-400 cursor-pointer hover:bg-slate-50 rounded-lg font-medium" onclick="selectPromo('', '-- Không chọn --')">
                                            -- Không chọn --
                                        </div>
                                        @foreach($promoItems as $promo)
                                            <div class="flex items-center justify-between p-2 hover:bg-slate-50 rounded-xl cursor-pointer transition" onclick="selectPromo('{{ $promo->promo_id }}', '#{{ $promo->promo_id }} - {{ $promo->promo_type }} @if($promo->code)({{ $promo->code }})@endif')">
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-800">{{ $promo->promo_type }}</div>
                                                    @if($promo->code)
                                                        <div class="text-[10px] text-indigo-600 font-extrabold mt-0.5">Mã: {{ $promo->code }}</div>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-slate-400 shrink-0">#{{ $promo->promo_id }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50 -mx-8 -mb-8 p-6 px-8 rounded-b-[2rem]">
                    <button type="button" onclick="closeCreateModal()" class="px-5 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 hover:shadow-indigo-200">
                        Gửi thông báo
                    </button>
                </div>
            </form>
        </div>
    </div>
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

function openCreateModal() {
    const modal = document.getElementById('createNotificationModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeCreateModal() {
    const modal = document.getElementById('createNotificationModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Toggle Target Audience Area
function handleTargetChange() {
    const target = document.getElementById('notificationTarget').value;
    const specificUsersSection = document.getElementById('specificUsersSection');
    const userQueryInput = document.getElementById('userQueryInput');
    
    if (target === 'specific') {
        specificUsersSection.classList.remove('hidden');
        userQueryInput.setAttribute('required', 'required');
    } else {
        specificUsersSection.classList.add('hidden');
        userQueryInput.removeAttribute('required');
    }
}

// User Multiselect & Autocomplete Logic
const selectedUsers = {};

function toggleUserSearchResults(show) {
    const results = document.getElementById('userSearchResults');
    if (show) {
        results.classList.remove('hidden');
    } else {
        setTimeout(() => results.classList.add('hidden'), 200);
    }
}

document.getElementById('userQueryInput')?.addEventListener('focus', () => toggleUserSearchResults(true));

let userDebounceTimer;
document.getElementById('userQueryInput')?.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('userSearchResults');
    
    if (!query) {
        resultsContainer.innerHTML = '<div class="p-2 text-center text-xs text-slate-400">Nhập từ khóa để tìm...</div>';
        return;
    }
    
    clearTimeout(userDebounceTimer);
    userDebounceTimer = setTimeout(() => {
        resultsContainer.innerHTML = '<div class="p-2 text-center text-xs text-slate-400"><i class="fa-solid fa-spinner animate-spin mr-1"></i> Đang tìm...</div>';
        
        fetch(`{{ route('admin.notifications.search-users') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    resultsContainer.innerHTML = '<div class="p-2 text-center text-xs text-slate-400">Không tìm thấy tài khoản thích hợp</div>';
                    return;
                }
                
                let html = '';
                data.forEach(user => {
                    if (selectedUsers[user.user_id]) return;
                    
                    html += `
                        <div class="p-2 hover:bg-slate-50 rounded-xl cursor-pointer transition flex items-center justify-between" 
                             onmousedown="addUserTag(${user.user_id}, '${user.full_name.replace(/'/g, "\\'")}', '${user.email.replace(/'/g, "\\'")}')">
                            <div>
                                <div class="text-xs font-bold text-slate-800">${user.full_name}</div>
                                <div class="text-[10px] text-slate-400">${user.email}</div>
                            </div>
                            <div class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded">Chọn</div>
                        </div>
                    `;
                });
                resultsContainer.innerHTML = html || '<div class="p-2 text-center text-xs text-slate-400">Các tài khoản phù hợp đã được chọn hết</div>';
            })
            .catch(() => {
                resultsContainer.innerHTML = '<div class="p-2 text-center text-xs text-rose-500">Lỗi khi tìm kiếm</div>';
            });
    }, 300);
});

function addUserTag(id, name, email) {
    if (selectedUsers[id]) return;
    selectedUsers[id] = { name, email };
    updateUserTagsUI();
    document.getElementById('userQueryInput').value = '';
}

function removeUserTag(id) {
    delete selectedUsers[id];
    updateUserTagsUI();
}

function updateUserTagsUI() {
    const container = document.getElementById('selectedUsersContainer');
    let html = '';
    const keys = Object.keys(selectedUsers);
    
    if (keys.length === 0) {
        container.innerHTML = '<span class="text-xs text-slate-400" id="noUsersSelectedText">Chưa có tài khoản nào được chọn.</span>';
        return;
    }
    
    keys.forEach(id => {
        const u = selectedUsers[id];
        html += `
            <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                ${u.name}
                <input type="hidden" name="user_ids[]" value="${id}">
                <button type="button" onclick="removeUserTag(${id})" class="w-4 h-4 rounded-full bg-indigo-200/50 hover:bg-indigo-200 text-indigo-800 text-[10px] flex items-center justify-center transition">&times;</button>
            </span>
        `;
    });
    container.innerHTML = html;
}

// Custom Dropdowns (Product & Promo) Search Logic
let productDebounceTimer;
let promoDebounceTimer;

function toggleProductDropdown() {
    const menu = document.getElementById('productDropdownMenu');
    if (menu) {
        menu.classList.toggle('hidden');
        if (!menu.classList.contains('hidden')) {
            document.getElementById('productQueryInput').focus();
        }
    }
}

function togglePromoDropdown() {
    const menu = document.getElementById('promoDropdownMenu');
    if (menu) {
        menu.classList.toggle('hidden');
        if (!menu.classList.contains('hidden')) {
            document.getElementById('promoQueryInput').focus();
        }
    }
}

// Close Dropdowns on Click Outside
document.addEventListener('click', function(event) {
    const productDropdown = document.getElementById('productSearchDropdown');
    const productMenu = document.getElementById('productDropdownMenu');
    if (productDropdown && !productDropdown.contains(event.target) && productMenu) {
        productMenu.classList.add('hidden');
    }

    const promoDropdown = document.getElementById('promoSearchDropdown');
    const promoMenu = document.getElementById('promoDropdownMenu');
    if (promoDropdown && !promoDropdown.contains(event.target) && promoMenu) {
        promoMenu.classList.add('hidden');
    }
    
    const userDropdown = document.getElementById('userSearchDropdown');
    if (userDropdown && !userDropdown.contains(event.target)) {
        toggleUserSearchResults(false);
    }
});

function selectProduct(id, displayName) {
    document.getElementById('submitProductId').value = id;
    document.getElementById('selectedProductText').textContent = displayName;
    document.getElementById('productDropdownMenu').classList.add('hidden');
}

function selectPromo(id, displayName) {
    document.getElementById('submitPromoId').value = id;
    document.getElementById('selectedPromoText').textContent = displayName;
    document.getElementById('promoDropdownMenu').classList.add('hidden');
}

// Search Autocomplete Handlers
document.getElementById('productQueryInput')?.addEventListener('input', function() {
    const query = this.value.trim();
    clearTimeout(productDebounceTimer);

    productDebounceTimer = setTimeout(() => {
        const resultsContainer = document.getElementById('productSearchResults');
        resultsContainer.innerHTML = '<div class="p-4 text-center text-xs text-slate-400"><i class="fa-solid fa-spinner animate-spin mr-2"></i>Đang tìm kiếm...</div>';

        fetch(`{{ route('admin.api.products.search') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                let html = `<div class="p-3 text-center text-xs text-slate-400 cursor-pointer hover:bg-slate-50 rounded-lg font-medium" onclick="selectProduct('', '-- Không chọn --')">-- Không chọn --</div>`;

                if (data.length === 0) {
                    html += `<div class="p-4 text-center text-xs text-slate-400">Không tìm thấy sản phẩm phù hợp</div>`;
                } else {
                    data.forEach(prod => {
                        const price = new Intl.NumberFormat('vi-VN').format(prod.base_price) + 'đ';
                        const thumbnail = prod.thumbnail || 'https://via.placeholder.com/40';
                        const cleanName = prod.name.replace(/'/g, "\\'").replace(/"/g, '\\"');
                        html += `
                            <div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-xl cursor-pointer transition" onclick="selectProduct('${prod.product_id}', '#${prod.product_id} - ${cleanName}')">
                                <img src="${thumbnail}" class="w-8 h-8 object-cover rounded-lg border border-slate-100 shrink-0">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-slate-800 truncate">${prod.name}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">#${prod.product_id} • ${price}</div>
                                </div>
                            </div>
                        `;
                    });
                }
                resultsContainer.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                resultsContainer.innerHTML = '<div class="p-4 text-center text-xs text-rose-500">Đã xảy ra lỗi khi tìm kiếm.</div>';
            });
    }, 300);
});

document.getElementById('promoQueryInput')?.addEventListener('input', function() {
    const query = this.value.trim();
    clearTimeout(promoDebounceTimer);

    promoDebounceTimer = setTimeout(() => {
        const resultsContainer = document.getElementById('promoSearchResults');
        resultsContainer.innerHTML = '<div class="p-4 text-center text-xs text-slate-400"><i class="fa-solid fa-spinner animate-spin mr-2"></i>Đang tìm kiếm...</div>';

        fetch(`{{ route('admin.notifications.search-promo') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                let html = `<div class="p-3 text-center text-xs text-slate-400 cursor-pointer hover:bg-slate-50 rounded-lg font-medium" onclick="selectPromo('', '-- Không chọn --')">-- Không chọn --</div>`;

                if (data.length === 0) {
                    html += `<div class="p-4 text-center text-xs text-slate-400">Không tìm thấy mã khuyến mãi phù hợp</div>`;
                } else {
                    data.forEach(promo => {
                        const codeText = promo.code ? ` (Mã: ${promo.code})` : '';
                        html += `
                            <div class="flex items-center justify-between p-2 hover:bg-slate-50 rounded-xl cursor-pointer transition" onclick="selectPromo('${promo.promo_id}', '#${promo.promo_id} - ${promo.promo_type}${codeText}')">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-slate-800">${promo.promo_type}</div>
                                    ${promo.code ? `<div class="text-[10px] text-indigo-600 font-extrabold mt-0.5">Mã: ${promo.code}</div>` : ''}
                                </div>
                                <div class="text-[10px] text-slate-400 shrink-0">#${promo.promo_id}</div>
                            </div>
                        `;
                    });
                }
                resultsContainer.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                resultsContainer.innerHTML = '<div class="p-4 text-center text-xs text-rose-500">Đã xảy ra lỗi khi tìm kiếm.</div>';
            });
    }, 300);
});
</script>
@endpush
