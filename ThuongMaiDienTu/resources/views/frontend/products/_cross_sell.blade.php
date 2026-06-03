{{-- =====================================================
     CROSS-SELL SECTION: "Thường mua cùng nhau" (Frequently Bought Together)
     Hiển thị danh sách các sản phẩm gợi ý bán chéo (Cross-selling) dựa trên lịch sử mua sắm của khách hàng
     hoặc dựa trên cài đặt danh mục. Có tích hợp nút thêm giỏ hàng nhanh bằng AJAX ngay tại chỗ.
     Nhận biến đầu vào: $crossSellProducts (Collection<Product>)
     ===================================================== --}}

@if(isset($crossSellProducts) && $crossSellProducts->isNotEmpty())
@push('styles')
<!-- CSS TÙY CHỈNH CHO PHÂN HỆ ĐỀ XUẤT BÁN CHÉO (CROSS-SELL SECTION) DẠNG THANH TRƯỢT NGANG -->
<style>
.cs-section {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    padding: 24px;
    margin-bottom: 24px;
    overflow: hidden;
}

.cs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    flex-wrap: wrap;
    gap: 10px;
}

.cs-title {
    font-size: 18px;
    font-weight: 800;
    color: #1a1a2e;
    display: flex;
    align-items: center;
    gap: 10px;
    text-transform: uppercase;
}

/* Biểu tượng tia chớp đại diện cho ưu đãi mua kèm nhanh */
.cs-title .cs-title-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #ff6b35, #f7931e);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
}

.cs-subtitle {
    font-size: 13px;
    color: #888;
    font-weight: 400;
    text-transform: none;
}

/* Khung cuộn ngang chứa danh sách sản phẩm đề xuất */
.cs-scroll-wrapper {
    position: relative;
}

.cs-track {
    display: flex;
    gap: 14px;
    overflow-x: auto;
    padding-bottom: 8px;
    scroll-behavior: smooth;
    scrollbar-width: thin;
    scrollbar-color: #e5e7eb transparent;
}

.cs-track::-webkit-scrollbar { height: 4px; }
.cs-track::-webkit-scrollbar-track { background: transparent; }
.cs-track::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

/* Các nút điều hướng trái/phải nổi trên thanh trượt */
.cs-scroll-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #555;
    font-size: 14px;
    z-index: 10;
    transition: .2s;
}
.cs-scroll-btn:hover { background: #0046ab; color: #fff; border-color: #0046ab; }
.cs-scroll-btn.prev { left: -14px; }
.cs-scroll-btn.next { right: -14px; }

/* Thiết kế thẻ Card sản phẩm */
.cs-card {
    flex: 0 0 190px;
    border: 1.5px solid #f0f0f0;
    border-radius: 12px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: .25s;
    background: #fff;
    position: relative;
    text-decoration: none;
    color: inherit;
}

.cs-card:hover {
    border-color: #0046ab;
    box-shadow: 0 8px 24px rgba(0, 70, 171, .12);
    transform: translateY(-4px);
}

/* Nhãn hiển thị cho sản phẩm Flash Sale */
.cs-badge-flash {
    position: absolute;
    top: 10px;
    left: 10px;
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 3px;
    z-index: 2;
    letter-spacing: .3px;
}

/* Nhãn hiển thị độ ưu tiên "Hay mua cùng" (Thường gán cho 3 sản phẩm đầu tiên) */
.cs-badge-fbt {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #0046ab, #003380);
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: 20px;
    z-index: 2;
}

.cs-img-wrap {
    width: 100%;
    height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
}

.cs-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform .3s;
}
.cs-card:hover .cs-img { transform: scale(1.06); }

.cs-name {
    font-size: 12.5px;
    font-weight: 600;
    color: #333;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

.cs-price-block { margin-top: auto; }

.cs-price {
    font-size: 15px;
    font-weight: 800;
    color: #d70018;
    line-height: 1.2;
}

.cs-price-old {
    font-size: 11px;
    color: #aaa;
    text-decoration: line-through;
    margin-top: 2px;
}

.cs-price-sale {
    font-size: 15px;
    font-weight: 800;
    color: #ff416c;
    line-height: 1.2;
}

/* Nút thêm giỏ hàng nhanh (AJAX Cart Add) */
.cs-btn-add {
    width: 100%;
    padding: 8px;
    background: #f0f4ff;
    color: #0046ab;
    border: 1.5px solid #0046ab;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin-top: 8px;
}
.cs-btn-add:hover {
    background: #0046ab;
    color: #fff;
}

/* Ẩn các nút cuộn ngang trên màn hình di động vì đã có thể lướt tay (Swipe) */
@media (max-width: 768px) {
    .cs-card { flex: 0 0 155px; }
    .cs-scroll-btn { display: none; }
}
</style>
@endpush

<div class="cs-section" id="crossSellSection">
    <!-- TIÊU ĐỀ KHỐI GỢI Ý MUA CÙNG -->
    <div class="cs-header">
        <h2 class="cs-title">
            <span class="cs-title-icon">
                <i class="fa-solid fa-bolt"></i>
            </span>
            <span>
                Thường mua cùng nhau
                <br>
                <span class="cs-subtitle">Sản phẩm khách hàng hay chọn kèm</span>
            </span>
        </h2>
    </div>

    <!-- KHU VỰC CUỘN SLIDER SẢN PHẨM -->
    <div class="cs-scroll-wrapper">
        <!-- Nút cuộn sang bên trái -->
        <button class="cs-scroll-btn prev" onclick="csScroll(-1)" aria-label="Cuộn trái">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="cs-track" id="csTrack">
            @foreach($crossSellProducts as $index => $cs)
                @php
                    // Kiểm tra xem sản phẩm đề xuất này có đang được giảm giá Flash Sale hay không
                    $csIsFlash = isset($cs->flash_sale_price);
                    $csDisplayPrice = $csIsFlash ? $cs->flash_sale_price : $cs->base_price;
                @endphp
                <a href="{{ route('product.show', $cs->product_id) }}"
                   class="cs-card"
                   id="cs-card-{{ $cs->product_id }}"
                   title="{{ $cs->name }}">

                    <!-- Badge nhãn màu cam/đỏ nếu là Flash Sale -->
                    @if($csIsFlash)
                        <span class="cs-badge-flash">
                            <i class="fa-solid fa-fire"></i> SALE
                        </span>
                    @endif

                    <!-- Gán nhãn "Hay mua cùng" cho 3 sản phẩm đầu tiên có tần suất ghép đơn cao nhất -->
                    @if($index < 3 && !$csIsFlash)
                        <span class="cs-badge-fbt">Hay mua cùng</span>
                    @endif

                    <!-- Ảnh sản phẩm thu nhỏ -->
                    <div class="cs-img-wrap">
                        <img class="cs-img"
                             src="{{ $cs->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300' }}"
                             alt="{{ $cs->name }}"
                             loading="lazy">
                    </div>

                    <!-- Tên sản phẩm giới hạn 2 dòng -->
                    <div class="cs-name">{{ $cs->name }}</div>

                    <!-- Khối hiển thị giá bán (Đang Sale / Giá gốc / Giá cũ) -->
                    <div class="cs-price-block">
                        @if($csIsFlash)
                            <div class="cs-price-sale">
                                {{ number_format($cs->flash_sale_price, 0, ',', '.') }}đ
                            </div>
                            <div class="cs-price-old">
                                {{ number_format($cs->base_price, 0, ',', '.') }}đ
                            </div>
                        @else
                            <div class="cs-price">
                                {{ number_format($cs->base_price, 0, ',', '.') }}đ
                            </div>
                            @if($cs->old_price && $cs->old_price > $cs->base_price)
                                <div class="cs-price-old">
                                    {{ number_format($cs->old_price, 0, ',', '.') }}đ
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Nút bấm Đặt mua nhanh lập tức bằng AJAX mà không tải lại trang -->
                    <button class="cs-btn-add"
                            onclick="event.preventDefault(); csBuyNow({{ $cs->product_id }}, '{{ addslashes($cs->name) }}', {{ $csDisplayPrice }})"
                            id="cs-btn-{{ $cs->product_id }}">
                        <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                    </button>
                </a>
            @endforeach
        </div>

        <!-- Nút cuộn sang bên phải -->
        <button class="cs-scroll-btn next" onclick="csScroll(1)" aria-label="Cuộn phải">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>

@push('scripts')
<script>
/**
 * Hàm: csScroll
 * Công dụng: Điều hướng cuộn thanh trượt ngang chứa danh sách sản phẩm.
 * @param {Number} dir Hướng cuộn (-1 là cuộn sang trái, 1 là cuộn sang phải)
 */
function csScroll(dir) {
    const track = document.getElementById('csTrack');
    if (!track) return;
    track.scrollBy({ left: dir * 220, behavior: 'smooth' });
}

/**
 * Hàm: csBuyNow
 * Công dụng: Thực hiện gọi API thêm sản phẩm đề xuất vào giỏ hàng và hiển thị Optimistic UI.
 * @param {Number} productId ID sản phẩm
 * @param {String} productName Tên sản phẩm phục vụ thông báo
 * @param {Number} price Giá bán hiện hành
 */
function csBuyNow(productId, productName, price) {
    const btn = document.getElementById('cs-btn-' + productId);
    if (!btn) return;

    // Phản hồi giao diện tức thì (Optimistic UI) bằng cách tắt nút bấm và hiện hiệu ứng spinner
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang thêm...';
    btn.disabled = true;

    // Gửi yêu cầu POST lên endpoint giỏ hàng
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
        },
        body: JSON.stringify({ product_id: productId, quantity: 1 })
    })
    .then(r => r.json())
    .then(data => {
        // Nếu thêm thành công, đổi màu nút sang xanh lá biểu thị hoàn tất và cập nhật số giỏ hàng ở header
        if (data.success || data.status === 'ok' || data.message) {
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Đã thêm!';
            btn.style.background = '#16a34a';
            btn.style.borderColor = '#16a34a';
            btn.style.color = '#fff';

            // Gọi hàm hiển thị thông báo góc màn hình nếu trang chi tiết có hỗ trợ
            if (typeof showToast === 'function') {
                showToast('Đã thêm "' + productName + '" vào giỏ hàng!');
            }

            // Đồng bộ số lượng giỏ hàng trên badge
            const cartBadge = document.getElementById('cartCount');
            if (cartBadge && data.cart_count !== undefined) {
                cartBadge.innerText = data.cart_count;
            }
            
            // Cập nhật giỏ hàng trên header chính
            const headerCartBadge = document.getElementById('headerCartBadge');
            if(headerCartBadge && data.cart_count !== undefined) {
                headerCartBadge.innerText = data.cart_count;
                headerCartBadge.style.display = data.cart_count > 0 ? 'block' : 'none';
            }
        } else {
            throw new Error(data.message || 'Lỗi thêm giỏ hàng');
        }
    })
    .catch(err => {
        // Trong trường hợp lỗi mạng hoặc session hết hạn, chuyển hướng người dùng đến trang chi tiết để xử lý thủ công
        window.location.href = '/san-pham/' + productId;
    })
    .finally(() => {
        // Reset nút bấm về trạng thái ban đầu sau 2 giây để người dùng có thể click lại nếu muốn mua thêm
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            btn.style.background = '';
            btn.style.borderColor = '';
            btn.style.color = '';
        }, 2000);
    });
}
</script>
@endpush

@endif
