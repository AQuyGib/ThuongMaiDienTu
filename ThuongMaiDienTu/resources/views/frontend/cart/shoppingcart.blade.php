<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng của bạn - DIENMAYPRO</title>
    <!-- Sử dụng Tailwind CSS cho giao diện -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome cho icon thùng rác -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
    <!-- Mock Header -->
    <header class="bg-[#0f4a9b] text-white p-4 flex items-center justify-between shadow-md">
        <div class="max-w-7xl mx-auto w-full flex items-center justify-between px-4">
            <div class="font-bold text-2xl flex items-center gap-2 text-yellow-400">
                <i class="fa-solid fa-bolt"></i> DIENMAYPRO
            </div>
            <div class="flex-1 max-w-2xl mx-8">
                <div class="bg-white rounded flex">
                    <input type="text" placeholder="Hôm nay bạn cần tìm gì?" class="w-full p-2 rounded-l text-black outline-none">
                    <button class="bg-yellow-400 text-black px-4 rounded-r"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
            <div class="flex gap-6 items-center text-sm">
                <div class="text-center"><i class="fa-solid fa-truck"></i><br>Tra cứu đơn</div>
                <div class="text-center relative text-yellow-400">
                    <i class="fa-solid fa-cart-shopping text-xl"></i>
                    <span class="absolute -top-2 -right-2 bg-yellow-400 text-black rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold" id="header-cart-count">2</span>
                    <br>Giỏ hàng
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto mt-8 px-4 pb-20">
        <!-- Tiêu đề in đậm, kích thước lớn -->
        <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
            <i class="fa-solid fa-cart-shopping text-blue-600"></i> Giỏ hàng của bạn
        </h1>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Cột trái: Danh sách sản phẩm -->
            <div class="w-full lg:w-2/3 flex flex-col gap-4">
                
                <!-- Box Chọn tất cả -->
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-center gap-3">
                    <input type="checkbox" id="selectAllCheckbox" class="w-5 h-5 text-blue-600 rounded cursor-pointer" checked onchange="toggleAll(this.checked)">
                    <label for="selectAllCheckbox" class="cursor-pointer select-none">
                        Chọn tất cả (<span id="total-items-count-text">2</span> sản phẩm)
                    </label>
                </div>

                <!-- Danh sách items -->
                <div id="cart-items-container" class="flex flex-col gap-4">
                    <!-- Sản phẩm sẽ được render bằng JS ở đây -->
                </div>
            </div>

            <!-- Cột phải: Tóm tắt đơn hàng (Sticky) -->
            <div class="w-full lg:w-1/3">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 sticky top-4">
                    <h2 class="text-lg font-bold mb-4">Tóm tắt đơn hàng</h2>
                    
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-gray-600">Tổng cộng (<span id="summary-count">2</span>):</span>
                        <span class="text-2xl font-bold text-red-600" id="summary-total">38.970.000đ</span>
                    </div>

                    <button id="checkout-btn" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed mb-3" onclick="proceedToCheckout()">
                        TIẾN HÀNH ĐẶT HÀNG
                    </button>
                    
                    <a id="shipping-link" href="{{ route('cart.shipping') }}" class="block w-full text-center border border-blue-600 text-blue-600 font-semibold py-2 rounded-lg hover:bg-blue-50 transition-colors">
                        <i class="fa-solid fa-calculator mr-1"></i> Tính phí vận chuyển
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-5 right-5 bg-green-500 text-white px-6 py-3 rounded shadow-lg transform transition-transform duration-300 translate-y-20 opacity-0 flex items-center gap-2 z-50">
        <i class="fa-solid fa-circle-check"></i>
        <span id="toast-message">Đã xóa sản phẩm khỏi giỏ.</span>
    </div>

    <!-- Script xử lý Logic -->
    <script>
        // 1. Dữ liệu lấy từ Database (Thông qua Controller truyền biến $cartItems)
        let cartData = @json($cartItems);

        // Format tiền tệ VNĐ
        const formatMoney = (amount) => {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        };

        // Render danh sách sản phẩm
        const renderCart = () => {
            const container = document.getElementById('cart-items-container');
            container.innerHTML = ''; // Xóa cũ

            if (cartData.length === 0) {
                container.innerHTML = '<div class="bg-white p-8 text-center text-gray-500 rounded-lg shadow-sm border border-gray-100">Giỏ hàng của bạn đang trống.</div>';
                document.getElementById('selectAllCheckbox').disabled = true;
                updateSummary();
                return;
            }

            document.getElementById('selectAllCheckbox').disabled = false;

            cartData.forEach(item => {
                // Kiểm tra trạng thái disable của nút + và -
                const isMinusDisabled = item.quantity <= 1;
                const isPlusDisabled = item.quantity >= item.stock;

                const itemHTML = `
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-center gap-4 relative transition-all" id="item-${item.id}">
                        <!-- Checkbox -->
                        <input type="checkbox" class="w-5 h-5 text-blue-600 rounded cursor-pointer item-checkbox" 
                            ${item.selected ? 'checked' : ''} 
                            onchange="toggleItem(${item.id}, this.checked)">
                        
                        <!-- Ảnh (Hyperlink) -->
                        <a href="${item.url}" class="w-24 h-24 flex-shrink-0 border rounded p-1">
                            <img src="${item.image}" alt="${item.name}" class="w-full h-full object-contain">
                        </a>

                        <!-- Thông tin -->
                        <div class="flex-1">
                            <!-- Tên (Hyperlink) -->
                            <a href="${item.url}" class="text-gray-800 font-medium hover:text-blue-600 line-clamp-2 pr-8">${item.name}</a>
                            
                            <!-- Giá -->
                            <div class="text-red-600 font-bold mt-1 text-lg">${formatMoney(item.price)}</div>
                            
                            <!-- Control số lượng -->
                            <div class="flex items-center mt-3 border rounded w-max border-gray-300">
                                <button class="px-3 py-1 bg-gray-50 hover:bg-gray-200 text-gray-600 transition-colors ${isMinusDisabled ? 'opacity-50 cursor-not-allowed' : ''}" 
                                    onclick="changeQuantity(${item.id}, -1)" ${isMinusDisabled ? 'disabled' : ''}>
                                    <i class="fa-solid fa-minus text-xs"></i>
                                </button>
                                
                                <input type="number" 
                                    class="w-12 text-center py-1 border-x border-gray-300 outline-none text-sm font-medium" 
                                    value="${item.quantity}" 
                                    onchange="handleDirectInput(${item.id}, this.value)"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"> <!-- Chỉ cho nhập số -->
                                
                                <button class="px-3 py-1 bg-gray-50 hover:bg-gray-200 text-gray-600 transition-colors ${isPlusDisabled ? 'opacity-50 cursor-not-allowed' : ''}" 
                                    onclick="changeQuantity(${item.id}, 1)" ${isPlusDisabled ? 'disabled' : ''}>
                                    <i class="fa-solid fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Nút xóa -->
                        <button class="absolute top-4 right-4 text-gray-400 hover:text-red-500 p-2" onclick="deleteItem(${item.id})">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                `;
                container.innerHTML += itemHTML;
            });

            updateSummary();
        };

        // Cập nhật Tổng tiền, Số lượng và Trạng thái Checkbox
        const updateSummary = () => {
            let totalMoney = 0;
            let selectedCount = 0;
            let allChecked = true;

            cartData.forEach(item => {
                if (item.selected) {
                    totalMoney += (item.price * item.quantity);
                    selectedCount += 1;
                } else {
                    allChecked = false;
                }
            });

            // Nếu giỏ hàng trống thì không thể "Chọn tất cả"
            if (cartData.length === 0) allChecked = false;

            // Cập nhật DOM
            document.getElementById('summary-total').innerText = formatMoney(totalMoney);
            document.getElementById('summary-count').innerText = selectedCount;
            document.getElementById('total-items-count-text').innerText = cartData.length;
            document.getElementById('header-cart-count').innerText = cartData.length;
            
            // Xử lý Checkbox "Chọn tất cả"
            document.getElementById('selectAllCheckbox').checked = allChecked;

            // Xử lý nút Đặt hàng
            const checkoutBtn = document.getElementById('checkout-btn');
            if (selectedCount === 0) {
                checkoutBtn.disabled = true;
            } else {
                checkoutBtn.disabled = false;
            }
            
            // Cập nhật link tính phí vận chuyển với tổng tiền
            const shippingLink = document.getElementById('shipping-link');
            shippingLink.href = "{{ route('cart.shipping') }}?total=" + totalMoney;
        };

        // Logic check/uncheck tất cả
        const toggleAll = (isChecked) => {
            cartData = cartData.map(item => ({...item, selected: isChecked}));
            renderCart();
        };

        // Logic check/uncheck 1 item
        const toggleItem = (id, isChecked) => {
            const item = cartData.find(i => i.id === id);
            if(item) {
                item.selected = isChecked;
            }
            // Không cần render lại toàn bộ list, chỉ cần tính lại tổng
            updateSummary(); 
            
            // Sync trạng thái của checkbox Select All
            const allChecked = cartData.every(i => i.selected);
            document.getElementById('selectAllCheckbox').checked = allChecked;
        };

        // Logic tăng giảm số lượng (+ / -)
        const changeQuantity = (id, delta) => {
            const item = cartData.find(i => i.id === id);
            if(item) {
                let newQty = item.quantity + delta;
                if (newQty >= 1 && newQty <= item.stock) {
                    item.quantity = newQty;
                    // Trong Laravel thực tế, chỗ này sẽ gọi Axios/Fetch tới API để lưu DB:
                    // axios.post('/cart/update', {id: item.id, qty: item.quantity})
                    renderCart();
                }
            }
        };

        // Logic khi người dùng gõ số trực tiếp vào input
        const handleDirectInput = (id, value) => {
            const item = cartData.find(i => i.id === id);
            if(item) {
                let parsedValue = parseInt(value);
                
                // Validate
                if (isNaN(parsedValue) || parsedValue < 1) {
                    parsedValue = 1;
                } else if (parsedValue > item.stock) {
                    parsedValue = item.stock;
                    showToast(`Chỉ còn ${item.stock} sản phẩm trong kho!`, 'warning');
                }

                item.quantity = parsedValue;
                renderCart();
            }
        };

        // Xóa ngay lập tức (không cần confirm)
        const deleteItem = (id) => {
            cartData = cartData.filter(item => item.id !== id);
            // Trong Laravel thực tế: Gọi API xóa
            // axios.post('/cart/remove', {id: id});
            
            renderCart();
            showToast('Đã xóa sản phẩm khỏi giỏ.');
        };

        // Hiển thị Toast
        let toastTimeout;
        const showToast = (message, type = 'success') => {
            const toast = document.getElementById('toast');
            document.getElementById('toast-message').innerText = message;
            
            // Đổi màu theo type nếu cần
            if (type === 'warning') {
                toast.classList.remove('bg-green-500');
                toast.classList.add('bg-orange-500');
            } else {
                toast.classList.remove('bg-orange-500');
                toast.classList.add('bg-green-500');
            }

            toast.classList.remove('translate-y-20', 'opacity-0');
            
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000); // Ẩn sau 3 giây
        };

        // Nút Thanh toán
        const proceedToCheckout = () => {
            const selectedItems = cartData.filter(item => item.selected);
            if (selectedItems.length > 0) {
                // Lấy ID các sản phẩm được tick để gửi qua trang thanh toán
                const selectedIds = selectedItems.map(item => item.id);
                console.log("Tiến hành thanh toán cho các ID:", selectedIds);
                
                // Chuyển hướng trong Laravel:
                // window.location.href = `/checkout?items=${selectedIds.join(',')}`;
                alert(`Chuyển hướng sang trang Thanh Toán với ${selectedItems.length} sản phẩm.`);
            }
        };

        // Init lần đầu
        renderCart();
    </script>
</body>
</html>