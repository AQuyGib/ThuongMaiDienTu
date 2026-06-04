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
<div style="background:#f8f7ff; min-height:100vh; padding:32px 0;">
<div style="max-width:860px; margin:0 auto; padding:0 16px;">

    {{-- TIÊU ĐỀ --}}
    <div style="margin-bottom:24px;">
        <h1 style="font-size:22px; font-weight:800; color:#111827; margin:0 0 4px;">
            <i class="fa-solid fa-box-open" style="color:#4f46e5; margin-right:8px;"></i>Đơn hàng của tôi
        </h1>
        <p style="font-size:14px; color:#6b7280; margin:0;">Theo dõi và quản lý tất cả đơn hàng của bạn</p>
    </div>

    {{-- TÌM KIẾM THEO MÃ (Dùng chung cho cả khách và thành viên) --}}
    <div class="search-wrap">
        <input type="text" id="search-input" placeholder="Tìm theo mã đơn hàng (VD: ORD2026...)">
        <button onclick="doSearchCode()"><i class="fa-solid fa-magnifying-glass" style="margin-right:6px;"></i>Tra cứu</button>
    </div>
    <div id="search-result" style="display:none;" class="order-card"></div>

    @if(!Auth::check())
    {{-- Chưa đăng nhập --}}
    <div class="order-card" style="text-align:center; padding:48px 24px;">
        <i class="fa-solid fa-user-lock" style="font-size:48px; color:#d1d5db; display:block; margin-bottom:16px;"></i>
        <p style="font-size:16px; font-weight:600; color:#374151; margin:0 0 8px;">Vui lòng đăng nhập để xem toàn bộ danh sách đơn hàng</p>
        <p style="font-size:14px; color:#6b7280; margin:0 0 20px;">Hoặc bạn có thể tra cứu nhanh đơn hàng bất kỳ bằng ô tìm kiếm ở trên</p>
        <a href="{{ route('login') }}" style="display:inline-block; padding:10px 28px; background:#4f46e5; color:#fff; border-radius:10px; font-weight:700; text-decoration:none;">Đăng nhập ngay</a>
    </div>
    @else

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

    {{-- DANH SÁCH ĐƠN HÀNG --}}
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
    @endif

</div>
</div>
@endsection

@push('scripts')
<script>
function doSearchCode() {
    const code = document.getElementById('search-input').value.trim();
    if (!code) return;

    const resultBox = document.getElementById('search-result');
    resultBox.style.display = 'block';
    resultBox.innerHTML = '<div style="text-align:center;padding:20px;"><i class="fa-solid fa-circle-notch fa-spin" style="color:#4f46e5;font-size:24px;"></i></div>';

    fetch(`/orders/search?code=${encodeURIComponent(code)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                resultBox.innerHTML = `<div style="text-align:center;color:#dc2626;padding:20px;">
                    <i class="fa-solid fa-circle-xmark" style="font-size:32px;margin-bottom:8px;display:block;"></i>
                    <p style="font-weight:600;">Không tìm thấy đơn hàng với mã "<strong>${code}</strong>"</p></div>`;
                return;
            }
            const fmt = n => new Intl.NumberFormat('vi-VN').format(n) + 'đ';
            const itemsHtml = (data.items || []).map(item => `
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-top:1px solid #f3f4f6;">
                    <img src="${item.image||''}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;flex-shrink:0;" onerror="this.src='/images/no-image.png'">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:14px;font-weight:600;color:#111827;">${item.product_name}</div>
                        <div style="font-size:13px;color:#6b7280;margin-top:2px;">Số lượng: ${item.quantity}</div>
                    </div>
                    <div style="font-size:14px;font-weight:800;color:#111827;white-space:nowrap;">${fmt(item.price * item.quantity)}</div>
                </div>`).join('');
            resultBox.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                    <div>
                        <div style="font-size:15px;font-weight:800;color:#111827;">Đơn hàng #${data.order_code||data.order_id}</div>
                        <div style="font-size:12px;color:#9ca3af;margin-top:2px;">${data.customer_name} · ${data.customer_phone}</div>
                    </div>
                    <span style="background:#ede9fe;color:#4f46e5;font-size:12px;font-weight:700;padding:4px 12px;border-radius:20px;">${data.status_label}</span>
                </div>
                ${itemsHtml}
                <div style="display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid #e5e7eb;margin-top:4px;">
                    <span style="font-size:13px;font-weight:600;color:#6b7280;">Tổng tiền</span>
                    <span style="font-size:20px;font-weight:900;color:#dc2626;">${fmt(data.final_amount)}</span>
                </div>`;
        })
        .catch(() => {
            resultBox.innerHTML = `<div style="text-align:center;color:#dc2626;padding:20px;">Đã xảy ra lỗi. Vui lòng thử lại.</div>`;
        });
}

document.getElementById('search-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') doSearchCode();
});

// Auto search hoặc highlight nếu có ?code= hoặc ?new_order= trên URL
const urlParams = new URLSearchParams(window.location.search);
const urlCode = urlParams.get('code') || urlParams.get('new_order');
if (urlCode) {
    const card = document.getElementById('order-card-' + urlCode);
    if (card) {
        // Nếu tìm thấy thẻ đơn hàng trong DOM (thường là đã đăng nhập)
        card.classList.add('new-order-highlight');
        setTimeout(() => {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    } else {
        // Nếu không có trong DOM (chưa đăng nhập hoặc đang ở tab lọc khác)
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.value = urlCode;
            doSearchCode();
        }
    }
}
</script>
@endpush
