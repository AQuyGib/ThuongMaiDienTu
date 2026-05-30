@foreach($products as $product)
    <!-- KHỐI 1: KHUNG THẺ SẢN PHẨM CAO CẤP (PRODUCT CARD PREMIUM)
         Chứa ảnh đại diện, nhãn khuyến mãi, các nút yêu thích/giỏ hàng và thông tin tên/giá bán.
    -->
    <div class="product-card-premium">
        <!-- 1. Vùng hiển thị nhãn nổi bật (Badges): Nhãn phần trăm giảm giá và trả góp -->
        <div class="badge-container">
            @if($product->sale_price && $product->price > 0)
                <!-- Tự động tính toán tỷ lệ giảm giá dựa trên chênh lệch giữa giá gốc và giá bán khuyến mãi -->
                <div class="badge-promo">
                    -{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
                </div>
            @endif
            <div class="badge-installment">Trả góp 0%</div>
        </div>

        <!-- 2. Khối các nút hành động nhanh (Wishlist và Add-to-cart)
             Mặc định ẩn, hover vào thẻ card sẽ hiển thị lên bằng CSS transition.
        -->
        <div class="product-card-actions">
            <!-- Nút Thêm vào Danh sách yêu thích (Wishlist) -->
            <button class="action-btn-circle btn-wishlist" 
                onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist('{{ $product->product_id }}', this)" 
                title="Yêu thích">
                <i class="fa-regular fa-heart"></i>
            </button>
            <!-- Nút Thêm nhanh vào Giỏ hàng (AJAX Add To Cart) -->
            <button class="action-btn-circle btn-add-cart" 
                onclick="event.preventDefault(); event.stopPropagation(); addToCart('{{ $product->product_id }}')" 
                title="Thêm vào giỏ">
                <i class="fa-solid fa-cart-plus"></i>
            </button>
        </div>

        <!-- 3. Liên kết chi tiết sản phẩm: Bao bọc phần Ảnh và Nội dung chữ -->
        <a href="{{ route('product.show', $product->product_id) }}" style="text-decoration: none; display: flex; flex-direction: column; height: 100%;">
            <!-- 3.1. Vùng chứa ảnh đại diện sản phẩm (Cố định chiều cao 160px để đảm bảo lưới thẳng hàng) -->
            <div class="product-img-wrapper" style="width: 100%; height: 160px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; overflow: hidden;">
                <img src="{{ $product->thumbnail ?? 'https://via.placeholder.com/300x300?text=' . urlencode($product->name) }}"
                    alt="{{ $product->name }}" class="product-img" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            </div>
            
            <!-- 3.2. Khối thông tin chi tiết sản phẩm (Tên, Giá, Đánh giá) -->
            <div class="product-info-premium" style="flex: 1; display: flex; flex-direction: column;">
                <!-- Tên sản phẩm (Giới hạn tối đa 2 dòng hiển thị bằng line-clamp) -->
                <h3 class="product-name" style="font-size: 14px; font-weight: 600; line-height: 1.5; color: #333; margin-bottom: 10px; height: 42px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $product->name }}
                </h3>
                
                <!-- Hộp hiển thị giá bán (Bao gồm giá bán hiện tại và giá gốc gạch ngang nếu có) -->
                <div class="price-box" style="margin-top: auto; margin-bottom: 8px;">
                    <div style="font-size: 16px; font-weight: 700; color: #d70018;">
                        {{ number_format($product->base_price, 0, ',', '.') }}đ
                    </div>
                    @if($product->old_price)
                        <div style="font-size: 12px; color: #888; text-decoration: line-through;">
                            {{ number_format($product->old_price, 0, ',', '.') }}đ
                        </div>
                    @endif
                </div>

                <!-- Đánh giá sao (Mặc định hiển thị 5 sao màu vàng và số lượng đánh giá ngẫu nhiên giả lập) -->
                <div class="product-rating" style="font-size: 11px; color: #f59e0b; display: flex; align-items: center; gap: 3px;">
                    <div class="stars">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <span style="color: #999; margin-left: 4px;">({{ rand(10, 500) }})</span>
                </div>
            </div>
        </a>
    </div>
@endforeach
