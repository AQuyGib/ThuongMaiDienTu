<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin thanh toán - DIENMAYPRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Tùy chỉnh radio button */
        .radio-custom:checked + label {
            border-color: #3b82f6; /* blue-500 */
            background-color: #eff6ff; /* blue-50 */
        }
        .radio-custom:checked + label .outer-circle {
            border-color: #3b82f6;
        }
        .radio-custom:checked + label .inner-circle {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans pb-12">

    <!-- Header giả lập -->
    <header class="bg-[#003399] text-white py-3 shadow-md mb-8">
        <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-bolt text-yellow-400 text-2xl"></i>
                <span class="text-2xl font-bold text-yellow-400 uppercase tracking-wider">Dienmaypro</span>
            </div>
            <div class="hidden md:flex gap-6 font-semibold">
                <a href="#" class="hover:text-yellow-400"><i class="fa-solid fa-house"></i> TRANG CHỦ</a>
                <a href="#" class="hover:text-yellow-400"><i class="fa-solid fa-snowflake"></i> MÁY LẠNH</a>
                <a href="#" class="hover:text-yellow-400"><i class="fa-solid fa-box"></i> TỦ LẠNH</a>
                <a href="#" class="hover:text-yellow-400"><i class="fa-solid fa-tv"></i> TIVI</a>
            </div>
            <div class="flex gap-4">
                <a href="#" class="hover:text-yellow-400"><i class="fa-solid fa-magnifying-glass"></i> Tra cứu đơn</a>
                <a href="{{ route('cart.index') }}" class="hover:text-yellow-400 relative">
                    <i class="fa-solid fa-cart-shopping"></i> Giỏ hàng
                    <span class="absolute -top-2 -right-2 bg-yellow-400 text-[#003399] text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center">2</span>
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4">
        <!-- Tiêu đề -->
        <div class="mb-6 flex items-center gap-3 text-2xl font-bold text-[#003399]">
            <div class="bg-[#003399] text-white p-1 rounded">
                <i class="fa-solid fa-money-check-dollar"></i>
            </div>
            Thông tin thanh toán
        </div>

        <form action="/orders" method="POST" id="checkoutForm">
            <!-- Token bảo mật của Laravel -->
            <input type="hidden" name="_token" value="{{ csrf_token() ?? '' }}">
            
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Cột trái: Thông tin & Phương thức thanh toán -->
                <div class="w-full lg:w-2/3 space-y-6">
                    
                    <!-- Thông tin người nhận -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-800">Thông tin người nhận</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Họ và tên *</label>
                                <input type="text" name="fullname" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" value="{{ Auth::check() ? Auth::user()->name : '' }}" placeholder="VD: Quản Trị Viên">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Số điện thoại *</label>
                                <input type="tel" name="phone" required pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '');" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="VD: 0901234567" value="{{ Auth::check() ? Auth::user()->phone : '' }}">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Địa chỉ giao hàng chi tiết *</label>
                            <input type="text" name="address" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Số nhà, Tên đường, Phường/Xã...">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Ghi chú đơn hàng (Tùy chọn)</label>
                            <textarea name="note" rows="3" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Ghi chú thêm về thời gian giao hàng..."></textarea>
                        </div>
                    </div>

                    <!-- Phương thức thanh toán -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-800">Phương thức thanh toán</h3>
                        
                        <div class="space-y-3">
                            <!-- VNPay -->
                            <div class="relative">
                                <input type="radio" name="payment_method" id="vnpay" value="vnpay" class="peer sr-only radio-custom" checked>
                                <label for="vnpay" class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer transition-all hover:bg-gray-50">
                                    <div class="flex items-center gap-4 w-full">
                                        <div class="outer-circle w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center transition-colors">
                                            <div class="inner-circle w-2.5 h-2.5 rounded-full bg-blue-500 opacity-0 transition-opacity"></div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-gray-800">Chuyển khoản QR Code</p>
                                                <span class="bg-red-100 text-red-500 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Khuyên dùng</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-0.5">Mở ứng dụng ngân hàng và quét mã QR. Nhanh chóng, tiện lợi, tự động xác nhận.</p>
                                        </div>
                                        <div class="shrink-0 hidden sm:block">
                                            <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png" alt="VNPay" class="h-6 object-contain">
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- MoMo -->
                            <div class="relative">
                                <input type="radio" name="payment_method" id="momo" value="momo" class="peer sr-only radio-custom">
                                <label for="momo" class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer transition-all hover:bg-gray-50">
                                    <div class="flex items-center gap-4 w-full">
                                        <div class="outer-circle w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center transition-colors">
                                            <div class="inner-circle w-2.5 h-2.5 rounded-full bg-blue-500 opacity-0 transition-opacity"></div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-800">Thanh toán qua Ví MoMo</p>
                                            <p class="text-sm text-gray-500 mt-0.5">Quét mã QR qua ứng dụng MoMo.</p>
                                        </div>
                                        <div class="shrink-0 hidden sm:block">
                                            <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo" class="h-6 object-contain">
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Stripe -->
                            <div class="relative">
                                <input type="radio" name="payment_method" id="stripe" value="stripe" class="peer sr-only radio-custom">
                                <label for="stripe" class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer transition-all hover:bg-gray-50">
                                    <div class="flex items-center gap-4 w-full">
                                        <div class="outer-circle w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center transition-colors">
                                            <div class="inner-circle w-2.5 h-2.5 rounded-full bg-blue-500 opacity-0 transition-opacity"></div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-800">Thanh toán bằng Thẻ quốc tế (Stripe)</p>
                                            <p class="text-sm text-gray-500 mt-0.5">Hỗ trợ Visa, Mastercard, JCB, Amex...</p>
                                        </div>
                                        <div class="shrink-0 hidden sm:flex gap-1 text-2xl">
                                            <i class="fa-brands fa-cc-visa text-blue-800"></i>
                                            <i class="fa-brands fa-cc-mastercard text-red-600"></i>
                                            <i class="fa-brands fa-cc-stripe text-indigo-500"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- COD -->
                            <div class="relative">
                                <input type="radio" name="payment_method" id="cod" value="cod" class="peer sr-only radio-custom">
                                <label for="cod" class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer transition-all hover:bg-gray-50">
                                    <div class="flex items-center gap-4 w-full">
                                        <div class="outer-circle w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center transition-colors">
                                            <div class="inner-circle w-2.5 h-2.5 rounded-full bg-blue-500 opacity-0 transition-opacity"></div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-800">Thanh toán khi nhận hàng (COD)</p>
                                            <p class="text-sm text-gray-500 mt-0.5">Thanh toán bằng tiền mặt cho nhân viên giao hàng.</p>
                                        </div>
                                        <div class="shrink-0 hidden sm:block text-2xl text-green-600">
                                            <i class="fa-solid fa-hand-holding-dollar"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Tổng quan đơn hàng -->
                <div class="w-full lg:w-1/3">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-800 border-b pb-3">Đơn hàng của bạn (2 sp)</h3>
                        
                        <!-- Danh sách sản phẩm -->
                        <div class="space-y-4 mb-6">
                            <!-- Item 1 -->
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex gap-2 flex-1 min-w-0 pr-4">
                                    <span class="font-semibold text-gray-600 shrink-0">2x</span>
                                    <p class="font-medium text-gray-800 leading-snug truncate" title="Android Tivi Sony 4K 65 inch KD-123456">Android Tivi Sony 4K 65 inch KD-123456</p>
                                </div>
                                <span class="font-bold whitespace-nowrap shrink-0">33,980,000đ</span>
                            </div>
                            <!-- Item 2 -->
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex gap-2 flex-1 min-w-0 pr-4">
                                    <span class="font-semibold text-gray-600 shrink-0">1x</span>
                                    <p class="font-medium text-gray-800 leading-snug truncate" title="Tủ lạnh Aqua Inverter 189 lít AQR-T219FA(PB)">Tủ lạnh Aqua Inverter 189 lít AQR-T219FA(PB)</p>
                                </div>
                                <span class="font-bold whitespace-nowrap shrink-0">4,990,000đ</span>
                            </div>
                        </div>

                        <!-- Mã giảm giá -->
                        <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Mã giảm giá</label>
                            <div class="flex gap-2">
                                <input type="text" id="discount_code" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 outline-none transition-colors" placeholder="Nhập mã (VD: PRO10)">
                                <button type="button" id="btn-apply-discount" onclick="applyDiscount()" class="bg-gray-800 text-white px-4 rounded-lg font-semibold hover:bg-gray-900 transition-colors whitespace-nowrap">Áp dụng</button>
                            </div>
                            <p id="discount-message" class="text-xs mt-2 font-medium flex items-center gap-1 hidden">
                            </p>
                        </div>

                        <!-- Tóm tắt chi phí -->
                        <div class="space-y-3 text-sm border-t border-gray-100 pt-4 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tạm tính:</span>
                                <span class="font-medium text-gray-800" id="subtotal">38,970,000đ</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Phí vận chuyển:</span>
                                <span class="font-medium text-green-600" id="shipping-fee">Miễn phí</span>
                            </div>
                            <div class="flex justify-between hidden" id="discount-row">
                                <span class="text-gray-600">Giảm giá:</span>
                                <span class="font-medium text-green-600" id="discount-amount">-0đ</span>
                            </div>
                        </div>

                        <!-- Tổng tiền -->
                        <div class="border-t border-gray-200 pt-4 mb-6">
                            <div class="flex justify-between items-end mb-1">
                                <span class="font-bold text-gray-800 text-base">Thành tiền:</span>
                                <span class="text-3xl font-bold text-red-600 leading-none" id="total-price">38,970,000đ</span>
                            </div>
                            <p class="text-right text-xs text-gray-500 italic">(Đã bao gồm VAT nếu có)</p>
                        </div>

                        <!-- Nút đặt hàng -->
                        <button type="submit" id="btn-submit-order" class="w-full bg-[#e30019] text-white p-3.5 rounded-lg font-bold text-lg hover:bg-red-700 transition-all shadow-md text-center opacity-50 cursor-not-allowed" disabled>
                            XÁC NHẬN ĐẶT HÀNG
                        </button>
                        
                        <!-- Quay lại giỏ hàng -->
                        <div class="mt-4 text-center">
                            <a href="{{ route('cart.index') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1 font-medium">
                                <i class="fa-solid fa-arrow-left"></i> Quay lại giỏ hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // --- XỬ LÝ MÃ GIẢM GIÁ VÀ TÍNH TỔNG TIỀN ---
        let subtotal = 38970000;
        let shippingFee = 0;
        let discountValue = 0;

        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        }

        function updateTotals() {
            const total = subtotal + shippingFee - discountValue;
            document.getElementById('total-price').innerText = formatMoney(total);
            
            const discountRow = document.getElementById('discount-row');
            if (discountValue > 0) {
                discountRow.classList.remove('hidden');
                document.getElementById('discount-amount').innerText = '-' + formatMoney(discountValue);
            } else {
                discountRow.classList.add('hidden');
            }
        }

        function applyDiscount() {
            const input = document.getElementById('discount_code');
            const btn = document.getElementById('btn-apply-discount');
            const msg = document.getElementById('discount-message');
            
            if (btn.innerText === 'Áp dụng') {
                const code = input.value.trim();
                if (!code) return;
                
                // Giả lập API call
                btn.innerText = 'Đang xử lý...';
                btn.disabled = true;
                
                setTimeout(() => {
                    if (code.toUpperCase() === 'PRO10') {
                        discountValue = 1000; // Giảm 1000đ
                        input.readOnly = true;
                        input.classList.remove('border-gray-300', 'focus:ring-gray-400');
                        input.classList.add('border-green-500', 'focus:ring-green-500', 'bg-green-50', 'text-green-700');
                        
                        btn.innerText = 'Xóa';
                        btn.classList.remove('bg-gray-800', 'hover:bg-gray-900');
                        btn.classList.add('bg-red-500', 'hover:bg-red-600');
                        btn.disabled = false;
                        
                        msg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Áp dụng mã thành công! (Giảm ' + formatMoney(discountValue) + ')';
                        msg.classList.remove('hidden', 'text-red-500');
                        msg.classList.add('text-green-600');
                        
                        updateTotals();
                    } else {
                        btn.innerText = 'Áp dụng';
                        btn.disabled = false;
                        msg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Mã giảm giá không hợp lệ!';
                        msg.classList.remove('hidden', 'text-green-600');
                        msg.classList.add('text-red-500');
                    }
                }, 500);
            } else {
                // Hủy / Xóa mã
                discountValue = 0;
                input.value = '';
                input.readOnly = false;
                input.classList.add('border-gray-300', 'focus:ring-gray-400');
                input.classList.remove('border-green-500', 'focus:ring-green-500', 'bg-green-50', 'text-green-700');
                
                btn.innerText = 'Áp dụng';
                btn.classList.add('bg-gray-800', 'hover:bg-gray-900');
                btn.classList.remove('bg-red-500', 'hover:bg-red-600');
                
                msg.classList.add('hidden');
                updateTotals();
            }
        }

        // --- KIỂM TRA FORM HỢP LỆ ĐỂ KÍCH HOẠT NÚT ĐẶT HÀNG ---
        const form = document.getElementById('checkoutForm');
        const btnSubmit = document.getElementById('btn-submit-order');
        const requiredInputs = form.querySelectorAll('input[required]');

        function checkFormValidity() {
            let isValid = true;
            requiredInputs.forEach(input => {
                if (!input.value.trim() || !input.checkValidity()) {
                    isValid = false;
                }
            });
            
            if (isValid) {
                btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
                btnSubmit.disabled = false;
            } else {
                btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
                btnSubmit.disabled = true;
            }
        }

        requiredInputs.forEach(input => {
            input.addEventListener('input', checkFormValidity);
        });

        // Chạy lần đầu lúc tải trang
        checkFormValidity();
    </script>

</body>
</html>
