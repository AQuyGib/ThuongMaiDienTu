@extends('layouts.app')

@section('title', 'Kho Voucher & Mã Giảm Giá - DIENMAYPRO')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .voucher-card {
            border-left: 6px dashed #0046ab;
            transition: all 0.3s ease;
        }
        .voucher-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .voucher-freeship { border-left-color: #10b981; } /* Emerald */
        .voucher-hot { border-left-color: #ef4444; } /* Red */
    </style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-4xl mx-auto px-4">
        
        <!-- Header -->
        <div class="flex items-center gap-3 mb-8">
            <a href="{{ route('cart.pay') }}" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm text-gray-500 hover:text-[#0046ab] transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-black text-gray-800">Khuyến Mãi & Mã Giảm Giá</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Cột trái: Nhập mã thủ công -->
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-ticket text-[#0046ab]"></i> Nhập mã khuyến mãi
                    </h2>
                    <form id="applyCouponForm" class="space-y-3">
                        <input type="text" id="couponInput" placeholder="VD: DIENMAYPRO10" 
                               class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:border-[#0046ab] focus:ring-0 outline-none uppercase font-bold text-gray-700 transition-colors">
                        <button type="submit" class="w-full bg-[#0046ab] text-white py-3 rounded-xl font-bold hover:bg-blue-800 transition-colors shadow-lg shadow-blue-100 flex items-center justify-center gap-2">
                            Áp dụng ngay
                        </button>
                    </form>
                    <div id="couponMessage" class="hidden mt-3 text-sm font-medium p-3 rounded-lg"></div>
                </div>

                <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                    <h3 class="font-bold text-[#0046ab] mb-2"><i class="fa-solid fa-circle-info mr-1"></i> Lưu ý</h3>
                    <ul class="text-sm text-blue-800 space-y-2 list-disc list-inside opacity-80">
                        <li>Mỗi đơn hàng chỉ áp dụng 1 mã giảm giá.</li>
                        <li>Có thể áp dụng cùng lúc với mã Miễn phí vận chuyển.</li>
                        <li>Mã giảm giá không quy đổi thành tiền mặt.</li>
                    </ul>
                </div>
            </div>

            <!-- Cột phải: Danh sách Voucher -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Tab filter (Demo) -->
                <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                    <button class="px-5 py-2 bg-[#0046ab] text-white font-bold rounded-full text-sm whitespace-nowrap">Tất cả</button>
                    <button class="px-5 py-2 bg-white border border-gray-200 text-gray-600 font-bold rounded-full text-sm hover:bg-gray-50 whitespace-nowrap">Điện thoại</button>
                    <button class="px-5 py-2 bg-white border border-gray-200 text-gray-600 font-bold rounded-full text-sm hover:bg-gray-50 whitespace-nowrap">Laptop</button>
                    <button class="px-5 py-2 bg-white border border-gray-200 text-gray-600 font-bold rounded-full text-sm hover:bg-gray-50 whitespace-nowrap">Phụ kiện</button>
                </div>

                <!-- Danh sách Voucher -->
                <div class="space-y-4">
                    
                    <!-- Voucher Freeship -->
                    <div class="bg-white rounded-xl shadow-sm flex overflow-hidden border border-gray-100 voucher-card voucher-freeship">
                        <div class="bg-emerald-50 w-28 flex flex-col items-center justify-center p-4 border-r border-dashed border-gray-200">
                            <i class="fa-solid fa-truck-fast text-3xl text-emerald-500 mb-2"></i>
                            <span class="text-xs font-bold text-emerald-600 text-center">Freeship</span>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between relative">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Miễn phí vận chuyển</h3>
                                <p class="text-sm text-gray-500">Giảm tối đa 30k cho đơn từ 500k</p>
                            </div>
                            <div class="flex justify-between items-end mt-4">
                                <span class="text-xs text-red-500 font-medium"><i class="fa-regular fa-clock mr-1"></i>Hết hạn: 31/05/2026</span>
                                <button onclick="selectVoucher('FREESHIP30', this)" class="px-4 py-1.5 bg-emerald-500 text-white font-bold text-sm rounded-lg hover:bg-emerald-600 transition-colors">
                                    Dùng ngay
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Voucher Hot -->
                    <div class="bg-white rounded-xl shadow-sm flex overflow-hidden border border-gray-100 voucher-card voucher-hot relative">
                        <!-- Badge -->
                        <div class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg z-10">SẮP HẾT</div>
                        
                        <div class="bg-red-50 w-28 flex flex-col items-center justify-center p-4 border-r border-dashed border-gray-200">
                            <i class="fa-solid fa-fire text-3xl text-red-500 mb-2"></i>
                            <span class="text-xs font-bold text-red-600 text-center">Giảm 5%</span>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between relative">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Giảm 5% Điện thoại iPhone</h3>
                                <p class="text-sm text-gray-500">Giảm tối đa 500.000đ. Đơn tối thiểu 10Tr.</p>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3">
                                  <div class="bg-red-500 h-1.5 rounded-full" style="width: 85%"></div>
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 block">Đã dùng 85%</span>
                            </div>
                            <div class="flex justify-between items-end mt-2">
                                <span class="text-xs text-gray-400 font-medium">HSD: 15/05/2026</span>
                                <button onclick="selectVoucher('IPHONE5', this)" class="px-4 py-1.5 bg-[#0046ab] text-white font-bold text-sm rounded-lg hover:bg-blue-800 transition-colors">
                                    Dùng ngay
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Voucher Normal -->
                    <div class="bg-white rounded-xl shadow-sm flex overflow-hidden border border-gray-100 voucher-card">
                        <div class="bg-blue-50 w-28 flex flex-col items-center justify-center p-4 border-r border-dashed border-gray-200">
                            <i class="fa-solid fa-laptop text-3xl text-[#0046ab] mb-2"></i>
                            <span class="text-xs font-bold text-[#0046ab] text-center">Giảm 200K</span>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between relative">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Ưu đãi Laptop tựu trường</h3>
                                <p class="text-sm text-gray-500">Áp dụng cho tất cả Laptop trên 15 triệu.</p>
                            </div>
                            <div class="flex justify-between items-end mt-4">
                                <span class="text-xs text-gray-400 font-medium">HSD: 30/06/2026</span>
                                <button onclick="selectVoucher('LAPTOP200', this)" class="px-4 py-1.5 border-2 border-[#0046ab] text-[#0046ab] font-bold text-sm rounded-lg hover:bg-blue-50 transition-colors">
                                    Dùng ngay
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Xử lý nút dùng ngay
    function selectVoucher(code, btnElement) {
        document.getElementById('couponInput').value = code;
        
        // Tạo hiệu ứng feedback
        const originalText = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fa-solid fa-check"></i> Đã chọn';
        btnElement.classList.add('bg-gray-200', 'text-gray-600', 'border-gray-200');
        btnElement.classList.remove('bg-[#0046ab]', 'text-white', 'border-[#0046ab]', 'bg-emerald-500');
        
        setTimeout(() => {
            document.getElementById('applyCouponForm').dispatchEvent(new Event('submit'));
        }, 500);
    }

    // Xử lý submit form
    document.getElementById('applyCouponForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('couponInput').value.trim().toUpperCase();
        const msg = document.getElementById('couponMessage');
        const btn = this.querySelector('button');
        
        if (!input) {
            msg.textContent = 'Vui lòng nhập mã giảm giá!';
            msg.className = 'mt-3 text-sm font-medium p-3 rounded-lg bg-red-50 text-red-600 block';
            return;
        }

        // Loading
        const originalBtnText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang kiểm tra...';
        btn.disabled = true;

        // Simulate API call
        setTimeout(() => {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;

            const validCodes = ['FREESHIP30', 'IPHONE5', 'LAPTOP200', 'DIENMAYPRO10'];
            
            if (validCodes.includes(input)) {
                msg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Áp dụng mã <strong>' + input + '</strong> thành công!';
                msg.className = 'mt-3 text-sm font-medium p-3 rounded-lg bg-emerald-50 text-emerald-600 block border border-emerald-100';
                
                // Tự động chuyển về trang giỏ hàng/thanh toán sau 1.5s
                setTimeout(() => {
                    // Cập nhật session storage hoặc params để qua trang thanh toán biết
                    sessionStorage.setItem('applied_coupon', input);
                    window.location.href = '/pay';
                }, 1500);
            } else {
                msg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Mã giảm giá không hợp lệ hoặc đã hết hạn!';
                msg.className = 'mt-3 text-sm font-medium p-3 rounded-lg bg-red-50 text-red-600 block border border-red-100';
            }
        }, 800);
    });
</script>
@endsection
