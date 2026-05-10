@extends('layouts.app')

@section('title', $product->name . ' - TechZone')

@push('styles')
<style>
/* ===== BREADCRUMB ===== */
.breadcrumb { display:flex; align-items:center; gap:6px; font-size:13px; color:#666; margin:16px 0; flex-wrap:wrap; }
.breadcrumb a { color:#0046ab; }
.breadcrumb a:hover { text-decoration:underline; }

/* ===== PRODUCT DETAIL CARD ===== */
.pd-wrapper { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:28px; display:grid; grid-template-columns:1fr 1fr; gap:36px; margin-bottom:24px; }

/* Gallery */
.pd-gallery { display:flex; flex-direction:column; gap:12px; }
.pd-main-img-wrap { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; display:flex; align-items:center; justify-content:center; height:380px; background:#f8f9fa; position:relative; cursor: zoom-in; }
.pd-main-img { max-height:360px; max-width:100%; object-fit:contain; transition:transform .3s; }
.pd-main-img-wrap:hover .pd-main-img { transform:scale(1.06); }
.badge-discount { position:absolute; top:12px; left:12px; background:#d70018; color:#fff; font-size:13px; font-weight:700; padding:5px 10px; border-radius:6px; z-index: 2;}
.pd-thumbs { display:flex; gap:8px; flex-wrap:wrap; }
.pd-thumb { width:68px; height:68px; border:2px solid #e5e7eb; border-radius:8px; overflow:hidden; cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center; background:#f8f9fa; }
.pd-thumb:hover, .pd-thumb.active { border-color:#0046ab; }
.pd-thumb img { max-width:100%; max-height:100%; object-fit:contain; }

/* Modal Zoom Image */
#imageZoomModal { display: none; position: fixed; z-index: 9999; inset: 0; background: rgba(0,0,0,0.85); justify-content: center; align-items: center; }
#imageZoomModal.active { display: flex; }
#zoomedImg { width: 70vw; height: 70vh; object-fit: contain; }
.close-zoom { position: absolute; top: 20px; right: 30px; color: #fff; font-size: 30px; cursor: pointer; transition: 0.2s; }
.close-zoom:hover { color: #d70018; }

/* Info */
.pd-info { display:flex; flex-direction:column; gap:14px; }
.pd-category { font-size:12px; font-weight:600; color:#0046ab; background:#eef2ff; padding:3px 10px; border-radius:20px; display:inline-block; width:fit-content; }
.pd-name { font-size:22px; font-weight:800; color:#1a1a2e; line-height:1.3; }
.pd-rating { display:flex; align-items:center; gap:8px; font-size:13px; }
.pd-stars { color:#f59e0b; display:flex; gap:2px; }
.pd-price-block { background:#fff5f5; border-radius:10px; padding:14px 16px; }
.pd-price { font-size:28px; font-weight:900; color:#d70018; }
.pd-old-price { font-size:15px; color:#aaa; text-decoration:line-through; margin-top:2px; }
.pd-saving { font-size:13px; color:#16a34a; font-weight:600; margin-top:4px; }

/* Variants */
.pd-section-label { font-size:13px; font-weight:700; color:#444; margin-bottom:8px; }
.variant-group { display:flex; flex-wrap:wrap; gap:8px; }
.variant-btn { padding:7px 16px; border:2px solid #e5e7eb; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; transition:.2s; background:#fff; color:#333; }
.variant-btn:hover { border-color:#0046ab; color:#0046ab; }
.variant-btn.selected { border-color:#0046ab; background:#0046ab; color:#fff; }

/* Buttons */
.pd-actions { display:flex; flex-direction:column; gap:10px; margin-top: 5px; }
.btn-buy { padding:14px; font-size:16px; font-weight:700; border-radius:10px; border:none; cursor:pointer; transition:.2s; text-align:center; }
.btn-buy-now { background:linear-gradient(135deg,#d70018,#ff4444); color:#fff; }
.btn-buy-now:hover { background:linear-gradient(135deg,#b50014,#e03333); transform:translateY(-1px); box-shadow:0 6px 16px rgba(215,0,24,.3); }
.btn-add-cart { background:#0046ab; color:#fff; }
.btn-add-cart:hover { background:#003380; transform:translateY(-1px); box-shadow:0 6px 16px rgba(0,70,171,.3); }
.btn-wishlist { padding:14px; font-size:14px; font-weight:600; border-radius:10px; border:2px solid #e5e7eb; background:#fff; color:#555; cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center; gap:8px; }
.btn-wishlist:hover { border-color:#d70018; color:#d70018; }
.btn-wishlist.active { border-color:#d70018; color:#d70018; }

/* Policies */
.pd-policies { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top: 10px; }
.policy-item { display:flex; align-items:center; gap:10px; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:12px; color:#555; }
.policy-item i { color:#0046ab; font-size:18px; flex-shrink:0; }

/* Middle Section: Intro + Specs */
.middle-section { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px; }

/* Description (Giới thiệu) */
.pd-desc { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:24px; }
.pd-desc h2 { font-size:18px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:8px; text-transform: uppercase;}
.pd-desc-content { font-size: 14.5px; line-height: 1.6; color: #444; }
.pd-desc-content img { max-width: 100%; border-radius: 8px; margin: 15px 0; }

/* Specs */
.pd-specs { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:24px; height: fit-content;}
.pd-specs h2 { font-size:18px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:8px; text-transform: uppercase;}
.specs-table { width:100%; border-collapse:collapse; font-size:13px; }
.specs-table tr:nth-child(even) td { background:#f8f9fa; }
.specs-table td { padding:10px 14px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
.specs-table td:first-child { width:40%; font-weight:600; color:#555; }
.specs-table td:last-child { color:#222; }

/* Reviews */
.pd-reviews { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:24px; margin-bottom:24px; }
.pd-reviews h2 { font-size:18px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:8px; text-transform: uppercase;}
.review-stats { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
.review-average { text-align: center; }
.review-average h3 { font-size: 32px; color: #d70018; margin-bottom: 5px; }
.review-average .stars { color: #f59e0b; font-size: 14px; }
.review-item { padding: 15px 0; border-bottom: 1px solid #f5f5f5; }
.review-item:last-child { border-bottom: none; }
.review-user { font-weight: 600; font-size: 14px; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; }
.review-user span { background: #16a34a; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: normal;}
.review-stars { color: #f59e0b; font-size: 12px; margin-bottom: 8px; }
.review-content { font-size: 14px; color: #444; }

/* ===== RELATED ===== */
.pd-related { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:24px; margin-bottom:24px; }
.pd-related h2 { font-size:18px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:8px; text-transform: uppercase;}
.related-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:12px; }
.related-card { border:1px solid #e5e7eb; border-radius:10px; padding:12px; transition:.25s; display:flex; flex-direction:column; cursor:pointer; }
.related-card:hover { border-color:#0046ab; box-shadow:0 6px 18px rgba(0,0,0,.1); transform:translateY(-3px); }
.related-img { width:100%; height:120px; object-fit:contain; margin-bottom:8px; }
.related-name { font-size:12px; font-weight:600; color:#333; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; flex:1; }
.related-price { font-size:13px; font-weight:800; color:#d70018; margin-top:6px; }

/* Toast Notification Centered */
.toast-notification {
    position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(0.9);
    background: rgba(0,0,0,0.85); padding: 20px 30px; border-radius: 12px; z-index: 10000;
    display: flex; flex-direction: column; align-items: center; gap: 15px; font-size: 16px; font-weight: 600; color: #fff;
    opacity: 0; pointer-events: none; transition: 0.3s ease; text-align: center;
}
.toast-notification.show { opacity: 1; transform: translate(-50%, -50%) scale(1); }
.toast-notification i { color: #16a34a; font-size: 40px; margin-bottom: 5px; }

/* Sticky Bottom Action Bar */
.bottom-action-bar {
    position: fixed; bottom: 0; left: 0; width: 100%; background: #fff; box-shadow: 0 -4px 15px rgba(0,0,0,0.08); padding: 12px 0; z-index: 9999;
    transform: translateY(100%); transition: transform 0.3s ease;
}
.bottom-action-bar.show { transform: translateY(0); }

/* Installment Modal */
#installmentModal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 10002; align-items: center; justify-content: center; }
#installmentModal.active { display: flex; }
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

/* Zoom Navigation */
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
.zoom-nav.prev { left: 20px; }
.zoom-nav.next { right: 20px; }
</style>
@endpush

@section('content')

@php
    $basePrice = $product->base_price;
    $oldPrice = $product->old_price;
    
    // Thu thập variants để dùng cho JS đổi giá
    $variantsJson = $product->variants->map(function($v) {
        return [
            'id' => $v->variant_id,
            'color' => $v->color,
            'rom' => $v->rom_capacity,
            'extra_price' => $v->extra_price
        ];
    })->toJson();
    
    // Load reviews
    $reviews = App\Models\Review::where('product_id', $product->product_id)->orderBy('created_at', 'desc')->get();
    $reviewCount = $reviews->count();
    $avgRating = $reviewCount > 0 ? round($reviews->avg('rating'), 1) : 0;

    $discountPercent = 0;
    if ($oldPrice > 0 && $oldPrice > $basePrice) {
        $discountPercent = round((($oldPrice - $basePrice) / $oldPrice) * 100);
    }
@endphp

<div class="container">
    {{-- Breadcrumb --}}
    <nav class="breadcrumb">
        <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
        <i class="fa-solid fa-angle-right" style="font-size:10px;color:#bbb"></i>
        @if($product->category)
            <a href="#">{{ $product->category->name }}</a>
            <i class="fa-solid fa-angle-right" style="font-size:10px;color:#bbb"></i>
        @endif
        <span>{{ $product->name }}</span>
    </nav>

    {{-- Main Card --}}
    <div class="pd-wrapper">
        {{-- Gallery --}}
        <div class="pd-gallery">
            <div class="pd-main-img-wrap" onclick="openZoom()">
                @if($discountPercent)
                    <span class="badge-discount">-<span id="discountBadge">{{ $discountPercent }}</span>%</span>
                @endif
                <img id="mainImg"
                     src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600' }}"
                     alt="{{ $product->name }}" class="pd-main-img">
            </div>
            {{-- Thumbnails --}}
            <div class="pd-thumbs">
                @php
                    $thumbs = [
                        $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300',
                        'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=300',
                        'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=300',
                        'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=300',
                    ];
                @endphp
                <script>
                    const galleryImages = {!! json_encode($thumbs) !!};
                </script>
                @foreach($thumbs as $i => $t)
                    <div class="pd-thumb {{ $i===0?'active':'' }}" onclick="switchImg(this,'{{ $t }}')">
                        <img src="{{ $t }}" alt="Ảnh {{ $i+1 }}">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Info --}}
        <div class="pd-info">
            @if($product->category)
                <span class="pd-category">{{ $product->category->name }}</span>
            @endif

            <h1 class="pd-name">{{ $product->name }}</h1>

            <div class="pd-rating">
                <div class="pd-stars" id="topReviewStars">
                    @for($i=1; $i<=5; $i++)
                        @if($i <= round($avgRating))
                            <i class="fa-solid fa-star" style="color:#f59e0b"></i>
                        @else
                            <i class="fa-regular fa-star" style="color:#ccc"></i>
                        @endif
                    @endfor
                </div>
                <span class="pd-rating-count"><span id="topReviewCount">({{ $reviewCount }} đánh giá)</span> · <span style="color:#16a34a;font-weight:600;">Còn hàng</span></span>
            </div>

            {{-- Giá --}}
            <div class="pd-price-block">
                <div class="pd-price" id="displayPrice">{{ number_format($basePrice, 0, ',', '.') }}đ</div>
                @if($oldPrice)
                    <div class="pd-old-price" id="displayOldPrice">{{ number_format($oldPrice, 0, ',', '.') }}đ</div>
                    <div class="pd-saving" id="displaySaving">
                        Tiết kiệm: {{ number_format($oldPrice - $basePrice, 0, ',', '.') }}đ
                    </div>
                @endif
            </div>

            {{-- Dung lượng (ROM) - Quyết định extra_price --}}
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

            {{-- Màu sắc --}}
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

            {{-- Hành động --}}
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
                    <button class="btn-wishlist" id="btnWishlist" onclick="toggleWishlist()" style="flex:1; justify-content:center;">
                        <i class="fa-regular fa-heart" id="wishlistIcon"></i> <span id="wishlistText">Thêm yêu thích</span>
                    </button>
                </div>
            </div>

            {{-- Chính sách --}}
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

    {{-- Layout Giữa: Giới thiệu + Specs --}}
    <div class="middle-section">
        {{-- Giới thiệu --}}
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

        {{-- Thông số kỹ thuật --}}
        @if($product->specifications->count())
            <div class="pd-specs">
                <h2><i class="fa-solid fa-microchip" style="color:#0046ab"></i> Cấu hình chi tiết</h2>
                <table class="specs-table">
                    @php $spec = $product->specifications->first(); @endphp
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
                </table>
            </div>
        @endif
    </div>

    {{-- Đánh giá sản phẩm --}}
    <div class="pd-reviews">
        <h2><i class="fa-solid fa-comments" style="color:#0046ab"></i> Đánh giá & Nhận xét</h2>
        <div class="review-stats">
            <div class="review-average">
                <h3 id="avgReviewScore">{{ $avgRating }}/5</h3>
                <div class="stars" id="avgReviewStars">
                    @for($i=1; $i<=5; $i++)
                        @if($i <= round($avgRating))
                            <i class="fa-solid fa-star" style="color:#f59e0b"></i>
                        @else
                            <i class="fa-regular fa-star" style="color:#ccc"></i>
                        @endif
                    @endfor
                </div>
                <p style="font-size:12px; color:#666; margin-top:5px;" id="totalReviewCount">{{ $reviewCount }} đánh giá</p>
            </div>
            <div style="flex:1;">
                <p style="font-size:14px; color:#555;" id="reviewStatusText">
                    @if($reviewCount > 0)
                        Đã có {{ $reviewCount }} đánh giá cho sản phẩm này.
                    @else
                        Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá sản phẩm này!
                    @endif
                </p>
            </div>
        </div>
        
        <div class="review-form" style="margin-bottom: 25px; background: #f9f9f9; padding: 20px; border-radius: 10px;">
            <h4 style="margin-bottom: 15px; font-size: 15px;">Viết đánh giá của bạn</h4>
            <div style="margin-bottom: 10px; display: flex; gap: 10px; color: #ccc; font-size: 20px; cursor: pointer;">
                <i class="fa-solid fa-star star-rating" data-val="1" style="color:#f59e0b"></i>
                <i class="fa-solid fa-star star-rating" data-val="2" style="color:#f59e0b"></i>
                <i class="fa-solid fa-star star-rating" data-val="3" style="color:#f59e0b"></i>
                <i class="fa-solid fa-star star-rating" data-val="4" style="color:#f59e0b"></i>
                <i class="fa-solid fa-star star-rating" data-val="5" style="color:#f59e0b"></i>
            </div>
            <textarea id="reviewText" placeholder="Nhập đánh giá của bạn về sản phẩm này..." style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; resize: none;"></textarea>
            <button type="button" onclick="submitReview()" style="background: #0046ab; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;">Gửi đánh giá</button>
        </div>
        
        <div class="review-list">
            @if($reviewCount > 0)
                @foreach($reviews as $r)
                    <div class="review-item" style="padding: 15px 0; border-bottom: 1px solid #f5f5f5;">
                        <div class="review-user" style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">Khách hàng <span style="background: #16a34a; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: normal;"><i class="fa-solid fa-check"></i> Đã mua hàng</span></div>
                        <div class="review-stars" style="color: #f59e0b; font-size: 12px; margin-bottom: 8px;">
                            @for($i=1; $i<=5; $i++)
                                @if($i <= $r->rating)
                                    <i class="fa-solid fa-star"></i>
                                @else
                                    <i class="fa-regular fa-star"></i>
                                @endif
                            @endfor
                        </div>
                        <div class="review-content" style="font-size: 14px; color: #444;">{{ $r->content }}</div>
                    </div>
                @endforeach
            @else
                <p id="noReviewMsg" style="text-align: center; color: #888; font-style: italic; padding: 20px 0;">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
            @endif
        </div>
    </div>

    {{-- Sản phẩm liên quan --}}
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

{{-- Sticky Bottom Action Bar --}}
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

<!-- Modal Trả góp -->
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
            
            <div class="inst-tabs">
                <div class="inst-tab active" onclick="switchInstTab(0)">Trả góp qua công ty tài chính<br><span style="font-weight:normal; font-size:12px;">(Trả trước từ 30%)</span></div>
                <div class="inst-tab" onclick="switchInstTab(1)">Trả góp qua thẻ tín dụng<br><span style="font-weight:normal; font-size:12px;">(Không phí chuyển đổi)</span></div>
                <div class="inst-tab" onclick="switchInstTab(2)">Mua trước trả sau<br><span style="font-weight:normal; font-size:12px;">(Hạn mức đến 50 triệu)</span></div>
            </div>
            
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
            
            <div id="instSuccessMsg" style="display:none; background:#fff; padding:30px; text-align:center; border-radius:8px; margin-top:20px; box-shadow:0 4px 15px rgba(0,0,0,0.1); border:1px solid #eee;">
                <i class="fa-solid fa-circle-check" style="font-size:60px; color:#16a34a; margin-bottom:15px;"></i>
                <h3 style="font-size:20px; color:#333; margin-bottom:10px;">Đăng ký trả góp thành công!</h3>
                <p style="font-size:15px; color:#555; line-height:1.5; margin-bottom:0;">Cảm ơn quý khách. Nhân viên tư vấn sẽ liên hệ qua số điện thoại của bạn trong ít phút để hoàn tất thủ tục.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Zoom Ảnh -->
<div id="imageZoomModal">
    <i class="fa-solid fa-xmark close-zoom" onclick="closeZoom()"></i>
    <i class="fa-solid fa-chevron-left zoom-nav prev" onclick="prevZoomImage()"></i>
    <img id="zoomedImg" src="" alt="Zoom">
    <i class="fa-solid fa-chevron-right zoom-nav next" onclick="nextZoomImage()"></i>
</div>

<!-- Toast -->
<div class="toast-notification" id="toast">
    <i class="fa-solid fa-circle-check"></i>
    <span id="toastMsg">Thêm vào giỏ hàng thành công!</span>
</div>

    {{-- Đăng ký nhận khuyến mãi --}}
    <div style="background: #eef2ff; padding: 40px 0; border-top: 1px solid #ddd; margin-top: 50px;">
        <div class="container" style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:30px;">
            <div style="flex:1; min-width:300px;">
                <h3 style="color:#0046ab; font-size:22px; font-weight:800; margin-bottom:10px;"><i class="fa-solid fa-envelope-open-text"></i> Đăng ký nhận thông tin khuyến mãi</h3>
                <p style="font-size:15px; color:#555; line-height:1.5;">Đăng ký ngay để nhận ưu đãi <strong>giảm 10%</strong> cho đơn hàng đầu tiên và thông tin sản phẩm mới nhất từ DienMayPro!</p>
            </div>
            <div style="flex:1; min-width:300px; background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <form action="#" method="POST" style="display:flex; flex-direction:column; gap:15px;" onsubmit="event.preventDefault(); showPromoSuccess();">
                    <div style="display:flex; gap:10px;">
                        <input type="email" placeholder="Email của bạn *" required style="flex:1; padding:12px 15px; border:1px solid #ccc; border-radius:8px; font-size:14px; outline:none;">
                        <input type="tel" placeholder="Số điện thoại *" required style="flex:1; padding:12px 15px; border:1px solid #ccc; border-radius:8px; font-size:14px; outline:none;">
                    </div>
                    <div style="display:flex; align-items:flex-start; gap:8px;">
                        <input type="checkbox" id="agreeTerms" required style="margin-top:3px; cursor:pointer;">
                        <label for="agreeTerms" style="font-size:13px; color:#555; cursor:pointer;">Tôi đồng ý với các <a href="#" style="color:#0046ab; text-decoration:underline;">điều kiện và điều khoản</a> của DienMayPro</label>
                    </div>
                    <button type="submit" style="background:linear-gradient(135deg, #0046ab, #003380); color:#fff; border:none; padding:14px; border-radius:8px; font-weight:bold; cursor:pointer; font-size:15px; text-transform:uppercase; transition:0.2s;">Đăng ký nhận mã</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Promo Success Modal --}}
    <div id="promoSuccessModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 10001; justify-content: center; align-items: center;">
        <div style="background: #fff; padding: 40px; border-radius: 12px; text-align: center; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <i class="fa-solid fa-circle-check" style="font-size: 60px; color: #16a34a; margin-bottom: 20px;"></i>
            <h3 style="font-size: 22px; color: #333; margin-bottom: 10px;">Cảm ơn quý khách!</h3>
            <p style="font-size: 15px; color: #555; line-height: 1.5; margin-bottom: 0;">Đăng ký nhận khuyến mãi thành công. Chúng tôi sẽ gửi mã giảm giá 10% qua Email và Số điện thoại của quý khách.</p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// --- Tự cuộn lên đầu khi F5 ---
if (history.scrollRestoration) {
    history.scrollRestoration = 'manual';
}
window.scrollTo(0, 0);
window.addEventListener('load', function() {
    setTimeout(() => { window.scrollTo(0, 0); }, 50);
});

// --- Zoom Ảnh ---
let currentZoomIndex = 0;

function openZoom() {
    const mainSrc = document.getElementById('mainImg').src;
    document.getElementById('zoomedImg').src = mainSrc;
    document.getElementById('imageZoomModal').classList.add('active');
    
    // Tìm index hiện tại trong mảng galleryImages
    currentZoomIndex = galleryImages.findIndex(src => {
        // So sánh tương đối vì src có thể là full URL hoặc partial
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
// Đóng modal khi click ra ngoài ảnh
document.getElementById('imageZoomModal').addEventListener('click', function(e) {
    if(e.target === this) closeZoom();
});

// --- Chọn ảnh Thumbnail ---
function switchImg(el, src) {
    document.getElementById('mainImg').src = src;
    document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

// --- Xử lý Chọn Cấu hình & Đổi Giá ---
const basePrice = {{ $basePrice }};
const oldPrice = {{ $oldPrice ?? 0 }};
const variants = {!! $variantsJson !!}; 

let currentExtraPrice = 0;

function formatCurrency(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
}

function updatePriceDisplay() {
    const finalPrice = basePrice + currentExtraPrice;
    document.getElementById('displayPrice').innerText = formatCurrency(finalPrice);
    
    if (oldPrice > 0) {
        const finalOldPrice = oldPrice + currentExtraPrice;
        document.getElementById('displayOldPrice').innerText = formatCurrency(finalOldPrice);
        document.getElementById('displaySaving').innerText = 'Tiết kiệm: ' + formatCurrency(finalOldPrice - finalPrice);
        
        // Cập nhật lại % giảm giá
        const newDiscount = Math.round(((finalOldPrice - finalPrice) / finalOldPrice) * 100);
        const badge = document.getElementById('discountBadge');
        if (badge) badge.innerText = newDiscount;
        
        const stickyOldPrice = document.getElementById('stickyOldPrice');
        if (stickyOldPrice) stickyOldPrice.innerText = formatCurrency(finalOldPrice);
    }
    
    const stickyPrice = document.getElementById('stickyPrice');
    if (stickyPrice) stickyPrice.innerText = formatCurrency(finalPrice);
    
    // Cập nhật tên trong thanh sticky và modal trả góp
    const activeRom = document.querySelector('#romGroup .variant-btn.selected');
    const activeColor = document.querySelector('#colorGroup .variant-btn.selected');
    let romVal = activeRom ? activeRom.innerText : '';
    let colorVal = activeColor ? activeColor.innerText : '';
    let variantStr = '';
    if(romVal || colorVal) {
        variantStr = ' - ' + [romVal, colorVal].filter(Boolean).join(' ');
    }
    
    const baseName = `{{ $product->name }}`;
    const fullName = baseName + variantStr;
    
    const stickyProductName = document.getElementById('stickyProductName');
    if (stickyProductName) stickyProductName.innerText = fullName;
    
    const instProductName = document.getElementById('instProductName');
    if (instProductName) instProductName.innerText = fullName;
    
    // Update Installment variables
    instCurrentBasePrice = finalPrice;
    if(document.getElementById('installmentModal').classList.contains('active')) {
        updateInstallmentTable();
    }
}

// Khởi tạo giá từ rom đầu tiên nếu có
document.addEventListener("DOMContentLoaded", function() {
    const firstRomBtn = document.querySelector('#romGroup .variant-btn.selected');
    if(firstRomBtn) {
        selectRom(firstRomBtn, firstRomBtn.innerText);
    }
});

function calculateVariantPrice() {
    const activeRom = document.querySelector('#romGroup .variant-btn.selected');
    const activeColor = document.querySelector('#colorGroup .variant-btn.selected');
    
    let romVal = activeRom ? activeRom.innerText : null;
    let colorVal = activeColor ? activeColor.innerText : null;

    // Tìm variant khớp cả rom và color (nếu cả 2 cùng có nhóm)
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
        // Fallback: nếu không tìm thấy tổ hợp chính xác, lấy extra price của rom
        const fallbackRom = variants.find(v => v.rom === romVal);
        currentExtraPrice = fallbackRom ? (parseInt(fallbackRom.extra_price) || 0) : 0;
    }
    
    // Thêm giá ảo cho màu sắc để UI thay đổi giá khi bấm chọn màu
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
    // Đổi active
    document.querySelectorAll('#romGroup .variant-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    calculateVariantPrice();
}

function selectColor(el) {
    document.querySelectorAll('#colorGroup .variant-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    calculateVariantPrice();
}

// Khởi tạo trạng thái ban đầu cho chức năng đánh giá sao
let currentRating = 5;
document.querySelectorAll('.star-rating').forEach(star => {
    star.addEventListener('click', function() {
        const val = this.getAttribute('data-val');
        currentRating = parseInt(val);
        document.querySelectorAll('.star-rating').forEach(s => {
            if(parseInt(s.getAttribute('data-val')) <= parseInt(val)) {
                s.style.color = '#f59e0b';
            } else {
                s.style.color = '#ccc';
            }
        });
    });
});

function submitReview() {
    const textarea = document.getElementById('reviewText');
    const content = textarea.value.trim();
    if(!content) { 
        alert('Vui lòng nhập nội dung đánh giá!'); 
        return; 
    }
    
    // Gọi AJAX lưu vào DB
    fetch('{{ route("reviews.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            product_id: '{{ $product->product_id }}',
            rating: currentRating,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Render sao
            let starsHtml = '';
            for(let i=1; i<=5; i++) {
                starsHtml += (i <= currentRating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
            }
            
            const reviewList = document.querySelector('.review-list');
            if(document.getElementById('noReviewMsg')) {
                document.getElementById('noReviewMsg').remove(); // Xóa dòng "Chưa có đánh giá nào..."
            }
            
            // Thêm review mới lên đầu
            reviewList.innerHTML = `
                <div class="review-item" style="padding: 15px 0; border-bottom: 1px solid #f5f5f5;">
                    <div class="review-user" style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">Bạn <span style="background: #16a34a; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: normal;"><i class="fa-solid fa-check"></i> Vừa đánh giá</span></div>
                    <div class="review-stars" style="color: #f59e0b; font-size: 12px; margin-bottom: 8px;">${starsHtml}</div>
                    <div class="review-content" style="font-size: 14px; color: #444;">${content}</div>
                </div>
            ` + reviewList.innerHTML;
            
            textarea.value = '';
            showToast('Đã gửi đánh giá thành công!');
        } else {
            alert('Đã xảy ra lỗi khi lưu đánh giá.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Đã xảy ra lỗi kết nối!');
    });
}

// --- Toast & Actions ---
function showToast(msg) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMsg').innerText = msg;
    
    toast.classList.add('show');
    setTimeout(() => { toast.classList.remove('show'); }, 2000);
}

function buyNow() {
    window.location.href = "{{ route('cart.index') }}";
}

let userId = '{{ Auth::id() ?? "guest" }}';
let cartCount = parseInt(localStorage.getItem('cartCount_' + userId) || document.getElementById('headerCartBadge')?.innerText || 0);

function addToCart() {
    showToast('Đã thêm sản phẩm vào giỏ hàng thành công!');
    
    // Cập nhật số lượng trên header và lưu vào localStorage theo user
    cartCount++;
    localStorage.setItem('cartCount_' + userId, cartCount);
    
    const headerBadge = document.getElementById('headerCartBadge');
    if (headerBadge) {
        headerBadge.innerText = cartCount;
        headerBadge.style.display = 'block';
    }
}

// Wishlist Toggle
let isWishlist = false;
function toggleWishlist() {
    const btn = document.getElementById('btnWishlist');
    const icon = document.getElementById('wishlistIcon');
    const text = document.getElementById('wishlistText');
    
    isWishlist = !isWishlist;
    if(isWishlist) {
        btn.classList.add('active');
        icon.classList.remove('fa-regular');
        icon.classList.add('fa-solid');
        text.innerText = 'Đã thêm yêu thích';
    } else {
        btn.classList.remove('active');
        icon.classList.remove('fa-solid');
        icon.classList.add('fa-regular');
        text.innerText = 'Thêm vào yêu thích';
    }
}

// --- Installment Modal ---
let instCurrentBasePrice = basePrice;
let instSelectedCompany = 'Shinhan Finance';
let instSelectedMonth = 6;

function checkAuthAndOpenInstallment() {
    @auth
        openInstallmentModal();
    @else
        showToast('Vui lòng đăng nhập để đăng ký trả góp!');
        setTimeout(() => {
            window.location.href = "{{ route('login_register') }}";
        }, 1500);
    @endauth
}

function openInstallmentModal() {
    document.getElementById('installmentModal').classList.add('active');
    
    // Reset lại giao diện
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
    // Hiện thông báo thành công đẹp mắt ngay dưới nút bấm
    const msg = document.getElementById('instSuccessMsg');
    msg.style.display = 'block';
    
    // Cuộn xuống nhẹ để thấy thông báo nếu bị khuất
    msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Close when clicking outside
document.getElementById('installmentModal').addEventListener('click', function(e) {
    if(e.target === this) closeInstallmentModal();
});

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

function updateInstallmentTable() {
    const format = (num) => num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
    
    document.getElementById('instProductPrice').innerText = format(instCurrentBasePrice);
    document.getElementById('instBasePrice').innerText = format(instCurrentBasePrice);
    document.getElementById('instCompanyName').innerText = instSelectedCompany;
    
    // Tính toán giả lập
    const prepay = Math.round(instCurrentBasePrice * 0.3); // Trả trước 30% mặc định
    const loan = instCurrentBasePrice - prepay;
    
    let interestRate = 0; // % per month
    let flatFee = 0;      // Phí bảo hiểm / thu hộ mỗi tháng
    
    // Fake data to match user request styles
    if (instSelectedCompany === 'Home Credit') { interestRate = 0.01; flatFee = 50000; }
    else if (instSelectedCompany === 'HD Saison') { interestRate = 0.015; flatFee = 60000; }
    else if (instSelectedCompany === 'Mirae Asset') { interestRate = 0.02; flatFee = 70000; }
    // Shinhan = 0% interest, 0 fee
    
    document.getElementById('instInterestRate').innerText = (interestRate === 0) ? '0%' : (interestRate * 100).toFixed(1) + '%';
    document.getElementById('instPhi').innerText = format(flatFee);
    
    const monthlyNoInterest = loan / instSelectedMonth;
    const monthlyInterest = loan * interestRate;
    const monthlyPayment = Math.round(monthlyNoInterest + monthlyInterest + flatFee);
    
    const totalPayment = prepay + (monthlyPayment * instSelectedMonth);
    const diff = totalPayment - instCurrentBasePrice;
    
    document.getElementById('instPrepay').innerText = format(prepay);
    document.getElementById('instMonthly').innerText = format(monthlyPayment);
    document.getElementById('instGocLai').innerText = format(Math.round(monthlyNoInterest + monthlyInterest));
    document.getElementById('instTotal').innerText = format(totalPayment);
    document.getElementById('instDiff').innerText = format(diff > 0 ? diff : 0);
}

// --- Promo Success ---
function showPromoSuccess() {
    const modal = document.getElementById('promoSuccessModal');
    modal.style.display = 'flex';
    // Clear form inputs
    const form = document.querySelector('form[action="#"]');
    if(form) form.reset();
    
    // Auto hide after 2.5 seconds
    setTimeout(() => {
        modal.style.display = 'none';
    }, 2500);
}

// --- Scroll to show Sticky Bottom Bar ---
window.addEventListener('scroll', function() {
    const bottomBar = document.getElementById('bottomActionBar');
    if (bottomBar) {
        // Hiển thị thanh bottom nếu người dùng cuộn xuống quá 400px (tức là qua phần hiển thị giá ở trên)
        if (window.scrollY > 400) {
            bottomBar.classList.add('show');
        } else {
            bottomBar.classList.remove('show');
        }
    }
});
</script>
@endpush
