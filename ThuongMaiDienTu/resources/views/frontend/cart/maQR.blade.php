@extends('layouts.app')

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
        @keyframes scanLine {
            0%,100%{top:0;opacity:0} 50%{top:calc(100% - 4px);opacity:1}
        }
        .qr-scan-line { animation: scanLine 2.5s ease-in-out infinite; }
        
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
            <div class="bg-[#003399] p-6 text-white text-center relative overflow-hidden" id="payment-header">
                <!-- Decorative background patterns -->
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-blue-400 rounded-full opacity-10"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 bg-white rounded-full opacity-5"></div>
                
                <div class="relative z-10">
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">Tổng số tiền cần thanh toán</p>
                    <h3 id="display-total" class="text-4xl font-black mt-1">0đ</h3>
                </div>
            </div>

            <div class="p-8 sm:p-12 text-center" id="payment-body">
                <!-- QR Code Container -->
                <div id="qr-container" class="relative inline-block p-4 bg-white rounded-2xl shadow-inner border-2 border-dashed border-gray-200 mb-8 group transition-all hover:border-blue-300">
                    <div class="bg-white p-2 rounded-xl">
                        <img src="" 
                             alt="Payment QR Code" 
                             class="w-64 h-64 mx-auto rounded-lg"
                             id="qr-image">
                    </div>
                    
                    <!-- Corner ornaments -->
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-600 rounded-tl-xl"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-600 rounded-tr-xl"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-600 rounded-bl-xl"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-600 rounded-br-xl"></div>
                    
                    <!-- Scan indicator -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-blue-500 opacity-50 qr-scan-line"></div>
                </div>

                <!-- Timer -->
                <div id="timer-container" class="mb-8 inline-flex items-center gap-3 px-6 py-2 bg-red-50 rounded-full border border-red-100 text-red-600 font-bold">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Hết hạn sau: <span id="countdown">15:00</span></span>
                </div>

                <!-- Instructions -->
                <div id="instructions" class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-left mb-10">
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

                <!-- Bank Info -->
                <div id="bank-info" class="bg-blue-50 rounded-2xl p-6 mb-8 text-left border border-blue-100">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-blue-600 font-bold uppercase">Ngân hàng</p>
                            <p class="font-bold text-gray-800">MBBANK</p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-600 font-bold uppercase">Số tài khoản</p>
                            <p class="font-bold text-gray-800 tracking-wider">0559763134</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-blue-600 font-bold uppercase">Chủ tài khoản</p>
                            <p class="font-bold text-gray-800 uppercase">HUYNH VAN VINH EM</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-blue-600 font-bold uppercase">Nội dung chuyển khoản</p>
                            <p id="qr-ref" class="font-bold text-blue-600">DMP123456</p>
                        </div>
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
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <button onclick="window.print()" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 px-8 rounded-xl transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-print"></i> In hóa đơn
                            </button>
                            <a href="{{ route('home') }}" class="bg-white hover:bg-gray-50 text-blue-600 border border-blue-600 font-bold py-3 px-8 rounded-xl transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-house"></i> Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer info -->
            <div class="bg-gray-50 p-6 border-t border-gray-100" id="card-footer">
                <div class="flex items-center justify-between text-xs text-gray-400 font-medium">
                    <span id="order-id-footer">Mã đơn hàng: #ORD-99821</span>
                    <span>An toàn & Bảo mật 100%</span>
                </div>
            </div>
        </div>

        <p class="mt-8 text-center text-sm text-gray-500" id="help-text">
            Nếu gặp khó khăn trong quá trình thanh toán, vui lòng liên hệ hotline <span class="font-bold text-blue-600">1900 1234</span>
        </p>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Configuration
    const BANK_ID = 'MB';
    const ACCOUNT_NO = '0559763134';
    const ACCOUNT_NAME = 'HUYNH VAN VINH EM';
    
    // Timer Logic
    let time = 15 * 60; 
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
            window.location.href = "{{ route('home') }}";
        }
        time--;
    }, 1000);

    // QR & Data Initialization
    function initQR() {
        const order = {!! isset($order) ? json_encode($order) : "null" !!};
        
        let total = order ? order.final_amount : 0;
        let ref = order ? 'DMP' + order.order_id : 'DMP' + Math.random().toString(36).substring(2, 8).toUpperCase();
        
        const fmt = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
        
        document.getElementById('display-total').textContent = fmt;
        document.getElementById('qr-ref').textContent = ref;
        document.getElementById('order-id-footer').textContent = 'Mã đơn hàng: #' + (order ? order.order_id : 'ORD-99821');

        const qrUrl = `https://img.vietqr.io/image/${BANK_ID}-${ACCOUNT_NO}-compact2.png?amount=${total}&addInfo=${ref}&accountName=${encodeURIComponent(ACCOUNT_NAME)}`;
        document.getElementById('qr-image').src = qrUrl;
    }

    function confirmPayment() {
        isFinished = true;
        clearInterval(timer);

        // UI transitions
        document.querySelector('h2').textContent = 'Thanh toán hoàn tất';
        document.querySelector('p.mt-4').textContent = 'Giao dịch của bạn đã được ghi nhận.';
        
        document.getElementById('qr-container').classList.add('hidden');
        document.getElementById('timer-container').classList.add('hidden');
        document.getElementById('instructions').classList.add('hidden');
        document.getElementById('bank-info').classList.add('hidden');
        document.getElementById('action-buttons').classList.add('hidden');
        
        document.getElementById('success-state').classList.remove('hidden');
        
        const header = document.getElementById('payment-header');
        header.classList.remove('bg-[#003399]');
        header.classList.add('bg-green-600');
        
        // Clear session
        sessionStorage.removeItem('checkoutItems');
        sessionStorage.removeItem('paymentTotal');
    }

    document.addEventListener('DOMContentLoaded', initQR);
</script>
@endpush
