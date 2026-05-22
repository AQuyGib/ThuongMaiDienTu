@extends('layouts.app')
@section('title', 'Thanh toán - DIENMAYPRO')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
.pay-radio:checked ~ .pay-label { border-color:#2563eb; background:#eff6ff; }
.pay-radio:checked ~ .pay-label .dot-outer { border-color:#2563eb; }
.pay-radio:checked ~ .pay-label .dot-inner { opacity:1; }
.method-panel { display:none; }
.method-panel.active { display:block; }
@keyframes scanLine {
  0%,100%{top:0;opacity:0} 50%{top:calc(100% - 4px);opacity:1}
}
.qr-scan-line { animation: scanLine 2.5s ease-in-out infinite; }
.step-done { background:#16a34a!important; }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
<div class="max-w-6xl mx-auto px-4">

  {{-- Breadcrumb --}}
  <nav class="text-sm text-gray-500 mb-6">
    <a href="{{ url('/') }}" class="hover:text-blue-600">Trang chủ</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cart.index') }}" class="hover:text-blue-600">Giỏ hàng</a>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-semibold">Thanh toán</span>
  </nav>

  {{-- Progress Steps --}}
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

  <form id="checkout-form" method="POST" action="{{ route('cart.place-order') }}" class="flex flex-col lg:flex-row gap-6">
    @csrf
    <input type="hidden" name="payment_method" id="payment_method_input" value="COD">
    <input type="hidden" name="wallet_points_used" id="wallet_points_used_input" value="0">

    {{-- ===== CỘT TRÁI ===== --}}
    <div class="w-full lg:w-3/5 space-y-5">

      {{-- Thông tin người nhận --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-bold mb-4 flex items-center gap-2 text-gray-800">
          <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
          Thông tin người nhận
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Họ và tên *</label>
            <input id="inp-name" name="customer_name" type="text" required
              class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
              value="{{ Auth::check() ? Auth::user()->name : '' }}" placeholder="Nguyễn Văn A">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Số điện thoại *</label>
            <input id="inp-phone" name="customer_phone" type="tel" required
              class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
              value="{{ Auth::check() && Auth::user()->phone ? Auth::user()->phone : '' }}" placeholder="0901234567">
          </div>
        </div>
        <div class="mt-4">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Địa chỉ giao hàng *</label>
          <input id="inp-address" name="shipping_address" type="text" required
            class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
            placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành">
        </div>
        <div class="mt-4">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Ghi chú (tùy chọn)</label>
          <textarea id="inp-note" name="note" rows="2"
            class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm resize-none"
            placeholder="Giao giờ hành chính, gọi trước khi giao..."></textarea>
        </div>
      </div>

      {{-- Phương thức thanh toán --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-bold mb-4 flex items-center gap-2 text-gray-800">
          <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
          Phương thức thanh toán
        </h2>
        <div class="space-y-3" id="payment-methods">

          {{-- QR Code --}}
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

          {{-- COD --}}
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


        {{-- COD Panel --}}
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

    {{-- ===== CỘT PHẢI ===== --}}
    <div class="w-full lg:w-2/5">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-4">
        <h2 class="text-base font-bold mb-4 text-gray-800 border-b pb-3 flex items-center justify-between">
          <span>Đơn hàng của bạn</span>
          <span id="item-badge" class="text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full font-bold">0 sản phẩm</span>
        </h2>

        {{-- Danh sách sản phẩm --}}
        <div id="order-items" class="space-y-3 mb-5 max-h-56 overflow-y-auto pr-1">
          <p class="text-sm text-gray-400 text-center py-6">Đang tải đơn hàng...</p>
        </div>

        {{-- Mã giảm giá --}}
        <div class="mb-5 bg-gray-50 rounded-xl border border-gray-100 p-4">
          <label class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">Mã giảm giá</label>
          <div class="flex gap-2">
            <input id="discount-code" type="text"
              class="flex-1 p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-400 outline-none"
              placeholder="VD: PRO10">
            <button type="button" onclick="applyDiscount()" id="btn-discount"
              class="px-4 bg-gray-800 text-white text-sm rounded-lg font-semibold hover:bg-gray-900 transition whitespace-nowrap">
              Áp dụng
            </button>
          </div>
          <p id="discount-msg" class="text-xs mt-2 hidden font-medium"></p>
        </div>

        <div class="mb-5 bg-blue-50 rounded-xl border border-blue-100 p-4">
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-bold text-blue-700 uppercase tracking-wide">Điểm tiêu dùng</label>
            <span id="wallet-balance" class="text-xs font-semibold text-blue-600">{{ Auth::check() ? number_format(($balance['wallet_points'] ?? 0)) : 0 }} điểm</span>
          </div>
          <p class="text-[11px] text-blue-700 mb-2">Điểm đã được chuyển sang trang đổi thưởng <a href="{{ route('rewards.index') }}" class="font-semibold underline">/rewards</a>.</p>
        </div>

        {{-- Tóm tắt tiền --}}
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

        {{-- Nút đặt hàng --}}
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

  </div>
</div>
</div>

{{-- Success Overlay --}}
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
// ---- CONFIG ----
const BANK = { id: 'MB', account: '123456789', name: 'DIENMAYPRO' };

// ---- STATE ----
let cartItems = [];
let subtotalVal = 0;
let discountVal = 0;
let currentMethod = 'cod';

// ---- FORMAT ----
const fmt = n => new Intl.NumberFormat('vi-VN').format(n || 0) + 'đ';

// ---- LOAD CART FROM SESSIONSTORAGE ----
function loadCart() {
  try {
    const raw = sessionStorage.getItem('checkoutItems');
    if (raw) cartItems = JSON.parse(raw);
  } catch(e) {}

  renderItems();
}

function renderItems() {
  const el = document.getElementById('order-items');
  if (!cartItems.length) {
    el.innerHTML = '<p class="text-sm text-gray-400 text-center py-4">Không có sản phẩm.</p>';
    document.getElementById('btn-order').disabled = true;
    return;
  }
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

function updateTotals() {
  const total = subtotalVal - discountVal;
  document.getElementById('sum-subtotal').textContent = fmt(subtotalVal);
  document.getElementById('sum-total').textContent = fmt(total > 0 ? total : 0);
  document.getElementById('discount_amount_input').value = discountVal;
  if (discountVal > 0) {
    document.getElementById('sum-discount-row').classList.remove('hidden');
    document.getElementById('sum-discount').textContent = '-' + fmt(discountVal);
  } else {
    document.getElementById('sum-discount-row').classList.add('hidden');
  }
}

// ---- QR (Đã chuyển sang trang maQR) ----
// ---- PAYMENT METHOD ----
function selectMethod(method) {
  currentMethod = method;

  // Reset all labels
  document.querySelectorAll('.pay-label').forEach(l => {
    l.classList.remove('border-blue-500','bg-blue-50','border-pink-400','bg-pink-50','border-green-400','bg-green-50');
    l.classList.add('border-gray-200');
    l.querySelector('.dot-outer').style.borderColor = '#d1d5db';
    l.querySelector('.dot-inner').style.opacity = '0';
  });

  // Activate selected
  const sel = document.querySelector(`label[for="pm-${method}"]`);
  if (sel) {
    sel.classList.remove('border-gray-200');
    const colors = {qr:['border-blue-500','bg-blue-50','#2563eb'], cod:['border-green-500','bg-green-50','#16a34a']};
    const [bc, bg, dc] = colors[method] || colors.qr;
    sel.classList.add(bc, bg);
    sel.querySelector('.dot-outer').style.borderColor = dc;
    sel.querySelector('.dot-inner').style.opacity = '1';
    sel.querySelector('.dot-inner').style.backgroundColor = dc;
  }

  // Show/hide panels
  document.getElementById('cod-panel')?.classList.remove('active');
  if (method === 'qr') {
    // Không hiện panel QR nữa, sẽ redirect khi bấm xác nhận
  } else {
    document.getElementById('cod-panel')?.classList.add('active');
  }

  checkFormValidity();
}

// ---- DISCOUNT ----
function applyDiscount() {
  const inp = document.getElementById('discount-code');
  const btn = document.getElementById('btn-discount');
  const msg = document.getElementById('discount-msg');

  if (btn.textContent.trim() === 'Áp dụng') {
    const code = inp.value.trim().toUpperCase();
    if (!code) return;
    btn.textContent = '...';
    btn.disabled = true;
    setTimeout(() => {
      if (code === 'PRO10') {
        discountVal = Math.round(subtotalVal * 0.1);
        inp.readOnly = true;
        inp.classList.add('bg-green-50','border-green-400','text-green-700');
        btn.textContent = 'Xóa'; btn.disabled = false;
        btn.classList.replace('bg-gray-800','bg-red-500');
        msg.className = 'text-xs mt-2 font-medium text-green-600';
        msg.innerHTML = '<i class="fa-solid fa-circle-check mr-1"></i>Giảm 10% thành công!';
      } else {
        btn.textContent = 'Áp dụng'; btn.disabled = false;
        msg.className = 'text-xs mt-2 font-medium text-red-500';
        msg.innerHTML = '<i class="fa-solid fa-circle-xmark mr-1"></i>Mã không hợp lệ!';
      }
      msg.classList.remove('hidden');
      updateTotals();
    }, 500);
  } else {
    discountVal = 0;
    inp.value = ''; inp.readOnly = false;
    inp.classList.remove('bg-green-50','border-green-400','text-green-700');
    btn.textContent = 'Áp dụng';
    btn.classList.replace('bg-red-500','bg-gray-800');
    msg.classList.add('hidden');
    updateTotals();
  }
}

// ---- FORM VALIDITY ----
function checkFormValidity() {
  const name = document.getElementById('inp-name').value.trim();
  const phone = document.getElementById('inp-phone').value.trim();
  const addr = document.getElementById('inp-address').value.trim();
  const valid = name && phone && /^0[0-9]{8,9}$/.test(phone) && addr;
  const btn = document.getElementById('btn-order');
  btn.disabled = !valid;
}

['inp-name','inp-phone','inp-address'].forEach(id => {
  document.getElementById(id)?.addEventListener('input', checkFormValidity);
});

// ---- SUBMIT ----
document.getElementById('checkout-form')?.addEventListener('submit', function (e) {
  e.preventDefault();

  const btn = document.getElementById('btn-order');
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Đang xử lý...';
  btn.disabled = true;

  const payload = new FormData(this);
  payload.set('customer_name', document.getElementById('inp-name').value.trim());
  payload.set('customer_phone', document.getElementById('inp-phone').value.trim());
  payload.set('shipping_address', document.getElementById('inp-address').value.trim());
  payload.set('note', document.getElementById('inp-note').value.trim());
  payload.set('payment_method', currentMethod === 'qr' ? 'VNPAY' : 'COD');

  fetch(this.action, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
      'Accept': 'application/json'
    },
    body: payload
  })
    .then(async (response) => {
      const data = await response.json();
      if (!response.ok || !data.success) {
        throw new Error(data.message || 'Không thể tạo đơn hàng');
      }
      sessionStorage.removeItem('checkoutItems');
      sessionStorage.removeItem('paymentTotal');
      window.location.href = data.redirect_url;
    })
    .catch((error) => {
      alert(error.message || 'Đã xảy ra lỗi');
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-lock mr-2 text-sm"></i>XÁC NHẬN ĐẶT HÀNG';
    });
});

// ---- INIT ----
document.addEventListener('DOMContentLoaded', () => {
  loadCart();
  selectMethod('cod');
});
</script>
@endpush
