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
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .hero-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
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
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
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
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .product-grid-white .product-card {
        border: 1px solid var(--border-color);
    }

    .product-card:hover {
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
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
        box-shadow: 0 2px 5px rgba(215,0,24,0.3);
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
        overflow: hidden;
        height: 40px;
        line-height: 1.4;
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
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: 0.2s;
    }

    .quick-link-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
</style>
@endpush

@section('content')
<div class="container">
    <!-- Hero Section (Menu + Banner) -->
    <div class="hero-section">
        <!-- Sidebar Menu -->
        <div class="category-menu">
            <ul>
                <li><a href="#"><div class="menu-icon"><i class="fa-solid fa-mobile-screen-button main-icon"></i> Điện thoại, Tablet</div> <i class="fa-solid fa-angle-right text-xs text-gray-400"></i></a></li>
                <li><a href="#"><div class="menu-icon"><i class="fa-solid fa-laptop main-icon"></i> Laptop</div> <i class="fa-solid fa-angle-right text-xs text-gray-400"></i></a></li>
                <li><a href="#"><div class="menu-icon"><i class="fa-solid fa-headphones main-icon"></i> Âm thanh</div> <i class="fa-solid fa-angle-right text-xs text-gray-400"></i></a></li>
                <li><a href="#"><div class="menu-icon"><i class="fa-regular fa-clock main-icon"></i> Đồng hồ, Camera</div> <i class="fa-solid fa-angle-right text-xs text-gray-400"></i></a></li>
                <li><a href="#"><div class="menu-icon"><i class="fa-solid fa-plug main-icon"></i> Gia dụng, Smarthome</div> <i class="fa-solid fa-angle-right text-xs text-gray-400"></i></a></li>
                <li><a href="#"><div class="menu-icon"><i class="fa-solid fa-keyboard main-icon"></i> Phụ kiện, Máy in</div> <i class="fa-solid fa-angle-right text-xs text-gray-400"></i></a></li>
                <li><a href="#"><div class="menu-icon"><i class="fa-solid fa-tv main-icon"></i> Tivi, Màn hình</div> <i class="fa-solid fa-angle-right text-xs text-gray-400"></i></a></li>
                <li><a href="#"><div class="menu-icon"><i class="fa-solid fa-gamepad main-icon"></i> Thu cũ đổi mới</div> <i class="fa-solid fa-angle-right text-xs text-gray-400"></i></a></li>
                <li><a href="#"><div class="menu-icon"><i class="fa-solid fa-tags main-icon"></i> Hàng cũ giá rẻ</div> <i class="fa-solid fa-angle-right text-xs text-gray-400"></i></a></li>
                <li><a href="#"><div class="menu-icon"><i class="fa-solid fa-newspaper main-icon"></i> Tin công nghệ</div> <i class="fa-solid fa-angle-right text-xs text-gray-400"></i></a></li>
            </ul>
        </div>

        <!-- Banner -->
        <div class="hero-banner">
            <img src="https://images.unsplash.com/photo-1593640495253-23196b27a87f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Banner Khuyến Mãi">
        </div>
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <a href="#" class="quick-link-item">
            <img src="https://cdn-icons-png.flaticon.com/512/0/191.png" alt="Phone">
            <span>Điện thoại</span>
        </a>
        <a href="#" class="quick-link-item">
            <img src="https://cdn-icons-png.flaticon.com/512/3254/3254096.png" alt="Laptop">
            <span>Laptop</span>
        </a>
        <a href="#" class="quick-link-item">
            <img src="https://cdn-icons-png.flaticon.com/512/2888/2888728.png" alt="Tablet">
            <span>Tablet</span>
        </a>
        <a href="#" class="quick-link-item">
            <img src="https://cdn-icons-png.flaticon.com/512/3052/3052562.png" alt="Smartwatch">
            <span>Đồng hồ thông minh</span>
        </a>
        <a href="#" class="quick-link-item">
            <img src="https://cdn-icons-png.flaticon.com/512/2933/2933100.png" alt="Airpods">
            <span>Tai nghe</span>
        </a>
        <a href="#" class="quick-link-item">
            <img src="https://cdn-icons-png.flaticon.com/512/10002/10002279.png" alt="Smarthome">
            <span>Smarthome</span>
        </a>
        <a href="#" class="quick-link-item">
            <img src="https://cdn-icons-png.flaticon.com/512/3254/3254215.png" alt="Phụ kiện">
            <span>Phụ kiện</span>
        </a>
    </div>

    <!-- Flash Sale Section -->
    <div class="flash-sale-section">
        <div class="flash-sale-header">
            <div class="flash-title">
                <i class="fa-solid fa-bolt"></i> F L A S H  S A L E
            </div>
            <div class="countdown">
                <span>Kết thúc trong:</span>
                <span class="countdown-box">02</span> : 
                <span class="countdown-box">45</span> : 
                <span class="countdown-box">30</span>
            </div>
        </div>
        
        <div class="product-grid">
            <!-- Product Item 1 -->
            <a href="#" class="product-card">
                <span class="badge-top-left">Trả góp 0%</span>
                <span class="badge-top-right">-25%</span>
                <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300" alt="iPhone 15 Pro Max" class="product-img">
                <h3 class="product-name">iPhone 15 Pro Max 256GB | Chính hãng VN/A</h3>
                <div class="product-price">29.490.000đ</div>
                <div class="product-old-price">34.990.000đ</div>
                <div class="product-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star-half-stroke"></i>
                    <span>(128)</span>
                </div>
            </a>

            <!-- Product Item 2 -->
            <a href="#" class="product-card">
                <span class="badge-top-left">Trả góp 0%</span>
                <span class="badge-top-right">-15%</span>
                <img src="https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=300" alt="Samsung Galaxy S24 Ultra" class="product-img">
                <h3 class="product-name">Samsung Galaxy S24 Ultra 5G 256GB</h3>
                <div class="product-price">26.990.000đ</div>
                <div class="product-old-price">33.990.000đ</div>
                <div class="product-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <span>(86)</span>
                </div>
            </a>

            <!-- Product Item 3 -->
            <a href="#" class="product-card">
                <span class="badge-top-right">-20%</span>
                <img src="https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=300" alt="MacBook Air M1" class="product-img">
                <h3 class="product-name">Apple MacBook Air M1 256GB 2020 Chính hãng</h3>
                <div class="product-price">18.490.000đ</div>
                <div class="product-old-price">22.990.000đ</div>
                <div class="product-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-regular fa-star"></i>
                    <span>(324)</span>
                </div>
            </a>

            <!-- Product Item 4 -->
            <a href="#" class="product-card">
                <span class="badge-top-left">Trả góp 0%</span>
                <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=300" alt="Apple Watch" class="product-img">
                <h3 class="product-name">Apple Watch Series 9 GPS 41mm Viền Nhôm Dây Thể Thao</h3>
                <div class="product-price">9.290.000đ</div>
                <div class="product-old-price">10.490.000đ</div>
                <div class="product-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <span>(45)</span>
                </div>
            </a>

            <!-- Product Item 5 -->
            <a href="#" class="product-card">
                <span class="badge-top-right">-30%</span>
                <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300" alt="Tai nghe AirPods" class="product-img">
                <h3 class="product-name">Tai nghe Bluetooth AirPods Pro 2 MagSafe (USB-C) Chính hãng</h3>
                <div class="product-price">5.590.000đ</div>
                <div class="product-old-price">6.990.000đ</div>
                <div class="product-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star-half-stroke"></i>
                    <span>(210)</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Điện thoại nổi bật -->
    <h2 class="section-title">ĐIỆN THOẠI NỔI BẬT NHẤT</h2>
    <div class="product-grid-white">
        <!-- Vòng lặp giả lập 10 sản phẩm -->
        @for($i = 0; $i < 10; $i++)
        <a href="#" class="product-card">
            <span class="badge-top-left">Trả góp 0%</span>
            @if($i % 3 == 0)
            <span class="badge-top-right">-10%</span>
            @endif
            <img src="https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=300" alt="Phone" class="product-img">
            
            <!-- Tags -->
            <div class="tags-container">
                <span class="tag">Màn 120Hz</span>
                <span class="tag">Sạc nhanh 65W</span>
            </div>

            <h3 class="product-name">Smartphone Cao Cấp {{ $i + 1 }} - 8GB/256GB - Chính hãng</h3>
            <div class="product-price">{{ number_format((rand(8, 25) * 1000000) + 990000, 0, ',', '.') }}đ</div>
            <div class="product-old-price">{{ number_format((rand(26, 30) * 1000000) + 990000, 0, ',', '.') }}đ</div>
            <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span>({{ rand(10, 500) }})</span>
            </div>
        </a>
        @endfor
    </div>
    
    <!-- Laptop nổi bật -->
    <h2 class="section-title">LAPTOP GIÁ SỐC</h2>
    <div class="product-grid-white">
        @for($i = 0; $i < 5; $i++)
        <a href="#" class="product-card">
            <img src="https://images.unsplash.com/photo-1531297172867-11dcd459d243?w=300" alt="Laptop" class="product-img">
            
            <div class="tags-container">
                <span class="tag">Core i5</span>
                <span class="tag">RAM 16GB</span>
                <span class="tag">SSD 512GB</span>
            </div>

            <h3 class="product-name">Laptop Gaming Mẫu {{ $i + 1 }} 2024 Chính hãng</h3>
            <div class="product-price">{{ number_format((rand(15, 30) * 1000000) + 990000, 0, ',', '.') }}đ</div>
            <div class="product-old-price">{{ number_format((rand(31, 40) * 1000000) + 990000, 0, ',', '.') }}đ</div>
            <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half-stroke"></i>
                <span>({{ rand(5, 100) }})</span>
            </div>
        </a>
        @endfor
    </div>
</div>
@endsection
