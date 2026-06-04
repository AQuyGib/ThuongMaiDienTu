@push('styles')
<style>
    /* ===== DANH SÁCH YÊU THÍCH (WISHLIST SYSTEM) ===== */
    
    /* Thiết lập layout lưới tự co giãn thích ứng với kích thước màn hình */
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    /* Thiết kế thẻ Card của từng sản phẩm yêu thích */
    .wishlist-item {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 15px;
        position: relative;
        transition: 0.3s;
        display: flex;
        flex-direction: column;
    }
    
    /* Hiệu ứng di chuột nổi bật thẻ sản phẩm */
    .wishlist-item:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        transform: translateY(-5px);
        border-color: #0046ab;
    }
    
    /* Khung tỷ lệ ảnh vuông góc để không bị méo mó hình sản phẩm */
    .wishlist-img {
        width: 100%;
        aspect-ratio: 1/1;
        object-fit: contain;
        margin-bottom: 15px;
        border-radius: 8px;
    }
    
    /* Giới hạn tiêu đề sản phẩm hiển thị tối đa 2 dòng */
    .wishlist-item h4 {
        font-size: 14px;
        color: #333;
        margin-bottom: 10px;
        line-height: 1.4;
        height: 40px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .wishlist-price {
        font-size: 16px;
        font-weight: 700;
        color: #0046ab;
        margin-bottom: 15px;
    }
    
    /* Đẩy các nút hành động (Add to cart / Delete) xuống sát đáy thẻ Card */
    .wishlist-actions {
        display: flex;
        gap: 8px;
        margin-top: auto;
    }
    
    /* Nút thêm giỏ hàng */
    .btn-wishlist-cart {
        flex: 1;
        background: #0046ab;
        color: #fff;
        border: none;
        padding: 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
    
    /* Nút xóa sản phẩm khỏi Wishlist */
    .btn-wishlist-remove {
        width: 35px;
        height: 35px;
        background: #fee2e2;
        color: #e21033;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: 0.2s;
    }
    
    .btn-wishlist-remove:hover {
        background: #fecaca;
    }
</style>
@endpush

<div class="wishlist-grid">
    <!-- VÒNG LẶP DUYỆT QUA DANH SÁCH YÊU THÍCH -->
    @forelse($wishlist as $item)
        @if($item->product)
            <div class="wishlist-item" id="wishlist-item-{{ $item->id }}">
                <a href="{{ route('product.show', $item->product->product_id) }}">
                    <img src="{{ $item->product->thumbnail ?? 'https://via.placeholder.com/200' }}" alt="{{ $item->product->name }}" class="wishlist-img">
                </a>
                <a href="{{ route('product.show', $item->product->product_id) }}" style="text-decoration: none;">
                    <h4>{{ $item->product->name }}</h4>
                </a>
                <div class="wishlist-price">
                    {{ number_format($item->product->base_price, 0, ',', '.') }}đ
                </div>
                <div class="wishlist-actions">
                    <!-- Nút mua nhanh bằng AJAX -->
                    <button class="btn-wishlist-cart" onclick="addToCart('{{ $item->product->product_id }}')">
                        <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                    </button>
                    <!-- Nút xóa nhanh có Modal xác nhận bảo vệ -->
                    <button class="btn-wishlist-remove" onclick="removeFromWishlist({{ $item->id }})" title="Xóa khỏi yêu thích">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        @endif
    @empty
        <!-- TRẠNG THÁI TRỐNG: Hiển thị khi không có sản phẩm yêu thích nào -->
        <div class="dash-empty" style="grid-column: 1 / -1; padding: 50px 0;">
            <i class="fa-solid fa-heart-crack" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
            <p>Danh sách yêu thích của bạn đang trống.</p>
            <a href="{{ route('home') }}" class="btn-outline">Khám phá sản phẩm</a>
        </div>
    @endforelse
</div>

@push('scripts')
<script>
    /**
     * Hàm: removeFromWishlist
     * Công dụng: Xóa một sản phẩm khỏi danh sách yêu thích của người dùng qua AJAX.
     * Hoạt động:
     *   - Hiển thị hộp thoại xác nhận (Confirm Modal).
     *   - Nếu đồng ý, gửi yêu cầu DELETE tới Endpoint `/wishlist/${id}` kèm theo token CSRF.
     *   - Khi thành công, tạo hiệu ứng mượt mà (thu nhỏ và mờ dần) trước khi xóa thẻ khỏi DOM.
     *   - Nếu danh sách trống hoàn toàn sau khi xóa, tự động reload lại trang để chuyển sang trạng thái trống.
     */
    function removeFromWishlist(id) {
        showConfirm('Xóa khỏi yêu thích', 'Bạn muốn bỏ sản phẩm này khỏi danh sách yêu thích?', function() {
            fetch(`/wishlist/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast('Đã xóa', 'Đã bỏ sản phẩm khỏi danh sách yêu thích.', 'success');
                    const item = document.getElementById(`wishlist-item-${id}`);
                    if(item) {
                        // Hiệu ứng Optimistic UI mượt mà
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            item.remove();
                            // Tải lại trang nếu không còn sản phẩm yêu thích nào
                            if(document.querySelectorAll('.wishlist-item').length === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    }
                    closeConfirmModal();
                } else {
                    closeConfirmModal();
                    showToast('Lỗi', data.error || 'Không thể thực hiện thao tác này.', 'error');
                }
            })
            .catch(err => {
                closeConfirmModal();
                showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
            });
        });
    }

    /**
     * Hàm: clearWishlist
     * Công dụng: Gửi yêu cầu DELETE xóa sạch toàn bộ sản phẩm trong danh sách yêu thích của người dùng.
     */
    function clearWishlist() {
        showConfirm('Xóa tất cả', 'Bạn có chắc chắn muốn xóa toàn bộ danh sách yêu thích?', function() {
            fetch('{{ route('wishlist.clear') }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast('Thành công', 'Đã xóa toàn bộ danh sách yêu thích.', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    closeConfirmModal();
                    showToast('Lỗi', data.error || 'Không thể xóa danh sách lúc này.', 'error');
                }
            })
            .catch(err => {
                closeConfirmModal();
                showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
            });
        });
    }
</script>
@endpush
