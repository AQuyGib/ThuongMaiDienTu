@extends('layouts.app')

@section('title', 'DienMayPRO - Hệ thống bán lẻ điện thoại di động, máy tính')

@push('styles')
    <!-- CSS TÙY CHỈNH CHO TRANG CHỦ DIENMAYPRO (LUỒNG GIAO DIỆN PREMIUM VÀ HIỆU ỨNG DỰNG ĐỘNG) -->
    <style>
        /* ============================================================
           1. HERO SECTION: KHUNG BỐ CỤC CHÍNH BÊN DƯỚI HEADER
           Kết hợp Menu dọc (Flyout Mega Menu) bên trái và Banner slider bên phải.
           ============================================================ */
        .hero-section {
            margin-top: 20px;
            display: flex;
            gap: 15px;
        }

        /* 1.1. MENU DỌC BÊN TRÁI - PHÂN HỆ DANH MỤC TRÊN TRANG CHỦ
           Được thiết kế tinh gọn dạng thẻ nổi (Floating Card) với bo góc mềm mại 16px.
           Thiết lập position: relative để bảng điều khiển Mega Menu con (Flyout) 
           định vị chính xác theo lề của menu cha.
        */
        .category-menu {
            position: relative; 
            width: 230px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            flex-shrink: 0;
            border: 1px solid rgba(0,0,0,0.03);
            padding: 8px 0;
        }

        /* Định dạng liên kết từng danh mục */
        .category-menu ul li a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 18px;
            font-size: 14px;
            font-weight: 600;
            color: #444;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        /* Khối chứa icon và văn bản của danh mục */
        .category-menu ul li a .menu-icon {
            display: flex;
            align-items: center;
            gap: 12px;
            transition: transform 0.3s ease;
        }

        /* Biểu tượng đại diện (FontAwesome) */
        .category-menu ul li a i.main-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 17px;
            transition: all 0.3s ease;
        }

        /* Biểu tượng mũi tên đi sang phải báo hiệu có menu con */
        .category-menu ul li a .fa-angle-right {
            font-size: 11px;
            color: #ccc;
            transition: all 0.3s ease;
        }

        /* Hiệu ứng Hover:
           - background chuyển sang màu xanh dương nhạt dịu mát.
           - Đẩy văn bản sang phải 4px tạo hiệu ứng chuyển động mượt mà.
           - Phóng to nhẹ icon chính và đẩy mũi tên sang phải.
        */
        .category-menu ul li a:hover {
            background-color: #f5f8ff;
            color: var(--primary-color);
            padding-left: 22px; 
        }

        .category-menu ul li a:hover i.main-icon {
            color: var(--primary-color);
            transform: scale(1.15);
        }

        .category-menu ul li a:hover .fa-angle-right {
            color: var(--primary-color);
            transform: translateX(3px);
        }

        /* Tạo khoảng cách nhỏ giữa các dòng menu */
        .category-menu ul li:not(:last-child) {
            margin-bottom: 2px;
        }

        /* 1.2. HỆ THỐNG NHÃN NỔI BẬT (BADGES) TRÊN MENU
           - Nhãn HOT (Đỏ cam gradient) đại diện cho các ngành hàng bán chạy (Điện thoại, Laptop).
           - Nhãn NEW (Xanh lục gradient) cho các dòng sản phẩm mới (Smartwatch).
           - Tích hợp hiệu ứng nhấp nháy phát sáng (pulseGlow) bằng CSS Keyframe để thu hút tầm nhìn.
        */
        .menu-badge-hot {
            background: var(--primary-gradient);
            color: white;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 800;
            margin-left: 8px;
            animation: pulseGlow 2s infinite;
        }
        .menu-badge-new {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 800;
            margin-left: 8px;
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(215, 0, 24, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(215, 0, 24, 0); }
            100% { box-shadow: 0 0 0 0 rgba(215, 0, 24, 0); }
        }

        /* 1.3. BANNER SLIDER CHÍNH (SWIPER SLIDE) BÊN PHẢI
           Tự động co giãn theo tỷ lệ khung hình chuẩn 1200x380 để không bị vỡ ảnh trên các màn hình lớn.
        */
        .hero-banner {
            flex: 1;
            background: #f0f0f0;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            aspect-ratio: 1200 / 380;
            height: auto;
        }

        .hero-banner .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Thu nhỏ nhẹ các nút bấm Next/Prev điều hướng của Swiper và tạo nền mờ tối */
        .hero-banner .swiper-button-next,
        .hero-banner .swiper-button-prev {
            color: var(--white);
            background: rgba(0,0,0,0.3);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            transform: scale(0.7);
        }

        /* Đổi màu chấm định vị Swiper active sang màu đỏ/xanh thương hiệu */
        .hero-banner .swiper-pagination-bullet-active {
            background: var(--primary-color);
        }

        /* Tiêu đề chung cho các phần danh mục sản phẩm trên trang */
        .section-title {
            font-size: 20px;
            font-weight: 800;
            margin: 30px 0 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            color: var(--text-color);
        }

        /* ============================================================
           2. QUICK LINKS NGANG (DANH MỤC TRUY CẬP NHANH)
           Thiết kế dạng danh sách cuộn ngang (scrollbar ẩn hoàn toàn) giúp người dùng 
           trên di động dễ dàng vuốt chạm bằng ngón tay.
           ============================================================ */
        .quick-links {
            display: flex;
            gap: 12px;
            margin: 20px 0;
            overflow-x: auto;
            padding-bottom: 10px;
            scrollbar-width: none; /* Ẩn thanh cuộn trên Firefox */
        }
        
        .quick-links::-webkit-scrollbar {
            display: none; /* Ẩn thanh cuộn trên Chrome/Safari */
        }

        /* Thẻ liên kết nhanh:
           Tạo hiệu ứng nổi 3D (phóng to, nghiêng nhẹ icon và đổ bóng rực đỏ) khi hover.
        */
        .quick-link-item {
            background: var(--white);
            border-radius: 18px;
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-width: 110px;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(0,0,0,0.02);
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .quick-link-item:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 15px 30px rgba(215, 0, 24, 0.12);
            border-color: rgba(215, 0, 24, 0.1);
            color: var(--primary-color);
        }

        .quick-link-item img {
            width: 42px;
            height: 42px;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
            transition: transform 0.4s ease;
        }

        .quick-link-item:hover img {
            transform: scale(1.15) rotate(5deg);
        }

        .quick-link-item span {
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            line-height: 1.3;
            color: var(--text-main);
        }

        /* ============================================================
           3. PHÂN HỆ FLASH SALE ĐÈN LED ĐẾM NGƯỢC
           Tông màu chủ đạo chuyển sắc đỏ cam rực cháy.
           ============================================================ */
        .flash-sale-section {
            margin-top: 30px;
            position: relative;
        }

        .flash-sale-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        /* Tiêu đề chữ chạy chuyển sắc (Text Gradient Clip) */
        .flash-title {
            font-size: 26px;
            font-weight: 900;
            color: #d70018;
            display: flex;
            align-items: center;
            gap: 12px;
            text-transform: uppercase;
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(90deg, #d70018, #ff4b2b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Hộp hiển thị số giờ/phút/giây dạng LED phong cách neon */
        .countdown-box {
            background: linear-gradient(180deg, #d70018 0%, #a50012 100%);
            color: var(--white);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(215, 0, 24, 0.3);
            min-width: 45px;
            text-align: center;
        }

        /* 3.1. THANH TIẾN ĐỘ SẢN PHẨM FLASH SALE (LAVA PROGRESS BAR)
           Mô tả mức độ cháy hàng của sản phẩm. 
           Sử dụng CSS Animation dịch chuyển background liên tục (`lavaFlow`) tạo cảm giác 
           dòng dung nham rực lửa cuộn trào sinh động.
        */
        .fs-progress-wrapper {
            margin-top: 15px;
            background: #fee2e2;
            border-radius: 20px;
            height: 22px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(215, 0, 24, 0.1);
        }
        .fs-progress-bar {
            background: linear-gradient(90deg, #f87171, #ef4444, #dc2626);
            background-size: 200% 100%;
            animation: lavaFlow 2s linear infinite;
            height: 100%;
            border-radius: 20px;
        }
        @keyframes lavaFlow {
            0% { background-position: 100% 0%; }
            100% { background-position: 0% 0%; }
        }
        .fs-progress-text {
            position: absolute;
            width: 100%;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            text-shadow: 0px 0px 3px rgba(0,0,0,0.5);
            z-index: 2;
        }
        .fs-fire-icon {
            position: absolute;
            left: 5px;
            color: #fff;
            font-size: 12px;
            z-index: 2;
        }

        /* Lưới sản phẩm mặc định: 5 cột bằng nhau trải đều */
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

        /* 3.2. THẺ SẢN PHẨM MẶC ĐỊNH
           Tự động co dãn cột dọc, có hiệu ứng zoom ảnh nhẹ và đổ bóng khi hover chuột.
        */
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

        /* Nhãn Trả góp ở góc trên bên trái */
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

        /* Nhãn phần trăm giảm giá ở góc trên bên phải */
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

        /* Cố định tên sản phẩm hiển thị trong đúng 2 dòng, tránh vỡ layout lưới */
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

        /* ============================================================
           4. DYNAMIC PRODUCTS SECTIONS (CÁC KHUNG SẢN PHẨM KHỔ LỚN)
           Cơ cấu bố cục chia đôi: Banner đứng quảng cáo bên trái + Lưới sản phẩm bên phải.
           Hỗ trợ bộ Tabs chuyển đổi dữ liệu bằng AJAX.
           ============================================================ */
        .product-section-wrapper {
            margin: 40px 0;
            background: var(--white);
            border-radius: 24px;
            padding: 25px;
            box-shadow: var(--shadow-premium);
            display: flex;
            gap: 25px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.03);
        }

        /* Họa tiết trang trí dạng bóng tròn mờ ảo ở góc phải */
        .product-section-wrapper::before {
            content: "";
            position: absolute;
            top: -60px;
            right: -60px;
            width: 250px;
            height: 250px;
            background: var(--primary-gradient);
            opacity: 0.04;
            border-radius: 50%;
            z-index: 0;
            filter: blur(40px);
        }

        /* Banner dọc trang trí bên trái */
        .section-sidebar-banner {
            width: 200px;
            flex-shrink: 0;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            z-index: 1;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            align-self: stretch; 
        }

        .section-sidebar-banner:hover {
            transform: scale(1.03) rotate(-1deg);
            box-shadow: 0 20px 45px rgba(0,0,0,0.15);
        }

        .section-sidebar-banner a {
            display: block;
            width: 100%;
            height: 100%;
        }

        .section-sidebar-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            display: block;
            transition: transform 0.8s ease;
        }

        .section-main-content {
            flex: 1;
            position: relative;
            z-index: 1;
            min-width: 0;
        }

        .section-header-premium {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 18px;
        }

        .section-title-premium {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-color);
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }

        /* 4.1. TABS CHUYỂN ĐỔI SẢN PHẨM KHÔNG TẢI LẠI TRANG (AJAX TABS) */
        .section-tabs {
            display: flex;
            gap: 12px;
            align-items: center; 
        }

        .section-tab-item {
            padding: 8px 20px;
            height: 38px; 
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s;
            background: #f1f5f9;
            border: 1px solid rgba(0,0,0,0.05);
            white-space: nowrap;
        }

        /* Trạng thái tab đang được chọn: chuyển thành màu gradient xanh đậm */
        .section-tab-item.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 70, 171, 0.25);
            border: none;
        }

        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }

        .filter-tag-item {
            padding: 8px 16px;
            background: #f1f5f9;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            transition: all 0.2s;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .filter-tag-item:hover {
            background: #e2e8f0;
            color: var(--primary-color);
            border-color: rgba(0, 70, 171, 0.1);
        }

        /* 4.2. THẺ SẢN PHẨM BẢN PREMIUM (ĐỔI MỚI UX)
           Bổ sung thêm cụm nút chức năng tròn (wishlist, giỏ hàng nhanh) nổi lên 
           khi di chuột qua thẻ sản phẩm.
        */
        .product-card-premium {
            background: var(--white);
            border-radius: 18px;
            padding: 12px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            border: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 380px; 
        }

        .product-card-premium:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px -10px rgba(0, 70, 171, 0.15);
            border-color: rgba(0, 70, 171, 0.1);
        }

        .product-img {
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .product-card-premium:hover .product-img {
            transform: scale(1.12) rotate(2deg);
        }

        .badge-container {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            z-index: 5;
        }

        .badge-promo, .badge-installment {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            width: fit-content;
        }

        .badge-promo {
            background: #d70018;
            color: white;
        }

        .badge-installment {
            background: #f0f7ff;
            color: var(--primary-color);
            border: 1px solid rgba(0, 70, 171, 0.1);
        }

        .product-card-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            opacity: 0;
            transform: translateX(10px);
            transition: 0.3s ease;
            z-index: 10;
        }

        .product-card-premium:hover .product-card-actions {
            opacity: 1;
            transform: translateX(0);
        }

        .action-btn-circle {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.9);
            border: 1px solid #eee;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #666;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .action-btn-circle:hover {
            transform: scale(1.1);
        }

        .btn-wishlist:hover {
            background: #fff1f2;
            color: #e11d48;
            border-color: #fda4af;
        }

        .btn-wishlist:hover i {
            font-weight: 900; 
        }

        .btn-add-cart:hover {
            background: #eff6ff;
            color: var(--primary-color);
            border-color: #bfdbfe;
        }

        /* 4.3. HIỆU ỨNG LỚP PHỦ TẢI DỮ LIỆU AJAX (AJAX LOADING OVERLAY V2)
           Phủ một lớp màu trắng mờ (opacity 0.6) có kèm hiệu ứng nhòe (backdrop-filter) 
           và vòng xoay tròn loading vô tận ở giữa để ngăn người dùng thao tác khi grid đang tải dữ liệu.
        */
        .grid-loading {
            position: relative;
            min-height: 300px;
        }
        .grid-loading::after {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 20px;
        }
        .grid-loading::before {
            content: "";
            position: absolute;
            top: 50%; left: 50%;
            width: 40px; height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            z-index: 11;
            animation: spin 1s linear infinite;
            margin: -20px 0 0 -20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Khung trống khi không tìm thấy sản phẩm nào phù hợp */
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

        /* ============================================================
           5. RESPONSIVE LAYOUT (THÍCH ỨNG TRÊN DI ĐỘNG & TABLET)
           ============================================================ */
        @media (max-width: 1024px) {
            .product-grid-white {
                grid-template-columns: repeat(3, 1fr);
            }
            .hero-banner {
                max-height: 300px;
            }
        }

        /* 5.1. MENU FLYOUT CHI TIẾT (MEGA MENU DỌC)
           Bảng điều khiển bay ngang (Flyout Mega Menu) hiển thị chi tiết các sản phẩm 
           con khi di chuột qua menu danh mục chính ở trang chủ.
        */
        .sidebar-mega-panel {
            position: absolute;
            top: 0;
            left: 100%;
            width: 700px;
            background: var(--white);
            z-index: 1000;
            margin-left: 10px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            padding: 25px;
            display: none;
            min-height: 100%;
            border: 1px solid rgba(0,0,0,0.05);
            animation: fadeInMenu 0.2s ease;
        }

        /* Vùng đệm ảo bằng trong suốt để người dùng không bị mất menu khi rê chuột chéo góc */
        .sidebar-mega-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: -15px;
            width: 15px;
            height: 100%;
        }

        @keyframes fadeInMenu {
            from { opacity: 0; transform: translateX(10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Kích hoạt hiển thị flyout menu bằng hover thuần CSS */
        .category-menu ul li:hover .sidebar-mega-panel {
            display: block;
        }

        .sidebar-mega-title {
            font-size: 14px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .sidebar-mega-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .sidebar-mega-tag {
            display: inline-block;
            padding: 8px 16px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 500;
            color: #4b5563;
            transition: all 0.2s;
        }

        .sidebar-mega-tag:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: #f0f7ff;
            transform: translateY(-2px);
        }

        .sidebar-mega-tag.see-all {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: #fff;
        }

        .mt-6 { margin-top: 24px; }
        .my-6 { margin-top: 24px; margin-bottom: 24px; }
        .border-gray-100 { border-color: #f3f4f6; }
        .mr-2 { margin-right: 8px; }
        .mr-1 { margin-right: 4px; }

        .grid-loading {
            position: relative;
            min-height: 200px;
        }
        .grid-loading::after {
            content: "Đang tải...";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            font-weight: bold;
            color: var(--primary-color);
            border-radius: 12px;
        }

        /* Lớp responsive tinh chỉnh giao diện trên màn hình Tablet và Mobile */
        @media (max-width: 1024px) {
            .sidebar-mega-panel { width: 500px; }
        }

        @media (max-width: 768px) {
            .hero-section {
                flex-direction: column;
                gap: 10px;
                margin-top: 10px;
            }
            /* Ẩn hoàn toàn menu dọc và flyout panel trên di động để nhường không gian hiển thị banner */
            .category-menu {
                display: none;
            }
            .sidebar-mega-panel { display: none !important; }
            .hero-banner {
                aspect-ratio: 16 / 9;
                width: 100%;
                border-radius: 8px;
            }
            .hero-banner .swiper-button-next,
            .hero-banner .swiper-button-prev {
                display: none;
            }
            .section-sidebar-banner {
                display: none;
            }
            .product-section-wrapper {
                flex-direction: column;
            }
            .product-grid-white {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                padding: 10px;
            }
            .product-card-premium {
                padding: 8px;
            }
            .section-tabs {
                display: none; 
            }
            .section-header-premium {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }

        @media (max-width: 480px) {
             .product-grid-white {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endpush

@php
    // Gán thủ công icon danh mục cho menu dọc (Flyout)
    $sidebarIcons = [
        'Điện thoại'          => 'fa-mobile-screen-button',
        'Laptop'              => 'fa-laptop',
        'Tablet'              => 'fa-tablet-screen-button',
        'Âm thanh'            => 'fa-headphones',
        'Đồng hồ thông minh' => 'fa-clock',
        'Phụ kiện'            => 'fa-keyboard',
        'Tivi, Màn hình'      => 'fa-tv',
        'Gia dụng, Smarthome' => 'fa-plug',

        'Smartphones'         => 'fa-mobile-screen-button',
        'Laptops'             => 'fa-laptop',
        'Tablets'             => 'fa-tablet-screen-button',
        'Sound'               => 'fa-headphones',
        'Smart watch'         => 'fa-clock',
        'Accessory'           => 'fa-keyboard',
        'TV, Monitor'         => 'fa-tv',
        'Household appliances, Smarthome' => 'fa-plug',
    ];

    // Gán hình ảnh hoạt họa đại diện cho danh mục liên kết nhanh (Quick links)
    $quickLinkIcons = [
        'Điện thoại'          => 'https://cdn-icons-png.flaticon.com/512/3616/3616856.png',
        'Laptop'              => 'https://cdn-icons-png.flaticon.com/512/428/428001.png',
        'Tablet'              => 'https://cdn-icons-png.flaticon.com/512/3616/3616874.png',
        'Đồng hồ thông minh' => 'https://cdn-icons-png.flaticon.com/512/2972/2972185.png',
        'Âm thanh'            => 'https://cdn-icons-png.flaticon.com/512/3659/3659899.png',
        'Gia dụng, Smarthome' => 'https://cdn-icons-png.flaticon.com/512/2585/2585175.png',
        'Phụ kiện'            => 'https://cdn-icons-png.flaticon.com/512/1865/1865273.png',
        'Tivi, Màn hình'      => 'https://cdn-icons-png.flaticon.com/512/716/716429.png',

        'Smartphones'         => 'https://cdn-icons-png.flaticon.com/512/3616/3616856.png',
        'Laptops'             => 'https://cdn-icons-png.flaticon.com/512/428/428001.png',
        'Tablets'             => 'https://cdn-icons-png.flaticon.com/512/3616/3616874.png',
        'Smart watch'         => 'https://cdn-icons-png.flaticon.com/512/2972/2972185.png',
        'Sound'               => 'https://cdn-icons-png.flaticon.com/512/3659/3659899.png',
        'Household appliances, Smarthome' => 'https://cdn-icons-png.flaticon.com/512/2585/2585175.png',
        'Accessory'           => 'https://cdn-icons-png.flaticon.com/512/1865/1865273.png',
        'TV, Monitor'         => 'https://cdn-icons-png.flaticon.com/512/716/716429.png',
    ];
@endphp

@section('content')
    <div class="container">
        <!-- ============================================================
             1. KHỐI HERO: CHỨA MEGA MENU DỌC VÀ SLIDER SWIPER
             ============================================================ -->
        <div class="hero-section">
            <!-- Mega Menu dọc lấy dữ liệu động từ Database -->
            <div class="category-menu">
                <ul>
                    @foreach($categories as $cat)
                        <li class="sidebar-mega-container">
                            <a href="{{ route('products.category', $cat->slug) }}">
                                <div class="menu-icon">
                                    <i class="fa-solid {{ $sidebarIcons[$cat->name] ?? 'fa-tag' }} main-icon"></i>
                                    {{ $cat->name }}
                                    <!-- Hiển thị nhãn HOT/NEW dựa trên tên danh mục -->
                                    @if(in_array($cat->name, ['Điện thoại', 'Laptop', 'Smartphones', 'Laptops']))
                                        <span class="menu-badge-hot">HOT</span>
                                    @elseif(in_array($cat->name, ['Đồng hồ thông minh', 'Smart watch']))
                                        <span class="menu-badge-new">NEW</span>
                                    @endif
                                </div>
                                <i class="fa-solid fa-angle-right text-xs text-gray-400"></i>
                            </a>

                            <!-- Panel Flyout hiển thị thông tin chi tiết các dòng con, thương hiệu, nhu cầu -->
                            <div class="sidebar-mega-panel">
                                <div class="mega-panel-content">
                                    <!-- Khối 1: Các dòng sản phẩm chi tiết (Danh mục con) -->
                                    <div class="mega-section">
                                        <h3 class="sidebar-mega-title">{{ mb_strtoupper(__('ui.product_lines', ['name' => $cat->name])) }}</h3>
                                        <div class="sidebar-mega-tags">
                                            @foreach($cat->children as $child)
                                                <a href="{{ route('products.category', $child->slug) }}" class="sidebar-mega-tag">
                                                    {{ $child->name }}
                                                </a>
                                            @endforeach
                                            <a href="{{ route('products.category', $cat->slug) }}" class="sidebar-mega-tag see-all">
                                                {{ __('ui.view_all_cat', ['name' => $cat->name]) }}
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Khối 2: Đề xuất các hãng sản xuất nổi tiếng -->
                                    <div class="mega-section mt-6">
                                        <h3 class="sidebar-mega-title">{{ mb_strtoupper(__('ui.popular_brands')) }}</h3>
                                        <div class="sidebar-mega-tags">
                                            @php
                                                $brands = ['Apple', 'Samsung', 'Asus', 'HP', 'Dell', 'Lenovo', 'MSI', 'Acer'];
                                            @endphp
                                            @foreach($brands as $brand)
                                                <a href="#" class="sidebar-mega-tag">{{ $brand }}</a>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Khối 3: Đề xuất chọn sản phẩm nhanh theo nhu cầu công việc/học tập/gaming -->
                                    <div class="mega-section mt-6">
                                        <h3 class="sidebar-mega-title">{{ mb_strtoupper(__('ui.choose_by_need')) }}</h3>
                                        <div class="sidebar-mega-tags">
                                            <a href="#" class="sidebar-mega-tag"><i class="fa-solid fa-gamepad mr-1"></i> {{ __('ui.need_gaming') }}</a>
                                            <a href="#" class="sidebar-mega-tag"><i class="fa-solid fa-graduation-cap mr-1"></i> {{ __('ui.need_student') }}</a>
                                            <a href="#" class="sidebar-mega-tag"><i class="fa-solid fa-leaf mr-1"></i> {{ __('ui.need_eco') }}</a>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-6 border-gray-100">
                                    <a href="{{ route('products.category', $cat->slug) }}" class="flex items-center font-bold text-blue-800 hover:underline">
                                        <i class="fa-solid fa-arrow-right mr-2"></i> {{ __('ui.view_all_cat', ['name' => $cat->name]) }}
                                    </a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                    <!-- Các mục tĩnh tiện ích phía dưới menu -->
                    <li>
                        <a href="#">
                            <div class="menu-icon">
                                <i class="fa-solid fa-gamepad main-icon"></i> {{ __('ui.trade_in_renew') }}
                            </div>
                            <i class="fa-solid fa-angle-right text-xs text-gray-400"></i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('articles.index') }}">
                            <div class="menu-icon">
                                <i class="fa-solid fa-newspaper main-icon"></i> {{ __('ui.tech_news') }}
                            </div>
                            <i class="fa-solid fa-angle-right text-xs text-gray-400"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Banner Slider Swiper bên phải màn hình -->
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
                <!-- Nút bấm qua slide tiếp theo / slide cũ -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <!-- ============================================================
             2. CỤM LIÊN KẾT NHANH (QUICK LINKS)
             ============================================================ -->
        <div class="quick-links">
            @foreach($categories as $cat)
                <a href="{{ route('products.category', $cat->slug) }}" class="quick-link-item">
                    <img src="{{ $quickLinkIcons[$cat->name] ?? 'https://cdn-icons-png.flaticon.com/512/1261/1261163.png' }}" alt="{{ $cat->name }}">
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>

        <!-- ============================================================
             3. KHỐI TRÌNH DIỄN SẢN PHẨM KHUYẾN MÃI GIỚI HẠN (FLASH SALE)
             ============================================================ -->
        @foreach($activeFlashSales as $sale)
            <!-- Nhúng kèm thuộc tính data-end để Javascript đọc thời gian kết thúc của sự kiện -->
            <div class="flash-sale-section" data-end="{{ $sale->end_at->format('Y-m-d H:i:s') }}">
                <div class="flash-sale-header">
                    <div class="flash-title">
                        <i class="fa-solid fa-bolt"></i> {{ $sale->name }}
                    </div>
                    <!-- Đồng hồ đếm ngược được tự động điền số giờ/phút/giây qua JS mỗi giây -->
                    <div class="countdown" id="countdown-{{ $sale->flash_sale_id }}">
                        <span>Kết thúc trong:</span>
                        <span class="countdown-box h">00</span> :
                        <span class="countdown-box m">00</span> :
                        <span class="countdown-box s">00</span>
                    </div>
                </div>

                <div class="product-grid-white">
                    @foreach($sale->mapped_products as $product)
                        <a href="{{ route('product.show', $product->product_id) }}" class="product-card">
                            <span class="badge-top-left">Trả góp 0%</span>
                            @php
                                $currentPrice = $product->flash_sale_price;
                                $oldPrice = $product->base_price;
                                $discount = $oldPrice ? round((($oldPrice - $currentPrice) / $oldPrice) * 100) : 0;
                            @endphp
                            @if($discount > 0)
                                <span class="badge-top-right">-{{ $discount }}%</span>
                            @endif

                            <img src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300' }}"
                                alt="{{ $product->name }}" class="product-img" loading="lazy">

                            <span class="category-badge">{{ $product->category->name ?? '' }}</span>
                            <h3 class="product-name">{{ $product->name }}</h3>
                            <div class="product-price">{{ number_format($currentPrice, 0, ',', '.') }}đ</div>
                            <div class="product-old-price">{{ number_format($oldPrice, 0, ',', '.') }}đ</div>
                            
                            <!-- Thanh tiến độ dung nham Flash Sale: tính toán tỷ lệ % số lượng đã bán thực tế -->
                            @php
                                $sold = $product->flash_sale_sold ?? 0;
                                $limit = $product->flash_sale_limit ?? 1;
                                $percent = min(100, round(($sold / $limit) * 100));
                            @endphp
                            <div class="fs-progress-wrapper">
                                <div class="fs-progress-bar" style="width: {{ $percent }}%"></div>
                                <i class="fa-solid fa-fire fs-fire-icon"></i>
                                <span class="fs-progress-text">
                                    @if($percent >= 100)
                                        Đã bán hết
                                    @else
                                        Đã bán {{ $sold }}/{{ $limit }}
                                    @endif
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Fallback Flash Sale: Hiển thị danh sách sản phẩm giảm giá mặc định nếu không có chiến dịch Flash Sale đang chạy -->
        @if($activeFlashSales->isEmpty() && $flashSaleProducts->count() > 0)
            <div class="flash-sale-section">
                <div class="flash-sale-header">
                    <div class="flash-title">
                        <i class="fa-solid fa-bolt"></i> GIẢM GIÁ ĐẶC BIỆT
                    </div>
                </div>

                <div class="product-grid-white">
                    @foreach($flashSaleProducts as $product)
                        <a href="{{ route('product.show', $product->product_id) }}" class="product-card">
                            <span class="badge-top-left">Giá sốc</span>
                            @if($product->old_price && $product->old_price > $product->base_price)
                                <span class="badge-top-right">-{{ round((($product->old_price - $product->base_price) / $product->old_price) * 100) }}%</span>
                            @endif

                            <img src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300' }}"
                                alt="{{ $product->name }}" class="product-img" loading="lazy">

                            <span class="category-badge">{{ $product->category->name ?? '' }}</span>
                            <h3 class="product-name">{{ $product->name }}</h3>
                            <div class="product-price">{{ number_format($product->base_price, 0, ',', '.') }}đ</div>
                            @if($product->old_price)
                                <div class="product-old-price">{{ number_format($product->old_price, 0, ',', '.') }}đ</div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- ============================================================
             4. KHU VỰC CÁC PHẦN DỰNG SẢN PHẨM ĐỘNG (DYNAMIC SECTIONS)
             Hỗ trợ AJAX Tab switching để đổi nhóm sản phẩm con nhanh chóng.
             ============================================================ -->
        @foreach($homeSections as $section)
            <div class="product-section-wrapper" id="section-{{ $section->id }}">
                <!-- Banner đứng quảng cáo ở cột bên trái (nếu có cấu hình) -->
                @if($section->sidebar_banner)
                <div class="section-sidebar-banner">
                    <a href="{{ $section->sidebar_link ?? '#' }}">
                        <img src="{{ $section->sidebar_banner }}" alt="{{ $section->title }}">
                    </a>
                </div>
                @endif

                <!-- Nội dung chính chứa danh sách sản phẩm ở bên phải -->
                <div class="section-main-content">
                    <div class="section-header-premium">
                        <h2 class="section-title-premium">{{ $section->title }}</h2>
                        <!-- Tabs điều hướng chứa data-target trỏ về grid id tương ứng -->
                        <div class="section-tabs" data-target="grid-{{ $section->id }}">
                            <span class="section-tab-item active" data-id="{{ $section->category_id ?? '' }}">Tất cả</span>
                            @if($section->type === 'category' && $section->category)
                                <!-- Chỉ lấy tối đa 4 danh mục con để hiển thị thành các nút tab -->
                                @foreach($section->category->children->take(4) as $child)
                                    <span class="section-tab-item" data-id="{{ $child->category_id }}">{{ $child->name }}</span>
                                @endforeach
                                <a href="{{ route('products.category', $section->category->slug) }}" class="section-tab-item">Xem tất cả</a>
                            @endif
                        </div>
                    </div>

                    <!-- Khung lưới hiển thị sản phẩm (Mặc định render cục bộ từ Blade, khi bấm tab sẽ gửi AJAX ghi đè) -->
                    <div class="product-grid" id="grid-{{ $section->id }}" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; position: relative;">
                        @include('partials.product_grid_items', ['products' => $section->products_list])
                    </div>
                </div>
            </div>
        @endforeach

        <!-- ============================================================
             5. KHỐI TIN TỨC CÔNG NGHỆ & LIFESTYLE (TECH NEWS SECTION)
             ============================================================ -->
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
                .news-card:hover h3 { color: #d70018 !important; } 
            </style>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    /* ============================================================
       1. AJAX TABS - CHUYỂN ĐỔI SẢN PHẨM KHÔNG CẦN TẢI LẠI TRANG
       ============================================================ */
    document.addEventListener("DOMContentLoaded", function() {
        // Lấy toàn bộ các phần tử nút tab có chứa thuộc tính data-id danh mục con
        const tabItems = document.querySelectorAll('.section-tab-item[data-id]');
        
        tabItems.forEach(tab => {
            tab.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                const parentHeader = this.closest('.section-tabs');
                const targetId = parentHeader.getAttribute('data-target');
                const gridContainer = document.getElementById(targetId);

                if (!gridContainer || !categoryId) return;

                // 1.1. Cập nhật trạng thái Active trên giao diện tab
                parentHeader.querySelectorAll('.section-tab-item').forEach(item => item.classList.remove('active'));
                this.classList.add('active');

                // 1.2. Thêm class kích hoạt vòng quay Loading tròn xoay mờ màn hình
                gridContainer.classList.add('grid-loading');

                // 1.3. Gửi yêu cầu HTTP GET lấy mã HTML đã render của danh sách sản phẩm danh mục con từ Backend
                fetch(`/api/category-products/${categoryId}`)
                    .then(response => response.text())
                    .then(html => {
                        // Cập nhật nội dung HTML vào lưới sản phẩm
                        gridContainer.innerHTML = html;
                        gridContainer.classList.remove('grid-loading');
                        
                        // Áp dụng hiệu ứng fade-in mượt mà sau khi cập nhật dữ liệu DOM
                        gridContainer.style.opacity = 0;
                        setTimeout(() => {
                            gridContainer.style.transition = 'opacity 0.3s ease';
                            gridContainer.style.opacity = 1;
                        }, 50);
                    })
                    .catch(error => {
                        console.error('Lỗi khi tải sản phẩm qua AJAX:', error);
                        gridContainer.classList.remove('grid-loading');
                    });
            });
        });
    });

    /* ============================================================
       2. KHỞI TẠO BANNER TRƯỢT SWIPER SLIDER CHÍNH
       ============================================================ */
    document.addEventListener("DOMContentLoaded", function() {
        if(typeof Swiper !== 'undefined') {
            var swiper = new Swiper(".mySwiper", {
                loop: true, // Tự động xoay vòng lặp vô hạn
                autoplay: {
                    delay: 3000, // Chuyển slide sau mỗi 3 giây
                    disableOnInteraction: false, // Tiếp tục chạy tự động ngay cả khi người dùng vuốt trượt thủ công
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true, // Cho phép click vào dấu chấm để chuyển nhanh đến slide tương ứng
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
        }
    });

    /* ============================================================
       3. ĐỒNG HỒ ĐẾM NGƯỢC THỜI GIAN THỰC (FLASH SALE COUNTDOWN TIMER)
       ============================================================ */
    (function() {
        function updateCountdowns() {
            const now = new Date().getTime();
            // Lấy tất cả các block chiến dịch Flash Sale đang hiển thị
            const sections = document.querySelectorAll('.flash-sale-section[data-end]');

            sections.forEach(section => {
                const endTimeStr = section.getAttribute('data-end');
                const endTime = new Date(endTimeStr).getTime();
                const distance = endTime - now;

                const hEl = section.querySelector('.countdown-box.h');
                const mEl = section.querySelector('.countdown-box.m');
                const sEl = section.querySelector('.countdown-box.s');

                if (!hEl || !mEl || !sEl) return;

                // Nếu chiến dịch đã kết thúc, trả giá trị đồng hồ về 00:00:00
                if (distance < 0) {
                    hEl.textContent = "00";
                    mEl.textContent = "00";
                    sEl.textContent = "00";
                    return;
                }

                // Tính toán số giờ, phút, giây còn lại
                const h = Math.floor(distance / (1000 * 60 * 60));
                const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((distance % (1000 * 60)) / 1000);

                // Ghi dữ liệu vào DOM kèm đệm số 0 ở đầu nếu giá trị bé hơn 10 (VD: 09 thay vì 9)
                hEl.textContent = String(h).padStart(2, '0');
                mEl.textContent = String(m).padStart(2, '0');
                sEl.textContent = String(s).padStart(2, '0');
            });
        }

        // Kích hoạt cập nhật thời gian liên tục mỗi giây (1000ms)
        setInterval(updateCountdowns, 1000);
        updateCountdowns();
    })();

    /* ============================================================
       4. AJAX ADD TO CART - THÊM NHANH SẢN PHẨM VÀO GIỎ HÀNG
       ============================================================ */
    function addToCart(productId) {
        fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Hiển thị toast thông báo thành công xanh lá góc trên bên phải màn hình
                Swal.fire({
                    icon: 'success',
                    title: 'Đã thêm vào giỏ hàng!',
                    showConfirmButton: false,
                    timer: 1500,
                    toast: true,
                    position: 'top-end'
                });
                // Đồng bộ số lượng sản phẩm giỏ hàng mới lên Badge đỏ trên Header
                const cartCountElement = document.getElementById('headerCartBadge');
                if (cartCountElement && data.cart_count !== undefined) {
                    cartCountElement.innerText = data.cart_count;
                    cartCountElement.style.display = data.cart_count > 0 ? 'block' : 'none';
                }
            } else {
                Swal.fire('Thất bại', data.message || 'Lỗi khi thêm vào giỏ', 'error');
            }
        });
    }

    /* ============================================================
       5. AJAX WISHLIST - BẬT TẮT SẢN PHẨM YÊU THÍCH TỨC THỜI
       ============================================================ */
    function toggleWishlist(productId, element) {
        fetch('{{ route("wishlist.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'added') {
                // Thêm thành công: Đổi icon trái tim sang dạng đầy màu (fa-solid) và tô màu đỏ hồng
                element.querySelector('i').classList.remove('fa-regular');
                element.querySelector('i').classList.add('fa-solid');
                element.style.color = '#e11d48';
                Swal.fire({
                    icon: 'success',
                    title: 'Đã thêm vào yêu thích!',
                    showConfirmButton: false,
                    timer: 1500,
                    toast: true,
                    position: 'top-end'
                });
            } else if (data.status === 'removed') {
                // Xóa thành công: Đổi icon trái tim trở về dạng rỗng viền đen (fa-regular)
                element.querySelector('i').classList.remove('fa-solid');
                element.querySelector('i').classList.add('fa-regular');
                element.style.color = '';
                Swal.fire({
                    icon: 'info',
                    title: 'Đã xóa khỏi yêu thích!',
                    showConfirmButton: false,
                    timer: 1500,
                    toast: true,
                    position: 'top-end'
                });
            } else if (data.status === 'unauthenticated') {
                // Bảo mật: Nếu khách hàng chưa đăng nhập tài khoản, hiển thị cảnh báo chuyển hướng
                Swal.fire({
                    title: 'Yêu cầu đăng nhập',
                    text: "Vui lòng đăng nhập để sử dụng tính năng này!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0046ab',
                    confirmButtonText: 'Đăng nhập ngay'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route("login") }}';
                    }
                });
            }
        });
    }
</script>
@endpush