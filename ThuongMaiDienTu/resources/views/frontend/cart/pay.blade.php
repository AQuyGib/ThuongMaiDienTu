@extends('layouts.app')
@section('title', 'Thanh toán - DIENMAYPRO')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
/* ============================================================
   CSS HIỆU ỨNG TƯƠNG TÁC GIAO DIỆN THANH TOÁN (CHECKOUT)
   ============================================================ */

/* Trạng thái checked của phương thức thanh toán: Đổi màu viền và màu nền sang xanh nhạt */
.pay-radio:checked ~ .pay-label { border-color:#2563eb; background:#eff6ff; }
.pay-radio:checked ~ .pay-label .dot-outer { border-color:#2563eb; }
.pay-radio:checked ~ .pay-label .dot-inner { opacity:1; }

/* Ẩn/hiện panel hướng dẫn chi tiết cho từng phương thức thanh toán */
.method-panel { display:none; }
.method-panel.active { display:block; }

/* Hiệu ứng quét dòng sáng neon xanh lục trên mã QR Code giả lập */
@keyframes scanLine {
  0%,100%{top:0;opacity:0} 50%{top:calc(100% - 4px);opacity:1}
}
.qr-scan-line { animation: scanLine 2.5s ease-in-out infinite; }

/* Trạng thái hoàn thành của vòng tròn các bước thanh toán (Progress Step Done) */
.step-done { background:#16a34a!important; }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
<div class="max-w-6xl mx-auto px-4">

  <!-- BREADCRUMB: ĐIỀU HƯỚNG LIÊN KẾT NHANH -->
  <nav class="text-sm text-gray-500 mb-6">
    <a href="{{ url('/') }}" class="hover:text-blue-600">Trang chủ</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cart.index') }}" class="hover:text-blue-600">Giỏ hàng</a>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-semibold">Thanh toán</span>
  </nav>

  <!-- THANH TIẾN TRÌNH THANH TOÁN (PROGRESS STEPS BAR) -->
  <div class="flex items-center gap-3 mb-8">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center text-sm font-bold">✓</div>
      <span class="text-sm font-semibold text-green-600 hidden sm:inline">Giỏ hàng</span>
    </div>
    <div class="flex-1 h-0.5 bg-blue-500"></div>
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">2</div>
      <span class="text-sm font-semibold text-blue-600 hidden sm:inline">Thanh toán</span>
    </div>
    <div class="flex-1 h-0.5 bg-gray-200"></div>
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
      <span class="text-sm font-semibold text-gray-400 hidden sm:inline">Xác nhận</span>
    </div>
  </div>

  <!-- BIỂU MẪU ĐẶT HÀNG AJAX -->
  <form id="checkout-form" method="POST" action="{{ route('cart.place-order') }}" class="flex flex-col lg:flex-row gap-6">
    @csrf
    <!-- Input ẩn lưu phương thức thanh toán và điểm thưởng sử dụng để gửi lên Backend -->
    <input type="hidden" name="payment_method" id="payment_method_input" value="COD">
    <input type="hidden" name="wallet_points_used" id="wallet_points_used_input" value="0">

    <!-- ============================================================
         CỘT TRÁI (3/5 chiều rộng): THÔNG TIN NGƯỜI NHẬN & PHƯƠNG THỨC THANH TOÁN
         ============================================================ -->
    <div class="w-full lg:w-3/5 space-y-5">

      <!-- Khối 1: Thông tin liên hệ và địa chỉ giao hàng -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-bold mb-4 flex items-center gap-2 text-gray-800">
          <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
          Thông tin người nhận
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Họ và tên khách hàng -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Họ và tên *</label>
            <input id="inp-name" name="customer_name" type="text" required maxlength="50"
              class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
              value="{{ Auth::check() ? Auth::user()->name : '' }}" placeholder="Nguyễn Văn A">
            <p id="err-name" class="text-xs text-red-500 mt-1 hidden"></p>
          </div>
          <!-- Số điện thoại giao hàng -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Số điện thoại *</label>
            <input id="inp-phone" name="customer_phone" type="tel" required maxlength="10"
              class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
              value="{{ Auth::check() && Auth::user()->phone ? Auth::user()->phone : '' }}" placeholder="0901234567">
            <p id="err-phone" class="text-xs text-red-500 mt-1 hidden"></p>
          </div>
        </div>
        <!-- Địa chỉ nhận hàng chi tiết -->
        <div class="mt-4">
          <div class="flex justify-between items-center mb-1">
            <label class="block text-sm font-semibold text-gray-700">Địa chỉ giao hàng *</label>
            <span id="counter-address" class="text-xs text-gray-400 font-medium">0/150</span>
          </div>
          <input id="inp-address" name="shipping_address" type="text" required maxlength="150"
            class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
            placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành">
          <p id="err-address" class="text-xs text-red-500 mt-1 hidden"></p>
        </div>
        <!-- Ghi chú vận chuyển bổ sung -->
        <div class="mt-4">
          <div class="flex justify-between items-center mb-1">
            <label class="block text-sm font-semibold text-gray-700">Ghi chú (tùy chọn)</label>
            <span id="counter-note" class="text-xs text-gray-400 font-medium">0/250</span>
          </div>
          <textarea id="inp-note" name="note" rows="2" maxlength="250"
            class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm resize-none"
            placeholder="Giao giờ hành chính, gọi trước khi giao..."></textarea>
          <p id="err-note" class="text-xs text-red-500 mt-1 hidden"></p>
        </div>
      </div>

      <!-- Khối 2: Lựa chọn Phương thức thanh toán (COD hoặc chuyển khoản qua mã QR) -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-bold mb-4 flex items-center gap-2 text-gray-800">
          <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
          Phương thức thanh toán
        </h2>
        <div class="space-y-3" id="payment-methods">

          <!-- Tùy chọn 1: Chuyển khoản ngân hàng qua mã QR (Mặc định chọn) -->
          <div class="relative">
            <input type="radio" name="payment_method" id="pm-qr" value="qr" class="pay-radio sr-only" checked>
            <label for="pm-qr" onclick="selectMethod('qr')"
              class="pay-label flex items-center p-4 border-2 border-blue-500 bg-blue-50 rounded-xl cursor-pointer transition-all hover:border-blue-500">
              <div class="flex items-center gap-3 w-full">
                <div class="dot-outer w-5 h-5 rounded-full border-2 border-blue-500 flex items-center justify-center shrink-0">
                  <div class="dot-inner w-2.5 h-2.5 rounded-full bg-blue-500 opacity-100"></div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <p class="font-bold text-sm text-gray-800">Chuyển khoản qua mã QR (Ngân hàng)</p>
                    <span class="bg-red-100 text-red-600 text-[9px] px-2 py-0.5 rounded-full font-bold">KHUYÊN DÙNG</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-0.5">Hỗ trợ tất cả ứng dụng ngân hàng và ví điện tử. Tự động xác nhận.</p>
                </div>
                <i class="fa-solid fa-qrcode text-2xl text-blue-600 hidden sm:block"></i>
              </div>
            </label>
          </div>

          <!-- Tùy chọn 2: Thanh toán khi nhận hàng (COD) -->
          <div class="relative">
            <input type="radio" name="payment_method" id="pm-cod" value="cod" class="pay-radio sr-only">
            <label for="pm-cod" onclick="selectMethod('cod')"
              class="pay-label flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer transition-all hover:border-green-400">
              <div class="flex items-center gap-3 w-full">
                <div class="dot-outer w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0">
                  <div class="dot-inner w-2.5 h-2.5 rounded-full bg-blue-500 opacity-0"></div>
                </div>
                <div class="flex-1">
                  <p class="font-bold text-sm text-gray-800">Thanh toán khi nhận hàng (COD)</p>
                  <p class="text-xs text-gray-500 mt-0.5">Trả tiền mặt cho nhân viên giao hàng.</p>
                </div>
                <i class="fa-solid fa-hand-holding-dollar text-2xl text-green-600 hidden sm:block"></i>
              </div>
            </label>
          </div>
        </div>

        <!-- Panel hướng dẫn thêm đối với phương thức COD -->
        <div id="cod-panel" class="mt-5 p-4 bg-green-50 border border-green-200 rounded-2xl method-panel">
          <div class="flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-green-600 mt-0.5"></i>
            <div class="text-sm text-green-800">
              <p class="font-bold">Thanh toán khi nhận hàng</p>
              <p class="mt-1 text-green-700">Bạn sẽ thanh toán bằng tiền mặt khi nhân viên giao hàng đến. Vui lòng chuẩn bị đúng số tiền để thuận tiện cho quá trình giao hàng.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================
         CỘT PHẢI (2/5 chiều rộng): TÓM TẮT ĐƠN HÀNG, KHUYẾN MÃI VÀ NÚT XÁC NHẬN ĐẶT HÀNG
         ============================================================ -->
    <div class="w-full lg:w-2/5">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-4">
        <h2 class="text-base font-bold mb-4 text-gray-800 border-b pb-3 flex items-center justify-between">
          <span>Đơn hàng của bạn</span>
          <span id="item-badge" class="text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full font-bold">0 sản phẩm</span>
        </h2>

        <!-- Khung danh sách các sản phẩm tóm lược trong giỏ hàng (hỗ trợ cuộn dọc) -->
        <div id="order-items" class="space-y-3 mb-5 max-h-56 overflow-y-auto pr-1">
          <p class="text-sm text-gray-400 text-center py-6">Đang tải đơn hàng...</p>
        </div>

        <!-- Khung áp dụng Coupon giảm giá -->
        <div class="mb-5 bg-gray-50 rounded-xl border border-gray-100 p-4">
          <div class="flex justify-between items-center mb-2">
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Mã giảm giá</label>
            <!-- Đường dẫn chuyển nhanh sang trang xem toàn bộ Voucher khả dụng -->
            <a href="{{ route('cart.discount-code') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 transition">
              <i class="fa-solid fa-ticket text-sm"></i> Chọn Voucher
            </a>
          </div>
          <div class="flex gap-2">
            <!-- Ô nhập mã coupon -->
            <input id="discount-code" type="text"
              class="flex-1 p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-400 outline-none"
              value="{{ session('applied_coupon_code') }}"
              placeholder="VD: SUMMER30">
            <button type="button" onclick="applyDiscount()" id="btn-discount"
              class="px-4 bg-gray-800 text-white text-sm rounded-lg font-semibold hover:bg-gray-900 transition whitespace-nowrap">
              Áp dụng
            </button>
          </div>
          <p id="discount-msg" class="text-xs mt-2 hidden font-medium"></p>
        </div>

        <!-- Khung Điểm tiêu dùng: Hướng dẫn người dùng sang trang đổi thưởng /rewards -->
        <div class="mb-5 bg-blue-50 rounded-xl border border-blue-100 p-4">
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-bold text-blue-700 uppercase tracking-wide">Điểm tiêu dùng</label>
            <span id="wallet-balance" class="text-xs font-semibold text-blue-600">{{ Auth::check() ? number_format(($balance['wallet_points'] ?? 0)) : 0 }} điểm</span>
          </div>
          <p class="text-[11px] text-blue-700 mb-2">Điểm đã được chuyển sang trang đổi thưởng <a href="{{ route('rewards.index') }}" class="font-semibold underline">/rewards</a>.</p>
        </div>

        <!-- Khối hiển thị phân tích số tiền đơn hàng -->
        <div class="space-y-2.5 text-sm border-t pt-4">
          <div class="flex justify-between text-gray-600">
            <span>Tạm tính</span>
            <span id="sum-subtotal" class="font-medium">0đ</span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Phí vận chuyển</span>
            <span class="font-medium text-green-600">Miễn phí</span>
          </div>
          <div id="sum-discount-row" class="flex justify-between text-gray-600 hidden">
            <span>Giảm giá</span>
            <span id="sum-discount" class="font-medium text-green-600">-0đ</span>
          </div>
          <div id="sum-wallet-row" class="flex justify-between text-gray-600 hidden">
            <span>Điểm tiêu dùng</span>
            <span id="sum-wallet" class="font-medium text-green-600">-0đ</span>
          </div>
          <div class="flex justify-between items-end pt-3 border-t">
            <span class="font-bold text-gray-800">Thành tiền</span>
            <span id="sum-total" class="text-2xl font-bold text-red-600">0đ</span>
          </div>
          <p class="text-right text-xs text-gray-400 italic">Đã bao gồm VAT</p>
          <input type="hidden" name="discount_amount" id="discount_amount_input" value="0">
        </div>

        <!-- Nút đặt hàng: Mặc định bị khóa (disabled) cho đến khi người dùng điền đủ thông tin hợp lệ ở cột trái -->
        <button type="submit" id="btn-order"
          class="w-full mt-5 bg-red-600 text-white py-3.5 rounded-xl font-bold text-base hover:bg-red-700 transition-all shadow-md disabled:bg-gray-300 disabled:cursor-not-allowed disabled:shadow-none"
          disabled>
          <i class="fa-solid fa-lock mr-2 text-sm"></i>XÁC NHẬN ĐẶT HÀNG
        </button>

        <a href="{{ route('cart.index') }}" class="block mt-3 text-center text-sm text-blue-600 hover:underline">
          <i class="fa-solid fa-arrow-left mr-1"></i>Quay lại giỏ hàng
        </a>
      </div>
    </div>

  </form>
</div>
</div>

<!-- LỚP PHỦ THÀNH CÔNG (SUCCESS OVERLAY): Báo đặt hàng COD thành công -->
<div id="success-overlay" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center backdrop-blur-sm">
  <div class="bg-white rounded-3xl p-10 text-center max-w-sm mx-4 shadow-2xl">
    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
      <i class="fa-solid fa-circle-check text-5xl text-green-500"></i>
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-2">Đặt hàng thành công!</h3>
    <p class="text-gray-500 text-sm mb-6">Cảm ơn bạn đã mua hàng. Chúng tôi sẽ liên hệ xác nhận sớm nhất.</p>
    <a href="{{ url('/') }}" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
      Về trang chủ
    </a>
  </div>
</div>
@endsection

@push('scripts')
<script>
// ---- CẤU HÌNH NGÂN HÀNG CƠ BẢN ----
const BANK = { id: 'MB', account: '123456789', name: 'DIENMAYPRO' };

// ---- TRẠNG THÁI SỐ LIỆU BAN ĐẦU ----
let cartItems = [];
let subtotalVal = 0;
let discountVal = 0;
let currentMethod = 'cod';

// Tiện ích định dạng VND
const fmt = n => new Intl.NumberFormat('vi-VN').format(n || 0) + 'đ';

/**
 * 1. NẠP DỮ LIỆU SẢN PHẨM GIỎ HÀNG TỪ LARAVEL
 * Nhận dữ liệu truyền từ controller qua $cartItems và chuyển thành Object JS.
 */
function loadCart() {
  try {
    const raw = '{!! json_encode($cartItems) !!}';
    cartItems = JSON.parse(raw);
  } catch(e) {
    console.error("Lỗi nạp giỏ hàng từ server:", e);
    cartItems = [];
  }

  renderItems();
}

/**
 * 2. RENDER DANH SÁCH SẢN PHẨM TÓM TẮT
 * Vẽ lại danh sách sản phẩm ở cột phải.
 */
function renderItems() {
  const el = document.getElementById('order-items');
  if (!cartItems.length) {
    el.innerHTML = '<p class="text-sm text-gray-400 text-center py-4">Không có sản phẩm.</p>';
    document.getElementById('btn-order').disabled = true;
    return;
  }
  // Tính tổng tiền tạm tính trước khi chiết khấu
  subtotalVal = cartItems.reduce((s, i) => s + i.price * i.quantity, 0);
  el.innerHTML = cartItems.map(i => `
    <div class="flex justify-between items-start gap-3 text-sm">
      <div class="flex gap-1.5 flex-1 min-w-0">
        <span class="shrink-0 font-bold text-gray-500">${i.quantity}×</span>
        <p class="text-gray-800 font-medium leading-snug truncate" title="${i.name}">${i.name}</p>
      </div>
      <span class="shrink-0 font-bold text-gray-800">${fmt(i.price * i.quantity)}</span>
    </div>`).join('');
  document.getElementById('item-badge').textContent = cartItems.length + ' sản phẩm';
  updateTotals();
  checkFormValidity();
}

/**
 * 3. CẬP NHẬT TỔNG TIỀN CỦA ĐƠN HÀNG
 * Lấy tạm tính trừ giảm giá coupon để ra thành tiền cuối cùng.
 */
function updateTotals() {
  const total = subtotalVal - discountVal;
  document.getElementById('sum-subtotal').textContent = fmt(subtotalVal);
  document.getElementById('sum-total').textContent = fmt(total > 0 ? total : 0);
  document.getElementById('discount_amount_input').value = discountVal;
  
  // Hiển thị/ẩn hàng giảm giá coupon
  if (discountVal > 0) {
    document.getElementById('sum-discount-row').classList.remove('hidden');
    document.getElementById('sum-discount').textContent = '-' + fmt(discountVal);
  } else {
    document.getElementById('sum-discount-row').classList.add('hidden');
  }
}

/**
 * 4. LỰA CHỌN PHƯƠNG THỨC THANH TOÁN (COD HOẶC QR BANK)
 * Thay đổi viền nhãn, màu chấm tròn radio giả lập, ẩn/hiện các panel hướng dẫn tương ứng.
 */
function selectMethod(method) {
  currentMethod = method;
  document.getElementById('payment_method_input').value = method.toUpperCase();

  // Reset toàn bộ giao diện viền của các label phương thức
  document.querySelectorAll('.pay-label').forEach(l => {
    l.classList.remove('border-blue-500','bg-blue-50','border-pink-400','bg-pink-50','border-green-400','bg-green-50');
    l.classList.add('border-gray-200');
    l.querySelector('.dot-outer').style.borderColor = '#d1d5db';
    l.querySelector('.dot-inner').style.opacity = '0';
  });

  // Áp dụng class active màu sắc tương ứng cho phương thức được chọn
  const sel = document.querySelector(`label[for="pm-${method}"]`);
  if (sel) {
    sel.classList.remove('border-gray-200');
    const colors = {
        qr: ['border-blue-500', 'bg-blue-50', '#2563eb'], 
        cod: ['border-green-500', 'bg-green-50', '#16a34a']
    };
    const [bc, bg, dc] = colors[method] || colors.qr;
    sel.classList.add(bc, bg);
    sel.querySelector('.dot-outer').style.borderColor = dc;
    sel.querySelector('.dot-inner').style.opacity = '1';
    sel.querySelector('.dot-inner').style.backgroundColor = dc;
  }

  // Ẩn/hiện panel mô tả chi tiết của phương thức COD
  document.getElementById('cod-panel')?.classList.remove('active');
  if (method === 'cod') {
    document.getElementById('cod-panel')?.classList.add('active');
  }

  checkFormValidity();
}

/**
 * 5. AJAX: ÁP DỤNG / HỦY BỎ MÃ GIẢM GIÁ (COUPON DISCOUNT)
 * Gọi Fetch API lên route `cart.apply-coupon`.
 * Nếu áp dụng thành công: Khóa input sang readonly, đổi nút thành "Xóa", hiển thị text báo thành công.
 * Nếu bấm Xóa: Mở khóa input, reset số tiền chiết khấu, đổi nút về "Áp dụng".
 */
function applyDiscount() {
  const inp = document.getElementById('discount-code');
  const btn = document.getElementById('btn-discount');
  const msg = document.getElementById('discount-msg');

  if (btn.textContent.trim() === 'Áp dụng') {
    const code = inp.value.trim().toUpperCase();
    if (!code) return;
    btn.textContent = '...';
    btn.disabled = true;

    fetch('{{ route("cart.apply-coupon") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ code: code })
    })
    .then(async (response) => {
      const payload = await response.json();
      if (!response.ok || !payload.success) {
        throw new Error(payload.message || 'Mã không hợp lệ!');
      }
      return payload;
    })
    .then((payload) => {
      discountVal = Number(payload.discount || 0);
      inp.readOnly = true;
      inp.classList.add('bg-green-50','border-green-400','text-green-700');
      btn.textContent = 'Xóa'; btn.disabled = false;
      btn.classList.replace('bg-gray-800','bg-red-500');
      msg.className = 'text-xs mt-2 font-medium text-green-600';
      msg.innerHTML = `<i class="fa-solid fa-circle-check mr-1"></i>${payload.message || 'Áp dụng mã thành công!'}`;
      msg.classList.remove('hidden');
      updateTotals();
    })
    .catch((error) => {
      discountVal = 0;
      btn.textContent = 'Áp dụng'; btn.disabled = false;
      msg.className = 'text-xs mt-2 font-medium text-red-500';
      msg.innerHTML = `<i class="fa-solid fa-circle-xmark mr-1"></i>${error.message}`;
      msg.classList.remove('hidden');
      updateTotals();
    });
  } else {
    // Luồng HỦY bỏ coupon đang áp dụng
    fetch('{{ route("cart.apply-coupon") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ code: '' })
    })
    .then(r => r.json())
    .then(res => {
      discountVal = 0;
      inp.value = ''; inp.readOnly = false;
      inp.classList.remove('bg-green-50','border-green-400','text-green-700');
      btn.textContent = 'Áp dụng';
      btn.classList.replace('bg-red-500','bg-gray-800');
      msg.classList.add('hidden');
      updateTotals();
    });
  }
}

/**
 * 6. KIỂM TRA HỢP LỆ TOÀN BỘ BIỂU MẪU Ở CLIENT-SIDE (FORM VALIDATION LOGIC)
 * - Họ tên: Không chứa số, không chứa ký tự đặc biệt, dài 2-50 ký tự.
 * - SĐT: Bắt đầu bằng 0, độ dài 9-10 chữ số, không chứa chữ.
 * - Địa chỉ: Dài 10-150 ký tự, không chứa ký tự đặc biệt (chỉ cho phép , . - /). Cập nhật số đếm ký tự thực tế.
 * - Ghi chú: Tối đa 250 ký tự. Cập nhật số đếm ký tự thực tế.
 * Nếu tất cả đều hợp lệ, thuộc tính disabled trên nút đặt hàng sẽ được gỡ bỏ.
 */
function checkFormValidity() {
  const nameInp = document.getElementById('inp-name');
  const phoneInp = document.getElementById('inp-phone');
  const addrInp = document.getElementById('inp-address');
  const noteInp = document.getElementById('inp-note');

  const name = nameInp ? nameInp.value : '';
  const phone = phoneInp ? phoneInp.value : '';
  const addr = addrInp ? addrInp.value : '';
  const note = noteInp ? noteInp.value : '';

  const errName = document.getElementById('err-name');
  const errPhone = document.getElementById('err-phone');
  const errAddr = document.getElementById('err-address');
  const errNote = document.getElementById('err-note');

  let nameValid = true;
  let phoneValid = true;
  let addrValid = true;
  let noteValid = true;

  // 6.1. Họ và tên validation
  if (name.length > 0 && /\d/.test(name)) {
    if (errName) {
      errName.textContent = 'Nhập họ và tên bằng chữ';
      errName.classList.remove('hidden');
    }
    nameValid = false;
  } else if (name.length > 0 && /[!@#$%^&*()_+=\[\]{}|\\:;"'<>,.?\/~`]/.test(name)) {
    if (errName) {
      errName.textContent = 'Họ và tên không được chứa ký tự đặc biệt';
      errName.classList.remove('hidden');
    }
    nameValid = false;
  } else if (name.trim().length > 0 && name.trim().length < 2) {
    if (errName) {
      errName.textContent = 'Họ và tên phải từ 2 ký tự trở lên';
      errName.classList.remove('hidden');
    }
    nameValid = false;
  } else if (name.trim().length > 50) {
    if (errName) {
      errName.textContent = 'Họ và tên tối đa 50 ký tự';
      errName.classList.remove('hidden');
    }
    nameValid = false;
  } else {
    if (errName) errName.classList.add('hidden');
    if (name.trim().length === 0) nameValid = false;
  }

  // 6.2. Số điện thoại validation
  if (/[a-zA-Z]/.test(phone)) {
    if (errPhone) {
      errPhone.textContent = 'Bạn chỉ nhập số';
      errPhone.classList.remove('hidden');
    }
    phoneValid = false;
  } else if (phone.length > 0 && !/^0[0-9]{8,9}$/.test(phone)) {
    if (errPhone) {
      errPhone.textContent = 'Số điện thoại phải từ 9-10 chữ số và bắt đầu bằng số 0';
      errPhone.classList.remove('hidden');
    }
    phoneValid = false;
  } else {
    if (errPhone) errPhone.classList.add('hidden');
    if (phone.length === 0) phoneValid = false;
  }

  // 6.3. Địa chỉ giao hàng validation
  const addrLen = addr.length;
  const counterAddr = document.getElementById('counter-address');
  if (counterAddr) {
    counterAddr.textContent = `${addrLen}/150`;
  }
  if (addrLen > 0 && addrLen < 10) {
    if (errAddr) {
      errAddr.textContent = 'Địa chỉ giao hàng phải từ 10 ký tự trở lên';
      errAddr.classList.remove('hidden');
    }
    addrValid = false;
  } else if (addrLen > 150) {
    if (errAddr) {
      errAddr.textContent = 'Địa chỉ giao hàng tối đa 150 ký tự';
      errAddr.classList.remove('hidden');
    }
    addrValid = false;
  } else if (addrLen > 0 && /[!@#$%^&*()_+=\[\]{}|\\:;"'<>?~`]/.test(addr)) {
    if (errAddr) {
      errAddr.textContent = 'Địa chỉ không chứa ký tự đặc biệt (ngoại trừ , . - /)';
      errAddr.classList.remove('hidden');
    }
    addrValid = false;
  } else {
    if (errAddr) errAddr.classList.add('hidden');
    if (addrLen === 0) addrValid = false;
  }

  // 6.4. Ghi chú validation
  const noteLen = note.length;
  const counterNote = document.getElementById('counter-note');
  if (counterNote) {
    counterNote.textContent = `${noteLen}/250`;
  }
  if (noteLen > 250) {
    if (errNote) {
      errNote.textContent = 'Ghi chú tối đa 250 ký tự';
      errNote.classList.remove('hidden');
    }
    noteValid = false;
  } else {
    if (errNote) errNote.classList.add('hidden');
  }

  // Bật/tắt nút Xác nhận đặt hàng
  const btn = document.getElementById('btn-order');
  if (btn) {
    btn.disabled = !(nameValid && phoneValid && addrValid && noteValid);
  }
}

// Lắng nghe sự kiện gõ phím trên tất cả các input để kích hoạt hàm check liên tục
['inp-name', 'inp-phone', 'inp-address', 'inp-note'].forEach(id => {
  document.getElementById(id)?.addEventListener('input', checkFormValidity);
});

/**
 * 7. AJAX SUBMIT - GỬI ĐƠN HÀNG LÊN SERVER PHÍA SAU
 * - Double check tính hợp lệ của dữ liệu trước khi gửi.
 * - Khóa nút và thay đổi nhãn sang "Đang xử lý..." để chống spam request (Double Submit Prevention).
 * - Gửi Fetch POST lên `/cart/confirm` kèm token CSRF.
 * - Nếu thành công:
 *   + Tải lại badge giỏ hàng trên Header bằng cách fetch `/cart/count`.
 *   + Nếu là thanh toán COD: Hiện lớp phủ thành công `success-overlay` tại chỗ.
 *   + Nếu là thanh toán QR: Điều hướng sang trang mã QR ngân hàng động (`/cart/qr?order_id=...`).
 */
document.getElementById('checkout-form')?.addEventListener('submit', function (e) {
  e.preventDefault();

  const name = document.getElementById('inp-name').value.trim();
  const phone = document.getElementById('inp-phone').value.trim();
  const addr = document.getElementById('inp-address').value.trim();
  const note = document.getElementById('inp-note').value.trim();
  const discountInp = document.getElementById('discount-code');
  const discountCode = discountInp && discountInp.readOnly ? discountInp.value.trim().toUpperCase() : '';

  const isNameInvalid = /\d/.test(name) || /[!@#$%^&*()_+=\[\]{}|\\:;"'<>,.?\/~`]/.test(name) || name.length < 2 || name.length > 50;
  const isPhoneInvalid = /[a-zA-Z]/.test(phone) || !/^0[0-9]{8,9}$/.test(phone);
  const isAddrInvalid = addr.length < 10 || addr.length > 150 || /[!@#$%^&*()_+=\[\]{}|\\:;"'<>?~`]/.test(addr);
  const isNoteInvalid = note.length > 250;

  if (isNameInvalid || isPhoneInvalid || isAddrInvalid || isNoteInvalid) {
    alert('Vui lòng kiểm tra lại thông tin nhập vào hợp lệ!');
    return;
  }

  const btn = document.getElementById('btn-order');
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Đang xử lý...';
  btn.disabled = true;

  const data = {
    name: name,
    phone: phone,
    address: addr,
    note: note,
    payment_method: currentMethod,
    discount_code: discountCode
  };

  fetch('{{ route("cart.confirm") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(res => {
    if (res.status === 'success') {
      // AJAX cập nhật lại Badge số lượng giỏ hàng trên Header
      const badge = document.getElementById('headerCartBadge');
      if (badge) {
        fetch('{{ route("cart.count") }}')
          .then(r => r.json())
          .then(d => {
             badge.innerText = d.cart_count;
             if (d.cart_count === 0) badge.style.display = 'none';
          });
      }

      // Xử lý điều hướng/hiển thị tùy phương thức
      if (currentMethod === 'qr') {
        window.location.href = "{{ route('cart.qr') }}?order_id=" + res.order_id;
      } else {
        document.getElementById('success-overlay').classList.remove('hidden');
      }
    } else {
      alert(res.message || 'Đã xảy ra lỗi khi đặt hàng!');
      btn.innerHTML = '<i class="fa-solid fa-lock mr-2 text-sm"></i>XÁC NHẬN ĐẶT HÀNG';
      btn.disabled = false;
    }
  })
  .catch(err => {
    console.error(err);
    alert('Đã xảy ra lỗi hệ thống!');
    btn.innerHTML = '<i class="fa-solid fa-lock mr-2 text-sm"></i>XÁC NHẬN ĐẶT HÀNG';
    btn.disabled = false;
  });
});

// Khởi chạy các thiết lập ban đầu sau khi tải trang
document.addEventListener('DOMContentLoaded', () => {
  loadCart();
  selectMethod('cod'); // COD được chọn làm mặc định ban đầu
  
  // Tự động kiểm tra và áp dụng voucher nếu có sẵn trong session của PHP
  const initialCode = document.getElementById('discount-code').value.trim();
  if (initialCode) {
    applyDiscount();
  }
});
</script>
@endpush
