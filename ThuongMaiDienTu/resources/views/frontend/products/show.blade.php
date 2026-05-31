@extends('layouts.app')

@section('title', $product->name . ' - DIENMAYPRO')

@push('styles')
<!-- CSS TÙY CHỈNH TRANG CHI TIẾT SẢN PHẨM (PRODUCT DETAILS UI) CỦA DIENMAYPRO -->
<style>
/* ===== BREADCRUMB: Đường dẫn định vị liên kết ===== */
.breadcrumb { display:flex; align-items:center; gap:6px; font-size:13px; color:#666; margin:16px 0; flex-wrap:wrap; }
.breadcrumb a { color:#0046ab; }
.breadcrumb a:hover { text-decoration:underline; }

/* ===== PRODUCT DETAIL CARD: Khung thông tin tổng quan sản phẩm ===== */
.pd-wrapper { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:28px; display:grid; grid-template-columns:1fr 1fr; gap:36px; margin-bottom:24px; }

/* Thư viện hình ảnh (Gallery) bên trái */
.pd-gallery { display:flex; flex-direction:column; gap:12px; }
.pd-main-img-wrap { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; display:flex; align-items:center; justify-content:center; height:380px; background:#f8f9fa; position:relative; cursor: zoom-in; }
.pd-main-img { max-height:360px; max-width:100%; object-fit:contain; transition:transform .3s; }
.pd-main-img-wrap:hover .pd-main-img { transform:scale(1.06); }
.badge-discount { position:absolute; top:12px; left:12px; background:#d70018; color:#fff; font-size:13px; font-weight:700; padding:5px 10px; border-radius:6px; z-index: 2;}
.pd-thumbs { display:flex; gap:8px; flex-wrap:wrap; }
.pd-thumb { width:68px; height:68px; border:2px solid #e5e7eb; border-radius:8px; overflow:hidden; cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center; background:#f8f9fa; }
.pd-thumb:hover, .pd-thumb.active { border-color:#0046ab; }
.pd-thumb img { max-width:100%; max-height:100%; object-fit:contain; }

/* Modal phóng to hình ảnh sản phẩm (Zoom Modal) */
#imageZoomModal { display: none; position: fixed; z-index: 10005; inset: 0; background: rgba(0,0,0,0.85); justify-content: center; align-items: center; }
#imageZoomModal.active { display: flex; }
#zoomedImg { width: 90vw; height: 90vh; object-fit: contain; }
.close-zoom { position: absolute; top: 20px; right: 30px; color: #fff; font-size: 40px; cursor: pointer; transition: 0.2s; z-index: 10010; }
.close-zoom:hover { color: #d70018; }

/* Thông tin sản phẩm bên phải (Info Block) */
.pd-info { display:flex; flex-direction:column; gap:14px; position: relative; z-index: 500; }
.pd-category { font-size:12px; font-weight:600; color:#0046ab; background:#eef2ff; padding:3px 10px; border-radius:20px; display:inline-block; width:fit-content; }
.pd-name { font-size:22px; font-weight:800; color:#1a1a2e; line-height:1.3; }
.pd-rating { display:flex; align-items:center; gap:8px; font-size:13px; }
.pd-stars { color:#f59e0b; display:flex; gap:2px; }
.pd-price-block { background:#fff5f5; border-radius:10px; padding:14px 16px; }
.pd-price { font-size:28px; font-weight:900; color:#d70018; }
.pd-old-price { font-size:15px; color:#aaa; text-decoration:line-through; margin-top:2px; }
.pd-saving { font-size:13px; color:#16a34a; font-weight:600; margin-top:4px; }

/* Nhóm nút lựa chọn phân loại biến thể (Variants selection) */
.pd-section-label { font-size:13px; font-weight:700; color:#444; margin-bottom:8px; }
.variant-group { display:flex; flex-wrap:wrap; gap:8px; }
.variant-btn { padding:7px 16px; border:2px solid #e5e7eb; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; transition:.2s; background:#fff; color:#333; }
.variant-btn:hover { border-color:#0046ab; color:#0046ab; }
.variant-btn.selected { border-color:#0046ab; background:#0046ab; color:#fff; }

/* Các nút hành động chính (Mua ngay, Thêm giỏ hàng, Yêu thích) */
.pd-actions { display:flex; flex-direction:column; gap:10px; margin-top: 5px; position: relative; z-index: 600; }
.pd-actions button { pointer-events: auto !important; }
.btn-buy { padding:14px; font-size:16px; font-weight:700; border-radius:10px; border:none; cursor:pointer; transition:.2s; text-align:center; }
.btn-buy-now { background:linear-gradient(135deg,#d70018,#ff4444); color:#fff; }
.btn-buy-now:hover { background:linear-gradient(135deg,#b50014,#e03333); transform:translateY(-1px); box-shadow:0 6px 16px rgba(215,0,24,.3); }
.btn-add-cart { background:#0046ab; color:#fff; }
.btn-add-cart:hover { background:#003380; transform:translateY(-1px); box-shadow:0 6px 16px rgba(0,70,171,.3); }
.btn-wishlist { padding:14px; font-size:14px; font-weight:600; border-radius:10px; border:2px solid #e5e7eb; background:#fff; color:#555; cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center; gap:8px; }
.btn-wishlist:hover { border-color:#d70018; color:#d70018; }
.btn-wishlist.active { border-color:#d70018; color:#d70018; }

/* Grid hiển thị các cam kết dịch vụ / chính sách bán hàng */
.pd-policies { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top: 10px; }
.policy-item { display:flex; align-items:center; gap:10px; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:12px; color:#555; }
.policy-item i { color:#0046ab; font-size:18px; flex-shrink:0; }

/* Khung cấu trúc giữa: Mô tả đặc điểm & bảng thông số */
.middle-section { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px; }

/* Khung soạn thảo mô tả chi tiết */
.pd-desc { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:24px; }
.pd-desc h2 { font-size:18px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:8px; text-transform: uppercase;}
.pd-desc-content { font-size: 14.5px; line-height: 1.6; color: #444; }
.pd-desc-content img { max-width: 100%; border-radius: 8px; margin: 15px 0; }

/* Khung cấu hình chi tiết (Specs) */
.pd-specs { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:24px; height: fit-content;}
.pd-specs h2 { font-size:18px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:8px; text-transform: uppercase;}
.specs-table-wrapper { position: relative; max-height: 260px; overflow: hidden; transition: max-height 0.4s ease; }
.specs-table-wrapper.expanded { max-height: 1200px; }
.specs-table-wrapper:not(.expanded)::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 60px; background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1)); pointer-events: none; }
.specs-table { width:100%; border-collapse:collapse; font-size:13px; }
.specs-table tr:nth-child(even) td { background:#f8f9fa; }
.specs-table td { padding:10px 14px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
.specs-table td:first-child { width:40%; font-weight:600; color:#555; }
.specs-table td:last-child { color:#222; }
.btn-show-more-specs { display: block; width: 100%; margin-top: 15px; padding: 10px; border: 1px solid #0046ab; background: #fff; color: #0046ab; font-size: 13px; font-weight: 700; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.2s ease; }
.btn-show-more-specs:hover { background: #0046ab; color: #fff; }

/* Khu vực hiển thị sản phẩm liên quan (Sản phẩm tương tự) */
.pd-related { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:24px; margin-bottom:24px; }
.pd-related h2 { font-size:18px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:8px; text-transform: uppercase;}
.related-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:12px; }
.related-card { border:1px solid #e5e7eb; border-radius:10px; padding:12px; transition:.25s; display:flex; flex-direction:column; cursor:pointer; }
.related-card:hover { border-color:#0046ab; box-shadow:0 6px 18px rgba(0,0,0,.1); transform:translateY(-3px); }
.related-img { width:100%; height:120px; object-fit:contain; margin-bottom:8px; }
.related-name { font-size:12px; font-weight:600; color:#333; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; flex:1; }
.related-price { font-size:13px; font-weight:800; color:#d70018; margin-top:6px; }

/* Hộp thông báo nổi ở giữa màn hình (Centered Toast) */
.toast-notification {
    position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(0.9);
    background: rgba(0,0,0,0.85); padding: 20px 30px; border-radius: 12px; z-index: 10000;
    display: flex; flex-direction: column; align-items: center; gap: 15px; font-size: 16px; font-weight: 600; color: #fff;
    opacity: 0; pointer-events: none; transition: 0.3s ease; text-align: center;
}
.toast-notification.show { opacity: 1; transform: translate(-50%, -50%) scale(1); }
.toast-notification i { color: #16a34a; font-size: 40px; margin-bottom: 5px; }

/* Thanh tác vụ ghim dưới cùng màn hình (Sticky Bottom Action Bar) */
.bottom-action-bar {
    position: fixed; bottom: 0; left: 0; width: 100%; background: #fff; box-shadow: 0 -4px 15px rgba(0,0,0,0.08); padding: 12px 0; z-index: 9999;
    transform: translateY(100%); transition: transform 0.3s ease;
    visibility: hidden; pointer-events: none;
}
.bottom-action-bar.show { transform: translateY(0); visibility: visible; pointer-events: auto; }

/* Tránh đè lên footer ở trang chi tiết sản phẩm */
.footer {
    padding-bottom: 110px !important;
}

/* Khung giao diện Modal tính toán trả góp (Installment Modal) */
#installmentModal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 10002; align-items: center; justify-content: center; pointer-events: none; }
#installmentModal.active { display: flex; pointer-events: auto; }
.installment-content { background: #fff; width: 90%; max-width: 800px; max-height: 90vh; border-radius: 12px; overflow-y: auto; position: relative; }
.installment-header { position: sticky; top: 0; background: #fff; padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; z-index: 10; border-radius: 12px 12px 0 0; }
.installment-body { padding: 20px; }
.inst-tabs { display: flex; border-bottom: 2px solid #eee; margin-bottom: 20px; overflow-x: auto;}
.inst-tab { padding: 10px 15px; cursor: pointer; font-weight: 600; color: #555; border-bottom: 2px solid transparent; margin-bottom: -2px; white-space: nowrap; }
.inst-tab.active { color: #0046ab; border-bottom-color: #0046ab; }
.inst-companies { display: flex; gap: 10px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 5px; }
.inst-company { border: 1px solid #ccc; padding: 10px; border-radius: 8px; cursor: pointer; min-width: 120px; text-align: center; }
.inst-company.active { border-color: #0046ab; background: #eef2ff; color: #0046ab; font-weight: bold; }
.inst-table { width: 100%; border-collapse: collapse; }
.inst-table th, .inst-table td { border: 1px solid #eee; padding: 10px; text-align: left; }
.inst-table th { background: #f9f9f9; width: 40%; }
.inst-months { display: flex; gap: 10px; margin-bottom: 20px; overflow-x: auto;}
.inst-month { border: 1px solid #ccc; padding: 8px 15px; border-radius: 8px; cursor: pointer; white-space: nowrap;}
.inst-month.active { border-color: #0046ab; background: #eef2ff; color: #0046ab; font-weight: bold; }

/* Thanh điều hướng ảnh trong zoom gallery */
.zoom-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: 50px;
    color: rgba(255,255,255,0.7);
    cursor: pointer;
    transition: 0.3s;
    z-index: 10005;
    padding: 20px;
}
.zoom-nav:hover { color: #fff; }
.zoom-nav.next { right: 20px; }

/* Thanh tiến trình thể hiện số lượng đã bán của Flash Sale */
.fs-progress-wrapper { margin-top: 15px; background: #ffcdd2; border-radius: 10px; height: 22px; position: relative; overflow: hidden; display: flex; align-items: center; }
.fs-progress-bar { background: linear-gradient(90deg, #ff416c 0%, #ff4b2b 100%); height: 100%; border-radius: 10px; transition: width 0.5s ease; }
.fs-progress-text { position: absolute; width: 100%; text-align: center; font-size: 13px; font-weight: 700; color: #fff; text-shadow: 0px 0px 3px rgba(0,0,0,0.5); z-index: 2; }
.fs-fire-icon { position: absolute; left: 8px; color: #fff; font-size: 14px; z-index: 2; }
</style>
@endpush

@section('content')

@php
    $isWishlisted = $isWishlisted ?? false;
    $basePrice = $product->base_price;
    $oldPrice = $product->old_price;
    
    // Thu thập danh sách variants để đưa sang dạng JSON phục vụ logic đổi giá ở JS
    $variantsJson = $product->variants->map(function($v) {
        return [
            'id' => $v->variant_id,
            'color' => $v->color,
            'rom' => $v->rom_capacity,
            'extra_price' => $v->extra_price
        ];
    })->toJson();

    // Tính % giảm giá gốc
    $discountPercent = 0;
    if ($oldPrice > 0 && $oldPrice > $basePrice) {
        $discountPercent = round((($oldPrice - $basePrice) / $oldPrice) * 100);
    }
    
    // Kiểm tra xem sản phẩm có nằm trong danh sách yêu thích của user không
    $isWishlisted = false;
    if(auth()->check()){
        $isWishlisted = auth()->user()->wishlists()->where('product_id', $product->product_id)->where('type', 'Wishlist')->exists();
    }
    
    // Đánh giá xem có đang chạy chương trình Flash Sale cho sản phẩm hay không
    $isFlashSale = isset($flashSaleProduct) && $flashSaleProduct;
@endphp

<div class="container">
    {{-- Breadcrumb điều hướng --}}
    <nav class="breadcrumb">
        <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
        <i class="fa-solid fa-angle-right" style="font-size:10px;color:#bbb"></i>
        @if($product->category)
            <a href="#">{{ $product->category->name }}</a>
            <i class="fa-solid fa-angle-right" style="font-size:10px;color:#bbb"></i>
        @endif
        <span>{{ $product->name }}</span>
    </nav>

    {{-- Thẻ thông tin chi tiết sản phẩm --}}
    <div class="pd-wrapper">
        {{-- Gallery: Khu hiển thị ảnh & thumbnails --}}
        <div class="pd-gallery">
            <div class="pd-main-img-wrap" onclick="openZoom()">
                @if($discountPercent)
                    <span class="badge-discount">-<span id="discountBadge">{{ $discountPercent }}</span>%</span>
                @endif
                <img id="mainImg"
                     src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600' }}"
                     alt="{{ $product->name }}" class="pd-main-img">
            </div>
            
            {{-- Danh sách ảnh thu nhỏ (Thumbnails) --}}
            <div class="pd-thumbs">
                @php
                    // Mảng các ảnh trình chiếu trong Gallery
                    $thumbs = [
                        $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300',
                        'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=300',
                        'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=300',
                        'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=300',
                    ];
                @endphp
                <script>
                    // Truyền mảng ảnh sang cho JS để chuyển đổi ảnh lúc Zoom
                    const galleryImages = {!! json_encode($thumbs) !!};
                </script>
                @foreach($thumbs as $i => $t)
                    <div class="pd-thumb {{ $i===0?'active':'' }}" onclick="switchImg(this,'{{ $t }}')">
                        <img src="{{ $t }}" alt="Ảnh {{ $i+1 }}">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Info: Tên sản phẩm, thương hiệu, giá cả & lựa chọn cấu hình --}}
        <div class="pd-info">
            @if($product->category)
                <span class="pd-category">{{ $product->category->name }}</span>
            @endif

            <h1 class="pd-name">{{ $product->name }}</h1>

            <!-- Hiển thị đồng hồ đếm ngược nếu sản phẩm thuộc Flash Sale đang diễn ra -->
            @if($isFlashSale)
                <div style="display:inline-flex; align-items:center; gap:8px; background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; padding:8px 12px; border-radius:999px; font-size:13px; font-weight:700; width:fit-content;">
                    <i class="fa-solid fa-bolt"></i>
                    Flash Sale đang diễn ra
                    <span id="flashSaleCountdown" data-end-at="{{ optional($flashSaleProduct->flashSale->end_at)->toIso8601String() }}"></span>
                </div>
            @endif

            <div class="pd-rating">
                <span class="pd-rating-count"><span style="color:#16a34a;font-weight:600;">Còn hàng</span></span>
            </div>

            {{-- Khối Giá sản phẩm --}}
            <div class="pd-price-block">
                <div class="pd-price" id="displayPrice">{{ number_format($effectivePrice ?? $basePrice, 0, ',', '.') }}đ</div>
                @if($oldPrice || $isFlashSale)
                    <div class="pd-old-price" id="displayOldPrice">{{ number_format($oldPrice > 0 ? $oldPrice : $basePrice, 0, ',', '.') }}đ</div>
                    <div class="pd-saving" id="displaySaving">
                        Tiết kiệm: {{ number_format(($oldPrice > 0 ? $oldPrice : $basePrice) - ($effectivePrice ?? $basePrice), 0, ',', '.') }}đ
                    </div>
                @endif
                
                <!-- Thanh tiến độ thể hiện số lượng đã bán trong phiên Flash Sale -->
                @if($isFlashSale && isset($flashSaleProduct))
                    @php
                        $sold = $flashSaleProduct->sold_quantity ?? 0;
                        $limit = $flashSaleProduct->stock_limit ?? 1;
                        $percent = min(100, round(($sold / $limit) * 100));
                    @endphp
                    <div class="fs-progress-wrapper">
                        <div class="fs-progress-bar" style="width: {{ $percent }}%"></div>
                        <i class="fa-solid fa-fire fs-fire-icon"></i>
                        <span class="fs-progress-text">
                            @if($percent >= 100)
                                Đã bán hết (Sale kết thúc)
                            @else
                                Đã bán {{ $sold }}/{{ $limit }}
                            @endif
                        </span>
                    </div>
                @endif
            </div>

            {{-- Lựa chọn Dung lượng (ROM) - Tác động trực tiếp lên extra_price --}}
            @php $roms = $product->variants->pluck('rom_capacity')->filter()->unique(); @endphp
            @if($roms->count())
                <div>
                    <div class="pd-section-label">Tùy chọn dung lượng</div>
                    <div class="variant-group" id="romGroup">
                        @foreach($roms as $index => $rom)
                            <button class="variant-btn {{ $index === 0 ? 'selected' : '' }}" 
                                    onclick="selectRom(this, '{{ $rom }}')">{{ $rom }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Lựa chọn Màu sắc --}}
            @php $colors = $product->variants->pluck('color')->filter()->unique(); @endphp
            @if($colors->count())
                <div>
                    <div class="pd-section-label">Chọn màu sắc</div>
                    <div class="variant-group" id="colorGroup">
                        @foreach($colors as $index => $color)
                            <button class="variant-btn {{ $index === 0 ? 'selected' : '' }}" 
                                    onclick="selectColor(this)">{{ $color }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Nút mua hàng / Trả góp / Yêu thích / So sánh --}}
            <div class="pd-actions">
                <div style="display:flex; gap:10px; width:100%;">
                    <button class="btn-buy btn-buy-now" id="btnBuyNow" onclick="buyNow()" style="flex:1;">
                        <i class="fa-solid fa-bolt"></i> MUA NGAY
                    </button>
                    <button class="btn-buy" style="flex:1; background:#fff; color:#0046ab; border:2px solid #0046ab;" onclick="checkAuthAndOpenInstallment()">
                        <i class="fa-solid fa-credit-card"></i> TRẢ GÓP 0%
                    </button>
                </div>
                <div style="display:flex; gap:10px; width:100%; margin-top:10px;">
                    <button class="btn-buy btn-add-cart" id="btnAddCart" onclick="addToCart()" style="flex:1; font-size:13px; font-weight:700;">
                        <i class="fa-solid fa-cart-plus"></i> THÊM VÀO GIỎ HÀNG
                    </button>
                    <button type="button" class="btn-wishlist {{ $isWishlisted ? 'active' : '' }}" id="btnWishlist" onclick="toggleWishlist()" style="flex:1; min-width:140px;">
                        <i class="{{ $isWishlisted ? 'fa-solid' : 'fa-regular' }} fa-heart" id="wishlistIcon" style="{{ $isWishlisted ? 'color:#d70018' : '' }}"></i>
                        <span id="wishlistText">{{ $isWishlisted ? 'Đã thêm yêu thích' : 'Thêm yêu thích' }}</span>
                    </button>
                </div>
                <!-- So sánh thông số kỹ thuật nhanh -->
                <div style="margin-top: 12px; display: flex; justify-content: flex-start; padding-left: 5px;">
                    <a href="javascript:void(0)" id="btnCompareDetail" onclick="addToCompare('{{ $product->product_id }}')" 
                       style="font-size: 12px; color: #666; display: flex; align-items: center; gap: 6px; text-decoration: none; transition: 0.2s; font-weight: 500;"
                       onmouseover="this.style.color='#0046ab'" onmouseout="this.style.color='#666'">
                        <i class="fa-solid fa-scale-balanced"></i> <span id="compareDetailLabel">So sánh sản phẩm</span>
                    </a>
                </div>
            </div>

            {{-- Cam kết của cửa hàng --}}
            <div class="pd-policies">
                <div class="policy-item">
                    <i class="fa-solid fa-shield-halved"></i>
                    <div><strong>Bảo hành chính hãng</strong><br>12 tháng tại TTBH</div>
                </div>
                <div class="policy-item">
                    <i class="fa-solid fa-rotate-left"></i>
                    <div><strong>Đổi trả miễn phí</strong><br>Trong 30 ngày</div>
                </div>
                <div class="policy-item">
                    <i class="fa-solid fa-truck-fast"></i>
                    <div><strong>Giao hàng nhanh</strong><br>Toàn quốc 2H</div>
                </div>
                <div class="policy-item">
                    <i class="fa-solid fa-credit-card"></i>
                    <div><strong>Trả góp 0%</strong><br>Qua thẻ tín dụng</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. MUA KÈM COMBO TIẾT KIỆM (Combo Bundles Module) --}}
    @include('frontend.products._combo_bundle', ['comboProducts' => $comboProducts])

    {{-- Layout Giữa: Đặc điểm nổi bật + Thông số cấu hình chi tiết --}}
    <div class="middle-section">
        {{-- Giới thiệu đặc điểm nổi bật --}}
        <div class="pd-desc">
            <h2><i class="fa-solid fa-circle-info" style="color:#0046ab"></i> Đặc điểm nổi bật</h2>
            <div class="pd-desc-content">
                <p>
                    <strong>{{ $product->name }}</strong> mang đến trải nghiệm đột phá với hiệu năng siêu mạnh mẽ, thiết kế sang trọng và hệ thống camera đỉnh cao. Sản phẩm được trang bị công nghệ màn hình tiên tiến, hiển thị sắc nét từng chi tiết.
                </p>
                <img src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=800' }}" alt="Mô tả">
                <p>
                    Thời lượng pin ấn tượng giúp bạn yên tâm sử dụng suốt cả ngày dài. Khả năng sạc nhanh siêu tốc tiết kiệm tối đa thời gian chờ đợi. Giao diện mượt mà, trực quan, hỗ trợ tối đa cho cả công việc và giải trí.
                </p>
                <ul>
                    <li>Thiết kế cao cấp, chất liệu bền bỉ.</li>
                    <li>Camera chuyên nghiệp, chụp đêm xuất sắc.</li>
                    <li>Hiệu năng mượt mà, xử lý đa nhiệm không độ trễ.</li>
                </ul>
            </div>
        </div>

        {{-- Bảng thông số kỹ thuật chi tiết --}}
        @php
            $specsArray = [];
            if (!empty($product->specifications)) {
                $specsArray = is_string($product->specifications) ? json_decode($product->specifications, true) : $product->specifications;
            }
            $hasSpecsTable = $product->productSpecifications->count() > 0;
            $hasSpecsJson = !empty($specsArray) && is_array($specsArray) && count($specsArray) > 0;
        @endphp

        @if($hasSpecsTable || $hasSpecsJson)
            <div class="pd-specs">
                <h2><i class="fa-solid fa-microchip" style="color:#0046ab"></i> Cấu hình chi tiết</h2>
                <div class="specs-table-wrapper" id="specsWrapper">
                    <table class="specs-table">
                        @if($hasSpecsTable)
                            @php $spec = $product->productSpecifications->first(); @endphp
                            @if($spec->cpu_chip)
                                <tr><td>Vi xử lý (CPU)</td><td>{{ $spec->cpu_chip }}</td></tr>
                            @endif
                            @if($spec->ram_capacity)
                                <tr><td>RAM</td><td>{{ $spec->ram_capacity }}</td></tr>
                            @endif
                            @if($spec->screen_size)
                                <tr><td>Màn hình</td><td>{{ $spec->screen_size }}</td></tr>
                            @endif
                            @if($spec->battery)
                                <tr><td>Pin</td><td>{{ $spec->battery }}</td></tr>
                            @endif
                            @if($roms->count())
                                <tr><td>Bộ nhớ trong</td><td>{{ $roms->implode(' / ') }}</td></tr>
                            @endif
                            <tr><td>Hệ điều hành</td><td>Bản mới nhất</td></tr>
                            <tr><td>Trọng lượng</td><td>Tiêu chuẩn</td></tr>
                        @else
                            @foreach($specsArray as $key => $val)
                                @if(!in_array(strtolower($key), ['eco_friendly']))
                                    <tr><td>{{ $key }}</td><td>{{ is_array($val) ? implode(', ', $val) : $val }}</td></tr>
                                @endif
                            @endforeach
                        @endif
                    </table>
                </div>
                <button type="button" class="btn-show-more-specs" id="btnShowMoreSpecs" onclick="toggleSpecs()">
                    Xem chi tiết cấu hình <i class="fa-solid fa-chevron-down"></i>
                </button>
            </div>
        @endif
    </div>

    {{-- 4. BÌNH LUẬN & ĐÁNH GIÁ SẢN PHẨM (Reviews & Rating Partial) --}}
    @include('frontend.products.partials.reviews')

    {{-- 5. BÁN CHÉO (CROSS-SELL): Thường mua cùng nhau --}}
    @include('frontend.products._cross_sell', ['crossSellProducts' => $crossSellProducts])
    
    {{-- Danh sách các sản phẩm liên quan gợi ý --}}
    @if($relatedProducts->count())
        <div class="pd-related">
            <h2><i class="fa-solid fa-layer-group" style="color:#0046ab"></i> Sản phẩm tương tự</h2>
            <div class="related-grid">
                @foreach($relatedProducts as $rp)
                    <a href="{{ route('product.show', $rp->product_id) }}" class="related-card">
                        <img class="related-img"
                             src="{{ $rp->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300' }}"
                             alt="{{ $rp->name }}" loading="lazy">
                        <div class="related-name">{{ $rp->name }}</div>
                        <div class="related-price">{{ number_format($rp->base_price, 0, ',', '.') }}đ</div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- 6. STICKY BOTTOM ACTION BAR: Thanh thao tác ghim dưới đáy màn hình khi cuộn trang --}}
<div class="bottom-action-bar" id="bottomActionBar">
    <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:15px; flex: 1;">
            <img src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=100' }}" alt="{{ $product->name }}" style="width:45px; height:45px; object-fit:contain; border:1px solid #eee; border-radius:4px; padding:2px;" id="stickyImg">
            <span style="font-weight:700; font-size:14px; color:#333; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden;" id="stickyProductName">{{ $product->name }}</span>
        </div>
        <div style="display:flex; align-items:center; gap:15px; justify-content: flex-end; flex: 1;">
            <div style="text-align:right;">
                <div style="color:#d70018; font-weight:800; font-size:18px;" id="stickyPrice">{{ number_format($basePrice, 0, ',', '.') }}đ</div>
                @if($oldPrice)
                    <div style="color:#888; text-decoration:line-through; font-size:12px;" id="stickyOldPrice">{{ number_format($oldPrice, 0, ',', '.') }}đ</div>
                @else
                    <div style="color:#888; text-decoration:line-through; font-size:12px; display:none;" id="stickyOldPrice">0đ</div>
                @endif
            </div>
            <button style="border:1px solid #0046ab; color:#0046ab; background:#fff; padding:10px 15px; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px; white-space:nowrap;" onclick="checkAuthAndOpenInstallment()">Trả góp 0%</button>
            <button style="background:#d70018; color:#fff; border:none; padding:10px 25px; border-radius:8px; font-weight:700; cursor:pointer; font-size:14px; white-space:nowrap;" onclick="buyNow()">Mua Ngay</button>
            <button style="border:1px solid #d70018; color:#d70018; background:#fff; padding:10px 15px; border-radius:8px; cursor:pointer; font-size:16px;" onclick="addToCart()"><i class="fa-solid fa-cart-plus"></i></button>
        </div>
    </div>
</div>

{{-- 7. MODAL ĐĂNG KÝ TRẢ GÓP GIẢ LẬP (INSTALLMENT MODAL) --}}
<div id="installmentModal">
    <div class="installment-content">
        <div class="installment-header">
            <h3 style="margin:0; font-size:18px;">Thông tin các gói trả góp</h3>
            <i class="fa-solid fa-xmark" style="font-size:24px; cursor:pointer; color:#888; transition:0.2s;" onclick="closeInstallmentModal()" onmouseover="this.style.color='#d70018'" onmouseout="this.style.color='#888'"></i>
        </div>
        <div class="installment-body">
            <div style="display:flex; gap:15px; margin-bottom:20px; align-items:center;">
                <img src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=100' }}" style="width:60px; height:60px; object-fit:contain; border:1px solid #eee; border-radius:8px; padding:2px;">
                <div>
                    <h4 style="margin:0; font-size:16px; color:#333;" id="instProductName">{{ $product->name }}</h4>
                    <div style="color:#d70018; font-weight:bold; font-size:18px; margin-top:5px;" id="instProductPrice">{{ number_format($basePrice, 0, ',', '.') }}đ</div>
                </div>
            </div>
            
            <!-- Tabs chọn phương thức trả góp -->
            <div class="inst-tabs">
                <div class="inst-tab active" onclick="switchInstTab(0)">Trả góp qua công ty tài chính<br><span style="font-weight:normal; font-size:12px;">(Trả trước từ 30%)</span></div>
                <div class="inst-tab" onclick="switchInstTab(1)">Trả góp qua thẻ tín dụng<br><span style="font-weight:normal; font-size:12px;">(Không phí chuyển đổi)</span></div>
                <div class="inst-tab" onclick="switchInstTab(2)">Mua trước trả sau<br><span style="font-weight:normal; font-size:12px;">(Hạn mức đến 50 triệu)</span></div>
            </div>
            
            <!-- TAB 0: Trả góp qua công ty tài chính (Shinhan, Home Credit, HD Saison, Mirae Asset) -->
            <div id="instTabContent0" class="inst-tab-content" style="display:block;">
                <h4 style="margin-bottom:10px; font-size:14px;">Chọn công ty tài chính</h4>
                <div class="inst-companies">
                    <div class="inst-company active" onclick="selectCompany(this, 'Shinhan Finance')">Shinhan Finance</div>
                    <div class="inst-company" onclick="selectCompany(this, 'Home Credit')">Home Credit</div>
                    <div class="inst-company" onclick="selectCompany(this, 'HD Saison')">HD Saison</div>
                    <div class="inst-company" onclick="selectCompany(this, 'Mirae Asset')">Mirae Asset</div>
                </div>
                
                <h4 style="margin-bottom:10px; font-size:14px;">Chọn số tháng trả góp</h4>
                <div class="inst-months">
                    <div class="inst-month" onclick="selectMonth(this, 3)">3 tháng</div>
                    <div class="inst-month" onclick="selectMonth(this, 4)">4 tháng</div>
                    <div class="inst-month active" onclick="selectMonth(this, 6)">6 tháng</div>
                    <div class="inst-month" onclick="selectMonth(this, 9)">9 tháng</div>
                    <div class="inst-month" onclick="selectMonth(this, 12)">12 tháng</div>
                </div>
                
                <p style="font-size:13px; color:#888; font-style:italic; margin-bottom:10px;">(Bảng tính tham khảo, số tiền trả trước và hạn mức tuỳ thuộc vào hồ sơ được duyệt.)</p>
                
                <!-- Bảng số liệu chi tiết tự động tính toán bằng JS -->
                <table class="inst-table">
                    <tr><th>Công ty</th><td id="instCompanyName" style="font-weight:bold; color:#0046ab;">Shinhan Finance</td></tr>
                    <tr><th>Giá mua trả góp</th><td id="instBasePrice">32.490.000đ</td></tr>
                    <tr><th>Trả trước (30%)</th><td id="instPrepay" style="font-weight:bold;">9.747.000đ</td></tr>
                    <tr><th>Lãi suất</th><td id="instInterestRate">0%</td></tr>
                    <tr><th>Giấy tờ cần có</th><td>CMND/CCCD + Bằng lái xe/Hộ khẩu</td></tr>
                    <tr><th>Góp mỗi tháng</th><td id="instMonthly" style="color:#d70018; font-weight:bold; font-size:16px;">3.790.500đ</td></tr>
                    <tr><th>Gốc + Lãi</th><td id="instGocLai">3.790.500đ</td></tr>
                    <tr><th>Phí thu hộ/Bảo hiểm</th><td id="instPhi">0đ</td></tr>
                    <tr><th>Tổng tiền phải trả</th><td id="instTotal">32.490.000đ</td></tr>
                    <tr><th>Chênh lệch</th><td id="instDiff">0đ</td></tr>
                </table>
            </div>

            <!-- TAB 1: Trả góp qua thẻ tín dụng (Visa, MasterCard, JCB) -->
            <div id="instTabContent1" class="inst-tab-content" style="display:none;">
                <h4 style="margin-bottom:10px; font-size:14px;">Chọn phương thức trả góp</h4>
                <div style="border:1px solid #0046ab; border-radius:8px; padding:15px; margin-bottom:15px; background:#eef2ff;">
                    <strong>Trả góp qua Onepay (thẻ Visa/MasterCard/JCB/Napas)</strong>
                </div>
                <div style="margin-bottom:10px;">1. Chọn ngân hàng trả góp</div>
                <select style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; margin-bottom:15px; outline:none;">
                    <option>Vietcombank</option>
                    <option>Techcombank</option>
                    <option>MB Bank</option>
                    <option>Sacombank</option>
                    <option>VPBank</option>
                </select>
                <div style="margin-bottom:10px;">2. Chọn loại thẻ</div>
                <select style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; margin-bottom:15px; outline:none;">
                    <option>Visa</option>
                    <option>MasterCard</option>
                    <option>JCB</option>
                </select>
                <div style="margin-bottom:10px;">3. Chọn số tiền và kỳ hạn trả góp</div>
                <select style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; margin-bottom:15px; outline:none;">
                    <option>3 tháng - 0% lãi suất</option>
                    <option>6 tháng - 0% lãi suất</option>
                    <option>9 tháng - 1% lãi suất</option>
                    <option>12 tháng - 1% lãi suất</option>
                </select>
            </div>

            <!-- TAB 2: Mua trước trả sau (Kredivo) -->
            <div id="instTabContent2" class="inst-tab-content" style="display:none;">
                <h4 style="margin-bottom:10px; font-size:14px;">Chọn phương thức trả góp</h4>
                <div style="border:1px solid #0046ab; border-radius:8px; padding:15px; margin-bottom:15px; background:#eef2ff; display:flex; align-items:center; gap:10px;">
                    <div style="background:#f26b21; color:#fff; font-weight:bold; padding:5px 10px; border-radius:4px;">Kredivo</div>
                    <strong>Trả góp qua Kredivo</strong>
                </div>
                <p style="font-size:13px; color:#555;">Kredivo là giải pháp mua trước trả sau tiện lợi. Hạn mức lên đến 50 triệu đồng. Quy trình phê duyệt nhanh chóng, không cần thẻ tín dụng.</p>
            </div>
            
            <div id="instTradeInArea" style="margin-top:20px; display:flex; gap:10px; align-items:center;">
                <input type="checkbox" id="tradeInCheck" style="width:18px; height:18px; cursor:pointer;">
                <label for="tradeInCheck" style="font-size:14px; cursor:pointer; font-weight:600; color:#0046ab;">Bạn có muốn đăng ký thu cũ lên đời? (Trợ giá lên đến 2 triệu)</label>
            </div>
            
            <div id="instActionArea" style="display:flex; gap:10px; margin-top:20px;">
                <button onclick="closeInstallmentModal()" style="flex:1; background:#f1f5f9; color:#333; border:none; padding:12px; border-radius:8px; font-weight:bold; font-size:15px; cursor:pointer;">Đóng</button>
                <button onclick="confirmInstallment()" style="flex:2; background:#d70018; color:#fff; border:none; padding:12px; border-radius:8px; font-weight:bold; font-size:15px; cursor:pointer;">XÁC NHẬN TRẢ GÓP</button>
            </div>
            
            <!-- Thông báo thành công sau khi đăng ký trả góp -->
            <div id="instSuccessMsg" style="display:none; background:#fff; padding:30px; text-align:center; border-radius:8px; margin-top:20px; box-shadow:0 4px 15px rgba(0,0,0,0.1); border:1px solid #eee;">
                <i class="fa-solid fa-circle-check" style="font-size:60px; color:#16a34a; margin-bottom:15px;"></i>
                <h3 style="font-size:20px; color:#333; margin-bottom:10px;">Đăng ký trả góp thành công!</h3>
                <p style="font-size:15px; color:#555; line-height:1.5; margin-bottom:0;">Cảm ơn quý khách. Nhân viên tư vấn sẽ liên hệ qua số điện thoại của bạn trong ít phút để hoàn tất thủ tục.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Zoom Ảnh trong Gallery (Lightbox Zoom) -->
<div id="imageZoomModal">
    <i class="fa-solid fa-xmark close-zoom" onclick="closeZoom()"></i>
    <i class="fa-solid fa-chevron-left zoom-nav prev" onclick="prevZoomImage()"></i>
    <img id="zoomedImg" src="" alt="Zoom">
    <i class="fa-solid fa-chevron-right zoom-nav next" onclick="nextZoomImage()"></i>
</div>

<!-- Toast thông báo trượt góc -->
<div class="toast-notification" id="toast">
    <i id="toastIcon" class="fa-solid fa-circle-check"></i>
    <span id="toastMsg">Thêm vào giỏ hàng thành công!</span>
</div>


@endsection

@push('scripts')
<script>
// --- CHATBOT AI CONTEXT: Cung cấp ngữ cảnh sản phẩm đang xem cho chatbot ở layout ---
window.chatbotProductName = {!! json_encode($product->name) !!};
window.chatbotProductContext = {!! json_encode(
    $product->name . ' - Giá: ' . number_format($product->base_price, 0, ',', '.') . 'đ' .
    ($product->old_price ? ' (Giá gốc: ' . number_format($product->old_price, 0, ',', '.') . 'đ)' : '') .
    ($product->category ? ' - Danh mục: ' . $product->category->name : '')
) !!};

// --- CHỐNG NHẢY TRANG KHI F5: Đảm bảo trang cuộn về đỉnh khi refresh ---
if (history.scrollRestoration) {
    history.scrollRestoration = 'manual';
}
window.scrollTo(0, 0);
window.addEventListener('load', function() {
    setTimeout(() => { window.scrollTo(0, 0); }, 50);
});

// --- ZOOM GALLERY LIGHTBOX LOGIC ---
let currentZoomIndex = 0;

/**
 * Hàm: openZoom
 * Công dụng: Mở Modal phóng to ảnh và tìm kiếm vị trí ảnh hiện tại trong danh sách Gallery
 */
function openZoom() {
    const mainSrc = document.getElementById('mainImg').src;
    document.getElementById('zoomedImg').src = mainSrc;
    document.getElementById('imageZoomModal').classList.add('active');
    
    // Tìm kiếm chỉ số index tương đối của ảnh đang hiển thị
    currentZoomIndex = galleryImages.findIndex(src => {
        return mainSrc.includes(src.split('?')[0]);
    });
    if (currentZoomIndex === -1) currentZoomIndex = 0;
}

function nextZoomImage() {
    currentZoomIndex = (currentZoomIndex + 1) % galleryImages.length;
    document.getElementById('zoomedImg').src = galleryImages[currentZoomIndex];
}

function prevZoomImage() {
    currentZoomIndex = (currentZoomIndex - 1 + galleryImages.length) % galleryImages.length;
    document.getElementById('zoomedImg').src = galleryImages[currentZoomIndex];
}

function closeZoom() {
    document.getElementById('imageZoomModal').classList.remove('active');
}

// Đóng modal zoom khi click vào phần nền tối
document.getElementById('imageZoomModal').addEventListener('click', function(e) {
    if(e.target === this) closeZoom();
});

/**
 * Hàm: switchImg
 * Công dụng: Đổi ảnh hiển thị chính trên Gallery khi click chọn Thumbnail bên dưới
 */
function switchImg(el, src) {
    document.getElementById('mainImg').src = src;
    document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

// --- LOGIC XỬ LÝ CHỌN CẤU HÌNH BIẾN THỂ & CẬP NHẬT GIÁ KHUYẾN MÃI DỰA TRÊN EXTRA_PRICE ---
const basePrice = {{ $effectivePrice ?? $basePrice }};
const originalBasePrice = {{ $basePrice }};
const oldPrice = {{ $oldPrice ?? 0 }};
const variants = {!! $variantsJson !!}; // Nhận mảng đối tượng variants từ PHP sang

let currentExtraPrice = 0; // Giá trị chênh lệch (cộng thêm) của biến thể đang chọn

function formatCurrency(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
}

/**
 * Hàm: updateFlashSaleCountdown
 * Công dụng: Tính toán đếm ngược thời gian kết thúc Flash Sale theo thời gian thực (Giờ:Phút:Giây)
 */
function updateFlashSaleCountdown() {
    const el = document.getElementById('flashSaleCountdown');
    if (!el) return;
    const endAt = new Date(el.dataset.endAt);
    const diff = endAt - new Date();
    if (diff <= 0) {
        el.innerText = 'Đã kết thúc';
        return;
    }
    const totalSeconds = Math.floor(diff / 1000);
    const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
    const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
    const seconds = String(totalSeconds % 60).padStart(2, '0');
    el.innerText = `${hours}:${minutes}:${seconds}`;
}
setInterval(updateFlashSaleCountdown, 1000);
updateFlashSaleCountdown();

/**
 * Hàm: updatePriceDisplay
 * Công dụng: Cập nhật lại toàn bộ thẻ hiển thị giá trị tiền, tiền tiết kiệm, nhãn giảm giá và thanh sticky bottom
 */
function updatePriceDisplay() {
    const finalPrice = basePrice + currentExtraPrice;
    document.getElementById('displayPrice').innerText = formatCurrency(finalPrice);
    
    if (oldPrice > 0) {
        const finalOldPrice = oldPrice + currentExtraPrice;
        document.getElementById('displayOldPrice').innerText = formatCurrency(finalOldPrice);
        document.getElementById('displaySaving').innerText = 'Tiết kiệm: ' + formatCurrency(finalOldPrice - finalPrice);
        
        // Tính toán lại tỷ lệ phần trăm giảm giá theo giá trị mới
        const newDiscount = Math.round(((finalOldPrice - finalPrice) / finalOldPrice) * 100);
        const badge = document.getElementById('discountBadge');
        if (badge) badge.innerText = newDiscount;
        
        const stickyOldPrice = document.getElementById('stickyOldPrice');
        if (stickyOldPrice) stickyOldPrice.innerText = formatCurrency(finalOldPrice);
    }
    
    const stickyPrice = document.getElementById('stickyPrice');
    if (stickyPrice) stickyPrice.innerText = formatCurrency(finalPrice);
    
    // Cập nhật chuỗi thông tin phân loại biến thể vào tên sản phẩm (Ví dụ: iPhone 15 Pro - 256GB Vàng Titan)
    const activeRom = document.querySelector('#romGroup .variant-btn.selected');
    const activeColor = document.querySelector('#colorGroup .variant-btn.selected');
    let romVal = activeRom ? activeRom.innerText : '';
    let colorVal = activeColor ? activeColor.innerText : '';
    let variantStr = '';
    if(romVal || colorVal) {
        variantStr = ' - ' + [romVal, colorVal].filter(Boolean).join(' ');
    }
    
    const baseName = "{{ $product->name }}";
    const fullName = baseName + variantStr;
    
    const stickyProductName = document.getElementById('stickyProductName');
    if (stickyProductName) stickyProductName.innerText = fullName;
    
    const instProductName = document.getElementById('instProductName');
    if (instProductName) instProductName.innerText = fullName;
    
    // Cập nhật giá cơ sở cho phân hệ Trả góp
    instCurrentBasePrice = finalPrice;
    if(document.getElementById('installmentModal').classList.contains('active')) {
        updateInstallmentTable();
    }
}

// Thiết lập chọn mặc định dung lượng ROM đầu tiên khi vừa tải trang xong
document.addEventListener("DOMContentLoaded", function() {
    const firstRomBtn = document.querySelector('#romGroup .variant-btn.selected');
    if(firstRomBtn) {
        selectRom(firstRomBtn, firstRomBtn.innerText);
    }
});

/**
 * Hàm: calculateVariantPrice
 * Công dụng: Tìm kiếm biến thể phù hợp dựa trên ROM và Màu sắc để xác định extra_price tương ứng.
 */
function calculateVariantPrice() {
    const activeRom = document.querySelector('#romGroup .variant-btn.selected');
    const activeColor = document.querySelector('#colorGroup .variant-btn.selected');
    
    let romVal = activeRom ? activeRom.innerText : null;
    let colorVal = activeColor ? activeColor.innerText : null;

    let matchedVariant = null;
    
    if (romVal && colorVal) {
        matchedVariant = variants.find(v => v.rom === romVal && v.color === colorVal);
    } else if (romVal) {
        matchedVariant = variants.find(v => v.rom === romVal);
    } else if (colorVal) {
        matchedVariant = variants.find(v => v.color === colorVal);
    }

    if(matchedVariant) {
        currentExtraPrice = parseInt(matchedVariant.extra_price) || 0;
    } else {
        // Fallback: Lấy giá chênh lệch của tùy chọn ROM nếu không tìm thấy tổ hợp chính xác
        const fallbackRom = variants.find(v => v.rom === romVal);
        currentExtraPrice = fallbackRom ? (parseInt(fallbackRom.extra_price) || 0) : 0;
    }
    
    // Cộng thêm giá trị chênh lệch tượng trưng theo nhóm màu sắc cao cấp (Titan, Trắng...)
    if (colorVal) {
        const colorPriceMap = {
            'Vàng Titan': 1000000,
            'Titan Tự Nhiên': 1000000,
            'Trắng': 500000,
            'Đen': 0,
            'Xanh': 200000,
            'Bạc': 500000,
            'Xám không gian': 0
        };
        const colorPrice = colorPriceMap[colorVal] !== undefined ? colorPriceMap[colorVal] : (colorVal.length * 50000);
        currentExtraPrice += colorPrice;
    }
    
    updatePriceDisplay();
}

function selectRom(el, romValue) {
    document.querySelectorAll('#romGroup .variant-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    calculateVariantPrice();
}

function selectColor(el) {
    document.querySelectorAll('#colorGroup .variant-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    calculateVariantPrice();
}

/**
 * Hàm: showToast
 * Công dụng: Hiển thị nhanh một thanh Toast thông báo kết quả hành động thêm giỏ hàng.
 */
function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    if (toast) {
        const msgEl = document.getElementById('toastMsg');
        const iconEl = document.getElementById('toastIcon');
        if (msgEl) msgEl.innerText = msg;
        if (iconEl) {
            iconEl.className = 'fa-solid';
            if (type === 'error' || msg.toLowerCase().includes('lỗi') || msg.toLowerCase().includes('không')) {
                iconEl.classList.add('fa-circle-xmark');
                toast.style.borderLeft = '4px solid #ef4444';
            } else {
                iconEl.classList.add('fa-circle-check');
                toast.style.borderLeft = '4px solid #16a34a';
            }
        }
        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); }, 2500);
    }
}

function showProductToast(msg) { 
    if (typeof showToastReview === 'function') {
        showToastReview('Thông báo', msg, 'info');
    } else {
        showToast(msg);
    }
}

/**
 * Hàm: addToCart
 * Công dụng: Gửi yêu cầu thêm sản phẩm vào giỏ hàng thông qua AJAX bất đồng bộ.
 * @param {Boolean} redirect Nếu true sẽ điều hướng ngay lập tức về trang giỏ hàng (sử dụng cho Mua Ngay).
 */
function addToCart(redirect = false) {
    const data = {
        product_id: '{{ $product->product_id }}',
        quantity: 1
    };

    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(res => {
        if(res.status === 'success') {
            if (redirect) {
                window.location.href = "{{ route('cart.index') }}";
            } else {
                showToast('Đã thêm sản phẩm vào giỏ hàng thành công!');
                const headerBadge = document.getElementById('headerCartBadge');
                if (headerBadge && res.cart_count !== undefined) {
                    headerBadge.innerText = res.cart_count;
                    headerBadge.style.display = res.cart_count > 0 ? 'block' : 'none';
                }
            }
        } else if(res.error) {
            showToast(res.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Đã xảy ra lỗi khi thêm vào giỏ hàng!', 'error');
    });
}

function buyNow() {
    addToCart(true);
}

// --- LOGIC YÊU THÍCH SẢN PHẨM (WISHLIST TỐI ƯU HÓA) ---
let isWishlist = {{ $isWishlisted ? 'true' : 'false' }};
function toggleWishlist() {
    if (!{{ auth()->check() ? 'true' : 'false' }}) {
        window.location.href = "{{ route('login_register') }}";
        return;
    }

    const btn = document.getElementById('btnWishlist');
    const icon = document.getElementById('wishlistIcon');
    const text = document.getElementById('wishlistText');
    
    fetch('{{ route("wishlist.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ product_id: '{{ $product->product_id }}' })
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'added') {
            isWishlist = true;
            btn.classList.add('active');
            icon.classList.remove('fa-regular');
            icon.classList.add('fa-solid');
            icon.style.color = '#d70018';
            text.innerText = 'Đã thêm yêu thích';
            showToast('Đã thêm vào danh sách yêu thích!');
        } else if(data.status === 'removed') {
            isWishlist = false;
            btn.classList.remove('active');
            icon.classList.remove('fa-solid');
            icon.classList.add('fa-regular');
            icon.style.color = '';
            text.innerText = 'Thêm yêu thích';
            showToast('Đã xóa khỏi danh sách yêu thích.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Đã xảy ra lỗi!');
    });
}

// --- PHÂN HỆ MÔ PHỎNG VÀ TÍNH TOÁN TRẢ GÓP (INSTALLMENT INTERACTIVE CALCULATION) ---
let instCurrentBasePrice = basePrice;
let instSelectedCompany = 'Shinhan Finance';
let instSelectedMonth = 6;

function checkAuthAndOpenInstallment() {
    @auth
        openInstallmentModal();
    @else
        showProductToast('Vui lòng đăng nhập để đăng ký trả góp!');
        setTimeout(() => {
            window.location.href = "{{ route('login_register') }}";
        }, 1500);
    @endauth
}

function openInstallmentModal() {
    document.getElementById('installmentModal').classList.add('active');
    document.getElementById('instSuccessMsg').style.display = 'none';
    switchInstTab(0);
    updateInstallmentTable();
}

function closeInstallmentModal() {
    document.getElementById('installmentModal').classList.remove('active');
}

function switchInstTab(idx) {
    document.querySelectorAll('.inst-tab').forEach((el, i) => {
        if(i === idx) el.classList.add('active');
        else el.classList.remove('active');
    });
    document.querySelectorAll('.inst-tab-content').forEach((el, i) => {
        el.style.display = (i === idx) ? 'block' : 'none';
    });
}

function confirmInstallment() {
    const msg = document.getElementById('instSuccessMsg');
    msg.style.display = 'block';
    msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Đóng modal khi nhấn ra ngoài vùng nội dung modal
const instModal = document.getElementById('installmentModal');
if (instModal) {
    instModal.addEventListener('click', function(e) {
        if(e.target === this) closeInstallmentModal();
    });
}

function selectCompany(el, company) {
    document.querySelectorAll('.inst-company').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    instSelectedCompany = company;
    updateInstallmentTable();
}

function selectMonth(el, month) {
    document.querySelectorAll('.inst-month').forEach(m => m.classList.remove('active'));
    el.classList.add('active');
    instSelectedMonth = parseInt(month);
    updateInstallmentTable();
}

/**
 * Hàm: updateInstallmentTable
 * Công dụng: Tính toán các số liệu thực tế như khoản trả trước, trả góp mỗi tháng, lãi suất và chênh lệch dựa trên đối tác tài chính được lựa chọn.
 */
function updateInstallmentTable() {
    const format = (num) => Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
    
    document.getElementById('instProductPrice').innerText = format(instCurrentBasePrice);
    document.getElementById('instBasePrice').innerText = format(instCurrentBasePrice);
    document.getElementById('instCompanyName').innerText = instSelectedCompany;
    
    // Tính toán giả lập giá trị các gói tài chính
    const prepay = Math.round(instCurrentBasePrice * 0.3); // Trả trước 30% mặc định
    const loan = instCurrentBasePrice - prepay; // Khoản tiền cần vay
    
    let interestRate = 0; // Tỷ lệ lãi suất hàng tháng
    let flatFee = 0;      // Phí bảo hiểm hoặc phí thu hộ hàng tháng
    
    // Giả lập dữ liệu theo từng công ty tài chính
    if (instSelectedCompany === 'Home Credit') { interestRate = 0.01; flatFee = 50000; }
    else if (instSelectedCompany === 'HD Saison') { interestRate = 0.015; flatFee = 60000; }
    else if (instSelectedCompany === 'Mirae Asset') { interestRate = 0.02; flatFee = 70000; }
    // Shinhan Finance mặc định 0% lãi suất và không thu phí dịch vụ kèm theo
    
    document.getElementById('instInterestRate').innerText = (interestRate === 0) ? '0%' : (interestRate * 100).toFixed(1) + '%';
    document.getElementById('instPhi').innerText = format(flatFee);
    
    const monthlyNoInterest = loan / instSelectedMonth;
    const monthlyInterest = loan * interestRate;
    const monthlyPayment = Math.round(monthlyNoInterest + monthlyInterest + flatFee);
    
    const totalPayment = prepay + (monthlyPayment * instSelectedMonth);
    const diff = totalPayment - instCurrentBasePrice; // Tiền chênh lệch phải trả thêm
    
    document.getElementById('instPrepay').innerText = format(prepay);
    document.getElementById('instMonthly').innerText = format(monthlyPayment);
    document.getElementById('instGocLai').innerText = format(Math.round(monthlyNoInterest + monthlyInterest));
    document.getElementById('instTotal').innerText = format(totalPayment);
    document.getElementById('instDiff').innerText = format(diff > 0 ? diff : 0);
}

// Tự động ẩn/hiện thanh ghim mua nhanh (Sticky Bottom Bar) khi người dùng cuộn qua vùng ảnh đại diện sản phẩm (400px)
window.addEventListener('scroll', function() {
    const bottomBar = document.getElementById('bottomActionBar');
    if (bottomBar) {
        const fab = document.getElementById('chatbot-fab');
        const chatWin = document.getElementById('ai-chat-window');
        const alertBox = document.getElementById('pending-payment-alert');
        if (window.scrollY > 400) {
            bottomBar.classList.add('show');
            if (fab) fab.style.bottom = '90px';
            if (chatWin) chatWin.style.bottom = '155px';
            if (alertBox) alertBox.style.bottom = '155px';
        } else {
            bottomBar.classList.remove('show');
            if (fab) fab.style.removeProperty('bottom');
            if (chatWin) chatWin.style.removeProperty('bottom');
            if (alertBox) alertBox.style.removeProperty('bottom');
        }
    }
});

// --- PHÂN HỆ THU GỌN / XEM CHI TIẾT THÔNG SỐ KỸ THUẬT ---
function toggleSpecs() {
    const wrapper = document.getElementById('specsWrapper');
    const btn = document.getElementById('btnShowMoreSpecs');
    if (wrapper && btn) {
        if (wrapper.classList.contains('expanded')) {
            wrapper.classList.remove('expanded');
            btn.innerHTML = 'Xem chi tiết cấu hình <i class="fa-solid fa-chevron-down"></i>';
            wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            wrapper.classList.add('expanded');
            btn.innerHTML = 'Thu gọn <i class="fa-solid fa-chevron-up"></i>';
        }
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const wrapper = document.getElementById('specsWrapper');
    const btn = document.getElementById('btnShowMoreSpecs');
    if (wrapper && btn) {
        // Nếu chiều cao thực tế của bảng nhỏ hơn hoặc bằng giới hạn tối đa (khoảng 6 dòng), ẩn nút xem thêm
        if (wrapper.scrollHeight <= 265) {
            btn.style.display = 'none';
            wrapper.style.maxHeight = 'none';
        }
    }
});
</script>
@endpush
