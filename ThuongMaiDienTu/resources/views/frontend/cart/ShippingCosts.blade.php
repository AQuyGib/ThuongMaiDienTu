@extends('layouts.app')

@section('title', 'Tính phí vận chuyển - DIENMAYPRO')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="bg-gray-50 text-gray-800 font-sans p-6 min-h-screen pt-12">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <!-- Header -->
        <div class="bg-blue-600 p-4 text-white">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-truck-fast"></i> Ước tính phí vận chuyển
            </h2>
        </div>

        <div class="p-6">
            <!-- Chọn tỉnh thành -->
            <div class="mb-6">
                <label for="province" class="block text-sm font-semibold text-gray-700 mb-2">Chọn Tỉnh / Thành phố:</label>
                <select id="province" onchange="calculateShipping()" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    <option value="" disabled selected>-- Chọn tỉnh thành --</option>
                    <optgroup label="Thành phố lớn">
                        <option value="hcm" data-fee="20000">TP. Hồ Chí Minh</option>
                        <option value="hn" data-fee="20000">TP. Hà Nội</option>
                        <option value="dn" data-fee="25000">TP. Đà Nẵng</option>
                        <option value="ct" data-fee="30000">TP. Cần Thơ</option>
                        <option value="hp" data-fee="30000">TP. Hải Phòng</option>
                    </optgroup>
                    <optgroup label="Các Tỉnh khu vực khác">
                        <option value="bd" data-fee="35000">Tỉnh Bình Dương</option>
                        <option value="dnai" data-fee="35000">Tỉnh Đồng Nai</option>
                        <option value="la" data-fee="40000">Tỉnh Long An</option>
                        <option value="tg" data-fee="40000">Tỉnh Tiền Giang</option>
                        <option value="vt" data-fee="40000">Tỉnh Bà Rịa - Vũng Tàu</option>
                        <option value="other" data-fee="50000">Các tỉnh thành khác</option>
                    </optgroup>
                </select>
            </div>

            <!-- Tổng tiền đơn hàng (Giả lập) -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tổng tiền hàng (tạm tính):</label>
                <div class="flex items-center gap-2">
                    <input type="number" id="orderTotal" value="450000" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 font-medium cursor-not-allowed outline-none" readonly>
                    <span class="font-bold text-gray-500">đ</span>
                </div>
                <p class="text-xs text-blue-600 mt-1 italic">* Miễn phí vận chuyển cho đơn hàng từ 500.000đ</p>
            </div>

            <!-- Kết quả -->
            <div id="result-box" class="bg-blue-50 p-4 rounded-lg border border-blue-100 hidden">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Phí vận chuyển:</span>
                    <span id="shipping-fee-text" class="font-bold text-blue-700">0đ</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-blue-200">
                    <span class="text-lg font-bold">Tổng cộng:</span>
                    <span id="final-total-text" class="text-xl font-extrabold text-red-600">0đ</span>
                </div>
            </div>

            <div id="placeholder-box" class="text-center py-4 text-gray-400 italic">
                Vui lòng chọn tỉnh thành để tính phí
            </div>
        </div>

        <div class="p-4 bg-gray-50 text-center">
            <a href="{{ route('cart.index') }}" class="text-sm text-blue-600 hover:underline">
                <i class="fa-solid fa-arrow-left"></i> Quay lại giỏ hàng
            </a>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const formatMoney = (amount) => {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        };

        function calculateShipping() {
            const provinceSelect = document.getElementById('province');
            const orderTotalInput = document.getElementById('orderTotal');
            const resultBox = document.getElementById('result-box');
            const placeholderBox = document.getElementById('placeholder-box');
            
            const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
            const orderTotal = parseInt(orderTotalInput.value) || 0;

            if (!selectedOption.value) return;

            let baseFee = parseInt(selectedOption.getAttribute('data-fee')) || 0;
            
            // Logic miễn phí vận chuyển
            if (orderTotal >= 500000) {
                baseFee = 0;
            }

            const finalTotal = orderTotal + baseFee;

            // Hiển thị kết quả
            placeholderBox.classList.add('hidden');
            resultBox.classList.remove('hidden');

            document.getElementById('shipping-fee-text').innerText = baseFee === 0 ? 'Miễn phí' : formatMoney(baseFee);
            document.getElementById('final-total-text').innerText = formatMoney(finalTotal);

            // Thêm hiệu ứng flash nhẹ khi thay đổi
            resultBox.classList.add('animate-pulse');
            setTimeout(() => resultBox.classList.remove('animate-pulse'), 500);
        }

        // Tự động lấy tổng tiền từ URL (nếu có) khi load trang
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const totalParam = urlParams.get('total');
            if (totalParam) {
                document.getElementById('orderTotal').value = totalParam;
                // Có thể tự động tính luôn nếu đã có tỉnh thành (thường là chưa có)
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        @keyframes pulse {
            0% { background-color: rgb(239 246 255); }
            50% { background-color: rgb(219 234 254); }
            100% { background-color: rgb(239 246 255); }
        }
        .animate-pulse {
            animation: pulse 0.5s ease-in-out;
        }
    </style>
@endpush
@endsection
