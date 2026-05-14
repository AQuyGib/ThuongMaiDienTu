@extends('layouts.app')

@section('title', 'Tính phí vận chuyển - DIENMAYPRO')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="bg-gray-50 text-gray-800 font-sans p-6 min-h-screen pt-12">
    <div class="max-w-4xl mx-auto flex flex-col md:flex-row gap-6">
        
        <!-- Cột trái: Thông tin tính phí -->
        <div class="flex-1 bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 flex flex-col h-fit">
            <!-- Header -->
            <div class="bg-blue-600 p-4 text-white">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast"></i> Ước tính phí vận chuyển
                </h2>
            </div>

            <div class="p-6 flex-grow">
                <!-- Chọn tỉnh thành -->
                <div class="mb-6">
                    <label for="province" class="block text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Địa điểm giao hàng:</label>
                    <div class="relative">
                        <select id="province" onchange="calculateShipping()" class="w-full p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none appearance-none bg-white transition-all cursor-pointer text-lg font-medium">
                            <option value="" disabled selected>-- Chọn tỉnh thành --</option>
                            <optgroup label="Thành phố lớn (Miễn phí từ 500k)">
                                <option value="hcm" data-fee="20000" data-threshold="500000">TP. Hồ Chí Minh</option>
                                <option value="hn" data-fee="20000" data-threshold="500000">TP. Hà Nội</option>
                                <option value="dn" data-fee="25000" data-threshold="1000000">TP. Đà Nẵng</option>
                                <option value="ct" data-fee="30000" data-threshold="1000000">TP. Cần Thơ</option>
                                <option value="hp" data-fee="30000" data-threshold="1000000">TP. Hải Phòng</option>
                            </optgroup>
                            <optgroup label="Các Tỉnh khu vực khác (Miễn phí từ 2tr)">
                                <option value="bd" data-fee="35000" data-threshold="2000000">Tỉnh Bình Dương</option>
                                <option value="dnai" data-fee="35000" data-threshold="2000000">Tỉnh Đồng Nai</option>
                                <option value="la" data-fee="40000" data-threshold="2000000">Tỉnh Long An</option>
                                <option value="tg" data-fee="40000" data-threshold="2000000">Tỉnh Tiền Giang</option>
                                <option value="vt" data-fee="40000" data-threshold="2000000">Tỉnh Bà Rịa - Vũng Tàu</option>
                                <option value="other" data-fee="50000" data-threshold="5000000">Các tỉnh thành khác</option>
                            </optgroup>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Tổng tiền đơn hàng -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Tạm tính đơn hàng:</label>
                    <div class="flex items-center bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <span class="text-2xl font-bold text-gray-800" id="orderTotalText">0đ</span>
                        <input type="hidden" id="orderTotal" value="0">
                    </div>
                    <div id="shipping-policy-info" class="mt-3 p-3 bg-blue-50 rounded-lg flex items-center gap-3 border border-blue-100 transition-all">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        <p class="text-sm text-blue-700 font-medium" id="policy-text">Vui lòng chọn địa điểm để xem chính sách miễn phí vận chuyển</p>
                    </div>
                </div>

                <!-- Kết quả -->
                <div id="result-box" class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-100 hidden shadow-inner">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-gray-600">
                            <div class="flex flex-col">
                                <span class="font-medium text-lg">Phí vận chuyển:</span>
                                <span id="delivery-time" class="text-xs text-gray-400 italic">Dự kiến giao trong 2-3 ngày</span>
                            </div>
                            <span id="shipping-fee-text" class="text-xl font-bold text-blue-700">0đ</span>
                        </div>
                        <div class="h-px bg-blue-200 w-full"></div>
                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-gray-500 text-sm block mb-1">Tổng chi phí dự kiến:</span>
                                <span class="text-lg font-bold text-gray-800 uppercase">Tổng cộng</span>
                            </div>
                            <span id="final-total-text" class="text-3xl font-black text-red-600 tracking-tight">0đ</span>
                        </div>
                    </div>
                </div>

                <div id="placeholder-box" class="text-center py-10 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 text-gray-400">
                    <i class="fa-solid fa-location-dot text-4xl mb-3 block opacity-20"></i>
                    <p class="italic">Vui lòng chọn tỉnh thành để nhận báo giá vận chuyển</p>
                </div>
            </div>

            <div class="p-6 bg-gray-50 border-t border-gray-100">
                <a href="{{ route('cart.index') }}" class="flex items-center justify-center gap-2 w-full py-3 text-[#0047b3] font-bold hover:bg-blue-100 rounded-xl transition-all border border-[#0047b3]">
                    <i class="fa-solid fa-cart-shopping"></i> QUAY LẠI GIỎ HÀNG
                </a>
            </div>
        </div>

        <!-- Cột phải: Danh sách sản phẩm được tính -->
        <div class="w-full md:w-80 space-y-4">
            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-100">
                <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2 border-b pb-2">
                    <i class="fa-solid fa-list-check text-blue-600"></i> Sản phẩm vận chuyển
                </h3>
                <div id="mini-cart-items" class="space-y-3 max-h-96 overflow-y-auto pr-1 custom-scrollbar">
                    <!-- Items sẽ được render ở đây -->
                </div>
            </div>
            
            <div class="bg-indigo-900 text-white p-5 rounded-xl shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-xs uppercase tracking-widest opacity-70 font-bold mb-1">Dịch vụ hỏa tốc</p>
                    <h4 class="text-lg font-bold mb-2">Giao hàng nhanh 2H</h4>
                    <p class="text-sm opacity-80 leading-relaxed">Áp dụng cho khu vực nội thành TP.HCM và Hà Nội cho các đơn hàng có biểu tượng <i class="fa-solid fa-bolt text-yellow-400"></i></p>
                </div>
                <i class="fa-solid fa-truck-bolt absolute -bottom-4 -right-4 text-7xl opacity-10 -rotate-12"></i>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Khởi tạo dữ liệu tương tự shoppingcart.blade.php
        window.cartData = [];

        function initializeData() {
            try {
                const raw = '{!! isset($cartItems) ? json_encode($cartItems) : "[]" !!}';
                window.cartData = JSON.parse(raw);
                
                const urlParams = new URLSearchParams(window.location.search);
                const totalParam = urlParams.get('total');
                
                const container = document.getElementById('mini-cart-items');

                if (window.cartData && window.cartData.length > 0) {
                    calculateSubtotalFromCart();
                } else if (totalParam) {
                    // Nếu không có cartItems nhưng có total param (từ URL)
                    updateUIWithTotal(parseInt(totalParam));
                    if (container) {
                        container.innerHTML = `
                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg text-gray-500 border border-dashed border-gray-200">
                                <i class="fa-solid fa-circle-exclamation opacity-50"></i>
                                <p class="text-xs italic">Đang hiển thị phí cho giá trị từ trang giỏ hàng.</p>
                            </div>
                        `;
                    }
                } else {
                    if (container) container.innerHTML = '<p class="text-sm text-gray-400 italic text-center py-4">Giỏ hàng trống</p>';
                    updateUIWithTotal(0);
                }
            } catch (e) {
                console.error("Lỗi parse dữ liệu giỏ hàng", e);
            }
        }

        function calculateSubtotalFromCart() {
            let total = 0;
            const container = document.getElementById('mini-cart-items');
            if (container) container.innerHTML = '';

            window.cartData.forEach(item => {
                if (item.selected) {
                    total += (item.price * item.quantity);
                    
                    // Render mini item
                    if (container) {
                        const itemHtml = `
                            <div class="flex gap-3 items-center border-b border-gray-50 pb-2">
                                <img src="${item.image}" class="w-10 h-10 object-contain rounded bg-gray-50" alt="${item.name}">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-700 truncate">${item.name}</p>
                                    <p class="text-[10px] text-gray-400">SL: ${item.quantity} x ${formatMoney(item.price)}</p>
                                </div>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', itemHtml);
                    }
                }
            });

            if (total === 0 && container) {
                 container.innerHTML = '<p class="text-sm text-gray-400 italic text-center py-4">Chưa chọn sản phẩm</p>';
            }

            updateUIWithTotal(total);
        }

        function updateUIWithTotal(total) {
            document.getElementById('orderTotal').value = total;
            document.getElementById('orderTotalText').innerText = formatMoney(total);
            // Sau khi update total, nếu đã chọn tỉnh thì tính lại luôn
            if (document.getElementById('province').value) {
                calculateShipping();
            }
        }

        const formatMoney = (amount) => {
            return new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
        };

        function calculateShipping() {
            const provinceSelect = document.getElementById('province');
            const orderTotalInput = document.getElementById('orderTotal');
            const resultBox = document.getElementById('result-box');
            const placeholderBox = document.getElementById('placeholder-box');
            const policyText = document.getElementById('policy-text');
            const deliveryTime = document.getElementById('delivery-time');
            
            const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
            const orderTotal = parseInt(orderTotalInput.value) || 0;

            if (!selectedOption || !selectedOption.value) return;

            let baseFee = parseInt(selectedOption.getAttribute('data-fee')) || 0;
            const threshold = parseInt(selectedOption.getAttribute('data-threshold')) || 0;
            const provinceValue = selectedOption.value;
            
            // Cập nhật text chính sách
            policyText.innerHTML = `Miễn phí vận chuyển cho đơn hàng từ <span class="font-bold">${formatMoney(threshold)}</span>`;
            
            // Cập nhật thời gian giao hàng dự kiến
            if (['hcm', 'hn'].includes(provinceValue)) {
                deliveryTime.innerText = "Dự kiến giao trong 24h - 48h";
            } else {
                deliveryTime.innerText = "Dự kiến giao trong 3 - 5 ngày";
            }
            
            // Logic miễn phí vận chuyển theo từng tỉnh
            if (orderTotal >= threshold) {
                baseFee = 0;
            }

            const finalTotal = orderTotal + baseFee;

            // Hiển thị kết quả
            placeholderBox.classList.add('hidden');
            resultBox.classList.remove('hidden');

            document.getElementById('shipping-fee-text').innerText = baseFee === 0 ? 'MIỄN PHÍ' : formatMoney(baseFee);
            document.getElementById('final-total-text').innerText = formatMoney(finalTotal);

            // Thêm hiệu ứng flash nhẹ khi thay đổi
            resultBox.classList.add('animate-pop');
            setTimeout(() => resultBox.classList.remove('animate-pop'), 400);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initializeData();
        });
    </script>
@endpush

@push('styles')
    <style>
        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        .animate-pop {
            animation: pop 0.4s ease-out;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
@endpush
@endsection
