@foreach($products as $product)
    <div class="product-card-premium">
        <!-- Nhãn (Badges) -->
        <div class="badge-container">
            @if($product->sale_price)
                <div class="badge-promo">
                    Giảm {{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
                </div>
            @endif
            <div class="badge-installment">
                Trả góp 0%
            </div>
        </div>
        
        <a href="{{ route('product.show', $product->product_id) }}" style="display: block; flex: 1;">
            <img src="{{ $product->thumbnail ?? 'https://via.placeholder.com/300x300?text=' . urlencode($product->name) }}"
                alt="{{ $product->name }}" class="product-img" style="width: 100%; height: 160px; object-fit: contain; margin-bottom: 10px;">
            
            <h3 class="product-name" style="font-size: 14px; font-weight: 600; line-height: 1.4; margin-bottom: 8px; color: #333;">
                {{ $product->name }}
            </h3>
            
            <div class="price-box" style="margin-bottom: 8px;">
                <span class="product-price" style="font-size: 16px; font-weight: 700; color: #d70018;">{{ number_format($product->base_price, 0, ',', '.') }}đ</span>
                @if($product->old_price)
                    <span class="product-old-price" style="font-size: 12px; color: #888; text-decoration: line-through; margin-left: 5px;">{{ number_format($product->old_price, 0, ',', '.') }}đ</span>
                @endif
            </div>

            <div class="product-rating" style="font-size: 12px; color: #f59e0b; display: flex; align-items: center; gap: 3px;">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span style="color: #888; margin-left: 5px;">({{ rand(10, 500) }})</span>
            </div>
        </a>

        <!-- Action buttons ẩn khi hover -->
        <div class="product-card-actions">
            <button class="action-btn-circle btn-wishlist" 
                onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist('{{ $product->product_id }}', this)" 
                title="Yêu thích">
                <i class="fa-regular fa-heart"></i>
            </button>
            <button class="action-btn-circle btn-add-cart" 
                onclick="event.preventDefault(); event.stopPropagation(); addToCart('{{ $product->product_id }}')" 
                title="Thêm vào giỏ hàng">
                <i class="fa-solid fa-cart-plus"></i>
            </button>
        </div>
    </div>
@endforeach
