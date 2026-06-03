@extends('layouts.app')

@section('title', 'Kho Voucher & Mã Giảm Giá - DIENMAYPRO')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            }
        }
    </script>
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* Hiệu ứng trồi lên và bóng mờ khi rê chuột lên thẻ voucher */
        .voucher-card {
            border-left: 6px dashed #0046ab;
            transition: all 0.3s ease;
        }
        .voucher-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        /* Đường kẻ dọc màu xanh lá cho voucher freeship */
        .voucher-freeship { border-left-color: #10b981; } /* Emerald */
        /* Đường kẻ dọc màu đỏ cho voucher giảm giá trực tiếp */
        .voucher-hot { border-left-color: #ef4444; } /* Red */
    </style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-4xl mx-auto px-4">
        
        <!-- Nút quay lại trang checkout và tiêu đề -->
        <div class="flex items-center gap-3 mb-8">
            <a href="{{ route('cart.pay') }}" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm text-gray-500 hover:text-[#0046ab] transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-black text-gray-800">Khuyến Mãi & Mã Giảm Giá</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- CỘT BÊN TRÁI (1/3): NHẬP MÃ THỦ CÔNG & LƯU Ý SỬ DỤNG -->
            <div class="md:col-span-1 space-y-6">
                <!-- Form nhập mã giảm giá bằng bàn phím -->
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
                    <!-- Khung hiển thị thông báo phản hồi (Thành công / Thất bại) -->
                    <div id="couponMessage" class="hidden mt-3 text-sm font-medium p-3 rounded-lg"></div>
                </div>

                <!-- Box lưu ý nội quy áp dụng voucher -->
                <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                    <h3 class="font-bold text-[#0046ab] mb-2"><i class="fa-solid fa-circle-info mr-1"></i> Lưu ý</h3>
                    <ul class="text-sm text-blue-800 space-y-2 list-disc list-inside opacity-80">
                        <li>Mỗi đơn hàng chỉ áp dụng 1 mã giảm giá.</li>
                        <li>Có thể áp dụng cùng lúc với mã Miễn phí vận chuyển.</li>
                        <li>Mã giảm giá không quy đổi thành tiền mặt.</li>
                    </ul>
                </div>
            </div>

            <!-- CỘT BÊN PHẢI (2/3): HIỂN THỊ VÍ ĐIỂM & DANH SÁCH VOUCHER ĐANG SỞ HỮU -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Số dư điểm Loyalty Points của người dùng -->
                <div class="bg-[#0046ab] text-white p-4 rounded-xl shadow-sm flex items-center justify-between">
                    <span class="font-bold text-sm"><i class="fa-solid fa-wallet mr-1"></i> Ví điểm tích lũy:</span>
                    <span class="font-black text-lg">{{ number_format($balance->wallet_points ?? 0) }} điểm</span>
                </div>

                <!-- Danh sách Voucher được đổi điểm từ trước của người dùng -->
                <div class="space-y-4">
                    @forelse($myVouchers as $redemption)
                        @php
                            $reward = $redemption->reward;
                            $isFreeship = $reward && $reward->reward_type === 'shipping';
                            $discountText = '';
                            if ($reward) {
                                if ($reward->reward_type === 'shipping') {
                                    $discountText = 'Freeship ' . number_format($reward->shipping_discount_amount) . 'đ';
                                } else {
                                    $discountText = 'Giảm ' . number_format($reward->discount_amount) . 'đ';
                                }
                            }
                        @endphp
                        <!-- Thẻ Voucher -->
                        <div class="bg-white rounded-xl shadow-sm flex overflow-hidden border border-gray-100 voucher-card {{ $isFreeship ? 'voucher-freeship' : 'voucher-hot' }} relative">
                            <!-- Cột icon bên trái voucher (Phân loại theo loại: Ship/Giảm giá) -->
                            <div class="{{ $isFreeship ? 'bg-emerald-50' : 'bg-red-50' }} w-28 flex flex-col items-center justify-center p-4 border-r border-dashed border-gray-200 shrink-0">
                                @if($isFreeship)
                                    <i class="fa-solid fa-truck-fast text-3xl text-emerald-500 mb-2"></i>
                                @else
                                    <i class="fa-solid fa-fire text-3xl text-red-500 mb-2"></i>
                                @endif
                                <span class="text-xs font-bold {{ $isFreeship ? 'text-emerald-600' : 'text-red-600' }} text-center leading-tight">{{ $discountText }}</span>
                            </div>
                            
                            <!-- Nội dung chính của voucher ở bên phải -->
                            <div class="p-4 flex-1 flex flex-col justify-between relative">
                                <div>
                                    <h3 class="font-bold text-gray-800 text-base leading-snug">{{ $reward?->name ?? 'Mã đổi thưởng' }}</h3>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="text-[10px] font-mono bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200">Mã: {{ $redemption->redemption_code }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">{{ $reward?->description ?? 'Dùng để giảm giá trực tiếp khi thanh toán.' }}</p>
                                </div>
                                <div class="flex justify-between items-end mt-4">
                                    <span class="text-[11px] text-gray-400 font-medium">
                                        @if($redemption->expires_at)
                                            HSD: {{ $redemption->expires_at->format('d/m/Y') }}
                                        @else
                                            HSD: Vĩnh viễn
                                        @endif
                                    </span>
                                    <!-- Nhấp nút "Dùng ngay" sẽ copy mã và tự động submit -->
                                    <button onclick="selectVoucher('{{ $redemption->redemption_code }}', this)" class="px-4 py-1.5 {{ $isFreeship ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-[#0046ab] hover:bg-blue-800' }} text-white font-bold text-xs rounded-lg transition-colors">
                                        Dùng ngay
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white p-8 rounded-2xl border border-gray-100 text-center text-gray-500">
                            <i class="fa-solid fa-ticket-simple text-4xl text-gray-300 mb-3 block"></i>
                            <p class="font-bold text-gray-700">Không tìm thấy voucher khả dụng.</p>
                            <p class="text-xs text-gray-400 mt-1">Hãy đổi điểm tích lũy lấy voucher tại trang <a href="{{ route('rewards.index') }}" class="text-blue-600 font-bold hover:underline">Đổi thưởng</a>.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * 1. HÀM CHỌN NHANH VOUCHER TỪ DANH SÁCH SỞ HỮU
     * Copy mã code voucher được bấm vào ô input và tự động giả lập submit form.
     * Có hiệu ứng spinner xoay tròn tạo phản hồi trực quan.
     */
    function selectVoucher(code, btnElement) {
        document.getElementById('couponInput').value = code;
        
        const originalText = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        
        setTimeout(() => {
            document.getElementById('applyCouponForm').dispatchEvent(new Event('submit'));
        }, 200);
    }

    /**
     * 2. AJAX SUBMIT ÁP DỤNG MÃ GIẢM GIÁ
     * Gửi yêu cầu POST lên router `cart.apply-coupon` kèm CSRF token.
     * Xử lý hiển thị thông báo thành công hoặc thất bại.
     * Nếu thành công: Tự động điều hướng về lại trang thanh toán sau 1.2 giây để cập nhật lại số tiền.
     */
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

        // Bật trạng thái Loading
        const originalBtnText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang áp dụng...';
        btn.disabled = true;

        fetch('{{ route("cart.apply-coupon") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ code: input })
        })
        .then(r => r.json())
        .then(res => {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;

            if (res.success) {
                msg.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + res.message;
                msg.className = 'mt-3 text-sm font-medium p-3 rounded-lg bg-emerald-50 text-emerald-600 block border border-emerald-100';
                
                // Tự động chuyển hướng về trang thanh toán sau 1.2 giây
                setTimeout(() => {
                    window.location.href = '{{ route("cart.pay") }}';
                }, 1200);
            } else {
                msg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + res.message;
                msg.className = 'mt-3 text-sm font-medium p-3 rounded-lg bg-red-50 text-red-600 block border border-red-100';
            }
        })
        .catch(err => {
            console.error(err);
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
            msg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Lỗi hệ thống!';
            msg.className = 'mt-3 text-sm font-medium p-3 rounded-lg bg-red-50 text-red-600 block border border-red-100';
        });
    });
</script>
@endsection
