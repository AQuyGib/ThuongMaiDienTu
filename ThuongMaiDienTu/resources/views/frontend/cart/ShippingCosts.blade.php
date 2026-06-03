@extends('layouts.app')

@section('title', 'Tính phí vận chuyển - DIENMAYPRO')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            }
        }
    </script>
@endpush

@section('content')
<div class="bg-gray-50 text-gray-800 font-sans p-6 min-h-screen flex items-center justify-center">
    <div class="max-w-4xl w-full mx-auto flex flex-col md:flex-row gap-6 items-center justify-center">
        
        <!-- CỘT BÊN TRÁI: KHUNG ĐIỀU KHIỂN & ƯỚC TÍNH PHÍ VẬN CHUYỂN -->
        <div class="flex-1 bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 flex flex-col h-fit">
            <!-- Header -->
            <div class="bg-blue-600 p-4 text-white">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast"></i> Ước tính phí vận chuyển
                </h2>
            </div>

            <div class="p-6 flex-grow">
                <!-- Chọn địa chỉ đã lưu (Nếu có) -->
                @if(Auth::check() && isset($addresses) && $addresses->isNotEmpty())
                    <div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4 border border-blue-100 bg-blue-50/50 rounded-xl">
                        <div>
                            <div class="text-sm font-semibold text-gray-700">Chọn địa chỉ đã lưu</div>
                            <p class="text-xs text-gray-500 mt-1">Tự động chọn tỉnh/thành dựa trên địa chỉ của bạn.</p>
                        </div>
                        <button type="button" onclick="openSavedAddressModal()" class="shrink-0 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition w-full sm:w-auto">
                            <i class="fa-solid fa-map-marker-alt"></i> Chọn địa chỉ
                        </button>
                    </div>
                @endif

                <!-- Dropdown chọn Tỉnh/Thành phố -->
                <div class="mb-6">
                    <label for="province" class="block text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Địa điểm giao hàng:</label>
                    <div class="relative">
                        <!-- Khi người dùng chọn tỉnh thành, sự kiện onchange kích hoạt hàm calculateShipping() -->
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

                <!-- Tạm tính đơn hàng (Lấy từ URL hoặc giỏ hàng thực tế) -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Tạm tính đơn hàng:</label>
                    <div class="flex items-center bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <span class="text-2xl font-bold text-gray-800" id="orderTotalText">0đ</span>
                        <input type="hidden" id="orderTotal" value="0">
                    </div>
                    <!-- Hiển thị chính sách freeship cho tỉnh đã chọn -->
                    <div id="shipping-policy-info" class="mt-3 p-3 bg-blue-50 rounded-lg flex items-center gap-3 border border-blue-100 transition-all">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        <p class="text-sm text-blue-700 font-medium" id="policy-text">Vui lòng chọn địa điểm để xem chính sách miễn phí vận chuyển</p>
                    </div>
                </div>

                <!-- KHUNG KẾT QUẢ TÍNH PHÍ VẬN CHUYỂN (Chỉ hiển thị khi đã chọn tỉnh) -->
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

                <!-- Khung giữ chỗ khi người dùng chưa chọn tỉnh -->
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

        <!-- CỘT BÊN PHẢI: HIỂN THỊ DANH SÁCH SẢN PHẨM SẼ VẬN CHUYỂN -->
        <div class="w-full md:w-80 space-y-4">
            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-100">
                <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2 border-b pb-2">
                    <i class="fa-solid fa-list-check text-blue-600"></i> Sản phẩm vận chuyển
                </h3>
                <div id="mini-cart-items" class="space-y-3 max-h-96 overflow-y-auto pr-1 custom-scrollbar">
                    <!-- Javascript render các item tại đây -->
                </div>
            </div>
            
            <!-- Banner quảng cáo dịch vụ giao hỏa tốc 2H -->
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

@if(Auth::check() && isset($addresses) && $addresses->isNotEmpty())
    <div id="saved-address-modal" class="fixed inset-0 z-[99999] hidden flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden border border-gray-100" style="max-height:90vh; overflow:auto;">
        <div class="flex items-start justify-between gap-4 p-6 border-b border-gray-200">
        <div>
            <h3 class="text-base font-bold text-gray-900">Chọn địa chỉ nhận hàng</h3>
            <p class="text-sm text-gray-500 mt-1">Nhấn vào địa chỉ để tự động chọn tỉnh/thành phố tương ứng.</p>
        </div>
        <button type="button" onclick="closeSavedAddressModal()" class="text-gray-400 hover:text-gray-700">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
        </div>
        <div class="max-h-[420px] overflow-y-auto p-6 space-y-3">
        @foreach($addresses as $address)
            @php
            $savedAddressLabel = trim($address->name ?: ($address->type ?: 'Địa chỉ'));
            $savedAddressFull = trim(implode(', ', array_filter([$address->street, $address->ward, $address->district, $address->city])));
            @endphp
            <button type="button" onclick="selectSavedAddress(this)"
            class="saved-address-card w-full text-left p-4 border rounded-3xl transition shadow-sm hover:border-blue-500 flex items-start justify-between gap-3 border-gray-200 bg-white"
            data-city="{{ e($address->city) }}">
            <div class="min-w-0">
                <div class="font-semibold text-sm text-gray-900">{{ $savedAddressLabel }}</div>
                <div class="text-xs text-gray-500 mt-1 break-words">{{ $savedAddressFull }}</div>
            </div>
            <div class="text-right">
                @if($address->is_default)
                <span class="inline-flex px-3 py-1 text-[11px] font-semibold uppercase tracking-wider bg-blue-100 text-blue-700 rounded-full">Mặc định</span>
                @endif
            </div>
            </button>
        @endforeach
        </div>
        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200">
        <a href="{{ route('profile.index') }}" class="text-sm text-blue-600 hover:underline">Thêm / sửa địa chỉ</a>
        <button type="button" onclick="closeSavedAddressModal()" class="px-5 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition">Đóng</button>
        </div>
    </div>
    </div>
@endif


@push('scripts')
    <script>
        // Các hàm hỗ trợ cho Modal chọn địa chỉ
        function openSavedAddressModal() {
            const modal = document.getElementById('saved-address-modal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeSavedAddressModal() {
            const modal = document.getElementById('saved-address-modal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        function findProvinceCodeFromCity(city) {
            if (!city) return '';
            const text = city.toLowerCase();
            if (text.includes('hồ chí minh') || text.includes('hcm')) return 'hcm';
            if (text.includes('hà nội') || text.includes('hn')) return 'hn';
            if (text.includes('bình dương')) return 'bd';
            if (text.includes('đồng nai')) return 'dnai';
            if (text.includes('long an')) return 'la';
            if (text.includes('tiền giang')) return 'tg';
            if (text.includes('vũng tàu') || text.includes('bà rịa')) return 'vt';
            if (text.includes('bắc ninh')) return 'bn';
            if (text.includes('hưng yên')) return 'hy';
            if (text.includes('hà nam')) return 'hnam';
            if (text.includes('vĩnh phúc')) return 'vp';
            if (text.includes('hải phòng')) return 'hp';
            if (text.includes('cần thơ')) return 'ct';
            if (text.includes('hòa bình')) return 'hb';
            if (text.includes('nam định') || text.includes('ninh bình')) return 'nb';
            if (text.includes('an giang')) return 'ag';
            if (text.includes('kiên giang')) return 'kg';
            if (text.includes('đồng tháp')) return 'dt';
            if (text.includes('trà vinh') || text.includes('vĩnh long')) return 'tv';
            if (text.includes('bến tre') || text.includes('sóc trăng')) return 'bte';
            if (text.includes('đà nẵng')) return 'dn';
            if (text.includes('quảng nam') || text.includes('quảng ngãi')) return 'qng';
            if (text.includes('bình định') || text.includes('phú yên')) return 'bdinh';
            if (text.includes('khánh hòa') || text.includes('nha trang')) return 'nth';
            if (text.includes('thanh hóa') || text.includes('nghệ an')) return 'th';
            if (text.includes('quảng bình') || text.includes('quảng trị')) return 'qbi';
            if (text.includes('thừa thiên') || text.includes('huế')) return 'hue';
            if (text.includes('gia lai') || text.includes('kon tum')) return 'gl';
            if (text.includes('đắk lắk') || text.includes('đắk nông')) return 'dkl';
            if (text.includes('lào cai') || text.includes('yên bái')) return 'lc';
            if (text.includes('điện biên') || text.includes('lai châu')) return 'dbi';
            if (text.includes('sơn la')) return 'ss';
            if (text.includes('cao bằng') || text.includes('bắc kạn')) return 'cb';
            if (text.includes('lạng sơn') || text.includes('hà giang')) return 'ls';
            if (text.includes('cà mau') || text.includes('bạc liêu')) return 'cm';
            return 'other';
        }

        function selectSavedAddress(button) {
            const city = button.dataset.city || '';
            const provinceCode = findProvinceCodeFromCity(city);
            const provinceSelect = document.getElementById('province');
            if (provinceSelect && provinceCode) {
                // Đảm bảo option tồn tại trước khi set value
                if (Array.from(provinceSelect.options).some(opt => opt.value === provinceCode)) {
                    provinceSelect.value = provinceCode;
                    calculateShipping();
                } else {
                    provinceSelect.value = 'other';
                    calculateShipping();
                }
            }
            closeSavedAddressModal();
        }

        // Mảng toàn cục lưu danh sách sản phẩm đồng bộ từ backend
        window.cartData = [];

        /**
         * 1. KHỞI TẠO DỮ LIỆU ĐƠN HÀNG TỪ GIỎ HÀNG HOẶC URL PARAM
         * Lấy dữ liệu $cartItems đã mã hóa JSON từ blade.
         * Nếu giỏ hàng có dữ liệu: Tính tổng tiền tạm tính và render mini items.
         * Nếu giỏ hàng trống nhưng có URL param 'total' (truyền từ trang cart): Dùng giá trị đó để tính phí ship.
         */
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
                    // Nếu không có sản phẩm chi tiết ở client nhưng có giá trị truyền qua URL
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

        /**
         * 2. TÍNH TỔNG TIỀN VÀ RENDER DANH SÁCH CHI TIẾT SẢN PHẨM RENDER
         * Cộng dồn tổng tiền các sản phẩm được tick (`selected === true`).
         */
        function calculateSubtotalFromCart() {
            let total = 0;
            const container = document.getElementById('mini-cart-items');
            if (container) container.innerHTML = '';

            window.cartData.forEach(item => {
                if (item.selected) {
                    total += (item.price * item.quantity);
                    
                    // Render dòng sản phẩm mini ở cột phải
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

        /**
         * Cập nhật số tiền tạm tính lên input ẩn và text giao diện.
         * Tính lại phí vận chuyển nếu địa điểm đã được chọn trước đó.
         */
        function updateUIWithTotal(total) {
            document.getElementById('orderTotal').value = total;
            document.getElementById('orderTotalText').innerText = formatMoney(total);
            if (document.getElementById('province').value) {
                calculateShipping();
            }
        }

        // Định dạng tiền tệ VND
        const formatMoney = (amount) => {
            return new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
        };

        /**
         * 3. LOGIC TÍNH PHÍ VẬN CHUYỂN (SHIPPING COST CALCULATE LOGIC)
         * Đọc phí gốc (`data-fee`) và ngưỡng miễn phí ship (`data-threshold`) từ option được chọn.
         * Nếu tạm tính đơn hàng >= ngưỡng miễn phí ship, phí giao hàng = 0đ (Freeship).
         * Hiển thị bảng tổng cộng cuối cùng kèm hiệu ứng flash zoom nhẹ.
         */
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
            
            // Hiện text chính sách freeship của tỉnh thành
            policyText.innerHTML = `Miễn phí vận chuyển cho đơn hàng từ <span class="font-bold">${formatMoney(threshold)}</span>`;
            
            // Ước tính thời gian giao hàng dự kiến dựa trên vùng miền địa lý
            if (['hcm', 'hn'].includes(provinceValue)) {
                deliveryTime.innerText = "Dự kiến giao trong 24h - 48h";
            } else {
                deliveryTime.innerText = "Dự kiến giao trong 3 - 5 ngày";
            }
            
            // Kiểm tra điều kiện miễn phí vận chuyển
            if (orderTotal >= threshold) {
                baseFee = 0;
            }

            const finalTotal = orderTotal + baseFee;

            // Ẩn placeholder, hiện bảng kết quả
            placeholderBox.classList.add('hidden');
            resultBox.classList.remove('hidden');

            document.getElementById('shipping-fee-text').innerText = baseFee === 0 ? 'MIỄN PHÍ' : formatMoney(baseFee);
            document.getElementById('final-total-text').innerText = formatMoney(finalTotal);

            // Thêm hiệu ứng flash nhẹ để báo người dùng biết dữ liệu đã được tính toán lại
            resultBox.classList.add('animate-pop');
            setTimeout(() => resultBox.classList.remove('animate-pop'), 400);
        }

        // Gọi hàm khởi tạo dữ liệu sau khi DOM hoàn tất load
        document.addEventListener('DOMContentLoaded', () => {
            initializeData();
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Hiệu ứng zoom nhẹ phản hồi tương tác (Flash Animation) */
        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        .animate-pop {
            animation: pop 0.4s ease-out;
        }

        /* Định dạng thanh cuộn (Scrollbar) tùy chỉnh cho bảng danh sách sản phẩm ở cột phải */
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
