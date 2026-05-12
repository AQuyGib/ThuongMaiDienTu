@extends('layouts.app')

@section('title', 'TechZone - Hệ thống bán lẻ điện thoại di động, máy tính')

@push('styles')
    <style>
        /* Slider & Menu Section */
        .hero-section {
            margin-top: 20px;
            display: flex;
            gap: 15px;
        }

        /* Menu dọc bên trái */
        .category-menu {
            width: 230px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            flex-shrink: 0;
        }

        .category-menu ul li a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-color);
            transition: 0.2s;
            border-bottom: 1px solid var(--border-color);
        }

        .category-menu ul li:last-child a {
            border-bottom: none;
        }

        .category-menu ul li a .menu-icon {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-menu ul li a i.main-icon {
            width: 20px;
            text-align: center;
            color: #777;
            font-size: 16px;
        }

        .category-menu ul li a:hover {
            background-color: #f0f7ff;
            color: var(--primary-color);
        }

        .category-menu ul li a:hover i.main-icon {
            color: var(--primary-color);
        }

        /* Banner chính */
        .hero-banner {
            flex: 1;
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-height: 380px; /* Thu nhỏ chiều cao của banner */
        }

        .hero-banner .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-banner .swiper-button-next,
        .hero-banner .swiper-button-prev {
            color: var(--white);
            background: rgba(0,0,0,0.3);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transform: scale(0.6);
        }

        .hero-banner .swiper-pagination-bullet-active {
            background: var(--primary-color);
        }

        /* Section Danh mục nổi bật */
        .section-title {
            font-size: 22px;
            font-weight: 800;
            margin: 35px 0 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            color: var(--text-color);
        }

        /* Flash Sale Section */
        .flash-sale-section {
            background: linear-gradient(135deg, #d70018 0%, #ff6a00 100%);
            border-radius: 15px;
            padding: 20px;
            margin-top: 30px;
            position: relative;
        }

        .flash-sale-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .flash-title {
            font-size: 28px;
            font-weight: 900;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            font-style: italic;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .countdown {
            display: flex;
            gap: 5px;
            align-items: center;
            color: var(--white);
            font-weight: 600;
        }

        .countdown-box {
            background: var(--white);
            color: var(--secondary-color);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .product-grid-white {
            background: var(--white);
            border-radius: 12px;
            padding: 15px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Thẻ Sản Phẩm */
        .product-card {
            background: var(--white);
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-grid .product-card {
            /* Trong flash sale có bg trắng nổi bật */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .product-grid-white .product-card {
            border: 1px solid var(--border-color);
        }

        .product-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
            border-color: var(--primary-color);
            z-index: 10;
        }

        .badge-top-left {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #f1f2f6;
            color: var(--primary-color);
            font-size: 10px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
            z-index: 10;
            text-transform: uppercase;
        }

        .badge-top-right {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--secondary-color);
            color: white;
            font-size: 12px;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(215, 0, 24, 0.3);
        }

        .product-img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            margin-bottom: 15px;
            transition: transform 0.3s;
        }

        .product-card:hover .product-img {
            transform: scale(1.05);
        }

        .product-name {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            height: 40px;
            line-height: 1.4;
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-word;
        }

        .product-name:hover {
            color: var(--primary-color);
        }

        .product-price {
            font-size: 16px;
            font-weight: 800;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }

        .product-old-price {
            font-size: 13px;
            color: #888;
            text-decoration: line-through;
            margin-bottom: 5px;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #f59e0b;
            margin-top: auto;
        }

        .product-rating span {
            color: #888;
            margin-left: 5px;
        }

        /* Các nhãn nhỏ (tags) */
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 10px;
        }

        .tag {
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 4px;
            background: #f1f2f6;
            color: #555;
        }

        /* Quick Links Ngang */
        .quick-links {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            overflow-x: auto;
        }

        .quick-link-item {
            background: var(--white);
            border-radius: 10px;
            padding: 15px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            min-width: 120px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: 0.2s;
        }

        .quick-link-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: var(--primary-color);
        }

        .quick-link-item img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .quick-link-item span {
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }

        /* Category badges cho section sản phẩm */
        .category-badge {
            display: inline-block;
            background: #eef2ff;
            color: var(--primary-color);
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
            color: #ddd;
        }
    </style>
@endpush

@php
    // Map icon cho từng danh mục trong sidebar dọc
    $sidebarIcons = [
        'Điện thoại'          => 'fa-mobile-screen-button',
        'Laptop'              => 'fa-laptop',
        'Tablet'              => 'fa-tablet-screen-button',
        'Âm thanh'            => 'fa-headphones',
        'Đồng hồ thông minh' => 'fa-clock',
        'Phụ kiện'            => 'fa-keyboard',
        'Tivi, Màn hình'      => 'fa-tv',
        'Gia dụng, Smarthome' => 'fa-plug',
    ];

    $quickLinkIcons = [
        'Điện thoại'          => 'https://cdn-icons-png.flaticon.com/512/0/191.png',
        'Laptop'              => 'https://cdn-icons-png.flaticon.com/512/3254/3254096.png',
        'Tablet'              => 'https://cdn-icons-png.flaticon.com/512/2888/2888728.png',
        'Đồng hồ thông minh' => 'https://cdn-icons-png.flaticon.com/512/3052/3052562.png',
        'Âm thanh'            => 'https://cdn-icons-png.flaticon.com/512/2933/2933100.png',
        'Gia dụng, Smarthome' => 'https://cdn-icons-png.flaticon.com/512/10002/10002279.png',
        'Phụ kiện'            => 'https://cdn-icons-png.flaticon.com/512/3254/3254215.png',
        'Tivi, Màn hình'      => 'https://cdn-icons-png.flaticon.com/512/2289/2289243.png',
    ];
@endphp

@section('content')
    <div class="container">
        <!-- Hero Section (Menu + Banner) -->
        <div class="hero-section">
            <!-- Sidebar Menu - DỮ LIỆU ĐỘNG TỪ DB -->
            <div class="category-menu">
                <ul>
                    @foreach($categories as $cat)
                        <li>
                            <a href="#">
                                <div class="menu-icon">
                                    <i class="fa-solid {{ $sidebarIcons[$cat->name] ?? 'fa-tag' }} main-icon"></i>
                                    {{ $cat->name }}
                                </div>
                                <i class="fa-solid fa-angle-right text-xs text-gray-400"></i>
                            </a>
                        </li>
                    @endforeach
                    {{-- Các mục tĩnh bổ sung --}}
                    <li>
                        <a href="#">
                            <div class="menu-icon">
                                <i class="fa-solid fa-gamepad main-icon"></i> Thu cũ đổi mới
                            </div>
                            <i class="fa-solid fa-angle-right text-xs text-gray-400"></i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('articles.index') }}">
                            <div class="menu-icon">
                                <i class="fa-solid fa-newspaper main-icon"></i> Tin công nghệ
                            </div>
                            <i class="fa-solid fa-angle-right text-xs text-gray-400"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Banner Slider (Swiper) -->
        <div class="hero-banner swiper mySwiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="https://images.unsplash.com/photo-1593640495253-23196b27a87f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Khuyến mãi Laptop">
                </div>
                <div class="swiper-slide">
                    <img src="https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="S24 Ultra Giảm Sốc">
                </div>
                <div class="swiper-slide">
                    <img src="https://images.unsplash.com/photo-1605236453806-6ff36851218e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="iPhone 15 Pro Max">
                </div>
            </div>
            <!-- Nút điều hướng -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
        </div>

        <!-- Quick Links - DỮ LIỆU ĐỘNG TỪ DB -->
        <div class="quick-links">
            @foreach($categories as $cat)
                <a href="#" class="quick-link-item">
                    <img src="{{ $quickLinkIcons[$cat->name] ?? 'https://cdn-icons-png.flaticon.com/512/1261/1261163.png' }}" alt="{{ $cat->name }}">
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>

        <!-- Flash Sale Section - DỮ LIỆU ĐỘNG TỪ DB -->
        @if($flashSaleProducts->count())
            <div class="flash-sale-section">
                <div class="flash-sale-header">
                    <div class="flash-title">
                        <i class="fa-solid fa-bolt"></i> F L A S H S A L E
                    </div>
                    <div class="countdown">
                        <span>Kết thúc trong:</span>
                        <span class="countdown-box" id="countdown-h">02</span> :
                        <span class="countdown-box" id="countdown-m">45</span> :
                        <span class="countdown-box" id="countdown-s">30</span>
                    </div>
                </div>

                <div class="product-grid">
                    @foreach($flashSaleProducts as $product)
                        <a href="{{ route('product.show', $product->product_id) }}" class="product-card">
                            <span class="badge-top-left">Trả góp 0%</span>
                            @if($product->old_price)
                                @php
                                    $discount = round((($product->old_price - $product->base_price) / $product->old_price) * 100);
                                @endphp
                                @if($discount > 0)
                                    <span class="badge-top-right">-{{ $discount }}%</span>
                                @endif
                            @endif

                            <img src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300' }}"
                                alt="{{ $product->name }}" class="product-img" loading="lazy">

                            <span class="category-badge">{{ $product->category->name ?? '' }}</span>

                            <h3 class="product-name">{{ $product->name }}</h3>
                            <div class="product-price">{{ number_format($product->base_price, 0, ',', '.') }}đ</div>
                            @if($product->old_price)
                                <div class="product-old-price">{{ number_format($product->old_price, 0, ',', '.') }}đ</div>
                            @else
                                <div class="product-old-price" style="visibility: hidden;">0đ</div>
                            @endif

                            <div class="product-rating">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star-half-stroke"></i>
                                <span>({{ rand(10, 500) }})</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Điện thoại nổi bật - DỮ LIỆU ĐỘNG TỪ DB -->
        @if($phoneProducts->count())
            <div class="section-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h2 class="section-title"><i class="fa-solid fa-mobile-screen-button"></i> ĐIỆN THOẠI NỔI BẬT NHẤT</h2>
                <a href="#" style="color:var(--primary-color); font-size:14px; font-weight:600;">Xem tất cả <i class="fa-solid fa-angle-right"></i></a>
            </div>
            <div class="product-grid-white">
                @foreach($phoneProducts as $product)
                    <a href="{{ route('product.show', $product->product_id) }}" class="product-card">
                        <span class="badge-top-left">Trả góp 0%</span>
                        @if($product->old_price && $product->old_price > $product->base_price)
                            @php
                                $discount = round((($product->old_price - $product->base_price) / $product->old_price) * 100);
                            @endphp
                            <span class="badge-top-right">-{{ $discount }}%</span>
                        @endif

                        <img src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=300' }}"
                            alt="{{ $product->name }}" class="product-img" loading="lazy">

                        <h3 class="product-name">{{ $product->name }}</h3>
                        <div class="product-price">{{ number_format($product->base_price, 0, ',', '.') }}đ</div>
                        @if($product->old_price)
                            <div class="product-old-price">{{ number_format($product->old_price, 0, ',', '.') }}đ</div>
                        @else
                            <div class="product-old-price" style="visibility: hidden;">0đ</div>
                        @endif

                        <div class="product-rating">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <span>({{ rand(10, 500) }})</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Laptop nổi bật - DỮ LIỆU ĐỘNG TỪ DB -->
        @if($laptopProducts->count())
            <div class="section-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h2 class="section-title"><i class="fa-solid fa-laptop"></i> LAPTOP GIÁ SỐC</h2>
                <a href="#" style="color:var(--primary-color); font-size:14px; font-weight:600;">Xem tất cả <i class="fa-solid fa-angle-right"></i></a>
            </div>
            <div class="product-grid-white">
                @foreach($laptopProducts as $product)
                    <a href="{{ route('product.show', $product->product_id) }}" class="product-card">
                        @if($product->old_price && $product->old_price > $product->base_price)
                            @php
                                $discount = round((($product->old_price - $product->base_price) / $product->old_price) * 100);
                            @endphp
                            <span class="badge-top-right">-{{ $discount }}%</span>
                        @endif

                        <img src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1531297172867-11dcd459d243?w=300' }}"
                            alt="{{ $product->name }}" class="product-img" loading="lazy">

                        <h3 class="product-name">{{ $product->name }}</h3>
                        <div class="product-price">{{ number_format($product->base_price, 0, ',', '.') }}đ</div>
                        @if($product->old_price)
                            <div class="product-old-price">{{ number_format($product->old_price, 0, ',', '.') }}đ</div>
                        @else
                            <div class="product-old-price" style="visibility: hidden;">0đ</div>
                        @endif

                        <div class="product-rating">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star-half-stroke"></i>
                            <span>({{ rand(5, 100) }})</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
        <!-- Góc Tin tức & Lifestyle - GIỐNG SFORUM -->
        @if($latestArticles->count())
            <div class="news-section" style="margin-top: 40px; margin-bottom: 40px;">
                <div class="news-header" style="display: flex; align-items: center; margin-bottom: 20px;">
                    <h2 style="font-size: 22px; font-weight: 800; text-transform: uppercase; color: #333; margin: 0;">TIN TỨC</h2>
                    <span style="color: #ccc; margin: 0 15px; font-size: 20px;">|</span>
                    <a href="{{ route('articles.index') }}" style="color: #2b6cb0; font-size: 14px; font-weight: 500; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                        Xem tất cả <i class="fa-solid fa-chevron-right" style="font-size: 12px;"></i>
                    </a>
                </div>

                <div class="news-grid" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px;">
                    @foreach($latestArticles as $article)
                        <a href="{{ route('articles.show', $article->slug) }}" class="news-card" style="background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-decoration: none; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">
                            <div class="news-img-wrapper" style="padding: 10px 10px 0 10px;">
                                <img src="{{ $article->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300' }}" alt="{{ $article->title }}" style="width: 100%; aspect-ratio: 16/9; object-fit: cover; border-radius: 8px;">
                            </div>
                            <div class="news-info" style="padding: 12px;">
                                <h3 style="font-size: 14px; font-weight: 600; color: #333; line-height: 1.5; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word;">
                                    {{ $article->title }}
                                </h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            <style>
                .news-card:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.1) !important; }
                .news-card:hover h3 { color: #d70018 !important; } /* Màu đỏ thương hiệu khi hover */
            </style>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    // Khởi tạo Swiper Banner
    document.addEventListener("DOMContentLoaded", function() {
        if(typeof Swiper !== 'undefined') {
            var swiper = new Swiper(".mySwiper", {
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
        }
    });

    // Countdown timer cho Flash Sale
    (function() {
        let totalSeconds = 2 * 3600 + 45 * 60 + 30;
        const hEl = document.getElementById('countdown-h');
        const mEl = document.getElementById('countdown-m');
        const sEl = document.getElementById('countdown-s');

        if (!hEl || !mEl || !sEl) return;

        setInterval(() => {
            if (totalSeconds <= 0) return;
            totalSeconds--;
            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = totalSeconds % 60;
            hEl.textContent = String(h).padStart(2, '0');
            mEl.textContent = String(m).padStart(2, '0');
            sEl.textContent = String(s).padStart(2, '0');
        }, 1000);
    })();
</script>
@endpush