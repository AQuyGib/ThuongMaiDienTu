@extends('layouts.app')

@section('title', 'Giỏ hàng của bạn - DIENMAYPRO')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <a id="shipping-link" href="{{ Route::has('cart.shipping') ? route('cart.shipping') : (Route::has('shipping.calc') ? route('shipping.calc') : '#') }}" class="block w-full text-center border border-[#0047b3] text-[#0047b3] font-semibold py-2 rounded-lg hover:bg-blue-50 transition-colors">
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

        <!-- Section: Gợi ý sản phẩm tương tự -->
        @if(isset($recommendedProducts) && $recommendedProducts->isNotEmpty())
            <div id="similar-products-section" class="mt-16">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-2 text-gray-800">
                    <i class="fa-solid fa-fire text-amber-500 animate-pulse"></i> Có thể bạn quan tâm
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($recommendedProducts as $product)
                        @php
                            $imageUrl = $product->thumbnail;
                            if (!$imageUrl || !Str::startsWith($imageUrl, 'http')) {
                                $imageUrl = asset('uploads/products/' . ($product->image ?: 'default.jpg'));
                            }
                        @endphp
                        <div class="product-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                            <!-- Image Container -->
                            <div class="relative h-44 overflow-hidden bg-gray-50 p-4 flex items-center justify-center">
                                @if($product->discount_percent)
                                    <span class="absolute left-3 top-3 z-10 inline-flex items-center gap-1.5 rounded-full bg-red-600 px-2.5 py-1 text-[10px] font-bold text-white shadow-sm">
                                        -{{ $product->discount_percent }}%
                                    </span>
                                @endif
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                                    class="max-w-full max-h-full object-contain group-hover:scale-105 transition-transform duration-500"
                                    onerror="this.src='https://loremflickr.com/400/400/technology?lock={{ $product->product_id }}'; this.onerror=null;">
                            </div>
                            
                            <!-- Product Info -->
                            <div class="p-4">
                                <!-- Category -->
                                <div class="text-xs text-gray-400 mb-1">
                                    {{ $product->category->name ?? 'Điện máy' }}
                                </div>
                                
                                <!-- Product Name -->
                                <h3 class="text-sm font-bold text-gray-800 mb-2 line-clamp-2 min-h-[40px]" title="{{ $product->name }}">
                                    <a href="{{ route('product.show', $product->product_id) }}" class="hover:text-[#0047b3] transition-colors">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                
                                <!-- Price -->
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="text-base font-bold text-red-600">
                                        {{ number_format($product->base_price, 0, ',', '.') }} ₫
                                    </span>
                                    @if($product->old_price && $product->old_price > $product->base_price)
                                        <span class="text-xs text-gray-400 line-through">
                                            {{ number_format($product->old_price, 0, ',', '.') }} ₫
                                        </span>
                                    @endif
                                </div>
                                
                                <!-- Buttons -->
                                <div class="flex gap-2">
                                    <a href="{{ route('product.show', $product->product_id) }}"
                                        class="flex-1 text-center bg-[#0047b3] text-white py-2 rounded-lg text-xs font-bold hover:bg-blue-700 transition-all shadow-sm hover:shadow-md">
                                        Xem chi tiết
                                    </a>
                                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                                        <button type="submit"
                                            class="w-full bg-gray-100 text-gray-800 py-2 rounded-lg text-xs font-bold hover:bg-gray-200 transition-all">
                                            Thêm vào giỏ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</main>


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
            
            // Nếu Controller không truyền data thì raw là "[]", JSON.parse vẫn trả về mảng rỗng.
        } catch (e) {
            console.warn("Lỗi parse dữ liệu giỏ hàng:", e);
            window.cartData = [];
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
            const similarSection = document.getElementById('similar-products-section');
            if (similarSection) {
                similarSection.style.display = 'none';
            }
            window.updateSummary();
            return;
        }

        const similarSection = document.getElementById('similar-products-section');
        if (similarSection) {
            similarSection.style.display = 'block';
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
        
        // Cập nhật link tính phí vận chuyển với tổng tiền (nếu cần)
        const shippingLink = document.getElementById('shipping-link');
        if (shippingLink && shippingLink.href !== '#') {
            const url = new URL(shippingLink.href, window.location.origin);
            url.searchParams.set('total', total);
            shippingLink.href = url.toString();
        }
    };

    window.toggleAll = (isChecked) => {
        fetch('{{ route("cart.toggleAll") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ selected: isChecked })
        })
        .then(response => response.json())
        .then(res => {
            if(res.status === 'success') {
                window.cartData.forEach(i => i.selected = isChecked);
                window.renderCart();
            }
        })
        .catch(err => console.error(err));
    };

    window.toggleItem = (id, isChecked) => {
        fetch('{{ route("cart.toggleSelect") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: id, selected: isChecked })
        })
        .then(response => response.json())
        .then(res => {
            if(res.status === 'success') {
                const item = window.cartData.find(i => i.id === id);
                if(item) item.selected = isChecked;
                window.updateSummary();
            }
        })
        .catch(err => console.error(err));
    };

    window.changeQuantity = (id, delta) => {
        const item = window.cartData.find(i => i.id === id);
        if(item) {
            const newQty = item.quantity + delta;
            if(newQty < 1) return;

            fetch('{{ route("cart.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ product_id: id, quantity: newQty })
            })
            .then(response => response.json())
            .then(res => {
                if(res.status === 'success') {
                    item.quantity = newQty;
                    window.renderCart();
                    showToast("Đã cập nhật số lượng");
                    
                    const badge = document.getElementById('headerCartBadge');
                    if (badge && res.cart_count !== undefined) {
                        badge.innerText = res.cart_count;
                        badge.style.display = res.cart_count > 0 ? 'block' : 'none';
                    }
                } else if(res.message) {
                    showToast(res.message, 'warning');
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Đã xảy ra lỗi!", 'error');
            });
        }
    };

    window.deleteItem = async (id) => {
        const result = await Swal.fire({
            title: 'Xóa sản phẩm?',
            text: 'Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy',
            reverseButtons: true
        });

        if (result.isConfirmed) {
            fetch('{{ route("cart.remove") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ product_id: id })
            })
            .then(response => response.json())
            .then(res => {
                if(res.status === 'success') {
                    window.cartData = window.cartData.filter(i => i.id !== id);
                    window.renderCart();
                    showToast("Đã xóa sản phẩm", "info");
                    
                    // Cập nhật số badge trên Header nếu có
                    const badge = document.getElementById('headerCartBadge');
                    if (badge && res.cart_count !== undefined) {
                        badge.innerText = res.cart_count;
                        badge.style.display = res.cart_count > 0 ? 'block' : 'none';
                    }
                }
            })
            .catch(err => console.error(err));
        }
    };

    window.clearCart = async () => {
        const result = await Swal.fire({
            title: 'Làm trống giỏ hàng?',
            text: 'Bạn có chắc chắn muốn xóa toàn bộ sản phẩm khỏi giỏ hàng?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Làm trống',
            cancelButtonText: 'Hủy',
            reverseButtons: true
        });

        if (result.isConfirmed) {
            fetch('{{ route("cart.clear") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(res => {
                if(res.status === 'success') {
                    window.cartData = [];
                    window.renderCart();
                    showToast("Đã làm trống giỏ hàng");
                    
                    const badge = document.getElementById('headerCartBadge');
                    if (badge) {
                        badge.style.display = 'none';
                        badge.innerText = '0';
                    }
                }
            })
            .catch(err => console.error(err));
        }
    };

    function showToast(msg, type = "success") {
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        Toast.fire({
            icon: type,
            title: msg
        });
    }

    window.proceedToCheckout = () => {
        const selectedItems = window.cartData.filter(i => i.selected);
        if (selectedItems.length > 0) {
            window.location.href = `{{ url('/pay') }}`;
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        initializeData();
        window.renderCart();
    });
</script>
@endpush
