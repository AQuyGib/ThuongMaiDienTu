@extends('layouts.app')
<<<<<<< HEAD
@section('title', 'Thanh toán QR - DIENMAYPRO')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
@keyframes scanLine {
  0%,100%{top:0;opacity:0} 50%{top:calc(100% - 4px);opacity:1}
}
.qr-scan-line { animation: scanLine 2.5s ease-in-out infinite; }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        
        {{-- Back Button at Top Right --}}
        <div class="mb-6 flex justify-end">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-600 transition group">
                <span>Quay lại trang chủ</span>
                <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                    <i class="fa-solid fa-house"></i>
                </div>
            </a>
            <a href="{{ route('cart.print') }}" target="_blank" class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-gray-500 hover:bg-blue-600 hover:text-white transition ml-2" title="In đơn hàng">
                <i class="fa-solid fa-print"></i>
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-8 text-white text-center">
                <h1 class="text-2xl font-bold mb-2">Quét mã QR để hoàn tất thanh toán</h1>
                <p class="text-blue-100 text-sm">Đơn hàng của bạn đã được khởi tạo thành công</p>
            </div>

            <div class="p-8">
                <div class="flex flex-col md:flex-row gap-10 items-center justify-center">
                    
                    {{-- QR Column --}}
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-blue-400 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                        <div class="relative bg-white p-4 rounded-2xl shadow-lg border border-gray-100">
                            <div class="absolute top-4 left-4 right-4 h-1 bg-blue-500 rounded-full qr-scan-line z-10"></div>
                            <img id="qr-img" src="" alt="VietQR" class="w-64 h-64 object-contain">
                            <div class="mt-4 flex items-center justify-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-widest">
                                <i class="fa-solid fa-expand"></i>
                                <span>Quét mã VietQR</span>
                            </div>
                        </div>
=======

@section('title', 'Thanh toán QR Code - DIENMAYPRO')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes pulse-custom {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        .animate-pulse-slow {
            animation: pulse-custom 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .glass-morphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-2xl mx-auto">
        <!-- Header Section -->
        <div class="text-center mb-10">
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">
                Quét mã QR để thanh toán
            </h2>
            <p class="mt-4 text-lg text-gray-600">
                Vui lòng sử dụng ứng dụng Ngân hàng hoặc Ví điện tử để quét mã dưới đây.
            </p>
        </div>

        <!-- Main Payment Card -->
        <div class="glass-morphism rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
            <div class="bg-[#003399] p-6 text-white text-center relative overflow-hidden">
                <!-- Decorative background patterns -->
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-blue-400 rounded-full opacity-10"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 bg-white rounded-full opacity-5"></div>
                
                <div class="relative z-10">
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">Tổng số tiền cần thanh toán</p>
                    <h3 class="text-4xl font-black mt-1">38,970,000đ</h3>
                </div>
            </div>

            <div class="p-8 sm:p-12 text-center">
                <!-- QR Code Container -->
                <div class="relative inline-block p-4 bg-white rounded-2xl shadow-inner border-2 border-dashed border-gray-200 mb-8 group transition-all hover:border-blue-300">
                    <div class="bg-white p-2 rounded-xl">
                        <!-- Simulated QR Code -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=DIENMAYPRO_PAYMENT_38970000_ORDER_12345" 
                             alt="Payment QR Code" 
                             class="w-64 h-64 mx-auto rounded-lg"
                             id="qr-image">
>>>>>>> origin/Vinhem/QuetMaQR
                    </div>
                    
                    <!-- Corner ornaments -->
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-600 rounded-tl-xl"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-600 rounded-tr-xl"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-600 rounded-bl-xl"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-600 rounded-br-xl"></div>
                    
                    <!-- Scan indicator -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-blue-500 opacity-50 animate-bounce mt-4"></div>
                </div>

<<<<<<< HEAD
                    {{-- Info Column --}}
                    <div class="flex-1 space-y-6 w-full">
                        <div class="grid grid-cols-1 gap-4">
                            <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100">
                                <p class="text-xs text-blue-600 font-bold uppercase mb-1">Tổng số tiền</p>
                                <p id="qr-total" class="text-3xl font-black text-blue-800">0đ</p>
                            </div>
                            
                            <div class="space-y-3 px-2">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Ngân hàng:</span>
                                    <span class="font-bold text-gray-800">MBBANK</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Số tài khoản:</span>
                                    <span class="font-bold text-gray-800 tracking-wider">0559763134</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Chủ tài khoản:</span>
                                    <span class="font-bold text-gray-800 uppercase">HUYNH VAN VINH EM</span>
                                </div>
                                <div class="flex justify-between items-center text-sm pt-3 border-t">
                                    <span class="text-gray-500">Nội dung CK:</span>
                                    <span id="qr-ref" class="font-bold text-blue-600">DMP123456</span>
=======
                <!-- Timer -->
                <div class="mb-8 inline-flex items-center gap-3 px-6 py-2 bg-red-50 rounded-full border border-red-100 text-red-600 font-bold">
                    <i class="fa-solid fa-clock-rotate-left animate-spin-slow"></i>
                    <span>Hết hạn sau: <span id="countdown">14:59</span></span>
                </div>

                <!-- Instructions -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-left mb-10">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm">1</div>
                        <p class="text-sm text-gray-600">Mở ứng dụng <strong>Ngân hàng/Ví</strong> của bạn</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm">2</div>
                        <p class="text-sm text-gray-600">Chọn <strong>Quét mã QR</strong> và quét mã phía trên</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm">3</div>
                        <p class="text-sm text-gray-600">Xác nhận <strong>số tiền</strong> và hoàn tất giao dịch</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4" id="action-buttons">
                    <button onclick="confirmPayment()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check-circle"></i>
                        Xác nhận đã thanh toán
                    </button>
                    <button onclick="history.back()" class="flex-1 bg-white hover:bg-gray-50 text-gray-700 font-bold py-4 px-8 rounded-2xl border border-gray-200 shadow-sm transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-xmark-circle"></i>
                        Hủy giao dịch
                    </button>
                </div>

                <!-- Success State (Hidden by default) -->
                <div id="success-state" class="hidden animate-fade-in">
                    <div class="py-10">
                        <div class="w-24 h-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-green-100">
                            <i class="fa-solid fa-check text-5xl"></i>
                        </div>
                        <h3 class="text-3xl font-black text-gray-900 mb-2">Thanh toán thành công!</h3>
                        <p class="text-gray-500 mb-8 text-lg">Cảm ơn bạn đã tin tưởng mua sắm tại DIENMAYPRO.</p>
                        
                        <!-- Mini Invoice for printing -->
                        <div id="invoice-print" class="bg-white border-2 border-dashed border-gray-200 rounded-2xl p-6 text-left mb-8 max-w-sm mx-auto">
                            <div class="flex justify-between border-b pb-3 mb-3">
                                <span class="font-bold text-gray-800">Mã đơn hàng:</span>
                                <span class="text-blue-600 font-bold">#ORD-99821</span>
                            </div>
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>Ngày đặt:</span>
                                    <span>{{ date('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Phương thức:</span>
                                    <span>QR Code</span>
                                </div>
                                <div class="flex justify-between font-bold text-gray-800 border-t pt-2 mt-2">
                                    <span>Tổng tiền:</span>
                                    <span class="text-red-600">38,970,000đ</span>
>>>>>>> origin/Vinhem/QuetMaQR
                                </div>
                            </div>
                        </div>

<<<<<<< HEAD
                        {{-- Action Button --}}
                        <div class="pt-4">
                            <button onclick="checkPayment()" id="btn-paid" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold shadow-lg shadow-blue-100 transition flex items-center justify-center gap-3">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>XÁC NHẬN</span>
=======
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button onclick="window.print()" class="flex-1 bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 px-6 rounded-xl transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-print"></i> In hóa đơn
>>>>>>> origin/Vinhem/QuetMaQR
                            </button>
                            <a href="{{ route('home') }}" class="flex-1 bg-white hover:bg-gray-50 text-blue-600 border border-blue-600 font-bold py-3 px-6 rounded-xl transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-house"></i> Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
<<<<<<< HEAD
        </div>

        <p class="text-center mt-8 text-gray-400 text-sm">
            Gặp khó khăn? Liên hệ hotline: <strong class="text-gray-600">1900 1234</strong>
=======

            <!-- Footer info -->
            <div class="bg-gray-50 p-6 border-t border-gray-100" id="card-footer">
                <div class="flex items-center justify-between text-xs text-gray-400 font-medium">
                    <span>Mã đơn hàng: #ORD-99821</span>
                    <span>An toàn & Bảo mật 100%</span>
                </div>
            </div>
        </div>

        <p class="mt-8 text-center text-sm text-gray-500" id="help-text">
            Nếu gặp khó khăn trong quá trình thanh toán, vui lòng liên hệ hotline <span class="font-bold text-blue-600">1900 1234</span>
>>>>>>> origin/Vinhem/QuetMaQR
        </p>
    </div>
</div>

<<<<<<< HEAD
{{-- Success Modal --}}
<div id="payment-success" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-3xl p-10 text-center max-w-sm mx-4 shadow-2xl">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="fa-solid fa-circle-check text-5xl text-green-500"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">Đã xác nhận thanh toán!</h3>
        <p class="text-gray-500 text-sm mb-6">Yêu cầu của bạn đã được gửi. Vui lòng chờ quản lý xác nhận đơn hàng.</p>
        <a href="{{ url('/') }}" class="inline-block w-full bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
            Về trang chủ
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const BANK_ID = 'MB';
    const ACCOUNT_NO = '0559763134';
    const ACCOUNT_NAME = 'HUYNH VAN VINH EM';

    function initQR() {
        let total = parseInt(sessionStorage.getItem('paymentTotal') || 0);
        
        if (!total) {
            const rawItems = sessionStorage.getItem('checkoutItems');
            if (rawItems) {
                const items = JSON.parse(rawItems);
                total = items.reduce((sum, i) => sum + (i.price * i.quantity), 0);
            }
=======
@push('scripts')
<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.5s ease-out forwards;
    }
    @media print {
        body * { visibility: hidden; }
        #invoice-print, #invoice-print * { visibility: visible; }
        #invoice-print {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            box-shadow: none;
        }
    }
</style>
<script>
    // Countdown Timer
    let time = 15 * 60; // 15 minutes in seconds
    const countdownElement = document.getElementById('countdown');
    let isFinished = false;

    const timer = setInterval(() => {
        if (isFinished) return;
        
        const minutes = Math.floor(time / 60);
        const seconds = time % 60;
        
        countdownElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        
        if (time <= 0) {
            clearInterval(timer);
            alert('Thời gian thanh toán đã hết hạn. Vui lòng thử lại!');
            window.location.href = "{{ route('cart.pay') }}";
>>>>>>> origin/Vinhem/QuetMaQR
        }
        time--;
    }, 1000);

<<<<<<< HEAD
        if (total === 0) {
            total = 38970000; 
        }

        const ref = 'DMP' + Math.random().toString(36).substring(2, 8).toUpperCase();
        const fmt = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
        
        document.getElementById('qr-total').textContent = fmt;
        document.getElementById('qr-ref').textContent = ref;

        const qrUrl = `https://img.vietqr.io/image/${BANK_ID}-${ACCOUNT_NO}-compact2.png?amount=${total}&addInfo=${ref}&accountName=${encodeURIComponent(ACCOUNT_NAME)}`;
        document.getElementById('qr-img').src = qrUrl;
    }

    function checkPayment() {
        const btn = document.getElementById('btn-paid');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Đang xác nhận...';
        btn.disabled = true;

        setTimeout(() => {
            document.getElementById('payment-success').classList.remove('hidden');
            sessionStorage.removeItem('checkoutItems');
            sessionStorage.removeItem('paymentTotal');
        }, 2000);
    }

    document.addEventListener('DOMContentLoaded', initQR);
</script>
@endpush
=======
    function confirmPayment() {
        isFinished = true;
        clearInterval(timer);

        // Hide payment elements
        document.querySelector('h2').textContent = 'Thanh toán hoàn tất';
        document.querySelector('p.mt-4').textContent = 'Giao dịch của bạn đã được ghi nhận.';
        
        // Hide QR container, timer, instructions, and buttons
        document.querySelector('.relative.inline-block').classList.add('hidden');
        document.querySelector('.bg-red-50').classList.add('hidden');
        document.querySelector('.grid').classList.add('hidden');
        document.getElementById('action-buttons').classList.add('hidden');
        
        // Show success state
        document.getElementById('success-state').classList.remove('hidden');
        
        // Change header background
        document.querySelector('.bg-\\[\\#003399\\]').classList.remove('bg-\\[\\#003399\\]');
        document.querySelector('.p-6.text-white').classList.add('bg-green-600');
        
        // Optional: Trigger print automatically
        // setTimeout(() => window.print(), 1000);
    }
</script>
@endpush
@endsection
>>>>>>> origin/Vinhem/QuetMaQR
