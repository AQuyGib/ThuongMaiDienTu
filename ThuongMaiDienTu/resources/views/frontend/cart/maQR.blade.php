@extends('layouts.app')

@section('title', 'Thanh toán QR Code - DIENMAYPRO')

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
        /* ============================================================
           CSS HIỆU ỨNG TRANG THANH TOÁN MÃ QR CODE
           ============================================================ */

        /* Hiệu ứng nhấp nháy phóng to nhẹ cho nút "Tôi đã chuyển khoản" */
        @keyframes pulse-custom {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        .animate-pulse-slow {
            animation: pulse-custom 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Lớp nền mờ kính thủy tinh (Glassmorphism Effect) */
        .glass-morphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Dòng quét la-ze đỏ/xanh chạy dọc theo mã QR để tăng tính trực quan */
        @keyframes scanLine {
            0%,100%{top:0;opacity:0} 50%{top:calc(100% - 4px);opacity:1}
        }
        .qr-scan-line { animation: scanLine 2.5s ease-in-out infinite; }
        
        /* Hiệu ứng trượt nhẹ từ dưới lên (Fade-in-up) cho các khối trạng thái */
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.5s ease-out forwards;
        }

        /* Thiết lập CSS Print khi người dùng bấm in hóa đơn mua hàng */
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
        <!-- Tiêu đề chính trang thanh toán -->
        <div class="text-center mb-10">
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">
                Quét mã QR để thanh toán
            </h2>
            <p class="mt-4 text-lg text-gray-600">
                Vui lòng sử dụng ứng dụng Ngân hàng hoặc Ví điện tử để quét mã dưới đây.
            </p>
        </div>

        <!-- Thẻ chính hiển thị thông tin chuyển khoản (Glassmorphism Card) -->
        <div class="glass-morphism rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
            <!-- Header của thẻ: hiển thị tổng tiền cần thanh toán -->
            <div class="bg-[#003399] p-6 text-white text-center relative overflow-hidden" id="payment-header">
                <!-- Họa tiết bóng tròn trang trí nền thẻ -->
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-blue-400 rounded-full opacity-10"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 bg-white rounded-full opacity-5"></div>
                
                <div class="relative z-10">
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">Tổng số tiền cần thanh toán</p>
                    <h3 id="display-total" class="text-4xl font-black mt-1">0đ</h3>
                </div>
            </div>

            <div class="p-8 sm:p-12 text-center" id="payment-body">
                <!-- KHUNG CHỨA MÃ QR CODE NGÂN HÀNG (GENERATED DYNAMICALLY VIA VIETQR) -->
                <div class="flex justify-center mb-8">
                    <div id="qr-container" class="relative inline-block p-4 bg-white rounded-2xl shadow-inner border-2 border-dashed border-gray-200 group transition-all hover:border-blue-300">
                        <div class="bg-white p-2 rounded-xl">
                            <!-- Ảnh QR sẽ được điền URL tự động bằng JS -->
                            <img src="" 
                                 alt="Payment QR Code" 
                                 class="w-64 h-64 mx-auto rounded-lg"
                                 id="qr-image">
                        </div>
                        
                        <!-- Đường viền trang trí 4 góc (Corners Ornament) -->
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-600 rounded-tl-xl"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-600 rounded-tr-xl"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-600 rounded-bl-xl"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-600 rounded-br-xl"></div>
                        
                        <!-- Thanh chạy quét laser động -->
                        <div class="absolute top-0 left-0 w-full h-1 bg-blue-500 opacity-50 qr-scan-line"></div>
                    </div>
                </div>

                <!-- Các bước hướng dẫn quét mã -->
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

                <!-- Bảng chi tiết thông tin tài khoản thụ hưởng -->
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
                            <!-- Nội dung này rất quan trọng, chứa mã đơn hàng khớp với DB để Admin đối chiếu nhanh -->
                            <p id="qr-ref" class="font-bold text-blue-600">DMP123456</p>
                        </div>
                    </div>
                </div>

                <!-- CỤM NÚT ĐIỀU HƯỚNG TƯƠNG TÁC -->
                <div class="flex flex-col gap-4" id="action-buttons">

                    <!-- Nút báo hiệu đã chuyển tiền (Nhấp nháy để thu hút hành động) -->
                    <button onclick="showTransferredModal()" id="btn-transferred"
                        class="w-full bg-green-500 hover:bg-green-600 text-white font-black py-5 px-8 rounded-2xl shadow-xl transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3 text-lg animate-pulse-slow border-2 border-green-400">
                        <i class="fa-solid fa-paper-plane text-xl"></i>
                        Tôi đã chuyển khoản
                    </button>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button onclick="confirmPayment()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check-circle"></i>
                            Xác nhận đã thanh toán
                        </button>
                        <button onclick="showCancelConfirmation()" class="flex-1 bg-white hover:bg-gray-50 text-gray-700 font-bold py-4 px-8 rounded-2xl border border-gray-200 shadow-sm transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-xmark-circle"></i>
                            Hủy giao dịch
                        </button>
                    </div>
                    <a href="{{ route('home') }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-4 px-8 rounded-2xl border border-slate-200 shadow-sm transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-house"></i>
                        Về trang chủ
                    </a>
                </div>

                <!-- MODAL XÁC NHẬN HỦY GIAO DỊCH (CANCEL CONFIRMATION MODAL) -->
                <div id="cancel-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300">
                    <div class="bg-white rounded-3xl p-6 max-w-sm w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce-subtle">
                                <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Bạn có muốn hủy đơn hàng không?</h3>
                            <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                                Đơn hàng của bạn sẽ bị hủy bỏ và các sản phẩm trong giỏ hàng sẽ được giải phóng. Bạn không thể hoàn tác hành động này.
                            </p>
                            <div class="flex gap-4">
                                <!-- Đồng ý hủy: Submit form để Server cập nhật trạng thái hủy đơn -->
                                <button onclick="confirmCancelOrder()" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-all active:scale-95">
                                    Có
                                </button>
                                <button onclick="hideCancelConfirmation()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-xl transition-all active:scale-95">
                                    Không
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL XÁC NHẬN ĐÃ CHUYỂN KHOẢN (TRANSFERRED CONFIRMATION MODAL) -->
                <div id="transferred-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50 backdrop-blur-sm">
                    <div class="bg-white rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-5">
                                <i class="fa-solid fa-paper-plane text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Bạn đã chuyển khoản thành công?</h3>
                            <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                                Sau khi xác nhận, hệ thống sẽ đối soát giao dịch và Ban quản trị sẽ duyệt đơn hàng của bạn trong thời gian sớm nhất.
                            </p>
                            <div class="flex gap-4">
                                <button onclick="confirmTransferred()" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-check"></i> Đã chuyển rồi
                                </button>
                                <button onclick="closeTransferredModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-xl transition-all active:scale-95">
                                    Chưa chuyển
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biểu mẫu ẩn dùng để gửi request hủy đơn lên Backend -->
                <form id="cancel-form" action="{{ route('cart.cancel') }}" method="POST" class="hidden">
                    @csrf
                </form>

                <!-- KHUNG CHỜ ADMIN DUYỆT ĐƠN (WAITING STATE - RENDER DYNAMICALLY VIA TIMEOUTS) -->
                <div id="waiting-state" class="hidden animate-fade-in">
                    <div class="py-10">
                        <div class="relative w-24 h-24 mx-auto mb-6">
                            <!-- Vòng tròn quay vô tận -->
                            <div class="absolute inset-0 rounded-full border-4 border-blue-600 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
                            <!-- Icon đồng hồ cát nhấp nháy ở trung tâm -->
                            <div class="absolute inset-2 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shadow-md animate-pulse">
                                <i class="fa-solid fa-hourglass-half text-4xl"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Đang chờ Admin duyệt đơn hàng...</h3>
                        <p class="text-gray-500 text-sm max-w-sm mx-auto mb-8 leading-relaxed">
                            Hệ thống đã nhận được yêu cầu thanh toán của bạn. Yêu cầu đang được chuyển đến Ban quản trị để kiểm tra và duyệt giao dịch. Vui lòng đợi trong giây lát!
                        </p>
                        
                        <!-- Tiến độ các bước xử lý (được JS cập nhật đổi màu tích xanh) -->
                        <div class="max-w-xs mx-auto space-y-3 text-left bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <div class="flex items-center gap-3 transition-opacity duration-300" id="step-verifying">
                                <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]" id="step-verifying-icon">
                                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                                </div>
                                <span class="text-xs font-semibold text-gray-700" id="step-verifying-text">Đang đối soát giao dịch ngân hàng</span>
                            </div>
                            <div class="flex items-center gap-3 opacity-40 transition-opacity duration-300" id="step-sending">
                                <div class="w-5 h-5 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-[10px]" id="step-sending-icon">✓</div>
                                <span class="text-xs font-semibold text-gray-500" id="step-sending-text">Đang gửi yêu cầu phê duyệt đơn hàng</span>
                            </div>
                            <div class="flex items-center gap-3 opacity-40 transition-opacity duration-300" id="step-approving">
                                <div class="w-5 h-5 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-[10px]" id="step-approving-icon">✓</div>
                                <span class="text-xs font-semibold text-gray-500" id="step-approving-text">Chờ quản trị viên xác nhận giao dịch</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KHUNG BÁO THÀNH CÔNG SAU KHI ADMIN PHÊ DUYỆT (SUCCESS STATE) -->
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

            <!-- Footer của thẻ: Hiển thị mã đơn hàng -->
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
    // Cấu hình tham số thẻ nhận tiền phục vụ VietQR API
    const BANK_ID = 'MB';
    const ACCOUNT_NO = '0559763134';
    const ACCOUNT_NAME = 'HUYNH VAN VINH EM';
    
    // Thiết lập thời gian đếm ngược (Mặc định 15 phút = 900 giây)
    let time = 15 * 60; 
    const countdownElement = document.getElementById('countdown');
    let isFinished = false;

    // Khởi chạy vòng lặp đếm ngược mỗi giây
    const timer = setInterval(() => {
        if (isFinished) return;
        
        const minutes = Math.floor(time / 60);
        const seconds = time % 60;
        
        if (countdownElement) {
            countdownElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        }
        
        // Hết hạn thời gian: Chuyển hướng người dùng về trang chủ
        if (time <= 0) {
            clearInterval(timer);
            alert('Thời gian thanh toán đã hết hạn. Vui lòng thử lại!');
            window.location.href = "{{ route('home') }}";
        }
        time--;
    }, 1000);

    /**
     * 1. KHỞI TẠO MÃ QR CODE ĐỘNG (VIETQR COMPACT API)
     * Parse thông tin đơn hàng được truyền từ PHP Laravel, thiết lập tổng tiền, 
     * tạo nội dung chuyển khoản tự động (ref = DMP + order_id), và chèn API URL vào thẻ ảnh img.
     */
    function initQR() {
        const order = {!! isset($order) ? json_encode($order) : "null" !!};
        
        let total = order ? order.final_amount : 0;
        let ref = order ? 'DMP' + order.order_id : 'DMP' + Math.random().toString(36).substring(2, 8).toUpperCase();
        
        const fmt = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
        
        document.getElementById('display-total').textContent = fmt;
        document.getElementById('qr-ref').textContent = ref;
        document.getElementById('order-id-footer').textContent = 'Mã đơn hàng: #' + (order ? order.order_id : 'ORD-99821');

        // Tạo chuỗi VietQR API để tự động điền ngân hàng, số tài khoản, số tiền và nội dung chuyển khoản
        const qrUrl = `https://img.vietqr.io/image/${BANK_ID}-${ACCOUNT_NO}-compact2.png?amount=${total}&addInfo=${ref}&accountName=${encodeURIComponent(ACCOUNT_NAME)}`;
        document.getElementById('qr-image').src = qrUrl;

        // Lưu trữ cục bộ ID đơn hàng đang chờ xử lý vào localStorage
        const orderId = (order && order.order_id) || new URLSearchParams(window.location.search).get('order_id');
        if (orderId) {
            localStorage.setItem('pending_payment_order_id', orderId);
        }
    }

    /**
     * 2. XỬ LÝ TRÌNH DỰNG CHUYỂN CẢNH TRẠNG THÁI (PROCESSING FLOW SIMULATION)
     * Sau khi người dùng xác nhận đã chuyển tiền, hệ thống ẩn toàn bộ khung mã QR/ngân hàng,
     * hiện khung chờ duyệt và lần lượt kích hoạt tích xanh các bước tiến độ (qua setTimeout):
     * - Bước 1 (1.5s): Hoàn tất đối soát ngân hàng. Kích hoạt spinner ở Bước 2.
     * - Bước 2 (3s): Hoàn tất gửi phê duyệt. Kích hoạt spinner ở Bước 3.
     * - Bước 3 (4.5s): Thành công phê duyệt đơn hàng. Hiện giao diện thanh toán thành công (Success State),
     * làm sạch bộ nhớ đệm giỏ hàng cục bộ ở Client.
     */
    function confirmPayment() {
        isFinished = true;
        clearInterval(timer);

        // Chuyển tiêu đề chính
        document.querySelector('h2').textContent = 'Đang xử lý thanh toán';
        document.querySelector('p.mt-4').textContent = 'Hệ thống đang đối soát dữ liệu giao dịch.';

        // Ẩn tất cả các khối tương tác mã QR
        document.getElementById('qr-container')?.classList.add('hidden');
        document.getElementById('timer-container')?.classList.add('hidden');
        document.getElementById('instructions')?.classList.add('hidden');
        document.getElementById('bank-info')?.classList.add('hidden');
        document.getElementById('action-buttons')?.classList.add('hidden');
        
        // Hiển thị khung chờ duyệt Admin
        document.getElementById('waiting-state')?.classList.remove('hidden');

        // Đổi màu nền Header của card sang màu cam vàng cảnh báo đang xử lý
        const header = document.getElementById('payment-header');
        if (header) {
            header.classList.remove('bg-[#003399]');
            header.classList.add('bg-amber-600');
        }

        // Bước 1 -> Bước 2 (Sau 1.5 giây)
        setTimeout(() => {
            const step1Icon = document.getElementById('step-verifying-icon');
            if (step1Icon) step1Icon.innerHTML = '✓';
            const step1Text = document.getElementById('step-verifying-text');
            if (step1Text) step1Text.className = 'text-xs font-semibold text-green-600';
            
            const step2 = document.getElementById('step-sending');
            if (step2) step2.classList.remove('opacity-40');
            const step2Icon = document.getElementById('step-sending-icon');
            if (step2Icon) {
                step2Icon.className = 'w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]';
                step2Icon.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            }
        }, 1500);

        // Bước 2 -> Bước 3 (Sau 3 giây)
        setTimeout(() => {
            const step2Icon = document.getElementById('step-sending-icon');
            if (step2Icon) {
                step2Icon.className = 'w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-[10px]';
                step2Icon.innerHTML = '✓';
            }
            const step2Text = document.getElementById('step-sending-text');
            if (step2Text) step2Text.className = 'text-xs font-semibold text-green-600';

            const step3 = document.getElementById('step-approving');
            if (step3) step3.classList.remove('opacity-40');
            const step3Icon = document.getElementById('step-approving-icon');
            if (step3Icon) {
                step3Icon.className = 'w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]';
                step3Icon.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            }
        }, 3000);

        // Hoàn thành hoàn toàn -> Thành công (Sau 4.5 giây)
        setTimeout(() => {
            document.getElementById('waiting-state')?.classList.add('hidden');
            document.getElementById('success-state')?.classList.remove('hidden');

            document.querySelector('h2').textContent = 'Thanh toán hoàn tất';
            document.querySelector('p.mt-4').textContent = 'Giao dịch của bạn đã được ghi nhận.';

            if (header) {
                header.classList.remove('bg-amber-600');
                header.classList.add('bg-green-600');
            }

            // Dọn dẹp dữ liệu lưu tạm của đơn hàng và giỏ hàng ở Client
            sessionStorage.removeItem('checkoutItems');
            sessionStorage.removeItem('paymentTotal');
            localStorage.removeItem('pending_payment_order_id');
        }, 4500);
    }

    // Hiển thị hộp xác nhận báo đã chuyển tiền
    function showTransferredModal() {
        document.getElementById('transferred-modal')?.classList.remove('hidden');
    }

    // Đóng hộp xác nhận báo đã chuyển tiền
    function closeTransferredModal() {
        document.getElementById('transferred-modal')?.classList.add('hidden');
    }

    // Đồng ý xác nhận đã chuyển khoản
    function confirmTransferred() {
        closeTransferredModal();
        confirmPayment();
    }

    // Hiển thị hộp thoại xác nhận hủy đơn
    function showCancelConfirmation() {
        const modal = document.getElementById('cancel-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Ẩn hộp thoại xác nhận hủy đơn
    function hideCancelConfirmation() {
        const modal = document.getElementById('cancel-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Đồng ý hủy đơn hàng: Thực hiện xóa localStorage và submit form hủy lên Server
    function confirmCancelOrder() {
        localStorage.removeItem('pending_payment_order_id');
        
        const form = document.getElementById('cancel-form');
        if (form) {
            form.submit();
        } else {
            window.location.href = "{{ route('home') }}";
        }
    }

    // Đăng ký sự kiện khởi tạo mã QR ngay sau khi DOM sẵn sàng
    document.addEventListener('DOMContentLoaded', initQR);
</script>
@endpush
