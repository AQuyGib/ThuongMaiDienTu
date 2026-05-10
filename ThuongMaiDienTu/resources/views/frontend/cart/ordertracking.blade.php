@extends('layouts.app')

@section('title', 'Tra cứu hành trình đơn hàng - DIENMAYPRO')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .tracking-line {
            position: absolute;
            left: 24px;
            top: 50px;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
            z-index: 0;
        }
        .tracking-dot {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            position: relative;
            flex-shrink: 0;
            transition: all 0.4s ease;
        }
        .step-completed .tracking-dot {
            background: #0046ab;
            border-color: #0046ab;
            color: #fff;
        }
        .step-active .tracking-dot {
            background: #fff;
            border-color: #0046ab;
            color: #0046ab;
            box-shadow: 0 0 0 6px rgba(0, 70, 171, 0.12);
        }
        .input-tab.active {
            border-color: #0046ab;
            background: #0046ab;
            color: #fff;
        }
        .input-tab {
            border: 2px solid #e5e7eb;
            background: #fff;
            color: #6b7280;
            transition: all 0.2s;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up {
            animation: fadeInUp 0.4s ease-out both;
        }
    </style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4">

        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#0046ab] text-white rounded-2xl mb-4 text-2xl shadow-lg shadow-blue-200">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <h1 class="text-3xl font-black text-gray-800 mb-2">Tra cứu hành trình đơn hàng</h1>
            <p class="text-gray-500 max-w-md mx-auto">Nhập thông tin bên dưới để theo dõi trạng thái đơn hàng của bạn</p>
        </div>

        <!-- Search Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8 mb-8 border border-gray-100">

            <!-- Tab chọn phương thức tra cứu -->
            <div class="flex gap-3 mb-8">
                <button id="tab-code" onclick="switchTab('code')"
                    class="input-tab active flex-1 py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-hashtag"></i> Theo mã đơn hàng
                </button>
                <button id="tab-name" onclick="switchTab('name')"
                    class="input-tab flex-1 py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user"></i> Theo tên người nhận
                </button>
            </div>

            <!-- Form tra cứu theo Mã -->
            <form id="form-code" onsubmit="doSearch(event, 'code')">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <i class="fa-solid fa-barcode absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                        <input type="text" id="input-code" placeholder="VD: DMP-481552"
                               class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-transparent rounded-2xl focus:border-[#0046ab] focus:bg-white transition-all outline-none font-bold text-gray-800 text-lg">
                    </div>
                    <button type="submit"
                        class="px-10 py-4 bg-[#0046ab] text-white rounded-2xl font-bold hover:bg-blue-800 transition-all shadow-lg shadow-blue-100 flex items-center justify-center gap-2 whitespace-nowrap">
                        Tra cứu <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-3 flex items-center gap-1">
                    <i class="fa-solid fa-circle-info"></i>
                    Mã đơn hàng có dạng DMP-XXXXXX (xem trong email xác nhận hoặc hóa đơn)
                </p>
            </form>

            <!-- Form tra cứu theo Tên -->
            <form id="form-name" class="hidden" onsubmit="doSearch(event, 'name')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="input-name" placeholder="Họ và tên người nhận (VD: Nguyễn Văn A)"
                               class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-transparent rounded-2xl focus:border-[#0046ab] focus:bg-white transition-all outline-none font-medium text-gray-800">
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="tel" id="input-phone" placeholder="Số điện thoại (VD: 0912345678)"
                               class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-transparent rounded-2xl focus:border-[#0046ab] focus:bg-white transition-all outline-none font-medium text-gray-800">
                    </div>
                </div>
                <button type="submit"
                    class="w-full py-4 bg-[#0046ab] text-white rounded-2xl font-bold hover:bg-blue-800 transition-all shadow-lg shadow-blue-100 flex items-center justify-center gap-2">
                    Tìm kiếm đơn hàng <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <p class="text-xs text-gray-400 mt-3 flex items-center gap-1">
                    <i class="fa-solid fa-circle-info"></i>
                    Vui lòng nhập chính xác tên và số điện thoại đã đăng ký khi đặt hàng
                </p>
            </form>
        </div>

        <!-- Loading State -->
        <div id="loading" class="hidden text-center py-16">
            <i class="fa-solid fa-circle-notch fa-spin text-4xl text-[#0046ab] mb-4 block"></i>
            <p class="text-gray-500 font-medium">Đang tìm kiếm đơn hàng...</p>
        </div>

        <!-- No Result State -->
        <div id="noResult" class="hidden">
            <div class="bg-white rounded-3xl shadow-xl p-12 border border-gray-100 text-center fade-in-up">
                <div class="w-20 h-20 bg-red-50 text-red-400 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Không tìm thấy đơn hàng</h3>
                <p class="text-gray-500 mb-6">Vui lòng kiểm tra lại thông tin đã nhập và thử lại.</p>
                <button onclick="resetSearch()" class="px-8 py-3 border-2 border-[#0046ab] text-[#0046ab] rounded-xl font-bold hover:bg-blue-50 transition-all">
                    Thử lại
                </button>
            </div>
        </div>

        <!-- Result Section -->
        <div id="trackingResult" class="hidden fade-in-up">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Timeline -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                        <div class="flex justify-between items-center mb-8">
                            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                                <i class="fa-solid fa-route text-[#0046ab]"></i> Hành trình đơn hàng
                            </h3>
                            <span id="order-id-badge"
                                class="bg-blue-50 text-[#0046ab] text-sm font-bold px-4 py-2 rounded-full"></span>
                        </div>

                        <div class="relative space-y-10">
                            <div class="tracking-line"></div>

                            <!-- Step 1 -->
                            <div class="flex items-start gap-5 step-completed relative">
                                <div class="tracking-dot"><i class="fa-solid fa-file-invoice"></i></div>
                                <div class="pt-2">
                                    <h4 class="font-bold text-gray-800 text-base">Đã đặt hàng thành công</h4>
                                    <p class="text-gray-500 text-sm">Hệ thống đã ghi nhận đơn hàng của bạn.</p>
                                    <span class="text-xs text-gray-400 mt-1 block"><i class="fa-regular fa-clock mr-1"></i>10:15 – 10/05/2026</span>
                                </div>
                            </div>

                            <!-- Step 2 -->
                            <div class="flex items-start gap-5 step-completed relative">
                                <div class="tracking-dot"><i class="fa-solid fa-check-double"></i></div>
                                <div class="pt-2">
                                    <h4 class="font-bold text-gray-800 text-base">Đã xác nhận thanh toán</h4>
                                    <p class="text-gray-500 text-sm">Giao dịch đã được xác thực qua QR Code.</p>
                                    <span class="text-xs text-gray-400 mt-1 block"><i class="fa-regular fa-clock mr-1"></i>10:30 – 10/05/2026</span>
                                </div>
                            </div>

                            <!-- Step 3: Active -->
                            <div class="flex items-start gap-5 step-active relative">
                                <div class="tracking-dot"><i class="fa-solid fa-box-open"></i></div>
                                <div class="pt-2">
                                    <h4 class="font-bold text-[#0046ab] text-base">Đang đóng gói</h4>
                                    <p class="text-gray-500 text-sm">Nhân viên kho đang chuẩn bị và đóng gói sản phẩm.</p>
                                    <span class="text-xs text-[#0046ab] font-bold mt-1 block flex items-center gap-1">
                                        <span class="w-2 h-2 bg-[#0046ab] rounded-full inline-block animate-pulse"></span>
                                        Cập nhật: 5 phút trước
                                    </span>
                                </div>
                            </div>

                            <!-- Step 4: Pending -->
                            <div class="flex items-start gap-5 relative">
                                <div class="tracking-dot"><i class="fa-solid fa-truck text-gray-300"></i></div>
                                <div class="pt-2">
                                    <h4 class="font-bold text-gray-300 text-base">Đang vận chuyển</h4>
                                    <p class="text-gray-300 text-sm">Chưa bàn giao cho đối tác vận chuyển.</p>
                                </div>
                            </div>

                            <!-- Step 5: Pending -->
                            <div class="flex items-start gap-5 relative">
                                <div class="tracking-dot"><i class="fa-solid fa-house-circle-check text-gray-300"></i></div>
                                <div class="pt-2">
                                    <h4 class="font-bold text-gray-300 text-base">Đã giao hàng</h4>
                                    <p class="text-gray-300 text-sm">Dự kiến giao hàng trong 1–2 ngày tới.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="space-y-6">
                    <div class="bg-white rounded-3xl shadow-xl p-6 border border-gray-100">
                        <h3 class="text-base font-bold text-gray-800 mb-5 border-b pb-4 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-[#0046ab]"></i> Thông tin đơn hàng
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Người nhận:</span>
                                <span id="result-name" class="font-bold text-gray-800"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Số điện thoại:</span>
                                <span id="result-phone" class="font-bold text-gray-800"></span>
                            </div>
                            <div class="pt-3 border-t border-gray-50 text-sm">
                                <span class="text-gray-500 block mb-1">Địa chỉ:</span>
                                <p class="font-medium text-gray-800">123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh</p>
                            </div>
                            <div class="pt-3 border-t border-gray-50 text-sm">
                                <span class="text-gray-500 block mb-1">Phương thức thanh toán:</span>
                                <p class="font-bold text-[#0046ab]"><i class="fa-solid fa-qrcode mr-1"></i>Chuyển khoản QR</p>
                            </div>
                        </div>
                        <div class="mt-6 pt-5 border-t-2 border-dashed border-gray-100 flex justify-between items-end">
                            <span class="text-sm text-gray-500 font-bold">TỔNG CỘNG:</span>
                            <span class="text-2xl font-black text-red-600">38.970.000đ</span>
                        </div>
                    </div>

                    <button onclick="resetSearch()"
                        class="w-full py-4 bg-gray-100 text-gray-600 rounded-2xl font-bold hover:bg-gray-200 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Tra cứu đơn khác
                    </button>

                    <a href="{{ url('/') }}"
                        class="w-full py-4 border-2 border-[#0046ab] text-[#0046ab] rounded-2xl font-bold hover:bg-blue-50 transition-all text-center block">
                        Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // ===== TAB SWITCH =====
    function switchTab(tab) {
        document.getElementById('tab-code').classList.toggle('active', tab === 'code');
        document.getElementById('tab-name').classList.toggle('active', tab === 'name');
        document.getElementById('form-code').classList.toggle('hidden', tab !== 'code');
        document.getElementById('form-name').classList.toggle('hidden', tab !== 'name');
        resetSearch();
    }

    // ===== DO SEARCH =====
    function doSearch(e, mode) {
        e.preventDefault();
        const loading   = document.getElementById('loading');
        const result    = document.getElementById('trackingResult');
        const noResult  = document.getElementById('noResult');

        // Reset
        result.classList.add('hidden');
        noResult.classList.add('hidden');
        loading.classList.remove('hidden');

        setTimeout(() => {
            loading.classList.add('hidden');

            if (mode === 'code') {
                const code = document.getElementById('input-code').value.trim().toUpperCase();
                if (code.startsWith('DMP-') && code.length >= 8) {
                    showResult({ id: code, name: 'Nguyễn Văn A', phone: '0912****78' });
                } else {
                    noResult.classList.remove('hidden');
                }
            } else {
                const name  = document.getElementById('input-name').value.trim();
                const phone = document.getElementById('input-phone').value.trim();
                // Giả lập: tìm thấy nếu tên không rỗng và SĐT có 10 chữ số
                if (name.length >= 2 && /^[0-9]{10}$/.test(phone)) {
                    const maskedPhone = phone.slice(0, 4) + '****' + phone.slice(-2);
                    showResult({ id: 'DMP-' + Math.floor(100000 + Math.random() * 900000), name: name, phone: maskedPhone });
                } else {
                    noResult.classList.remove('hidden');
                }
            }
        }, 800);
    }

    function showResult(data) {
        document.getElementById('order-id-badge').textContent = '#' + data.id;
        document.getElementById('result-name').textContent    = data.name;
        document.getElementById('result-phone').textContent   = data.phone;

        const result = document.getElementById('trackingResult');
        result.classList.remove('hidden');
        result.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function resetSearch() {
        document.getElementById('trackingResult').classList.add('hidden');
        document.getElementById('noResult').classList.add('hidden');
        document.getElementById('loading').classList.add('hidden');
    }
</script>
@endsection
