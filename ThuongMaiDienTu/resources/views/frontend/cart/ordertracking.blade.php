@extends('layouts.app')

@section('title', 'Đơn hàng của tôi - DIENMAYPRO')

@push('styles')
<style>
/* ===== TABS TRẠNG THÁI ===== */
.order-tabs { display:flex; gap:0; overflow-x:auto; border-bottom:2px solid #e5e7eb; }
.order-tab {
    display:flex; flex-direction:column; align-items:center; gap:2px;
    padding:12px 20px; cursor:pointer; white-space:nowrap;
    border-bottom:3px solid transparent; margin-bottom:-2px;
    color:#6b7280; font-size:13px; font-weight:600; transition:all .2s;
}
.order-tab .tab-count { font-size:18px; font-weight:800; color:#111827; }
.order-tab:hover { color:#4f46e5; background:#f5f3ff; }
.order-tab.active { color:#4f46e5; border-bottom-color:#4f46e5; background:#faf9ff; }
.order-tab.active .tab-count { color:#4f46e5; }

/* ===== CARD ĐƠN HÀNG ===== */
.order-card {
    background:#fff; border-radius:16px; padding:20px 24px;
    margin-bottom:16px; box-shadow:0 1px 4px rgba(0,0,0,.07);
    border:1px solid #f0f0f0; animation:fadeUp .35s ease both;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

/* ===== STEPPER NGANG ===== */
.stepper { display:flex; align-items:center; margin:16px 0; }
.step-item { display:flex; flex-direction:column; align-items:center; gap:4px; flex:1; position:relative; }
.step-item:not(:last-child)::after {
    content:''; position:absolute; top:18px; left:60%; right:-40%;
    height:2px; background:#e5e7eb; z-index:0;
}
.step-item.done:not(:last-child)::after { background:#4f46e5; }
.step-circle {
    width:36px; height:36px; border-radius:50%; border:2px solid #e5e7eb;
    display:flex; align-items:center; justify-content:center;
    background:#fff; color:#d1d5db; font-size:14px; z-index:1; position:relative;
    transition:all .3s;
}
.step-item.done .step-circle { background:#4f46e5; border-color:#4f46e5; color:#fff; }
.step-item.active .step-circle { border-color:#4f46e5; color:#4f46e5; box-shadow:0 0 0 5px rgba(79,70,229,.12); }
.step-label { font-size:11px; color:#9ca3af; font-weight:500; text-align:center; }
.step-item.done .step-label, .step-item.active .step-label { color:#4f46e5; font-weight:700; }

/* ===== BADGE TRẠNG THÁI ===== */
.status-badge {
    display:inline-flex; align-items:center; gap:5px;
    font-size:12px; font-weight:700; padding:4px 12px; border-radius:20px;
}
.badge-pending   { background:#fef9c3; color:#a16207; }
.badge-shipping  { background:#ede9fe; color:#4f46e5; }
.badge-delivered { background:#dcfce7; color:#16a34a; }
.badge-cancelled { background:#fee2e2; color:#dc2626; }
.badge-other     { background:#f3f4f6; color:#374151; }

/* ===== ITEM SẢN PHẨM TRONG CARD ===== */
.product-row {
    display:flex; align-items:center; gap:12px;
    padding:12px 0; border-top:1px solid #f3f4f6;
}
.product-row img { width:64px; height:64px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; flex-shrink:0; }
.product-name { font-size:14px; font-weight:600; color:#111827; line-height:1.4; }
.product-qty  { font-size:13px; color:#6b7280; margin-top:2px; }
.product-price{ font-size:14px; font-weight:800; color:#111827; white-space:nowrap; margin-left:auto; }

/* ===== FOOTER CARD ===== */
.card-footer { display:flex; justify-content:space-between; align-items:center; padding-top:14px; border-top:1px solid #f3f4f6; margin-top:8px; }
.card-meta { display:flex; gap:16px; flex-wrap:wrap; font-size:13px; color:#6b7280; }
.card-meta span { display:flex; align-items:center; gap:4px; }
.total-label { font-size:13px; color:#6b7280; font-weight:600; }
.total-amount { font-size:20px; font-weight:900; color:#dc2626; }

/* ===== SEARCH BAR ===== */
.search-wrap { display:flex; gap:10px; margin-bottom:20px; }
.search-wrap input { flex:1; padding:11px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:14px; outline:none; transition:.2s; }
.search-wrap input:focus { border-color:#4f46e5; }
.search-wrap button { padding:11px 22px; background:#4f46e5; color:#fff; border:none; border-radius:12px; font-weight:700; cursor:pointer; transition:.2s; }
.search-wrap button:hover { background:#4338ca; }

/* ===== HIGHLIGHT ĐƠN HÀNG MỚI ĐẶT ===== */
.new-order-highlight {
    border: 2px solid #10b981 !important;
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.4) !important;
    animation: pulseBorder 2s infinite !important;
}
@keyframes pulseBorder {
    0%, 100% { border-color: #10b981; box-shadow: 0 0 15px rgba(16, 185, 129, 0.4); }
    50% { border-color: #34d399; box-shadow: 0 0 25px rgba(52, 211, 153, 0.7); }
}

/* ===== EMPTY STATE ===== */
.empty-box { text-align:center; padding:60px 20px; color:#9ca3af; }
.empty-box i { font-size:48px; margin-bottom:16px; display:block; color:#d1d5db; }
.empty-box p { font-size:15px; font-weight:500; }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4">

        <!-- Tiêu đề trang -->
        <div class="text-center mb-10 animate-fade-in-down">
            <h1 class="text-4xl font-extrabold text-[#0046ab] tracking-tight mb-2">Tra Cứu Hành Trình Đơn Hàng</h1>
            <p class="text-sm text-gray-500 font-medium">Nhập số điện thoại hoặc mã đơn hàng để theo dõi lịch trình giao nhận thời gian thực.</p>
        </div>

        <!-- Khung tìm kiếm/tra cứu -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-100 p-8 border border-gray-100 mb-8 transform hover:scale-[1.01] transition-all duration-300">
            <form id="form-code" onsubmit="doSearch(event)" class="flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="input-code" required placeholder="Nhập Số điện thoại hoặc Mã đơn hàng (VD: ORD...)" 
                           class="w-full pl-12 pr-4 py-4 rounded-2xl border border-gray-200 focus:border-[#0046ab] focus:ring-2 focus:ring-blue-100 outline-none transition-all font-semibold text-gray-700 placeholder-gray-400 shadow-inner bg-gray-50/50">
                </div>
                <button type="submit" class="px-8 py-4 bg-[#0046ab] hover:bg-blue-800 text-white font-extrabold rounded-2xl transition-all shadow-md shadow-blue-100 hover:shadow-lg flex items-center justify-center gap-2">
                    <i class="fa-solid fa-search"></i> Tra cứu đơn hàng
                </button>
            </form>
        </div>

        <!-- Khung hiển thị thông tin Loading -->
        <div id="loading" class="hidden text-center py-16">
            <div class="inline-block w-12 h-12 border-4 border-[#0046ab] border-t-transparent rounded-full animate-spin"></div>
            <p class="text-gray-500 font-bold mt-4 animate-pulse">Đang định vị thông tin vận vận đơn...</p>
        </div>

        <!-- Khung hiển thị lỗi không tìm thấy (No Result) -->
        <div id="noResult" class="hidden bg-white rounded-3xl border border-red-100 p-12 text-center shadow-lg shadow-red-50/10">
            <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-circle-xmark text-red-500 text-3xl"></i>
            </div>
            <h3 class="text-lg font-black text-gray-800 mb-1">Không tìm thấy thông tin đơn hàng</h3>
            <p class="text-gray-500 text-sm max-w-sm mx-auto">Vui lòng kiểm tra lại Mã đơn hàng hoặc Số điện thoại người nhận chính xác của bạn.</p>
        </div>

        <!-- Khung danh sách đơn hàng tìm thấy theo Số Điện Thoại (Order List Result) -->
        <div id="orderListResult" class="hidden bg-white rounded-3xl shadow-xl shadow-gray-100 border border-gray-100 p-8 mb-8">
            <div class="flex items-center gap-3 pb-6 border-b border-gray-100 mb-6">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i class="fa-solid fa-boxes-stacked text-[#0046ab] text-lg"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-gray-800 text-lg leading-tight">Danh sách đơn hàng của bạn</h3>
                    <p class="text-xs text-gray-400 font-bold mt-0.5">Tìm thấy <span id="order-list-count" class="text-[#0046ab]">0</span> đơn hàng tương ứng</p>
                </div>
            </div>
            <div id="order-list-container" class="space-y-6"></div>
        </div>

        <!-- Bảng Hiển Thị Chi Tiết Hành Trình Đơn Hàng (Tracking Result Layout) -->
        <div id="trackingResult" class="hidden bg-white rounded-3xl shadow-xl shadow-gray-100 border border-gray-100 p-8 mb-8">
            
            <!-- Header kết quả -->
            <div class="flex flex-wrap justify-between items-center gap-4 pb-6 border-b border-gray-100 mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center">
                        <i class="fa-solid fa-truck-fast text-[#0046ab] text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 font-extrabold block">MÃ VẬN ĐƠN</span>
                        <div class="flex items-center gap-2">
                            <span id="order-id-badge" class="font-black text-gray-800 text-xl">#---</span>
                            <span id="result-status" class="text-[10px] font-black px-2 py-0.5 rounded">---</span>
                        </div>
                    </div>
                </div>
                
                <button onclick="openProductsModal()" class="px-5 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 text-sm font-extrabold rounded-xl border border-gray-200 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-boxes-packing"></i> Xem sản phẩm đã mua <i class="fa-solid fa-angle-right"></i>
                </button>
            </div>

            <!-- Grid thông tin giao nhận khách hàng -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
                    <span class="text-xs text-gray-400 font-bold block mb-3 uppercase tracking-wider">Thông tin người nhận</span>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-user text-gray-400 mt-0.5 w-4 text-center"></i>
                            <div>
                                <span class="text-xs text-gray-400 block leading-tight">Họ và tên</span>
                                <span id="result-name" class="text-sm font-bold text-gray-700">---</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot text-gray-400 mt-0.5 w-4 text-center"></i>
                            <div>
                                <span class="text-xs text-gray-400 block leading-tight">Địa chỉ nhận hàng</span>
                                <span id="result-address" class="text-sm font-bold text-gray-700">---</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100 flex flex-col justify-between">
                    <div>
                        <span class="text-xs text-gray-400 font-bold block mb-3 uppercase tracking-wider">Thanh toán</span>
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-receipt text-gray-400 w-4 text-center"></i>
                            <div>
                                <span class="text-xs text-gray-400 block leading-tight">Tổng số tiền thanh toán</span>
                                <span id="result-total" class="text-lg font-black text-red-600">0đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sơ Đồ Hành Trình Timeline Đơn Hàng (Vertical Stepper) -->
            <div class="relative space-y-10 pl-6 border-l-2 border-gray-100 ml-4">
                
                <!-- Mốc 1: Đặt hàng thành công -->
                <div class="flex items-start gap-6 relative">
                    <div class="tracking-dot">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-base">Đơn hàng được khởi tạo</h4>
                        <p class="text-gray-500 text-sm mt-0.5">Hệ thống đã xác nhận đơn hàng thành công.</p>
                    </div>
                </div>

                <!-- Mốc 2: Đang chuẩn bị hàng -->
                <div class="flex items-start gap-6 relative">
                    <div class="tracking-dot">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-base">Đang chuẩn bị hàng</h4>
                        <p class="text-gray-500 text-sm mt-0.5">Sản phẩm đang được kiểm tra kỹ thuật trước khi đóng gói.</p>
                    </div>
                </div>

                <!-- Mốc 3: Đang đóng gói -->
                <div class="flex items-start gap-6 relative">
                    <div class="tracking-dot">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-base">Đang đóng gói</h4>
                        <p class="text-gray-500 text-sm mt-0.5">Sản phẩm được niêm phong chống sốc và chống tháo.</p>
                    </div>
                </div>

                <!-- Mốc 4: Đang vận chuyển -->
                <div class="flex items-start gap-6 relative">
                    <div class="tracking-dot">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-base">Đang vận chuyển</h4>
                        <p class="text-gray-500 text-sm mt-0.5">Sản phẩm đang được vận chuyển tới quý khách hàng.</p>
                    </div>
                </div>

                <!-- Mốc 5: Đã giao thành công -->
                <div class="flex items-start gap-6 relative">
                    <div class="tracking-dot">
                        <i class="fa-solid fa-house-circle-check"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-base">Giao thành công</h4>
                        <p class="text-gray-500 text-sm mt-0.5">Giao vận hoàn tất, chúc quý khách có trải nghiệm tốt nhất.</p>
                    </div>
                </div>
            </div>

            <!-- Nút đóng/reset kết quả tìm kiếm -->
            <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row gap-4">
                <button onclick="resetSearch()"
                    class="flex-1 py-3.5 bg-gray-100 hover:bg-gray-200 active:scale-[0.98] text-gray-700 font-bold rounded-2xl transition-all duration-150 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-rotate-left"></i> Nhập mã khác
                </button>
                <a href="{{ url('/') }}"
                    class="flex-1 py-3.5 border-2 border-gray-200 hover:bg-gray-50 active:scale-[0.98] text-gray-700 font-bold rounded-2xl transition-all duration-150 text-center block">
                    Về trang chủ
                </a>
            </div>

        </div>

        {{-- DANH SÁCH ĐƠN HÀNG THÀNH VIÊN (Nếu đã đăng nhập) --}}
        @if(Auth::check())
        <div id="logged-in-orders" class="mt-8 animate-fade-in-up">
            @php
                $allOrders = $orders;
                $statusCounts = [
                    'all'       => $allOrders->count(),
                    'Pending'   => $allOrders->where('status','Pending')->count(),
                    'Shipping'  => $allOrders->where('status','Shipping')->count(),
                    'Delivered' => $allOrders->where('status','Delivered')->count(),
                    'Cancelled' => $allOrders->where('status','Cancelled')->count(),
                ];
                $currentStatus = request()->query('status', 'all');
            @endphp

            <div style="margin-bottom:16px;">
                <h3 class="font-extrabold text-gray-800 text-lg leading-tight">
                    <i class="fa-solid fa-box-open text-[#0046ab] mr-2"></i>Đơn hàng của tôi
                </h3>
                <p class="text-xs text-gray-400 font-bold mt-0.5">Theo dõi lịch sử mua hàng của tài khoản</p>
            </div>

            {{-- TABS TRẠNG THÁI --}}
            <div style="background:#fff; border-radius:16px; padding:0 8px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,.06); border:1px solid #f0f0f0; overflow:hidden;">
                <div class="order-tabs">
                    @php
                        $tabs = [
                            ['key'=>'all',       'icon'=>'fa-list',       'label'=>'Tất cả'],
                            ['key'=>'Pending',   'icon'=>'fa-clock',      'label'=>'Chờ xử lý'],
                            ['key'=>'Shipping',  'icon'=>'fa-truck',      'label'=>'Đang giao'],
                            ['key'=>'Delivered', 'icon'=>'fa-circle-check','label'=>'Hoàn thành'],
                            ['key'=>'Cancelled', 'icon'=>'fa-ban',        'label'=>'Đã hủy'],
                        ];
                    @endphp
                    @foreach($tabs as $tab)
                    <a href="{{ route('cart.tracking', ['status'=>$tab['key']]) }}"
                       class="order-tab {{ $currentStatus === $tab['key'] ? 'active' : '' }}"
                       style="text-decoration:none;">
                        <span class="tab-count">{{ $statusCounts[$tab['key']] }}</span>
                        <span><i class="fa-solid {{ $tab['icon'] }}" style="margin-right:4px;font-size:11px;"></i>{{ $tab['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- DANH SÁCH ĐƠN HÀNG CỦA USER --}}
            <div id="orders-list">
            @if($orders->isEmpty())
                <div class="empty-box">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <p>Bạn chưa có đơn hàng nào{{ $currentStatus !== 'all' ? ' ở trạng thái này' : '' }}.</p>
                    <a href="{{ url('/') }}" style="display:inline-block; margin-top:16px; padding:10px 24px; background:#4f46e5; color:#fff; border-radius:10px; font-weight:700; text-decoration:none;">Mua sắm ngay</a>
                </div>
            @else
                @foreach($orders as $order)
                @php
                    $badgeClass = match($order['status']) {
                        'Pending','BaoCK' => 'badge-pending',
                        'Shipping'        => 'badge-shipping',
                        'Delivered'       => 'badge-delivered',
                        'Cancelled'       => 'badge-cancelled',
                        default           => 'badge-other',
                    };
                    $badgeLabel = match($order['status']) {
                        'Pending'   => 'CHỜ XỬ LÝ',
                        'BaoCK'     => 'BÁO CHUYỂN KHOẢN',
                        'Shipping'  => 'ĐANG GIAO',
                        'Delivered' => 'HOÀN THÀNH',
                        'Cancelled' => 'ĐÃ HỦY',
                        default     => strtoupper($order['status']),
                    };
                    // Stepper: 4 bước cố định
                    $steps = [
                        ['icon'=>'fa-cart-shopping',  'label'=>'Chờ xử lý'],
                        ['icon'=>'fa-credit-card',    'label'=>'Đã thanh toán'],
                        ['icon'=>'fa-truck',          'label'=>'Đang giao'],
                        ['icon'=>'fa-box-archive',    'label'=>'Hoàn thành'],
                    ];
                    $activeStep = match($order['status']) {
                        'Pending','BaoCK' => 0,
                        'Shipping'        => 2,
                        'Delivered'       => 3,
                        'Cancelled'       => -1,
                        default           => 1,
                    };
                @endphp
                <div class="order-card" id="order-card-{{ $order['order_id'] }}">
                    {{-- Header card --}}
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px; margin-bottom:4px;">
                        <div>
                            <span style="font-size:15px; font-weight:800; color:#111827;">
                                <i class="fa-regular fa-rectangle-list" style="color:#4f46e5; margin-right:6px;"></i>
                                Đơn hàng #{{ $order['order_code'] ?? $order['order_id'] }}
                            </span>
                            <div style="font-size:12px; color:#9ca3af; margin-top:2px;">
                                <i class="fa-regular fa-clock" style="margin-right:4px;"></i>
                                {{ \Carbon\Carbon::parse($order['created_at'])->format('H:i · d/m/Y') }}
                            </div>
                        </div>
                        <span class="status-badge {{ $badgeClass }}">
                            <span style="width:7px;height:7px;border-radius:50%;background:currentColor;display:inline-block;opacity:.7;"></span>
                            {{ $badgeLabel }}
                        </span>
                    </div>

                    {{-- Stepper ngang --}}
                    @if($order['status'] !== 'Cancelled')
                    <div class="stepper">
                        @foreach($steps as $si => $step)
                        @php
                            $isDone   = $si < $activeStep;
                            $isActive = $si === $activeStep;
                            $cls = $isDone ? 'done' : ($isActive ? 'active' : '');
                        @endphp
                        <div class="step-item {{ $cls }} {{ $isDone ? 'done' : '' }}">
                            <div class="step-circle">
                                <i class="fa-solid {{ $isDone ? 'fa-check' : $step['icon'] }}"></i>
                            </div>
                            <span class="step-label">{{ $step['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Sản phẩm --}}
                    @foreach($order['items'] as $item)
                    <div class="product-row">
                        <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['product_name'] }}"
                             onerror="this.src='{{ asset('images/no-image.png') }}'">
                        <div style="flex:1; min-width:0;">
                            <div class="product-name">{{ $item['product_name'] }}</div>
                            <div class="product-qty">Số lượng: {{ $item['quantity'] }}</div>
                        </div>
                        <div class="product-price">{{ number_format($item['price'] * $item['quantity']) }}đ</div>
                    </div>
                    @endforeach

                    {{-- Footer card --}}
                    <div class="card-footer">
                        <div class="card-meta">
                            <span><i class="fa-solid fa-user"></i> {{ $order['customer_name'] }}</span>
                            <span><i class="fa-solid fa-phone"></i> {{ $order['customer_phone'] }}</span>
                            @if($order['shipping_address'])
                            <span><i class="fa-solid fa-location-dot"></i> {{ Str::limit($order['shipping_address'], 40) }}</span>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; flex-wrap:wrap; gap:8px;">
                        <span class="total-label">Tổng tiền</span>
                        <span class="total-amount">{{ number_format($order['final_amount']) }}đ</span>
                    </div>
                </div>
                @endforeach
            @endif
            </div>
        </div>
        @endif

    </div>
</div>

<!-- Modal hiển thị sản phẩm -->
<div id="products-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden" style="z-index: 1000;">
    <!-- Hộp thoại Modal trượt nhẹ -->
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[85vh] animate-modal-in overflow-hidden">

        <!-- Header Modal -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i class="fa-solid fa-list-check text-[#0046ab] text-lg"></i>
                </div>
                <div>
                    <h2 class="font-bold text-gray-800 text-base leading-tight">Sản phẩm đã đặt</h2>
                    <p id="modal-order-code" class="text-xs text-gray-400 font-medium"></p>
                </div>
            </div>
            <button onclick="closeProductsModal()" class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-50 hover:text-red-500 text-gray-500 transition-all">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Khung chứa danh sách chi tiết các item (tự động có thanh cuộn nếu danh sách dài) -->
        <div id="result-products" class="overflow-y-auto px-6 py-4 space-y-0 flex-1"></div>

        <!-- Footer Modal: Hiển thị tổng tiền -->
        <div class="flex-shrink-0 border-t border-dashed border-gray-200 px-6 py-4 flex justify-between items-center bg-gray-50">
            <span class="text-sm font-bold text-gray-500">TỔNG TIỀN ĐƠN HÀNG:</span>
            <span id="modal-total" class="text-xl font-black text-red-600"></span>
        </div>
    </div>
</div>

<!-- Claim Request Modal -->
<div id="claimModal" class="fixed inset-0 z-[9999] hidden" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); justify-content: center; align-items: center; padding: 12px;">
    <div id="claimModalContent" style="background: #fff; border-radius: 16px; width: 100%; max-width: 480px; max-height: 90vh; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); transform: scale(0.95); opacity: 0; transition: transform 0.3s ease, opacity 0.3s ease; display: flex; flex-direction: column;">
        <!-- Modal Header -->
        <div id="claimModalHeader" style="padding: 12px 18px; background: #f59e0b; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <h3 id="claimModalTitle" style="font-size: 16px; font-weight: 700; color: #ffffff; margin: 0;">Gửi yêu cầu đổi trả sản phẩm</h3>
            <button type="button" onclick="closeClaimModal()" style="background: none; border: none; font-size: 18px; color: #ffffff; cursor: pointer; display: flex; align-items: center;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <!-- Modal Body (Có thể cuộn nếu màn hình nhỏ) -->
        <form id="claimForm" style="padding: 16px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;" onsubmit="submitClaim(event)" enctype="multipart/form-data">
            @csrf
            
            <!-- Hộp thông tin sản phẩm và IMEI tinh gọn -->
            <div style="background: #f8fafc; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px;">
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    <span style="font-weight: 700; color: #475569;">Sản phẩm:</span>
                    <span style="color: #1e293b;" id="modalProductNameDisplay"></span>
                </div>
                <div style="display: flex; gap: 6px; align-items: center; margin-top: 6px; padding-top: 6px; border-top: 1px dashed #e2e8f0;">
                    <span style="font-weight: 700; color: #475569;">IMEI:</span>
                    <span style="color: #0f172a; font-family: monospace; font-weight: 600;" id="modalImeiDisplay"></span>
                </div>
            </div>

            <!-- Các input ẩn chứa giá trị để gửi form -->
            <input type="hidden" id="modalProductName">
            <input type="hidden" id="modalImei" name="imei_serial">

            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Loại yêu cầu</label>
                <select id="modalClaimType" name="claim_type" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 500; outline: none; background: #fff;" required>
                    <option value="warranty">Bảo hành sửa chữa</option>
                    <option value="return">Đổi trả hàng hoàn tiền</option>
                    <option value="exchange">Đổi máy mới/khách</option>
                </select>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Họ tên</label>
                    <input type="text" id="modalCustomerName" name="customer_name" value="{{ auth()->user() ? auth()->user()->full_name : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px;" required>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Số điện thoại</label>
                    <input type="text" id="modalCustomerPhone" name="customer_phone" value="{{ auth()->user() ? auth()->user()->phone_number : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px;" required>
                </div>
            </div>
            
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Mô tả chi tiết lý do</label>
                <textarea id="modalReason" name="reason" placeholder="Vui lòng cung cấp thêm thông tin chi tiết về sự cố hoặc lý do..." style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; height: 72px; resize: none; outline: none;" required></textarea>
            </div>
            
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Hình ảnh / video minh họa</label>
                <input type="file" id="modalMediaFile" name="media_file" accept="image/*,video/*" style="width: 100%; font-size: 12px;">
                <span style="font-size: 10px; color: #94a3b8; display: block; margin-top: 2px;">Dung lượng tối đa 20MB. Chấp nhận ảnh/video.</span>
            </div>

            <!-- Refund Method (Only shown for return) -->
            <div id="refundMethodSection" style="display: none; flex-direction: column; gap: 4px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;">Phương thức nhận tiền hoàn</label>
                <select id="modalRefundMethod" name="refund_method" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;background:#fff;">
                    <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                    <option value="cash">Tiền mặt tại cửa hàng</option>
                </select>
            </div>
            <!-- Bank Details (Only shown for return) -->
            <div id="bankDetailsSection" style="display: none; border-top: 1px dashed #e2e8f0; padding-top: 12px; margin-top: 4px; flex-direction: column; gap: 10px;">
                <h4 style="font-size: 13px; font-weight: 700; color: #d97706; margin: 0; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-building-columns"></i> Thông tin nhận tiền hoàn
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Ngân hàng</label>
                        <input type="text" id="modalBankName" name="bank_name" placeholder="VD: Vietcombank" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Số tài khoản</label>
                        <input type="text" id="modalBankAccountNumber" name="bank_account_number" placeholder="VD: 1023456789" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Tên chủ tài khoản</label>
                    <input type="text" id="modalBankAccountName" name="bank_account_name" placeholder="VD: NGUYEN VAN A" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;text-transform: uppercase;">
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 4px; padding-top: 12px; border-top: 1px solid #f1f5f9; flex-shrink: 0;">
                <button type="button" class="btn-lookup" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 8px 16px; font-size: 13px;" onclick="closeClaimModal()">Hủy</button>
                <button type="submit" class="btn-lookup" id="btnSubmitClaim" style="padding: 8px 16px; background: #f59e0b; color: #fff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 13px;">Gửi yêu cầu</button>
            </div>
        </form>
    </div>
</div>

<style>
/* CSS Keyframe hoạt ảnh trượt lên khi mở Modal */
@keyframes modalIn {
    from { opacity: 0; transform: translateY(32px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0)   scale(1);    }
}
.animate-modal-in { animation: modalIn 0.28s cubic-bezier(.22,1,.36,1) both; }
</style>

<script>
    let currentOrderCustomerName = '';
    let currentOrderCustomerPhone = '';

    // Mở hộp thoại Modal và tạm khóa thanh cuộn toàn trang web
    function openProductsModal() {
        document.getElementById('products-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Đóng hộp thoại Modal và trả lại thanh cuộn bình thường
    function closeProductsModal() {
        document.getElementById('products-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Lắng nghe phím ESC để đóng nhanh Modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeProductsModal();
    });

    /**
     * 1. AJAX: THỰC THI TRA CỨU ĐƠN HÀNG (DO SEARCH REQUEST)
     * Gửi yêu cầu GET lên route `/orders/search?code=...` hoặc `/orders/search?phone=...`.
     * Quản lý ẩn/hiện các khung thông báo Loading, No Result và Panel kết quả hợp lý.
     */
    let currentOrders = [];

    function doSearch(e) {
        e.preventDefault();
        closeProductsModal();
        
        const loading   = document.getElementById('loading');
        const result    = document.getElementById('trackingResult');
        const noResult  = document.getElementById('noResult');
        const listResult = document.getElementById('orderListResult');
        const codeInput = document.getElementById('input-code');
        const loggedInOrders = document.getElementById('logged-in-orders');

        // Reset trạng thái hiển thị
        result.classList.add('hidden');
        noResult.classList.add('hidden');
        listResult.classList.add('hidden');
        if (loggedInOrders) loggedInOrders.classList.add('hidden');
        
        const code = codeInput.value.trim();
        if (!code) return;

        // Kiểm tra xem đầu vào có phải là Số điện thoại hay không
        const isPhone = /^[+]?[0-9]{9,12}$/.test(code.replace(/[\s.-]/g, ''));
        if (isPhone) {
            doSearchPhone(code);
            return;
        }

        loading.classList.remove('hidden');

        fetch(`/orders/search?code=${encodeURIComponent(code)}`)
            .then(res => {
                if (!res.ok) throw new Error('Không tìm thấy đơn hàng');
                return res.json();
            })
            .then(data => {
                loading.classList.add('hidden');
                if (data.success) {
                    showResult(data);
                } else {
                    noResult.classList.remove('hidden');
                }
            })
            .catch(err => {
                loading.classList.add('hidden');
                noResult.classList.remove('hidden');
            });
    }

    function doSearchPhone(phone) {
        const loading   = document.getElementById('loading');
        const result    = document.getElementById('trackingResult');
        const noResult  = document.getElementById('noResult');
        const listResult = document.getElementById('orderListResult');
        const loggedInOrders = document.getElementById('logged-in-orders');

        result.classList.add('hidden');
        noResult.classList.add('hidden');
        listResult.classList.add('hidden');
        if (loggedInOrders) loggedInOrders.classList.add('hidden');
        loading.classList.remove('hidden');

        fetch(`/orders/search?phone=${encodeURIComponent(phone)}`)
            .then(res => {
                if (!res.ok) throw new Error('Không tìm thấy lịch sử đơn hàng');
                return res.json();
            })
            .then(data => {
                loading.classList.add('hidden');
                if (data.success && data.multiple) {
                    showOrderList(data.orders);
                } else if (data.success) {
                    showResult(data);
                } else {
                    noResult.classList.remove('hidden');
                }
            })
            .catch(err => {
                loading.classList.add('hidden');
                noResult.classList.remove('hidden');
            });
    }

    function showOrderList(orders) {
        currentOrders = orders;
        const listResult = document.getElementById('orderListResult');
        const container = document.getElementById('order-list-container');
        document.getElementById('order-list-count').textContent = orders.length;

        container.innerHTML = '';
        orders.forEach((order, index) => {
            container.innerHTML += `
                <div class="border border-gray-100 rounded-2xl p-5 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-50/50 transition-all duration-300 bg-gray-50/30">
                    <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
                        <div>
                            <span class="text-xs text-gray-400 font-bold block mb-1">MÃ ĐƠN HÀNG</span>
                            <span class="font-extrabold text-gray-800 text-lg">#${order.order_code || order.order_id}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-400 font-bold block mb-1">NGÀY MUA</span>
                            <span class="text-sm font-semibold text-gray-600">${order.created_at}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-3 border-t border-b border-gray-100/80 my-3">
                        <div>
                            <span class="text-xs text-gray-400 font-bold block mb-0.5">Người nhận</span>
                            <span class="text-sm font-bold text-gray-700">${order.customer_name}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 font-bold block mb-0.5">Số điện thoại</span>
                            <span class="text-sm font-semibold text-gray-600">${order.customer_phone}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 font-bold block mb-0.5">Trạng thái</span>
                            <span class="text-[10px] font-black px-2 py-0.5 rounded inline-block ${order.status_color}">${order.status_label}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-4">
                        <div>
                            <span class="text-xs text-gray-400 font-bold block">Tổng tiền</span>
                            <span class="text-lg font-black text-red-600">${new Intl.NumberFormat('vi-VN').format(order.final_amount)}đ</span>
                        </div>
                        <button onclick="showOrderProductsModal(${index})" 
                                class="px-5 py-2.5 bg-[#0046ab] hover:bg-blue-800 text-white text-sm font-bold rounded-xl shadow-md shadow-blue-100 hover:shadow-lg transition-all duration-200 flex items-center gap-2">
                            Xem sản phẩm & Hỗ trợ <i class="fa-solid fa-angle-right"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        listResult.classList.remove('hidden');
        listResult.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function showOrderProductsModal(index) {
        const order = currentOrders[index];
        if (order) {
            showResult(order);
            openProductsModal();
        }
    }

    /**
     * 2. HIỂN THỊ KẾT QUẢ ĐƠN HÀNG LÊN GIAO DIỆN
     */
    function showResult(data) {
        currentOrderCustomerName = data.customer_name || '';
        currentOrderCustomerPhone = data.customer_phone || '';
        const isOwner = data.is_owner || false;

        document.getElementById('order-id-badge').textContent = '#' + (data.order_code || data.order_id);
        document.getElementById('result-name').textContent    = data.customer_name;
        
        // Cập nhật nhãn trạng thái và tô màu phù hợp (VD: Chờ duyệt, Thành công)
        const statusEl = document.getElementById('result-status');
        statusEl.textContent = data.status_label;
        statusEl.className = 'text-[10px] font-black px-2 py-0.5 rounded ' + data.status_color;

        // Định dạng tiền tệ VND
        document.getElementById('result-address').textContent = data.shipping_address;
        const formatted = new Intl.NumberFormat('vi-VN').format(data.final_amount) + 'đ';
        document.getElementById('result-total').textContent  = formatted;
        document.getElementById('modal-total').textContent   = formatted;
        
        const orderCode = '#' + (data.order_code || data.order_id);
        document.getElementById('modal-order-code').textContent = 'Mã đơn: ' + orderCode;

        // Dựng HTML danh sách sản phẩm chi tiết
        const productsContainer = document.getElementById('result-products');
        productsContainer.innerHTML = '';
        if (data.items && data.items.length > 0) {
            data.items.forEach(item => {
                const imgUrl = item.image || 'https://via.placeholder.com/56x56?text=SP';
                let imeiHTML = '';
                if (item.units && item.units.length > 0) {
                    imeiHTML = `
                        <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 6px;">
                            ${item.units.map(unit => {
                                if (!unit.imei_serial) return '';
                                let buttonsHTML = '';
                                if (unit.can_claim_warranty && data.status === 'Delivered') {
                                    buttonsHTML += `
                                        <button onclick="openClaimModal('${unit.imei_serial}', '${item.product_name.replace(/'/g, "\\'")}', 'warranty')" 
                                                style="padding: 3px 8px; font-size: 11px; background: #0046ab; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 2px;">
                                            <i class="fa-solid fa-screwdriver-wrench" style="font-size: 9px;"></i> BH
                                        </button>
                                    `;
                                } else if (isOwner && data.status === 'Delivered') {
                                    buttonsHTML += `
                                        <a href="/profile?action=repair&imei=${unit.imei_serial}&product=${encodeURIComponent(item.product_name)}" 
                                           style="padding: 3px 8px; font-size: 11px; background: #64748b; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 2px; text-decoration: none;">
                                            <i class="fa-solid fa-screwdriver-wrench" style="font-size: 9px;"></i> Sửa chữa
                                        </a>
                                    `;
                                }
                                if (unit.can_claim_return && data.status === 'Delivered') {
                                    buttonsHTML += `
                                        <button onclick="openClaimModal('${unit.imei_serial}', '${item.product_name.replace(/'/g, "\\'")}', 'return')" 
                                                style="padding: 3px 8px; font-size: 11px; background: #f59e0b; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 2px;">
                                            <i class="fa-solid fa-rotate-left" style="font-size: 9px;"></i> Đổi trả
                                        </button>
                                    `;
                                }

                                let claimsHTML = '';
                                if (unit.claims && unit.claims.length > 0) {
                                    claimsHTML = `
                                        <div style="margin-top: 6px; padding: 6px 10px; background: #eff6ff; border-radius: 6px; border-left: 3px solid #0046ab; font-size: 11px; width: 100%; text-align: left;">
                                            <div style="font-weight: bold; color: #1e40af; margin-bottom: 4px;"><i class="fa-solid fa-clock-rotate-left"></i> Lịch sử yêu cầu hỗ trợ:</div>
                                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                                \${unit.claims.map(c => {
                                                    let typeStr = c.claim_type === 'warranty' ? 'Bảo hành' : (c.claim_type === 'return' ? 'Đổi trả' : 'Đổi máy');
                                                    let statusStr = c.status === 'pending' ? 'Chờ duyệt' : (c.status === 'approved' ? 'Đã duyệt' : 'Từ chối');
                                                    let statusColor = c.status === 'pending' ? '#b45309' : (c.status === 'approved' ? '#15803d' : '#b91c1c');
                                                    let replyHTML = c.admin_note ? `<div style="color: #64748b; padding-left: 8px; font-style: italic; margin-top: 2px;">↳ Phản hồi: \${c.admin_note}</div>` : '';
                                                    return `
                                                        <div>
                                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                                <span style="color: #475569;">• Yêu cầu \${typeStr} (\${c.created_at})</span>
                                                                <span style="font-weight: bold; color: \${statusColor};">\${statusStr}</span>
                                                            </div>
                                                            \${replyHTML}
                                                        </div>
                                                    `;
                                                }).join('')}
                                            </div>
                                        </div>
                                    `;
                                }

                                return `
                                    <div style="display: flex; flex-direction: column; background: #f8fafc; padding: 6px 10px; border-radius: 6px; border: 1px solid #e2e8f0; gap: 4px;">
                                        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                            <span style="font-family: monospace; font-size: 11px; color: #334155; font-weight: bold;">
                                                \${unit.imei_serial}
                                            </span>
                                            <div style="display: flex; gap: 4px;">
                                                \${buttonsHTML}
                                            </div>
                                        </div>
                                        \${claimsHTML}
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    `;
                }
                productsContainer.innerHTML += `
                    <div style="display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #f3f4f6;">
                        <img src="\${imgUrl}" style="width:56px; height:56px; min-width:56px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;" onerror="this.src='/images/no-image.png'">
                        <div style="flex:1; min-width:0; word-break:normal; overflow-wrap:anywhere;">
                            <div style="font-size:13px; font-weight:700; color:#1f2937; line-height:1.4;">\${item.product_name}</div>
                            \${imeiHTML}
                            <div style="font-size:12px; color:#6b7280; margin-top:4px;">Số lượng: \${item.quantity}</div>
                        </div>
                        <div style="font-size:13px; font-weight:800; color:#1f2937; white-space:nowrap; padding-left:8px;">\${new Intl.NumberFormat('vi-VN').format(item.price)}đ</div>
                    </div>
                `;
            });
        }

        // Cập nhật mốc trạng thái timeline hành trình
        updateTimeline(data.status);

        // Hiển thị khung kết quả và tự động cuộn màn hình xuống vùng kết quả
        const result = document.getElementById('trackingResult');
        result.classList.remove('hidden');
        result.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * 3. ĐỒNG BỘ HÓA SƠ ĐỒ HÀNH TRÌNH ĐƠN HÀNG (TIMELINE DYNAMIC LOGIC)
     * Đọc chuỗi `status` trả về từ server để lần lượt gán class active/completed cho các mốc:
     * - 'Pending' hoặc 'BaoCK' (Đã báo chuyển khoản): Mốc 2 hiển thị spin chờ duyệt.
     * - 'Cancelled' (Đơn hàng bị hủy): Mốc 2 hiển thị thông báo đã hủy.
     * - 'Shipping' (Đang giao): Mốc 1, 2, 3 hiển thị tích xanh completed, mốc 4 active.
     * - 'Delivered' hoặc 'Completed' (Giao thành công): Kích hoạt tích xanh cho tất cả 5 mốc.
     */
    function updateTimeline(status) {
        const steps = document.querySelectorAll('.relative.space-y-10 > .flex');
        
        // Reset sạch tất cả mốc về trạng thái xám ban đầu
        steps.forEach(step => {
            step.classList.remove('step-completed', 'step-active');
            step.querySelector('.tracking-dot').className = 'tracking-dot';
            
            const h4 = step.querySelector('h4');
            const p = step.querySelector('p');
            if (h4) h4.className = 'font-bold text-gray-300 text-base';
            if (p) p.className = 'text-gray-300 text-sm';
        });

        // Hàm gán mốc đang diễn ra (Active)
        const setActive = (stepIndex, title, desc, iconClass) => {
            const step = steps[stepIndex];
            if (!step) return;
            step.classList.add('step-active');
            step.querySelector('.tracking-dot').className = 'tracking-dot border-[#0046ab] text-[#0046ab]';
            step.querySelector('.tracking-dot').innerHTML = `<i class="${iconClass}"></i>`;
            const h4 = step.querySelector('h4');
            const p = step.querySelector('p');
            if (h4) {
                h4.textContent = title;
                h4.className = 'font-bold text-[#0046ab] text-base';
            }
            if (p) {
                p.textContent = desc;
                p.className = 'text-gray-500 text-sm';
            }
        };

        // Hàm gán mốc đã hoàn thành xong (Completed)
        const setCompleted = (stepIndex, title, desc, iconClass) => {
            const step = steps[stepIndex];
            if (!step) return;
            step.classList.add('step-completed');
            step.querySelector('.tracking-dot').className = 'tracking-dot bg-[#0046ab] border-[#0046ab] text-white';
            step.querySelector('.tracking-dot').innerHTML = `<i class="${iconClass}"></i>`;
            const h4 = step.querySelector('h4');
            const p = step.querySelector('p');
            if (h4) {
                h4.textContent = title;
                h4.className = 'font-bold text-gray-800 text-base';
            }
            if (p) {
                p.textContent = desc;
                p.className = 'text-gray-500 text-sm';
            }
        };

        // Bước 1 mặc định luôn hoàn thành (Đã đặt hàng thành công)
        setCompleted(0, 'Đã đặt hàng thành công', 'Hệ thống đã ghi nhận đơn hàng của bạn.', 'fa-solid fa-file-invoice');

        if (status === 'Pending' || status === 'BaoCK') {
            setActive(1, 'Chờ duyệt thanh toán', 'Giao dịch đang chờ Admin xác thực.', 'fa-solid fa-circle-notch fa-spin');
        } else if (status === 'Cancelled') {
            setActive(1, 'Đơn hàng đã bị hủy', 'Đơn hàng này không còn hiệu lực.', 'fa-solid fa-circle-xmark text-red-500');
        } else {
            setCompleted(1, 'Đã xác nhận thanh toán', 'Giao dịch đã được xác thực thành công.', 'fa-solid fa-check-double');
            
            if (status === 'Shipping') {
                setCompleted(2, 'Đang đóng gói', 'Sản phẩm đang được đóng gói chuẩn bị giao.', 'fa-solid fa-box-open');
                setActive(3, 'Đang vận chuyển', 'Sản phẩm đang được vận chuyển tới quý khách.', 'fa-solid fa-truck text-blue-500');
            } else if (status === 'Delivered' || status === 'Completed') {
                setCompleted(2, 'Đã đóng gói', 'Sản phẩm đã được kiểm tra và đóng gói.', 'fa-solid fa-box-open');
                setCompleted(3, 'Đang vận chuyển', 'Sản phẩm đã được giao cho đơn vị vận chuyển.', 'fa-solid fa-truck');
                setCompleted(4, 'Đã giao hàng thành công', 'Đơn hàng đã được giao đến tay quý khách.', 'fa-solid fa-house-circle-check');
            } else {
                setActive(2, 'Đang xử lý chuẩn bị hàng', 'Sản phẩm đang được chuẩn bị.', 'fa-solid fa-circle-notch fa-spin');
            }
        }
    }

    // Làm sạch form để tìm kiếm đơn mới
    function resetSearch() {
        document.getElementById('trackingResult').classList.add('hidden');
        document.getElementById('noResult').classList.add('hidden');
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('orderListResult').classList.add('hidden');
        document.getElementById('input-code').value = '';
        const loggedInOrders = document.getElementById('logged-in-orders');
        if (loggedInOrders) loggedInOrders.classList.remove('hidden');
        document.getElementById('input-code').focus();
    }

    // Tự động tra cứu hoặc highlight nếu có query parameter 'code', 'search' hoặc 'new_order' từ URL
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const code = urlParams.get('code') || urlParams.get('search') || urlParams.get('new_order');
        if (code) {
            // Kiểm tra xem đơn hàng có sẵn trong danh sách DOM hay không (nếu là user đã đăng nhập)
            const card = document.getElementById('order-card-' + code);
            if (card) {
                card.classList.add('new-order-highlight');
                setTimeout(() => {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            } else {
                // Nếu không có trong DOM, thực hiện tìm kiếm AJAX
                const inputCode = document.getElementById('input-code');
                if (inputCode) {
                    inputCode.value = code;
                    // Giả lập submit form
                    const formCode = document.getElementById('form-code');
                    if (formCode) {
                        const event = new Event('submit', { cancelable: true });
                        formCode.dispatchEvent(event);
                    }
                }
            }
        }
    });

    function openClaimModal(imei, productName, defaultType) {
        // Hide products modal first
        document.getElementById('products-modal').classList.add('hidden');
        
        document.getElementById('modalImei').value = imei;
        document.getElementById('modalProductName').value = productName;
        
        // Cập nhật thông tin hiển thị tinh gọn
        document.getElementById('modalProductNameDisplay').textContent = productName;
        document.getElementById('modalImeiDisplay').textContent = imei;
        
        // Cấu hình động các tùy chọn loại yêu cầu và giao diện
        const claimTypeSelect = document.getElementById('modalClaimType');
        const header = document.getElementById('claimModalHeader');
        const title = document.getElementById('claimModalTitle');
        const submitBtn = document.getElementById('btnSubmitClaim');
        
        if (defaultType === 'warranty') {
            claimTypeSelect.innerHTML = '<option value="warranty">Bảo hành sửa chữa (Miễn phí)</option>';
            header.style.background = '#0046ab';
            title.textContent = 'Gửi yêu cầu bảo hành chính hãng';
            submitBtn.style.background = '#0046ab';
            submitBtn.textContent = 'Gửi yêu cầu bảo hành';
        } else {
            claimTypeSelect.innerHTML = `
                <option value="return">Đổi trả hàng hoàn tiền</option>
                <option value="exchange">Đổi máy mới/khách</option>
            `;
            header.style.background = '#f59e0b';
            title.textContent = 'Gửi yêu cầu đổi trả sản phẩm';
            submitBtn.style.background = '#f59e0b';
            submitBtn.textContent = 'Gửi yêu cầu đổi trả';
        }
        claimTypeSelect.value = defaultType;
        
        document.getElementById('modalCustomerName').value = currentOrderCustomerName;
        document.getElementById('modalCustomerPhone').value = currentOrderCustomerPhone;
        document.getElementById('modalReason').value = '';
        
        const mediaInput = document.getElementById('modalMediaFile');
        if (mediaInput) {
            mediaInput.value = '';
        }

        const refMethod = document.getElementById('modalRefundMethod');
        if (refMethod) refMethod.value = 'bank_transfer';
        
        // Cập nhật trạng thái hiển thị các trường ngân hàng
        toggleBankFields();

        const modal = document.getElementById('claimModal');
        const content = document.getElementById('claimModalContent');
        
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        
        setTimeout(() => {
            content.style.transform = 'scale(1)';
            content.style.opacity = '1';
        }, 10);
    }

    function closeClaimModal() {
        const modal = document.getElementById('claimModal');
        const content = document.getElementById('claimModalContent');
        
        content.style.transform = 'scale(0.95)';
        content.style.opacity = '0';
        
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            // Show products modal back
            document.getElementById('products-modal').classList.remove('hidden');
        }, 300);
    }

    function submitClaim(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitClaim');
        const oldText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...';
        
        const mediaInput = document.getElementById('modalMediaFile');
        if (mediaInput && mediaInput.files.length > 0) {
            const file = mediaInput.files[0];
            if (file.size > 20 * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tệp quá lớn',
                    text: 'Dung lượng hình ảnh hoặc video minh họa không được vượt quá 20MB.',
                    confirmButtonColor: '#ef4444'
                });
                btn.disabled = false;
                btn.innerHTML = oldText;
                return;
            }
        }
        
        const formElement = document.getElementById('claimForm');
        const formData = new FormData(formElement);
        
        fetch('/warranty/claim', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(r => r.json().then(data => ({ status: r.status, body: data })))
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = oldText;
            
            if (res.status !== 200) {
                let errorMsg = res.body.message || 'Đã có lỗi xảy ra.';
                if (res.status === 419 || errorMsg === 'CSRF token mismatch.') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Phiên làm việc hết hạn',
                        text: 'Phiên làm việc của bạn đã hết hạn. Vui lòng tải lại trang để tiếp tục.',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Tải lại trang'
                    }).then(() => {
                        window.location.reload();
                    });
                    return;
                }
                if (res.body.errors) {
                    errorMsg = Object.values(res.body.errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi gửi yêu cầu',
                    html: errorMsg,
                    confirmButtonColor: '#ef4444'
                });
            } else {
                closeClaimModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: res.body.message,
                    confirmButtonColor: '#0046ab'
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = oldText;
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể kết nối đến máy chủ. Vui lòng thử lại.',
                confirmButtonColor: '#ef4444'
            });
        });
    }

    function toggleBankFields() {
        const sel = document.getElementById('modalClaimType');
        const refundMethodSection = document.getElementById('refundMethodSection');
        const bankSection = document.getElementById('bankDetailsSection');
        if (!sel) return;

        const refundMethodSelect = document.getElementById('modalRefundMethod');
        const isReturn = (sel.value === 'return');

        if (refundMethodSection) {
            refundMethodSection.style.display = isReturn ? 'flex' : 'none';
        }

        if (bankSection) {
            const inputs = bankSection.querySelectorAll('input');
            const isBankTransfer = isReturn && (refundMethodSelect ? refundMethodSelect.value === 'bank_transfer' : true);

            if (isBankTransfer) {
                bankSection.style.display = 'flex';
                inputs.forEach(input => input.setAttribute('required', 'true'));
            } else {
                bankSection.style.display = 'none';
                inputs.forEach(input => {
                    input.removeAttribute('required');
                    input.value = '';
                });
            }
        }
    }
    document.getElementById('modalClaimType').addEventListener('change', toggleBankFields);
    const refMethodEl = document.getElementById('modalRefundMethod');
    if (refMethodEl) {
        refMethodEl.addEventListener('change', toggleBankFields);
    }
</script>
@endsection
