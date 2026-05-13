@extends('layouts.app')
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
                    </div>

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
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-4">
                            <button onclick="checkPayment()" id="btn-paid" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold shadow-lg shadow-blue-100 transition flex items-center justify-center gap-3">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>XÁC NHẬN</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center mt-8 text-gray-400 text-sm">
            Gặp khó khăn? Liên hệ hotline: <strong class="text-gray-600">1900 1234</strong>
        </p>
    </div>
</div>

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
        }

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
