@extends('layouts.app')

@section('title', 'Giỏ hàng của bạn - DIENMAYPRO')

@push('styles')
    <style>
        /* Ẩn mũi tên tăng giảm mặc định của input number */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
        
        /* Ẩn thanh cuộn của danh mục */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endpush

@section('content')

{{-- Sử dụng file header đã tách riêng --}}
@include('layouts.header')

<main class="flex-grow bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 pb-20 pt-8">
        <!-- Breadcrumb đơn giản -->
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ url('/') }}" class="hover:text-[#0047b3]">Trang chủ</a> 
            <span class="mx-2">/</span> 
            <span class="text-gray-800 font-medium">Giỏ hàng</span>
        </nav>

        <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
            <i class="fa-solid fa-cart-shopping text-[#0047b3]"></i> Giỏ hàng của bạn
        </h1>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Cột trái: Danh sách sản phẩm -->
            <div class="w-full lg:w-2/3 flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="selectAllCheckbox" class="w-5 h-5 text-blue-600 rounded cursor-pointer border-gray-300 focus:ring-blue-500" checked onchange="window.toggleAll(this.checked)">
                        <label for="selectAllCheckbox" class="cursor-pointer select-none font-medium">
                            Chọn tất cả (<span id="total-items-count-text">0</span> sản phẩm)
                        </label>
                    </div>
                    <button onclick="window.clearCart()" class="text-sm text-red-500 hover:underline">Xóa tất cả</button>
                </div>

                <div id="cart-items-container" class="flex flex-col gap-4">
                    <!-- Sản phẩm sẽ được render bằng JS ở đây -->
                </div>
            </div>

            <!-- Cột phải: Tóm tắt đơn hàng (Sticky) -->
            <div class="w-full lg:w-1/3">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 sticky top-4">
                    <h2 class="text-lg font-bold mb-4 border-b pb-2">Tóm tắt đơn hàng</h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center text-gray-600">
                            <span>Tạm tính:</span>
                            <span id="summary-subtotal">0đ</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600 border-b pb-3">
                            <span>Số lượng chọn (<span id="summary-count">0</span>):</span>
                            <span>Sản phẩm</span>
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-lg font-bold">Tổng cộng:</span>
                            <span class="text-2xl font-bold text-red-600" id="summary-total">0đ</span>
                        </div>
                    </div>

                    <!-- Nút thanh toán -->
                    <button id="checkout-btn" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 rounded-lg transition-all shadow-lg hover:shadow-xl disabled:bg-gray-400 disabled:shadow-none mb-3" onclick="window.proceedToCheckout()">
                        TIẾN HÀNH THANH TOÁN
                    </button>
                    
                    <!-- Link tính phí vận chuyển -->
                    <a id="shipping-link" href="{{ Route::has('shipping.calc') ? route('shipping.calc') : '#' }}" class="block w-full text-center border border-[#0047b3] text-[#0047b3] font-semibold py-2 rounded-lg hover:bg-blue-50 transition-colors">
                        <i class="fa-solid fa-truck-fast mr-1"></i> Kiểm tra phí giao hàng
                    </a>

                    <div class="mt-4 text-center">
                        <a href="{{ url('/') }}" class="text-sm text-[#0047b3] hover:underline">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Sử dụng file footer đã tách riêng --}}
@include('layouts.footer')

<!-- Toast Thông báo -->
<div id="toast" class="fixed bottom-5 right-5 bg-gray-800 text-white px-6 py-3 rounded-xl shadow-2xl transform transition-all duration-300 translate-y-20 opacity-0 flex items-center gap-3 z-50">
    <div id="toast-icon" class="text-green-400">
        <i class="fa-solid fa-circle-check text-xl"></i>
    </div>
    <span id="toast-message" class="font-medium">Đã cập nhật giỏ hàng.</span>
</div>

@endsection

@push('scripts')
<script>
    // Khởi tạo dữ liệu
    window.cartData = [];

    function initializeData() {
        try {
            // Lấy dữ liệu từ Laravel gửi qua
            const raw = '{!! isset($cartItems) ? json_encode($cartItems) : "[]" !!}';
            window.cartData = JSON.parse(raw);
        } catch (e) {
            console.warn("Dữ liệu mẫu cho môi trường preview.");
            window.cartData = [
                { id: 101, name: "Tủ lạnh Samsung Inverter 300L", price: 8500000, quantity: 1, stock: 5, selected: true, image: "https://placehold.co/100x100?text=Samsung", url: "#" },
                { id: 102, name: "Máy giặt LG cửa ngang 9kg", price: 10200000, quantity: 1, stock: 3, selected: true, image: "https://placehold.co/100x100?text=LG", url: "#" }
            ];
        }
    }

    const formatMoney = (amount) => {
        return new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
    };

    window.renderCart = () => {
        const container = document.getElementById('cart-items-container');
        if (!container) return;
        
        container.innerHTML = ''; 

        if (window.cartData.length === 0) {
            container.innerHTML = `
                <div class="bg-white p-12 text-center rounded-lg shadow-sm border border-dashed border-gray-300">
                    <img src="https://placehold.co/200x150?text=Empty+Cart" class="mx-auto mb-4 opacity-50" alt="Empty">
                    <p class="text-gray-500 text-lg">Giỏ hàng của bạn còn trống</p>
                    <a href="{{ url('/') }}" class="mt-4 inline-block bg-[#0047b3] text-white px-6 py-2 rounded-full font-medium">Mua sắm ngay</a>
                </div>`;
            if (document.getElementById('selectAllCheckbox')) {
                document.getElementById('selectAllCheckbox').disabled = true;
                document.getElementById('selectAllCheckbox').checked = false;
            }
            window.updateSummary();
            return;
        }

        window.cartData.forEach(item => {
            const itemHTML = `
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-start gap-4 relative transition-all hover:border-blue-200" id="item-${item.id}">
                    <div class="pt-4">
                        <input type="checkbox" class="w-5 h-5 text-blue-600 rounded cursor-pointer border-gray-300" 
                            ${item.selected ? 'checked' : ''} 
                            onchange="window.toggleItem(${item.id}, this.checked)">
                    </div>
                    <div class="w-24 h-24 flex-shrink-0 bg-gray-50 rounded p-2">
                        <img src="${item.image}" class="w-full h-full object-contain" alt="${item.name}">
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-800 line-clamp-2 leading-tight pr-6">${item.name}</h3>
                        <p class="text-red-600 font-bold text-lg mt-1">${formatMoney(item.price)}</p>
                        
                        <div class="flex items-center mt-3 border w-max rounded-lg bg-gray-50">
                            <button class="w-8 h-8 flex items-center justify-center hover:bg-gray-200 transition-colors rounded-l-lg" 
                                    onclick="window.changeQuantity(${item.id}, -1)">
                                <i class="fa-solid fa-minus text-xs"></i>
                            </button>
                            <span class="w-10 text-center font-bold text-sm">${item.quantity}</span>
                            <button class="w-8 h-8 flex items-center justify-center hover:bg-gray-200 transition-colors rounded-r-lg" 
                                    onclick="window.changeQuantity(${item.id}, 1)">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <button class="absolute top-4 right-4 text-gray-300 hover:text-red-500 transition-colors p-1" 
                            onclick="window.deleteItem(${item.id})" title="Xóa">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>`;
            container.insertAdjacentHTML('beforeend', itemHTML);
        });

        window.updateSummary();
    };

    window.updateSummary = () => {
        let total = 0;
        let count = 0;
        window.cartData.forEach(item => {
            if (item.selected) {
                total += (item.price * item.quantity);
                count++;
            }
        });

        const elSub = document.getElementById('summary-subtotal');
        const elTotal = document.getElementById('summary-total');
        const elCount = document.getElementById('summary-count');
        const elTotalList = document.getElementById('total-items-count-text');

        if (elSub) elSub.innerText = formatMoney(total);
        if (elTotal) elTotal.innerText = formatMoney(total);
        if (elCount) elCount.innerText = count;
        if (elTotalList) elTotalList.innerText = window.cartData.length;
        
        const btn = document.getElementById('checkout-btn');
        if (btn) btn.disabled = count === 0;

        const checkAll = document.getElementById('selectAllCheckbox');
        if (checkAll && window.cartData.length > 0) {
            checkAll.checked = window.cartData.every(i => i.selected);
        }
    };

    window.toggleAll = (isChecked) => {
        window.cartData.forEach(i => i.selected = isChecked);
        window.renderCart();
    };

    window.toggleItem = (id, isChecked) => {
        const item = window.cartData.find(i => i.id === id);
        if(item) item.selected = isChecked;
        window.updateSummary();
    };

    window.changeQuantity = (id, delta) => {
        const item = window.cartData.find(i => i.id === id);
        if(item) {
            const newQty = item.quantity + delta;
            if(newQty >= 1 && newQty <= (item.stock || 99)) {
                item.quantity = newQty;
                window.renderCart();
                showToast("Đã cập nhật số lượng");
            }
        }
    };

    window.deleteItem = (id) => {
        if(confirm('Bạn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            window.cartData = window.cartData.filter(i => i.id !== id);
            window.renderCart();
            showToast("Đã xóa sản phẩm", "info");
        }
    };

    window.clearCart = () => {
        if(confirm('Bạn muốn làm trống giỏ hàng?')) {
            window.cartData = [];
            window.renderCart();
            showToast("Đã làm trống giỏ hàng");
        }
    };

    function showToast(msg, type = "success") {
        const t = document.getElementById('toast');
        const m = document.getElementById('toast-message');
        const icon = document.getElementById('toast-icon');
        
        if (!t || !m) return;
        
        m.innerText = msg;
        icon.innerHTML = type === "success" 
            ? '<i class="fa-solid fa-circle-check text-xl text-green-400"></i>'
            : '<i class="fa-solid fa-circle-info text-xl text-blue-400"></i>';

        t.classList.remove('translate-y-20', 'opacity-0');
        t.classList.add('translate-y-0', 'opacity-100');
        
        setTimeout(() => {
            t.classList.add('translate-y-20', 'opacity-0');
            t.classList.remove('translate-y-0', 'opacity-100');
        }, 3000);
    }

    window.proceedToCheckout = () => {
        const selectedIds = window.cartData.filter(i => i.selected).map(i => i.id);
        if (selectedIds.length > 0) {
            // Chuyển hướng đến trang thanh toán thật của Laravel
            window.location.href = `{{ url('/checkout') }}?items=${selectedIds.join(',')}`;
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        initializeData();
        window.renderCart();
    });
</script>
@endpush