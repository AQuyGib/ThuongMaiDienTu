@php
    // Đếm tổng số lượng sản phẩm trả về từ phân trang (Paginator)
    $productCount = isset($products) ? $products->total() : 0;
    
    // Lấy danh sách các sản phẩm đang được đưa vào khay so sánh lưu trữ trong Session
    $compareIds = session()->get('compare_list', []);
    if (!is_array($compareIds)) {
        $compareIds = [];
    }
    
    // Khởi tạo dịch vụ FlashSaleService để kiểm tra các mức giá khuyến mãi thời gian thực
    $flashSaleService = app(\App\Services\FlashSaleService::class);
@endphp

<!-- GRID LAYOUT HIỂN THỊ DANH SÁCH SẢN PHẨM -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" data-total-products="{{ $productCount }}">
    @forelse($products as $product)
        @php
            // Xác định xem sản phẩm hiện hành có nằm trong danh sách so sánh hay không
            $isOnCompare = in_array($product->product_id, $compareIds);
            
            // Xử lý ảnh đại diện của sản phẩm
            $imageUrl = $product->thumbnail;
            if (!$imageUrl || !Str::startsWith($imageUrl, 'http')) {
                $imageUrl = asset('uploads/products/' . ($product->image ?: 'default.jpg'));
            }
            
            // Lấy thông tin chiến dịch Flash Sale đang diễn ra áp dụng cho sản phẩm này
            $flashSaleProduct = $flashSaleService->getFlashSaleProductFor($product);
            $effectivePrice = $flashSaleService->getEffectivePrice($product);
            $isFlashSale = $flashSaleProduct && $flashSaleService->canApplySale($flashSaleProduct);
        @endphp
        
        <!-- CARD THẺ SẢN PHẨM (Premium Hover Effects) -->
        <div class="product-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg" data-product-id="{{ $product->product_id }}">
            
            <!-- 1. KHUNG CHỨA HÌNH ẢNH SẢN PHẨM (IMAGE CONTAINER) -->
            <div class="relative h-48 overflow-hidden bg-gray-100 p-4 flex items-center justify-center">
                
                <!-- Nhãn trạng thái (Badges) như So sánh hoặc Flash Sale -->
                <div class="absolute left-3 top-3 z-10 flex items-center gap-2">
                    <span class="compare-status-badge {{ $isOnCompare ? '' : 'hidden' }} inline-flex items-center gap-1.5 rounded-full bg-blue-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-md shadow-blue-100">
                        <i class="fa-solid fa-check"></i>
                        <span>Đã so sánh</span>
                    </span>
                    @if($isFlashSale)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-md shadow-red-100">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Flash Sale</span>
                        </span>
                    @endif
                </div>

                <!-- Thẻ ảnh chính kèm cơ chế tải ảnh dự phòng tự động nếu link bị lỗi -->
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                    class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform duration-500"
                    onerror="this.src='https://loremflickr.com/400/400/technology?lock={{ $product->product_id }}'; this.onerror=null;">
                
                <!-- Nút thao tác nhanh ở góc trên bên phải (Yêu thích & Thêm so sánh) -->
                <div class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    @php
                        // Kiểm tra xem sản phẩm có nằm trong danh sách yêu thích của User đang đăng nhập không
                        $isWishlisted = false;
                        if(auth()->check()){
                            $isWishlisted = auth()->user()->wishlists()->where('product_id', $product->product_id)->where('type', 'wishlist')->exists();
                        }
                    @endphp
                    <!-- Nút Thêm vào Danh sách yêu thích -->
                    <button
                        onclick="toggleWishlist(this, {{ $product->product_id }})"
                        class="wishlist-btn w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-md hover:scale-110 transition-all duration-300 {{ $isWishlisted ? 'text-red-500' : 'text-gray-400' }}"
                        title="Yêu thích">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                    </button>
                    <!-- Nút Thêm vào Khay so sánh thông số -->
                    <button type="button" 
                        class="compare-card-btn w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-md transition-all duration-300 hover:scale-110 {{ $isOnCompare ? 'bg-blue-600 text-white ring-2 ring-blue-200' : 'text-gray-400' }}" 
                        title="{{ $isOnCompare ? 'Đã so sánh' : 'So sánh' }}" 
                        data-product-id="{{ $product->product_id }}" 
                        onclick="event.preventDefault(); event.stopPropagation(); addToCompare({{ $product->product_id }})">
                        <span class="compare-card-btn-spinner hidden animate-spin"><i class="fa-solid fa-spinner"></i></span>
                        <svg class="compare-card-btn-icon w-5 h-5 {{ $isOnCompare ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- 2. PHẦN THÔNG TIN CHI TIẾT SẢN PHẨM (PRODUCT INFO) -->
            <div class="p-4">
                <!-- Tên danh mục cha -->
                <div class="text-xs text-gray-500 mb-1">
                    {{ $product->category->name ?? 'Sản phẩm' }}
                </div>

                <!-- Tiêu đề sản phẩm giới hạn tối đa 2 dòng -->
                <h3 class="text-base font-bold text-gray-800 mb-2 line-clamp-2 min-h-[40px]" title="{{ $product->name }}">
                    <a href="{{ route('product.show', $product->product_id) }}" class="hover:text-blue-600 transition-colors">
                        {{ $product->name }}
                    </a>
                </h3>

                <!-- Hiển thị các thông số kỹ thuật nổi bật (Highlights) cấu hình trong danh mục -->
                <div class="flex flex-wrap gap-1 mb-3">
                    @php
                        // Lấy cấu hình highlights từ danh mục sản phẩm
                        $filterConfig = $product->category->filter_config ?? [];
                        if (is_string($filterConfig)) {
                            $filterConfig = json_decode($filterConfig, true);
                        }

                        $highlightConfig = $filterConfig['highlights'] ?? [];

                        // Phân tích dữ liệu JSON chứa thông số kỹ thuật chi tiết của sản phẩm
                        $specs = is_string($product->specifications) ? json_decode($product->specifications, true) : ($product->specifications ?? []);
                        if (!is_array($specs)) {
                            $specs = [];
                        }

                        $highlights = [];

                        if (!empty($highlightConfig)) {
                            // Render các thông số theo thứ tự cấu hình ưu tiên của Admin
                            foreach ($highlightConfig as $key => $prefix) {
                                if (isset($specs[$key])) {
                                    $val = is_array($specs[$key]) ? implode(', ', $specs[$key]) : $specs[$key];
                                    $highlights[] = $prefix . $val;
                                }
                            }
                        } else {
                            // Cấu hình dự phòng (Fallback) nếu chưa cấu hình trong danh mục
                            if (isset($specs['ram']))
                                $highlights[] = 'RAM: ' . (is_array($specs['ram']) ? implode(',', $specs['ram']) : $specs['ram']);
                            if (isset($specs['rom']))
                                $highlights[] = 'ROM: ' . (is_array($specs['rom']) ? implode(',', $specs['rom']) : $specs['rom']);
                        }
                    @endphp

                    <!-- Hiển thị tối đa 3 thẻ thông số nổi bật nhất -->
                    @foreach(array_slice($highlights, 0, 3) as $hl)
                        <span
                            class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded border border-gray-200">{{ $hl }}</span>
                    @endforeach
                </div>

                <!-- Hiển thị giá bán chính thức và giá niêm yết (Gạch ngang nếu có chiết khấu) -->
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-lg font-bold text-red-600">
                        {{ number_format($effectivePrice, 0, ',', '.') }} ₫
                    </span>
                    @if($isFlashSale)
                        <span class="text-sm text-gray-400 line-through">
                            {{ number_format($product->base_price, 0, ',', '.') }} ₫
                        </span>
                    @elseif($product->old_price && $product->old_price > $product->base_price)
                        <span class="text-sm text-gray-400 line-through">
                            {{ number_format($product->old_price, 0, ',', '.') }} ₫
                        </span>
                    @endif
                </div>

                <!-- Các nút hành động chính (Mua ngay & Chi tiết) -->
                <div class="flex gap-2">
                    <a href="{{ route('product.show', $product->product_id) }}"
                        class="flex-1 bg-blue-600 text-white text-center py-2.5 rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-sm hover:shadow-md">
                        Xem chi tiết
                    </a>
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                        <input type="hidden" name="buy_now" value="1">
                        <button type="submit"
                            class="w-full bg-gray-100 text-gray-800 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all">
                            Mua ngay
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <!-- Giao diện trống khi không tìm thấy kết quả tìm kiếm hay lọc nào khớp -->
        <div class="col-span-full text-center py-12">
            <div class="inline-block p-4 rounded-full bg-gray-100 mb-3">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <p class="text-gray-500 text-lg">Không tìm thấy sản phẩm nào phù hợp với bộ lọc.</p>
            <p class="text-gray-400 text-sm mt-1">Hãy thử thay đổi bộ lọc để tìm sản phẩm phù hợp</p>
        </div>
    @endforelse
</div>

<!-- CONTAINER HIỂN THỊ CÁC LIÊN KẾT PHÂN TRANG -->
<div class="mt-8 pagination-container">
    {{ $products->links('vendor.pagination.custom') }}
</div>

<script>
/**
 * Hàm: toggleWishlist
 * Công dụng: Gửi yêu cầu AJAX để thêm hoặc xóa sản phẩm khỏi danh sách yêu thích của tài khoản người dùng.
 * @param {HTMLButtonElement} btn Đối tượng nút bấm kích hoạt
 * @param {Number} productId ID sản phẩm tương ứng
 */
function toggleWishlist(btn, productId) {
    fetch('{{ route("wishlist.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => {
        // Nếu chưa đăng nhập (mã 401), điều hướng về trang đăng nhập
        if (response.status === 401) {
            window.location.href = '{{ route("login") }}';
            return;
        }
        return response.json();
    })
    .then(data => {
        // Cập nhật lại màu sắc trái tim và hiển thị thông báo toast nổi ở góc dưới
        if (data && data.status === 'added') {
            btn.classList.remove('text-gray-400');
            btn.classList.add('text-red-500');
            showToast('Đã thêm vào danh sách yêu thích!');
        } else if (data && data.status === 'removed') {
            btn.classList.remove('text-red-500');
            btn.classList.add('text-gray-400');
            showToast('Đã xóa khỏi danh sách yêu thích.');
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Hàm: showToast
 * Công dụng: Hiển thị nhanh một bảng thông báo nhỏ tự biến mất sau 3 giây.
 * @param {String} message Nội dung thông báo
 */
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-3 rounded-xl shadow-lg z-50 animate-bounce';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
