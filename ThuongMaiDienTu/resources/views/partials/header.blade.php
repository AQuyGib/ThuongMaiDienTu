@php
    // KHỞI TẠO DỮ LIỆU ĐẦU VÀO CHO HEADER
    // Lấy danh sách các danh mục cha (parent_id là null) kèm theo các danh mục con (children) để hiển thị trên Mega Menu
    $headerCategories = \App\Models\Category::whereNull('parent_id')->with('children')->get();

    // Mảng bản đồ ánh xạ tên danh mục (cả tiếng Việt và tiếng Anh) với icon FontAwesome tương ứng
    $categoryIcons = [
        // Tiếng Việt
        'Điện thoại'          => 'fa-mobile-screen-button',
        'Laptop'              => 'fa-laptop',
        'Tablet'              => 'fa-tablet-screen-button',
        'Âm thanh'            => 'fa-headphones',
        'Đồng hồ thông minh' => 'fa-clock',
        'Phụ kiện'            => 'fa-keyboard',
        'Tivi, Màn hình'      => 'fa-tv',
        'Gia dụng, Smarthome' => 'fa-plug',

        // English
        'Smartphones'         => 'fa-mobile-screen-button',
        'Laptops'             => 'fa-laptop',
        'Tablets'             => 'fa-tablet-screen-button',
        'Sound'               => 'fa-headphones',
        'Smart watch'         => 'fa-clock',
        'Accessory'           => 'fa-keyboard',
        'TV, Monitor'         => 'fa-tv',
        'Household appliances, Smarthome' => 'fa-plug',
    ];

    // Danh sách 34 tỉnh thành Việt Nam mới nhất phục vụ tính năng chọn vị trí mua hàng để hiển thị tồn kho/khuyến mãi gần nhất
    $provinces = [
        'TP. Hồ Chí Minh', 'TP. Hà Nội', 'TP. Hải Phòng', 'TP. Đà Nẵng', 'TP. Cần Thơ', 'TP. Huế',
        'An Giang', 'Bắc Ninh', 'Cà Mau', 'Cao Bằng',
        'Đắk Lắk', 'Điện Biên', 'Đồng Nai', 'Đồng Tháp',
        'Gia Lai', 'Hà Tĩnh', 'Hưng Yên', 'Khánh Hòa',
        'Lai Châu', 'Lâm Đồng', 'Lạng Sơn', 'Lào Cai',
        'Nghệ An', 'Ninh Bình', 'Phú Thọ', 'Quảng Ngãi',
        'Quảng Ninh', 'Quảng Trị', 'Sơn La', 'Tây Ninh',
        'Thái Nguyên', 'Thanh Hóa', 'Tuyên Quang', 'Vĩnh Long',
    ];

    // Lấy thông tin người dùng hiện tại và tải trước danh sách thông báo chưa đọc (nếu đã đăng nhập)
    $currentUser = auth()->user();
    $headerNotifications = collect();
    $unreadNotificationCount = 0;

    if ($currentUser) {
        // Giới hạn hiển thị 5 thông báo mới nhất trên dropdown header
        $headerNotifications = $currentUser->notifications()->limit(5)->get();
        // Đếm tổng số thông báo chưa đọc (read_at là null) để hiển thị badge đỏ
        $unreadNotificationCount = $currentUser->notifications()->whereNull('read_at')->count();
    }
@endphp

<!-- ============================================================
     PHẦN 1: TOP BAR - THANH TIỆN ÍCH PHÍA TRÊN CÙNG
     Hiển thị các thông tin cam kết dịch vụ, tra cứu đơn hàng và nút chuyển đổi đa ngôn ngữ (VI/EN)
     ============================================================ -->
@if(($globalSettings['announcement_show'] ?? '1') !== '0')
<div class="top-bar">
    <div class="container">
        <!-- Các chính sách cam kết dịch vụ bán hàng -->
        <div class="top-bar-left">
            <span><i class="fa-solid fa-recycle"></i> {!! $globalSettings['topbar_text_1'] ?? __('ui.topbar_trade_in') !!}</span>
            <span><i class="fa-solid fa-certificate"></i> {!! $globalSettings['topbar_text_2'] ?? __('ui.topbar_genuine') !!}</span>
            <span><i class="fa-solid fa-truck-fast"></i> {!! $globalSettings['topbar_text_3'] ?? __('ui.topbar_fast_delivery') !!}</span>
        </div>
        <!-- Các liên kết tiện ích & Chuyển đổi ngôn ngữ -->
        <div class="top-bar-right">
            <span><i class="fa-solid fa-store"></i> {!! $globalSettings['topbar_text_4'] ?? __('ui.topbar_nearby_store') !!}</span>
            <a href="/orders" class="hover:text-white transition"><span><i class="fa-solid fa-truck"></i> {{ __('ui.topbar_track_order') }}</span></a>
            <span><i class="fa-solid fa-phone"></i> <strong>{{ $globalSettings['hotline'] ?? '1800 2097' }}</strong></span>
            
            {{-- Language Switcher - Nút chuyển đổi ngôn ngữ động --}}
            <div class="lang-switcher" id="langSwitcher">
                <button class="lang-switcher-btn" id="langToggleBtn">
                    <i class="fa-solid fa-globe"></i>
                    <span>{{ app()->getLocale() === 'en' ? 'EN' : 'VI' }}</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 8px; margin-left: 1px;"></i>
                </button>
                <!-- Menu thả xuống lựa chọn ngôn ngữ -->
                <div class="lang-dropdown" id="langDropdown">
                    <a href="{{ route('locale.switch', 'vi') }}" class="lang-option {{ app()->getLocale() === 'vi' ? 'active' : '' }}">
                        <span class="lang-flag">🇻🇳</span>
                        <span>Tiếng Việt</span>
                        @if(app()->getLocale() === 'vi')<i class="fa-solid fa-check"></i>@endif
                    </a>
                    <a href="{{ route('locale.switch', 'en') }}" class="lang-option {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                        <span class="lang-flag">🇺🇸</span>
                        <span>English</span>
                        @if(app()->getLocale() === 'en')<i class="fa-solid fa-check"></i>@endif
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- ============================================================
     PHẦN 2: HEADER MAIN - KHU VỰC ĐIỀU HƯỚNG VÀ TÌM KIẾM CHÍNH
     Chứa Logo RGB, Nút Danh mục, Chọn vị trí, Ô Tìm kiếm thông minh và các nút Giỏ hàng/Tài khoản
     ============================================================ -->
<header class="header-main">
    <div class="container header-content">
        @php
            // Xác định xem trang hiện tại có phải là Trang Chủ không để kích hoạt hiệu ứng RGB động trên Logo
            $isHomepage = request()->is('/') || request()->is('Home') || request()->is('home') || request()->routeIs('home');
        @endphp
        
        @if($isHomepage)
        <!-- CSS Animation tạo hiệu ứng RGB chuyển màu mượt mà cho Logo ở trang chủ -->
        <style>
            @keyframes rgb-text-animation {
                0% { color: #ff0000; text-shadow: 0 0 10px rgba(255, 0, 0, 0.4); }
                10% { color: #ff7700; text-shadow: 0 0 10px rgba(255, 119, 0, 0.4); }
                20% { color: #ffdd00; text-shadow: 0 0 10px rgba(255, 221, 0, 0.4); }
                35% { color: #00ff00; text-shadow: 0 0 10px rgba(0, 255, 0, 0.4); }
                50% { color: #00ffff; text-shadow: 0 0 10px rgba(0, 255, 255, 0.4); }
                65% { color: #0088ff; text-shadow: 0 0 10px rgba(0, 136, 255, 0.4); }
                78% { color: #7700ff; text-shadow: 0 0 10px rgba(119, 0, 255, 0.4); }
                88% { color: #ff00ff; text-shadow: 0 0 10px rgba(255, 0, 255, 0.4); }
                94% { color: #ff33aa; text-shadow: 0 0 10px rgba(255, 51, 170, 0.4); }
                100% { color: #ff0000; text-shadow: 0 0 10px rgba(255, 0, 0, 0.4); }
            }
            @keyframes rgb-span-animation {
                0% { color: #00ff00; }
                10% { color: #00ffff; }
                20% { color: #0088ff; }
                35% { color: #7700ff; }
                50% { color: #ff00ff; }
                65% { color: #ff33aa; }
                78% { color: #ff0000; }
                88% { color: #ff7700; }
                94% { color: #ffdd00; }
                100% { color: #00ff00; }
            }
            .logo.logo-rgb {
                animation: rgb-text-animation 6s infinite linear !important;
            }
            .logo.logo-rgb i {
                animation: rgb-text-animation 6s infinite linear !important;
            }
            .logo.logo-rgb span {
                animation: rgb-span-animation 6s infinite linear !important;
            }
        </style>
        @endif

        <!-- Logo trang web thương mại điện tử -->
        <a href="/" class="logo {{ $isHomepage ? 'logo-rgb' : '' }}">
            @if(!empty($globalSettings['logo']))
                <img src="{{ asset($globalSettings['logo']) }}" alt="Logo" style="max-height: 40px; width: auto; object-fit: contain;">
            @else
                <i class="fa-solid fa-bolt"></i>
                {{ $globalSettings['site_name'] ?? 'DIENMAY' }}<span>{{ $globalSettings['site_suffix'] ?? 'PRO' }}</span>
            @endif
        </a>

        <!-- Nút Kích hoạt Mega Menu (Danh mục sản phẩm đa cấp) -->
        <div class="header-category-btn" id="categoryToggleBtn">
            <i class="fa-solid fa-bars"></i> {{ __('ui.categories') }} <i class="fa-solid fa-chevron-down" style="font-size:10px; margin-left:2px;"></i>
        </div>

        <!-- Nút chọn Vị trí tỉnh thành để định vị kho hàng gần nhất -->
        <div class="header-province-btn" id="provinceToggleBtn">
            <i class="fa-solid fa-location-dot"></i>
            <span id="selectedProvinceName">TP. Hồ Chí Minh</span>
            <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
        </div>

        <!-- Thanh Tìm kiếm sản phẩm kèm Hộp gợi ý từ khóa thông minh (AJAX Search Autocomplete) -->
        <div class="search-bar">
            <form action="{{ route('search.index') }}" method="GET" id="globalSearchForm">
                <input type="text" name="q" id="globalSearchInput" placeholder="{{ __('ui.search_placeholder') }}" autocomplete="off">
                <button type="submit"><i class="fa-solid fa-search"></i></button>
            </form>
            
            <!-- Hộp gợi ý thông minh (Mặc định ẩn, hiển thị động thông qua JS khi gõ từ khóa) -->
            <div id="searchSuggestions" class="search-suggestions">
                {{-- Dữ liệu gợi ý danh mục và sản phẩm sẽ được Javascript render động vào đây --}}
            </div>
        </div>

        <!-- Script xử lý gợi ý tìm kiếm tức thời (Autocomplete Search) -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('globalSearchInput');
                const suggestionsBox = document.getElementById('searchSuggestions');
                let debounceTimer;

                if (searchInput && suggestionsBox) {
                    // Lắng nghe sự kiện gõ chữ trên ô tìm kiếm
                    searchInput.addEventListener('input', function() {
                        const query = this.value.trim();
                        
                        clearTimeout(debounceTimer);
                        
                        // Chỉ tìm kiếm khi độ dài từ khóa tối thiểu là 2 ký tự
                        if (query.length < 2) {
                            suggestionsBox.classList.remove('show');
                            return;
                        }

                        // Áp dụng Debounce 300ms để giảm tần suất gọi API lên máy chủ khi người dùng gõ nhanh
                        debounceTimer = setTimeout(() => {
                            fetch(`{{ route('api.search.suggestions') }}?q=${encodeURIComponent(query)}`)
                                .then(response => response.json())
                                .then(data => {
                                    renderSuggestions(data, query);
                                })
                                .catch(err => console.error('Search error:', err));
                        }, 300);
                    });

                    // Tự động đóng hộp gợi ý khi người dùng click ra ngoài khu vực tìm kiếm
                    document.addEventListener('click', function(e) {
                        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                            suggestionsBox.classList.remove('show');
                        }
                    });

                    // Hiện lại hộp gợi ý khi người dùng click (focus) lại vào ô tìm kiếm (nếu đã có từ khóa hợp lệ)
                    searchInput.addEventListener('focus', function() {
                        if (this.value.trim().length >= 2) {
                            suggestionsBox.classList.add('show');
                        }
                    });
                }

                /**
                 * Hàm render danh sách gợi ý tìm kiếm vào DOM
                 * @param {Object} data Đối tượng JSON trả về từ API gợi ý (categories, products)
                 * @param {String} query Từ khóa tìm kiếm của người dùng
                 */
                function renderSuggestions(data, query) {
                    let html = '';

                    if (data.categories.length === 0 && data.products.length === 0) {
                        html = '<div class="no-results">Không tìm thấy kết quả cho "' + query + '"</div>';
                    } else {
                        // 1. Render các Danh mục gợi ý tìm thấy phù hợp
                        if (data.categories.length > 0) {
                            html += '<div class="suggestion-group">';
                            html += '   <div class="suggestion-header">Danh mục gợi ý</div>';
                            data.categories.forEach(cat => {
                                html += `<a href="/products/${cat.slug}" class="suggestion-cat">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                            <span>${cat.name}</span>
                                         </a>`;
                            });
                            html += '</div>';
                        }

                        // 2. Render các Sản phẩm tìm thấy phù hợp kèm theo hình ảnh thu nhỏ và giá bán
                        if (data.products.length > 0) {
                            html += '<div class="suggestion-group">';
                            html += '   <div class="suggestion-header">Sản phẩm tìm thấy</div>';
                            data.products.forEach(prod => {
                                const price = new Intl.NumberFormat('vi-VN').format(prod.base_price) + 'đ';
                                const thumbnail = prod.thumbnail || 'https://via.placeholder.com/50';
                                html += `<a href="/san-pham/${prod.product_id}" class="suggestion-item">
                                            <img src="${thumbnail}" alt="${prod.name}">
                                            <div class="suggestion-info">
                                                <div class="suggestion-name">${prod.name}</div>
                                                <div class="suggestion-price">${price}</div>
                                            </div>
                                         </a>`;
                            });
                            html += '</div>';
                        }
                        
                        // Dòng liên kết xem toàn bộ kết quả tìm kiếm đầy đủ
                        html += `<a href="{{ route('search.index') }}?q=${encodeURIComponent(query)}" class="suggestion-cat" style="justify-content: center; background: #f0f7ff; border-top: 1px solid #e5e7eb;">
                                    <strong>Xem tất cả kết quả cho "${query}"</strong>
                                 </a>`;
                    }

                    suggestionsBox.innerHTML = html;
                    suggestionsBox.classList.add('show');
                }
            });
        </script>

        <!-- Nhóm các nút hành động chính ở bên phải Header -->
        <div class="header-actions">
            <!-- Nút Tra cứu nhanh trạng thái vận đơn đơn hàng -->
            <a href="/orders" class="action-item">
                <i class="fa-solid fa-truck-fast {{ request()->is('orders*') ? 'text-orange-400 animate-pulse' : '' }}"></i>
                <span>{{ __('ui.track_order_short') }}</span>
            </a>

            <!-- Menu thông báo và dropdown thông báo AJAX (Chỉ hiển thị khi đã đăng nhập) -->
            @auth
                <div class="action-item group" style="position: relative;">
                    <a href="{{ route('notifications.index') }}" class="action-item" id="notificationBell" style="position: relative;">
                        <i class="fa-regular fa-bell {{ request()->is('notifications*') ? 'text-yellow-300 animate-pulse' : '' }}"></i>
                        <!-- Badge hiển thị số thông báo chưa đọc, ẩn đi nếu không có thông báo nào -->
                        @if($unreadNotificationCount > 0)
                            <span id="notificationBadge" style="position: absolute; top: 0px; right: 8px; background: #d70018; color: #fff; font-size: 10px; font-weight: bold; padding: 1px 5px; border-radius: 10px;">{{ $unreadNotificationCount }}</span>
                        @else
                            <span id="notificationBadge" style="position: absolute; top: 0px; right: 8px; background: #d70018; color: #fff; font-size: 10px; font-weight: bold; padding: 1px 5px; border-radius: 10px; display:none;">0</span>
                        @endif
                        <span>{{ __('ui.notifications') }}</span>
                    </a>
                    
                    <!-- Dropdown danh sách nhanh các thông báo chưa đọc / mới nhận -->
                    <div class="notification-dropdown">
                        <div class="notification-dropdown-header">
                            <strong>{{ __('ui.new_notifications') }}</strong>
                            <a href="{{ route('notifications.index') }}">{{ __('ui.view_all') }}</a>
                        </div>
                        <div class="notification-dropdown-body">
                            @forelse($headerNotifications as $notification)
                                <!-- Mỗi thông báo chứa URL xử lý PATCH để đánh dấu đã đọc trước khi chuyển trang -->
                                <a href="javascript:void(0)" data-id="{{ $notification->notification_id }}" data-read-url="{{ route('notifications.read', $notification->notification_id) }}" data-action-url="{{ $notification->action_url ?: route('notifications.index') }}" class="notification-dropdown-item {{ $notification->read_at ? '' : 'unread' }}">
                                    <div class="notification-dot"></div>
                                    <div class="notification-content">
                                        <div class="notification-title">{{ $notification->title }}</div>
                                        <div class="notification-text">{{ \Illuminate\Support\Str::limit($notification->content, 70) }}</div>
                                        <div class="notification-time">{{ $notification->created_at?->diffForHumans() }}</div>
                                    </div>
                                </a>
                            @empty
                                <div class="notification-empty">{{ __('ui.no_notifications') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endauth

            <!-- Liên kết góc video, tin tức công nghệ -->
            <a href="{{ route('videos.index') }}" class="action-item">
                <i class="fa-solid fa-video text-secondary {{ request()->is('videos*') ? 'animate-pulse' : '' }}"></i>
                <span>{{ __('ui.video_corner') }}</span>
            </a>

            <!-- Giỏ hàng (Hiển thị badge số lượng sản phẩm động) -->
            <a href="{{ route('cart.index') }}" class="action-item" style="position: relative;">
                <i class="fa-solid fa-cart-shopping {{ request()->is('shoppingcart*') ? 'text-pink-400 animate-pulse' : '' }}"></i>
                <span id="headerCartBadge" style="position: absolute; top: 0px; right: 8px; background: #d70018; color: #fff; font-size: 10px; font-weight: bold; padding: 1px 5px; border-radius: 10px; display: none;">0</span>
                <span>{{ __('ui.cart') }}</span>
            </a>

            <!-- Script quản lý Polling & AJAX cập nhật thông báo/giỏ hàng động -->
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // 1. Gọi AJAX lấy số lượng sản phẩm trong giỏ hàng hiện tại của phiên làm việc
                    fetch('{{ route("cart.count") }}')
                        .then(response => response.json())
                        .then(res => {
                            let badge = document.getElementById('headerCartBadge');
                            if(badge) {
                                if (res.cart_count > 0) {
                                    badge.innerText = res.cart_count;
                                    badge.style.display = 'block';
                                } else {
                                    badge.style.display = 'none';
                                }
                            }
                        })
                        .catch(err => console.error(err));

                    // 2. Thực hiện Polling (truy vấn lặp) cập nhật số lượng thông báo chưa đọc của user sau mỗi 30 giây
                    let userId = '{{ Auth::id() ?? "guest" }}';
                    if (userId !== 'guest') {
                        const notifBadge = document.getElementById('notificationBadge');
                        const endpoint = '{{ route('notifications.unread-count') }}';

                        const refreshNotifications = () => {
                            fetch(endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                .then(response => response.ok ? response.json() : null)
                                .then(data => {
                                    if (!notifBadge || !data) return;
                                    const count = Number(data.unread_count || 0);
                                    notifBadge.textContent = count;
                                    if (count > 0) {
                                        notifBadge.style.display = 'block';
                                    } else {
                                        notifBadge.style.display = 'none';
                                    }
                                })
                                .catch(() => {});
                        };

                        refreshNotifications();
                        setInterval(refreshNotifications, 30000); // Gửi yêu cầu lặp mỗi 30s

                        // 3. Xử lý sự kiện click vào từng thông báo trong dropdown:
                        // Gửi yêu cầu PATCH đánh dấu đã đọc lên máy chủ trước khi chuyển tiếp khách hàng sang URL chi tiết đơn hàng/tin tức
                        const dropdownContainer = document.querySelector('.notification-dropdown-body');
                        if (dropdownContainer) {
                            dropdownContainer.addEventListener('click', function(e) {
                                const item = e.target.closest('.notification-dropdown-item');
                                if (!item) return;

                                e.preventDefault();
                                const actionUrl = item.getAttribute('data-action-url');
                                const readUrl = item.getAttribute('data-read-url');
                                const isUnread = item.classList.contains('unread');

                                if (isUnread && readUrl) {
                                    fetch(readUrl, {
                                        method: 'PATCH',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    })
                                    .then(() => {
                                        window.location.href = actionUrl;
                                    })
                                    .catch((err) => {
                                        console.error('Error marking notification as read:', err);
                                        window.location.href = actionUrl;
                                    });
                                } else {
                                    window.location.href = actionUrl;
                                }
                            });
                        }
                    }
                });
            </script>

            <!-- Khối Tài khoản / Đăng nhập / Bảng điều khiển quản trị (Admin Panel) -->
            @auth
                <div class="action-item relative group" style="position: relative;">
                    <a href="/profile" style="display:flex; flex-direction:column; align-items:center;">
                        <i class="fa-regular fa-circle-user {{ request()->is('profile*') ? 'text-fuchsia-400 animate-pulse' : '' }}"></i>
                        <!-- Chỉ lấy từ đầu tiên trong tên đầy đủ của người dùng để hiển thị cho gọn -->
                        <span style="max-width: 70px; overflow: hidden; text-overflow: ellipsis;">{{ explode(' ', Auth::user()->full_name)[0] }}</span>
                    </a>
                    <!-- Menu dropdown chứa thông tin trang cá nhân và nút Đăng xuất -->
                    <div class="user-dropdown">
                        <!-- Nếu tài khoản thuộc nhóm Admin/Quản lý/Kỹ thuật, hiển thị thêm nút chuyển sang Admin Dashboard -->
                        @if(in_array(Auth::user()->role_id, [1, 2, 4]))
                            <a href="{{ route('admin.dashboard') }}" style="color: #d70018; font-weight: bold;">
                                <i class="fa-solid fa-user-shield"></i> {{ __('ui.admin_panel') }}
                            </a>
                        @endif
                        <a href="/profile">{{ __('ui.profile') }}</a>
                        <a href="javascript:void(0)" onclick="fetch('{{ route('logout') }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).finally(()=>{window.location.href='/Home';})">{{ __('ui.logout') }}</a>
                    </div>
                </div>
            @else
                <!-- Khách vãng lai chưa đăng nhập sẽ hiển thị nút Đăng nhập / Đăng ký -->
                <a href="{{ route('login_register') }}" class="action-item">
                    <i class="fa-regular fa-circle-user {{ request()->is('login*') || request()->is('login-register*') ? 'text-fuchsia-400 animate-pulse' : '' }}"></i>
                    <span>{{ __('ui.login') }}</span>
                </a>
            @endauth
        </div>
    </div>
</header>

<!-- ============================================================
     PHẦN 3: MEGA MENU - THƯ MỤC SẢN PHẨM KHỔ LỚN ĐA CẤP
     ============================================================ -->
<div class="mega-menu-overlay" id="megaMenuOverlay"></div>
<div class="mega-menu" id="megaMenu">
    <div class="container mega-menu-inner">
        <!-- Cột bên trái: Danh sách các Danh mục sản phẩm cha chính -->
        <div class="mega-col-left">
            @foreach($headerCategories as $cat)
                <a href="{{ $cat->slug ? route('products.category', $cat->slug) : route('products.index') }}" class="mega-cat-item {{ $loop->first ? 'active' : '' }}"
                   data-cat="{{ $cat->category_id }}">
                    <i class="fa-solid {{ $categoryIcons[$cat->name] ?? 'fa-tag' }}"></i>
                    <span>{{ $cat->name }}</span>
                    <i class="fa-solid fa-angle-right mega-arrow"></i>
                </a>
            @endforeach
            <!-- Các lối tắt tiện ích khác trên hệ thống -->
            <a href="#" class="mega-cat-item"><i class="fa-solid fa-gamepad"></i><span>{{ __('ui.trade_in_renew') }}</span></a>
            <a href="#" class="mega-cat-item"><i class="fa-solid fa-tags"></i><span>{{ __('ui.used_products') }}</span></a>
            <a href="#" class="mega-cat-item"><i class="fa-solid fa-percent"></i><span>{{ __('ui.promotions') }}</span></a>
            <a href="{{ route('rewards.index') }}" class="mega-cat-item"><i class="fa-solid fa-gift"></i><span>{{ __('ui.rewards_page') }}</span></a>
            <a href="{{ route('articles.index') }}" class="mega-cat-item"><i class="fa-solid fa-newspaper"></i><span>{{ __('ui.tech_news') }}</span></a>
        </div>
        <!-- Cột bên phải: Hiển thị chi tiết dòng sản phẩm con, thương hiệu, nhu cầu tương ứng với danh mục cha đang hoạt động (Active) -->
        <div class="mega-col-right">
            @foreach($headerCategories as $cat)
                <div class="mega-detail-panel {{ $loop->first ? 'active' : '' }}" data-panel="{{ $cat->category_id }}">
                    <!-- Nhóm 1: Các dòng sản phẩm con (Children categories) -->
                    @if($cat->children->count())
                        <div class="mega-section mb-6">
                            <h4 class="mega-section-title">{{ __('ui.product_lines', ['name' => $cat->name]) }}</h4>
                            <div class="mega-tags">
                                @foreach($cat->children as $child)
                                    <a href="{{ $child->slug ? route('products.category', $child->slug) : route('products.index') }}" class="mega-tag">{{ $child->name }}</a>
                                @endforeach
                                <a href="{{ $cat->slug ? route('products.category', $cat->slug) : route('products.index') }}" class="mega-tag see-all">{{ __('ui.view_all_cat', ['name' => $cat->name]) }}</a>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Nhóm 2: Các thương hiệu nổi tiếng theo từng danh mục cụ thể -->
                    <div class="mega-section mb-6">
                        <h4 class="mega-section-title">{{ __('ui.popular_brands') }}</h4>
                        <div class="mega-tags">
                            @php
                                $brands = [];
                                if(Str::contains($cat->name, 'Laptop')) $brands = ['Apple (MacBook)', 'Asus', 'HP', 'Dell', 'Lenovo', 'MSI', 'Acer'];
                                elseif(Str::contains($cat->name, 'Điện thoại')) $brands = ['iPhone', 'Samsung', 'Oppo', 'Xiaomi', 'Vivo', 'Realme'];
                                elseif(Str::contains($cat->name, 'Tablet')) $brands = ['iPad', 'Samsung', 'Xiaomi', 'Lenovo'];
                            @endphp
                            @foreach($brands as $brand)
                                <a href="{{ route('products.category', $cat->slug) }}?brand={{ Str::before($brand, ' ') }}" class="mega-tag">{{ $brand }}</a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Nhóm 3: Lọc sản phẩm nhanh theo nhu cầu sử dụng (Chỉ hiển thị cho Laptop và Điện thoại) -->
                    @if(Str::contains($cat->name, ['Laptop', 'Điện thoại']))
                        <div class="mega-section mb-6">
                            <h4 class="mega-section-title">{{ __('ui.choose_by_need') }}</h4>
                            <div class="mega-tags">
                                <a href="{{ route('products.category', $cat->slug) }}?needs=gaming" class="mega-tag">{{ __('ui.need_gaming') }}</a>
                                <a href="{{ route('products.category', $cat->slug) }}?needs=student" class="mega-tag">{{ __('ui.need_student') }}</a>
                                <a href="{{ route('products.category', $cat->slug) }}?eco_friendly=1" class="mega-tag">{{ __('ui.need_eco') }}</a>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('products.category', $cat->slug) }}" class="text-primary font-bold hover:underline">
                            <i class="fa-solid fa-arrow-right-long mr-2"></i> {{ __('ui.view_all_cat', ['name' => $cat->name]) }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ============================================================
     PHẦN 4: MODAL LỰA CHỌN TỈNH THÀNH PHỐ
     ============================================================ -->
<div class="province-modal-overlay" id="provinceModalOverlay">
    <div class="province-modal">
        <!-- Tiêu đề modal và ô tìm kiếm nhanh tỉnh thành -->
        <div class="province-modal-header">
            <div class="province-search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="provinceSearchInput" placeholder="{{ __('ui.province_search') }}">
            </div>
            <button class="province-close-btn" id="provinceCloseBtn">{{ __('ui.province_close') }} <i class="fa-solid fa-xmark"></i></button>
        </div>
        <p class="province-hint">{{ __('ui.province_hint') }}</p>
        <!-- Danh sách các tỉnh thành phố cuộn dọc, tích hợp đánh dấu vị trí hiện tại -->
        <div class="province-list" id="provinceList">
            @foreach($provinces as $prov)
                <div class="province-item {{ $prov === 'TP. Hồ Chí Minh' ? 'selected' : '' }}" data-province="{{ $prov }}">
                    {{ $prov }}
                    @if($prov === 'TP. Hồ Chí Minh')
                        <i class="fa-solid fa-circle-check"></i>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ============================================================
     PHẦN 5: KHU VỰC CSS BỔ SUNG CHO DROPDOWN VÀ LANGUAGE SWITCHER
     ============================================================ -->
<style>
.notification-dropdown{display:none; position:absolute; top:100%; right:0; width:360px; background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 20px 50px rgba(0,0,0,.16); overflow:hidden; z-index:1002;}
.action-item.group:hover .notification-dropdown{display:block;}
.notification-dropdown-header{display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:#f8fafc; border-bottom:1px solid #e5e7eb; font-size:13px;}
.notification-dropdown-header a{color:#0046ab; font-weight:700;}
.notification-dropdown-body{max-height:360px; overflow:auto;}
.notification-dropdown-item{display:flex; gap:10px; padding:12px 14px; border-bottom:1px solid #f3f4f6; align-items:flex-start;}
.notification-dropdown-item.unread{background:#f8fbff;}
.notification-dropdown-item:hover{background:#f0f7ff;}
.notification-dot{width:9px; height:9px; border-radius:50%; background:#d1d5db; margin-top:5px; flex-shrink:0;}
.notification-dropdown-item.unread .notification-dot{background:#0046ab;}
.notification-content{min-width:0; flex:1;}
.notification-title{font-size:13px; font-weight:700; color:#111827; margin-bottom:3px;}
.notification-text{font-size:12px; color:#4b5563; line-height:1.45;}
.notification-time{font-size:11px; color:#9ca3af; margin-top:4px;}
.notification-empty{padding:18px 14px; font-size:13px; color:#6b7280; text-align:center;}

/* Language Switcher CSS */
.lang-switcher{position:relative; display:inline-flex; align-items:center;}
.lang-switcher-btn{display:flex; align-items:center; justify-content:center; gap:5px; background:transparent; border:none; padding:2px 8px; color:#fff; font-size:12px; font-weight:500; cursor:pointer; transition:opacity .2s;}
.lang-switcher-btn:hover{opacity:0.8;}
.lang-switcher-btn .fa-globe{font-size:13px;}
.lang-dropdown{display:none; position:absolute; top:calc(100% + 8px); right:0; background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 12px 36px rgba(0,0,0,.15); overflow:hidden; z-index:1100; min-width:160px;}
.lang-dropdown.show{display:block;}
.lang-option{display:flex; align-items:center; gap:10px; padding:10px 14px; font-size:13px; color:#374151; text-decoration:none; transition:background .15s;}
.lang-option:hover{background:#f0f7ff; color:#0046ab;}
.lang-option.active{background:#f0f7ff; color:#0046ab; font-weight:700;}
.lang-option .fa-check{margin-left:auto; font-size:11px; color:#0046ab;}
.lang-flag{font-size:18px; line-height:1;}
</style>

<!-- ============================================================
     PHẦN 6: JAVASCRIPT ĐIỀU KHIỂN ĐÓNG/MỞ DROPDOWN NGÔN NGỮ
     ============================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const langBtn = document.getElementById('langToggleBtn');
    const langDropdown = document.getElementById('langDropdown');
    if (langBtn && langDropdown) {
        // Lắng nghe sự kiện click nút đổi ngôn ngữ
        langBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            langDropdown.classList.toggle('show');
        });
        // Click ra bên ngoài sẽ ẩn dropdown
        document.addEventListener('click', function(e) {
            if (!langBtn.contains(e.target) && !langDropdown.contains(e.target)) {
                langDropdown.classList.remove('show');
            }
        });
    }
});
</script>