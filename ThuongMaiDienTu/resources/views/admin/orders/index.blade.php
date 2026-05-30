@extends('admin.layouts.master')
@section('title', 'Quản lý Đơn Hàng')

@section('content')
@php
    // Ánh xạ trạng thái DB → nhãn tiếng Việt hiển thị trên UI
    $statusMap = [
        'Pending'   => ['label' => 'Chờ xử lý', 'color' => 'bg-yellow-100 text-yellow-700', 'tab' => 'Chờ xử lý'],
        'BaoCK'     => ['label' => 'Báo CK chờ duyệt', 'color' => 'bg-blue-100 text-blue-700', 'tab' => 'Báo CK chờ duyệt'],
        'Shipping'  => ['label' => 'Đang giao hàng', 'color' => 'bg-emerald-100 text-emerald-700', 'tab' => 'Đang giao'],
        'Delivered'  => ['label' => 'Hoàn thành', 'color' => 'bg-green-100 text-green-700', 'tab' => 'Hoàn thành'],
        'Cancelled' => ['label' => 'Đã hủy', 'color' => 'bg-red-100 text-red-700', 'tab' => 'Đã hủy'],
    ];
    $activeStatus = request('status', '');
@endphp

<div class="space-y-6">
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Quản lý Đơn Hàng</h1>
        <form action="{{ route('admin.orders.index') }}" method="GET" class="relative w-full sm:w-80">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm..."
                   class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition">
        </form>
    </div>

    {{-- TAB LỌC TRẠNG THÁI --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <p class="text-sm text-slate-500 mb-3">Đang hiển thị: <strong class="text-slate-800">{{ $orders->total() }}</strong> đơn hàng</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.orders.index', request()->except('status', 'page')) }}"
               class="px-4 py-2 rounded-full text-sm font-bold border transition {{ $activeStatus == '' ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400' }}">
                Tất cả ({{ $totalOrders }})
            </a>
            @foreach($statusMap as $key => $info)
                <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => $key])) }}"
                   class="px-4 py-2 rounded-full text-sm font-bold border transition {{ $activeStatus == $key ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400' }}">
                    {{ $info['tab'] }} ({{ $statusCounts[$key] ?? 0 }})
                </a>
            @endforeach
        </div>
    </div>

    {{-- BẢNG ĐƠN HÀNG --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mã ĐH</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Khách hàng</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Tổng tiền</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Trạng thái</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ngày đặt</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($orders as $order)
                @php
                    $name = $order->customer_name ?? ($order->user->full_name ?? 'Khách vãng lai');
                    $phone = $order->customer_phone ?? ($order->user->phone_number ?? '');
                    $address = $order->shipping_address ?? ($order->user->address ?? '');
                    $st = $statusMap[$order->status] ?? ['label' => $order->status, 'color' => 'bg-slate-100 text-slate-600'];
                @endphp
                <tr class="hover:bg-slate-50/60 transition group">
                    <td class="px-6 py-4">
                        <span class="text-sm font-extrabold text-slate-700">#{{ $order->order_code ?? $order->order_id }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-slate-800">{{ $name }} @if($phone)- {{ $phone }}@endif</div>
                        <div class="text-xs text-slate-400 mt-0.5">{{ Str::limit($address, 40) }}</div>
                        @if($order->note)
                            <div class="text-xs text-blue-500 mt-0.5"><i class="fa-solid fa-comment-dots mr-1"></i>[{{ Str::limit($order->note, 50) }}]</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-extrabold text-red-600">{{ number_format($order->final_amount) }}đ</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold {{ $st['color'] }}">{{ $st['label'] }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        {{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-1">
                            <button onclick="openOrderDetail({{ $order->order_id }})" class="w-8 h-8 flex items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 transition" title="Xem chi tiết">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                            <form id="delete-form-{{ $order->order_id }}" action="{{ route('admin.orders.destroy', $order->order_id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDeleteOrder({{ $order->order_id }})" class="w-8 h-8 flex items-center justify-center rounded-lg text-red-500 hover:bg-red-50 transition" title="Xóa">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <i class="fa-solid fa-box-open text-4xl text-slate-200 mb-3 block"></i>
                        <p class="text-slate-400 font-medium">Không tìm thấy đơn hàng nào.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PHÂN TRANG --}}
    <div class="mt-4">{{ $orders->links('vendor.pagination.tailwind') }}</div>
</div>

{{-- ============ MODAL CHI TIẾT ĐƠN HÀNG ============ --}}
<div id="orderDetailModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-all">
    <div class="bg-white rounded-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl animate-in zoom-in-95 duration-300">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-extrabold text-slate-800">Chi tiết đơn hàng <span id="modalOrderCode" class="text-indigo-600"></span></h2>
            <button onclick="closeOrderDetail()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 overflow-y-auto flex-1 custom-scrollbar space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Thông tin khách hàng --}}
                <div>
                    <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-user-circle text-slate-400"></i> Thông tin khách hàng
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex"><span class="text-slate-400 w-20 shrink-0">Họ tên:</span><strong id="modalName" class="text-slate-800"></strong></div>
                        <div class="flex"><span class="text-slate-400 w-20 shrink-0">Điện thoại:</span><strong id="modalPhone" class="text-indigo-600"></strong></div>
                        <div class="flex"><span class="text-slate-400 w-20 shrink-0">Địa chỉ:</span><span id="modalAddress" class="text-slate-700"></span></div>
                        <div class="flex"><span class="text-slate-400 w-20 shrink-0">Ghi chú:</span><span id="modalNote" class="text-rose-500 italic"></span></div>
                    </div>
                    {{-- Nội dung chuyển khoản --}}
                    <div id="bankTransferBox" class="hidden mt-4 p-4 bg-blue-50/60 border-2 border-dashed border-blue-200 rounded-xl">
                        <p class="text-xs font-bold text-blue-600 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-building-columns"></i> Nội dung chuyển khoản (Kiểm tra ngân hàng):
                        </p>
                        <p id="bankTransferContent" class="text-sm font-bold text-slate-700 bg-white px-4 py-2.5 rounded-lg border border-blue-200 text-center tracking-wide"></p>
                    </div>
                </div>

                {{-- Cập nhật trạng thái --}}
                <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-5">
                    <h3 class="text-sm font-bold text-indigo-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-arrows-rotate"></i> Cập nhật trạng thái Đơn hàng
                    </h3>
                    <label class="block text-xs font-bold text-slate-500 mb-1.5">Trạng thái hiện tại:</label>
                    <select id="modalStatusSelect" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 bg-white text-sm font-bold text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition">
                        <option value="Pending">Chờ xử lý (COD - Thanh toán tiền mặt)</option>
                        <option value="BaoCK">Báo đã CK QR - Đang chờ kiểm tra tiền</option>
                        <option value="Shipping">Đã xác nhận - Đang giao hàng</option>
                        <option value="Delivered">Giao hàng thành công</option>
                        <option value="Cancelled">Khách hủy đơn / Đơn ảo</option>
                    </select>
                    <button onclick="saveOrderStatus()" class="mt-4 w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu Trạng Thái
                    </button>
                </div>
            </div>

            {{-- Thông tin tích điểm --}}
            <div id="pointsInfoBox" class="hidden bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-5">
                <h3 class="text-sm font-bold text-amber-700 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-coins"></i> Thông tin tích điểm
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="bg-white rounded-lg p-3 text-center border border-amber-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Điểm sẽ tích</p>
                        <p id="pointsPotential" class="text-lg font-black text-amber-600 mt-1">0</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center border border-amber-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Đã tích</p>
                        <p id="pointsEarned" class="text-lg font-black text-emerald-600 mt-1">0</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center border border-amber-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Điểm đã dùng</p>
                        <p id="pointsUsed" class="text-lg font-black text-rose-500 mt-1">0</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center border border-amber-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Trạng thái điểm</p>
                        <p id="pointsStatus" class="text-sm font-bold mt-1.5">—</p>
                    </div>
                </div>
                <div id="customerBalanceBox" class="hidden mt-3 flex items-center gap-3 bg-white rounded-lg p-3 border border-amber-100">
                    <i class="fa-solid fa-wallet text-amber-500"></i>
                    <div class="text-sm">
                        <span class="text-slate-500">Số dư khách hàng:</span>
                        <strong id="customerWalletPoints" class="text-amber-700 ml-1">0</strong>
                        <span class="text-slate-400">điểm tiêu dùng</span>
                        <span class="text-slate-300 mx-1">|</span>
                        <strong id="customerRankPoints" class="text-indigo-600">0</strong>
                        <span class="text-slate-400">điểm rank</span>
                        <span class="text-slate-300 mx-1">|</span>
                        <span id="customerRank" class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Bronze</span>
                    </div>
                </div>
            </div>

            {{-- Danh sách sản phẩm --}}
            <div>
                <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-cart-shopping text-slate-400"></i> Danh sách sản phẩm đã mua
                </h3>
                <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3 text-[11px] font-bold text-slate-400 uppercase">Sản phẩm</th>
                                <th class="px-4 py-3 text-[11px] font-bold text-slate-400 uppercase text-center">SL</th>
                                <th class="px-4 py-3 text-[11px] font-bold text-slate-400 uppercase text-right">Đơn giá</th>
                                <th class="px-4 py-3 text-[11px] font-bold text-slate-400 uppercase text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="modalProductList" class="divide-y divide-slate-50"></tbody>
                    </table>
                </div>
                <div class="flex justify-end mt-4 gap-3 items-center">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-wider">Tổng tiền phải thu:</span>
                    <span id="modalTotalAmount" class="text-xl font-black text-slate-900"></span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentOrderId = null;

    /**
     * Mở modal chi tiết đơn hàng bằng AJAX
     */
    function openOrderDetail(orderId) {
        currentOrderId = orderId;
        const modal = document.getElementById('orderDetailModal');

        fetch(`/admin/orders/${orderId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            // Điền thông tin header
            document.getElementById('modalOrderCode').textContent = '#' + (data.order_code || data.order_id);

            // Thông tin khách hàng
            document.getElementById('modalName').textContent = data.customer_name || 'N/A';
            document.getElementById('modalPhone').textContent = data.customer_phone || 'N/A';
            document.getElementById('modalAddress').textContent = data.shipping_address || 'N/A';
            document.getElementById('modalNote').textContent = data.note || 'Không có';

            // Nội dung chuyển khoản (hiển thị khi thanh toán không phải COD)
            const bankBox = document.getElementById('bankTransferBox');
            if (data.payment_method !== 'COD' && data.payment_method !== 'Cash_POS') {
                bankBox.classList.remove('hidden');
                document.getElementById('bankTransferContent').textContent =
                    'Thanh toan don hang ' + (data.order_code || data.order_id);
            } else {
                bankBox.classList.add('hidden');
            }

            // Trạng thái
            document.getElementById('modalStatusSelect').value = data.status || 'Pending';

            // Danh sách sản phẩm
            const tbody = document.getElementById('modalProductList');
            tbody.innerHTML = '';
            (data.items || []).forEach(item => {
                const imgTag = item.image
                    ? `<img src="/storage/${item.image}" class="w-10 h-10 rounded-lg object-cover border border-slate-100" onerror="this.src='/images/no-image.png'">`
                    : `<div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300"><i class="fa-solid fa-image"></i></div>`;
                tbody.innerHTML += `
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                ${imgTag}
                                <span class="text-sm font-medium text-slate-700">${escapeHtml(item.product_name)}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-bold text-slate-700">${item.quantity}</td>
                        <td class="px-4 py-3 text-right text-sm text-slate-600">${formatCurrency(item.price)}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-slate-800">${formatCurrency(item.subtotal)}</td>
                    </tr>`;
            });

            // Tổng tiền
            document.getElementById('modalTotalAmount').textContent = formatCurrency(data.final_amount);

            // Thông tin tích điểm
            renderPointsInfo(data.points, data.customer_balance);

            // Hiển thị modal
            modal.classList.remove('hidden');
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Không thể tải chi tiết đơn hàng.' });
        });
    }

    /**
     * Đóng modal chi tiết
     */
    function closeOrderDetail() {
        document.getElementById('orderDetailModal').classList.add('hidden');
        currentOrderId = null;
    }

    // Đóng modal khi click bên ngoài
    document.getElementById('orderDetailModal').addEventListener('click', function(e) {
        if (e.target === this) closeOrderDetail();
    });

    /**
     * Lưu trạng thái đơn hàng qua AJAX
     */
    function saveOrderStatus() {
        if (!currentOrderId) return;
        const status = document.getElementById('modalStatusSelect').value;

        fetch(`/admin/orders/${currentOrderId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let html = `<p>${escapeHtml(data.message)}</p>`;
                if (data.points_earned > 0) {
                    html += `<p class="mt-2 text-amber-600 font-bold"><i class="fa-solid fa-coins mr-1"></i> +${data.points_earned} điểm đã được tích</p>`;
                }
                Swal.fire({ icon: 'success', title: 'Thành công', html: html, timer: 2500, showConfirmButton: false });
                setTimeout(() => location.reload(), 2500);
            } else {
                Swal.fire({ icon: 'error', title: 'Lỗi', text: data.message || 'Có lỗi xảy ra!' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Không thể cập nhật trạng thái.' }));
    }

    /**
     * Xác nhận xóa đơn hàng
     */
    function confirmDeleteOrder(orderId) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            html: `Bạn có chắc muốn xóa đơn hàng <strong>#${orderId}</strong>?<br><small class="text-red-500">Hành động này không thể hoàn tác!</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa đơn hàng',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
        }).then(result => {
            if (result.isConfirmed) document.getElementById(`delete-form-${orderId}`).submit();
        });
    }

    /**
     * Render thông tin tích điểm trong modal
     */
    function renderPointsInfo(points, balance) {
        const box = document.getElementById('pointsInfoBox');
        if (!points) { box.classList.add('hidden'); return; }
        box.classList.remove('hidden');

        document.getElementById('pointsPotential').textContent = '+' + (points.potential_points || 0);
        document.getElementById('pointsEarned').textContent = '+' + (points.wallet_points_earned || 0);
        document.getElementById('pointsUsed').textContent = '-' + (points.wallet_points_used || 0);

        const statusEl = document.getElementById('pointsStatus');
        const statusMap = {
            'pending': { label: 'Chờ xử lý', color: 'text-yellow-600' },
            'processed': { label: 'Đã xử lý', color: 'text-emerald-600' },
            'cancelled': { label: 'Đã hủy', color: 'text-red-500' },
            'refunded': { label: 'Đã hoàn', color: 'text-blue-600' },
        };
        const st = statusMap[points.points_status] || { label: points.points_status, color: 'text-slate-600' };
        statusEl.textContent = st.label;
        statusEl.className = 'text-sm font-bold mt-1.5 ' + st.color;

        // Số dư khách hàng
        const balanceBox = document.getElementById('customerBalanceBox');
        if (balance) {
            balanceBox.classList.remove('hidden');
            document.getElementById('customerWalletPoints').textContent = balance.wallet_points || 0;
            document.getElementById('customerRankPoints').textContent = balance.rank_points || 0;
            const rankEl = document.getElementById('customerRank');
            const rankColors = {
                'Bronze': 'bg-orange-100 text-orange-700',
                'Silver': 'bg-slate-100 text-slate-700',
                'Gold': 'bg-amber-100 text-amber-700',
                'Diamond': 'bg-indigo-100 text-indigo-700',
            };
            rankEl.textContent = balance.current_rank || 'Bronze';
            rankEl.className = 'text-xs font-bold px-2 py-0.5 rounded-full ' + (rankColors[balance.current_rank] || 'bg-slate-100 text-slate-600');
        } else {
            balanceBox.classList.add('hidden');
        }
    }

    // Helpers
    function formatCurrency(val) { return new Intl.NumberFormat('vi-VN').format(val || 0) + 'đ'; }
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }
</script>
@endpush
