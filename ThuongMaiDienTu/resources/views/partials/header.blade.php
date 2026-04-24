@php
    $headerCategories = \App\Models\Category::whereNull('parent_id')->with('children')->get();

    $categoryIcons = [
        'Điện thoại'          => 'fa-mobile-screen-button',
        'Laptop'              => 'fa-laptop',
        'Tablet'              => 'fa-tablet-screen-button',
        'Âm thanh'            => 'fa-headphones',
        'Đồng hồ thông minh' => 'fa-clock',
        'Phụ kiện'            => 'fa-keyboard',
        'Tivi, Màn hình'      => 'fa-tv',
        'Gia dụng, Smarthome' => 'fa-plug',
    ];

    // 34 tỉnh thành Việt Nam mới nhất (Nghị quyết 202/2025/QH15, từ 01/07/2025)
    // 6 TP trực thuộc TW + 28 tỉnh
    $provinces = [
        // 6 Thành phố trực thuộc Trung ương
        'TP. Hồ Chí Minh', 'TP. Hà Nội', 'TP. Hải Phòng', 'TP. Đà Nẵng', 'TP. Cần Thơ', 'TP. Huế',
        // 28 Tỉnh (theo thứ tự ABC)
        'An Giang', 'Bắc Ninh', 'Cà Mau', 'Cao Bằng',
        'Đắk Lắk', 'Điện Biên', 'Đồng Nai', 'Đồng Tháp',
        'Gia Lai', 'Hà Tĩnh', 'Hưng Yên', 'Khánh Hòa',
        'Lai Châu', 'Lâm Đồng', 'Lạng Sơn', 'Lào Cai',
        'Nghệ An', 'Ninh Bình', 'Phú Thọ', 'Quảng Ngãi',
        'Quảng Ninh', 'Quảng Trị', 'Sơn La', 'Tây Ninh',
        'Thái Nguyên', 'Thanh Hóa', 'Tuyên Quang', 'Vĩnh Long',
    ];
@endphp

<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-left">
            <span><i class="fa-solid fa-recycle"></i> Thu cũ giá ngon - Lên đời tiết kiệm</span>
            <span><i class="fa-solid fa-certificate"></i> Sản phẩm <strong>Chính hãng</strong> - Xuất VAT đầy đủ</span>
            <span><i class="fa-solid fa-truck-fast"></i> Giao nhanh - <strong>Miễn phí</strong> cho đơn 300k</span>
        </div>
        <div class="top-bar-right">
            <span><i class="fa-solid fa-store"></i> Cửa hàng gần bạn</span>
            <span><i class="fa-solid fa-truck"></i> Tra cứu đơn hàng</span>
            <span><i class="fa-solid fa-phone"></i> <strong>1800 2097</strong></span>
        </div>
    </div>
</div>

<!-- Header -->
<header class="header-main">
    <div class="container header-content">
        <!-- Logo -->
        <a href="/" class="logo">
            <i class="fa-solid fa-bolt"></i>
            DIENMAY<span>PRO</span>
        </a>

        <!-- Nút Danh mục -->
        <div class="header-category-btn" id="categoryToggleBtn">
            <i class="fa-solid fa-bars"></i> Danh mục <i class="fa-solid fa-chevron-down" style="font-size:10px; margin-left:2px;"></i>
        </div>

        <!-- Nút Tỉnh thành -->
        <div class="header-province-btn" id="provinceToggleBtn">
            <i class="fa-solid fa-location-dot"></i>
            <span id="selectedProvinceName">TP. Hồ Chí Minh</span>
            <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
        </div>

        <!-- Thanh tìm kiếm -->
        <div class="search-bar">
            <form action="/search" method="GET">
                <input type="text" name="q" placeholder="Bạn muốn mua gì hôm nay?">
                <button type="submit"><i class="fa-solid fa-search"></i></button>
            </form>
        </div>

        <!-- Hành động -->
        <div class="header-actions">
            <a href="/orders" class="action-item">
                <i class="fa-solid fa-truck-fast"></i>
                <span>Tra cứu đơn</span>
            </a>
            <a href="/cart" class="action-item">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Giỏ hàng</span>
            </a>
            @auth
                <div class="action-item relative group" style="position: relative;">
                    <a href="/profile" style="display:flex; flex-direction:column; align-items:center;">
                        <i class="fa-regular fa-circle-user"></i>
                        <span style="max-width: 70px; overflow: hidden; text-overflow: ellipsis;">{{ explode(' ', Auth::user()->full_name)[0] }}</span>
                    </a>
                    <!-- Dropdown mini -->
                    <div class="user-dropdown">
                        <a href="/profile">Trang cá nhân</a>
                        <form action="{{ route('logout') ?? '/logout' }}" method="POST">
                            @csrf
                            <button type="submit">Đăng xuất</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="/login" class="action-item">
                    <i class="fa-regular fa-circle-user"></i>
                    <span>Đăng nhập</span>
                </a>
            @endauth
        </div>
    </div>
</header>

<!-- =====================================================
     MEGA MENU DANH MỤC (hiển thị khi click nút Danh mục)
     ===================================================== -->
<div class="mega-menu-overlay" id="megaMenuOverlay"></div>
<div class="mega-menu" id="megaMenu">
    <div class="container mega-menu-inner">
        <!-- CỘT TRÁI: Danh mục + icon -->
        <div class="mega-col-left">
            @foreach($headerCategories as $cat)
                <a href="#" class="mega-cat-item {{ $loop->first ? 'active' : '' }}"
                   data-cat="{{ $cat->category_id }}">
                    <i class="fa-solid {{ $categoryIcons[$cat->name] ?? 'fa-tag' }}"></i>
                    <span>{{ $cat->name }}</span>
                    @if($cat->children->count())
                        <i class="fa-solid fa-angle-right mega-arrow"></i>
                    @endif
                </a>
            @endforeach
            {{-- Mục bổ sung --}}
            <a href="#" class="mega-cat-item"><i class="fa-solid fa-gamepad"></i><span>Thu cũ đổi mới</span></a>
            <a href="#" class="mega-cat-item"><i class="fa-solid fa-tags"></i><span>Hàng cũ</span></a>
            <a href="#" class="mega-cat-item"><i class="fa-solid fa-percent"></i><span>Khuyến mãi</span></a>
            <a href="#" class="mega-cat-item"><i class="fa-solid fa-newspaper"></i><span>Tin công nghệ</span></a>
        </div>

        <!-- CỘT PHẢI: Nội dung chi tiết theo danh mục -->
        <div class="mega-col-right">
            @foreach($headerCategories as $cat)
                <div class="mega-detail-panel {{ $loop->first ? 'active' : '' }}"
                     data-panel="{{ $cat->category_id }}">
                    @if($cat->children->count())
                        <div class="mega-section">
                            <h4 class="mega-section-title">{{ $cat->name }}</h4>
                            <div class="mega-tags">
                                @foreach($cat->children as $child)
                                    <a href="#" class="mega-tag">{{ $child->name }}</a>
                                @endforeach
                                <a href="#" class="mega-tag see-all">Xem tất cả {{ $cat->name }}</a>
                            </div>
                        </div>
                    @else
                        <div class="mega-section">
                            <h4 class="mega-section-title">{{ $cat->name }}</h4>
                            <p style="color:#888; font-size:13px;">Đang cập nhật danh mục con...</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- =====================================================
     MODAL CHỌN TỈNH THÀNH
     ===================================================== -->
<div class="province-modal-overlay" id="provinceModalOverlay">
    <div class="province-modal">
        <div class="province-modal-header">
            <div class="province-search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="provinceSearchInput" placeholder="Nhập tên tỉnh thành">
            </div>
            <button class="province-close-btn" id="provinceCloseBtn">Đóng <i class="fa-solid fa-xmark"></i></button>
        </div>
        <p class="province-hint">Vui lòng chọn tỉnh, thành phố để biết chính xác giá, khuyến mãi và tồn kho</p>
        <div class="province-list" id="provinceList">
            @foreach($provinces as $prov)
                <div class="province-item {{ $prov === 'TP. Hồ Chí Minh' ? 'selected' : '' }}"
                     data-province="{{ $prov }}">
                    {{ $prov }}
                    @if($prov === 'TP. Hồ Chí Minh')
                        <i class="fa-solid fa-circle-check"></i>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>