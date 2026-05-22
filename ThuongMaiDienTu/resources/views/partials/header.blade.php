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
        'TP. Hồ Chí Minh', 'TP. Hà Nội', 'TP. Hải Phòng', 'TP. Đà Nẵng', 'TP. Cần Thơ', 'TP. Huế',
        'An Giang', 'Bắc Ninh', 'Cà Mau', 'Cao Bằng',
        'Đắk Lắk', 'Điện Biên', 'Đồng Nai', 'Đồng Tháp',
        'Gia Lai', 'Hà Tĩnh', 'Hưng Yên', 'Khánh Hòa',
        'Lai Châu', 'Lâm Đồng', 'Lạng Sơn', 'Lào Cai',
        'Nghệ An', 'Ninh Bình', 'Phú Thọ', 'Quảng Ngãi',
        'Quảng Ninh', 'Quảng Trị', 'Sơn La', 'Tây Ninh',
        'Thái Nguyên', 'Thanh Hóa', 'Tuyên Quang', 'Vĩnh Long',
    ];

    $currentUser = auth()->user();
    $headerNotifications = collect();
    $unreadNotificationCount = 0;

    if ($currentUser) {
        $headerNotifications = $currentUser->notifications()->limit(5)->get();
        $unreadNotificationCount = $currentUser->notifications()->whereNull('read_at')->count();
    }
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
            <a href="/orders" class="hover:text-white transition"><span><i class="fa-solid fa-truck"></i> Tra cứu đơn hàng</span></a>
            <span><i class="fa-solid fa-phone"></i> <strong>1800 2097</strong></span>
        </div>
    </div>
</div>

<!-- Header -->
<header class="header-main">
    <div class="container header-content">
        <a href="/" class="logo">
            <i class="fa-solid fa-bolt"></i>
            DIENMAY<span>PRO</span>
        </a>

        <div class="header-category-btn" id="categoryToggleBtn">
            <i class="fa-solid fa-bars"></i> Danh mục <i class="fa-solid fa-chevron-down" style="font-size:10px; margin-left:2px;"></i>
        </div>

        <div class="header-province-btn" id="provinceToggleBtn">
            <i class="fa-solid fa-location-dot"></i>
            <span id="selectedProvinceName">TP. Hồ Chí Minh</span>
            <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
        </div>

        <div class="search-bar">
            <form action="{{ route('search.index') }}" method="GET" id="globalSearchForm">
                <input type="text" name="q" id="globalSearchInput" placeholder="Bạn muốn mua gì hôm nay?" autocomplete="off">
                <button type="submit"><i class="fa-solid fa-search"></i></button>
            </form>
            
            <!-- Hộp gợi ý thông minh -->
            <div id="searchSuggestions" class="search-suggestions">
                {{-- Dữ liệu đổ từ JS --}}
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('globalSearchInput');
                const suggestionsBox = document.getElementById('searchSuggestions');
                let debounceTimer;

                if (searchInput && suggestionsBox) {
                    searchInput.addEventListener('input', function() {
                        const query = this.value.trim();
                        
                        clearTimeout(debounceTimer);
                        
                        if (query.length < 2) {
                            suggestionsBox.classList.remove('show');
                            return;
                        }

                        debounceTimer = setTimeout(() => {
                            fetch(`{{ route('api.search.suggestions') }}?q=${encodeURIComponent(query)}`)
                                .then(response => response.json())
                                .then(data => {
                                    renderSuggestions(data, query);
                                })
                                .catch(err => console.error('Search error:', err));
                        }, 300);
                    });

                    // Đóng khi click ngoài
                    document.addEventListener('click', function(e) {
                        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                            suggestionsBox.classList.remove('show');
                        }
                    });

                    // Hiện lại khi focus nếu đã có chữ
                    searchInput.addEventListener('focus', function() {
                        if (this.value.trim().length >= 2) {
                            suggestionsBox.classList.add('show');
                        }
                    });
                }

                function renderSuggestions(data, query) {
                    let html = '';

                    if (data.categories.length === 0 && data.products.length === 0) {
                        html = '<div class="no-results">Không tìm thấy kết quả cho "' + query + '"</div>';
                    } else {
                        // Nhóm danh mục
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

                        // Nhóm sản phẩm
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
                        
                        html += `<a href="{{ route('search.index') }}?q=${encodeURIComponent(query)}" class="suggestion-cat" style="justify-content: center; background: #f0f7ff; border-top: 1px solid #e5e7eb;">
                                    <strong>Xem tất cả kết quả cho "${query}"</strong>
                                 </a>`;
                    }

                    suggestionsBox.innerHTML = html;
                    suggestionsBox.classList.add('show');
                }
            });
        </script>

        <!-- Hành động -->
        <div class="header-actions">
            <a href="/orders" class="action-item">
                <i class="fa-solid fa-truck-fast"></i>
                <span>Tra cứu đơn</span>
            </a>
            @auth
                <div class="action-item group" style="position: relative;">
                    <a href="{{ route('notifications.index') }}" class="action-item" id="notificationBell" style="position: relative;">
                        <i class="fa-regular fa-bell"></i>
                        @if($unreadNotificationCount > 0)
                            <span id="notificationBadge" style="position: absolute; top: 0px; right: 8px; background: #d70018; color: #fff; font-size: 10px; font-weight: bold; padding: 1px 5px; border-radius: 10px;">{{ $unreadNotificationCount }}</span>
                        @else
                            <span id="notificationBadge" style="position: absolute; top: 0px; right: 8px; background: #d70018; color: #fff; font-size: 10px; font-weight: bold; padding: 1px 5px; border-radius: 10px; display:none;">0</span>
                        @endif
                        <span>Thông báo</span>
                    </a>
                    <div class="notification-dropdown">
                        <div class="notification-dropdown-header">
                            <strong>Thông báo mới</strong>
                            <a href="{{ route('notifications.index') }}">Xem tất cả</a>
                        </div>
                        <div class="notification-dropdown-body">
                            @forelse($headerNotifications as $notification)
                                <a href="javascript:void(0)" data-id="{{ $notification->notification_id }}" data-read-url="{{ route('notifications.read', $notification->notification_id) }}" data-action-url="{{ $notification->action_url ?: route('notifications.index') }}" class="notification-dropdown-item {{ $notification->read_at ? '' : 'unread' }}">
                                    <div class="notification-dot"></div>
                                    <div class="notification-content">
                                        <div class="notification-title">{{ $notification->title }}</div>
                                        <div class="notification-text">{{ \Illuminate\Support\Str::limit($notification->content, 70) }}</div>
                                        <div class="notification-time">{{ $notification->created_at?->diffForHumans() }}</div>
                                    </div>
                                </a>
                            @empty
                                <div class="notification-empty">Chưa có thông báo mới.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endauth
            <a href="{{ route('cart.index') }}" class="action-item" style="position: relative;">
                <i class="fa-solid fa-cart-shopping"></i>
                <span id="headerCartBadge" style="position: absolute; top: 0px; right: 8px; background: #d70018; color: #fff; font-size: 10px; font-weight: bold; padding: 1px 5px; border-radius: 10px; display: none;">0</span>
                <span>Giỏ hàng</span>
            </a>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let userId = '{{ Auth::id() ?? "guest" }}';
                    let savedCount = localStorage.getItem('cartCount_' + userId);
                    if(savedCount && parseInt(savedCount) > 0) {
                        let badge = document.getElementById('headerCartBadge');
                        if(badge) {
                            badge.innerText = savedCount;
                            badge.style.display = 'block';
                        }
                    }

                    // AJAX Polling for Client Unread Notifications Count
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
                        setInterval(refreshNotifications, 30000); // every 30 seconds

                        // Handle click on notification dropdown items via AJAX
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
            @auth
                <div class="action-item relative group" style="position: relative;">
                    <a href="/profile" style="display:flex; flex-direction:column; align-items:center;">
                        <i class="fa-regular fa-circle-user"></i>
                        <span style="max-width: 70px; overflow: hidden; text-overflow: ellipsis;">{{ explode(' ', Auth::user()->full_name)[0] }}</span>
                    </a>
                    <div class="user-dropdown">
                        @if(in_array(Auth::user()->role_id, [1, 2, 4]))
                            <a href="{{ route('admin.dashboard') }}" style="color: #d70018; font-weight: bold;">
                                <i class="fa-solid fa-user-shield"></i> Trang quản trị
                            </a>
                            <hr style="border: 0; border-top: 1px solid #eee; margin: 4px 0;">
                        @endif
                        <a href="/profile">Trang cá nhân</a>
                        <form action="{{ route('logout') ?? '/logout' }}" method="POST">
                            @csrf
                            <button type="submit">Đăng xuất</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login_register') }}" class="action-item">
                    <i class="fa-regular fa-circle-user"></i>
                    <span>Đăng nhập</span>
                </a>
            @endauth
        </div>
    </div>
</header>

<div class="mega-menu-overlay" id="megaMenuOverlay"></div>
<div class="mega-menu" id="megaMenu">
    <div class="container mega-menu-inner">
        <div class="mega-col-left">
            @foreach($headerCategories as $cat)
                <a href="{{ $cat->slug ? route('products.category', $cat->slug) : route('products.index') }}" class="mega-cat-item {{ $loop->first ? 'active' : '' }}"
                   data-cat="{{ $cat->category_id }}">
                    <i class="fa-solid {{ $categoryIcons[$cat->name] ?? 'fa-tag' }}"></i>
                    <span>{{ $cat->name }}</span>
                    <i class="fa-solid fa-angle-right mega-arrow"></i>
                </a>
            @endforeach
            <a href="#" class="mega-cat-item"><i class="fa-solid fa-gamepad"></i><span>Thu cũ đổi mới</span></a>
            <a href="#" class="mega-cat-item"><i class="fa-solid fa-tags"></i><span>Hàng cũ</span></a>
            <a href="#" class="mega-cat-item"><i class="fa-solid fa-percent"></i><span>Khuyến mãi</span></a>
            <a href="{{ route('articles.index') }}" class="mega-cat-item"><i class="fa-solid fa-newspaper"></i><span>Tin công nghệ</span></a>
        </div>
        <div class="mega-col-right">
            @foreach($headerCategories as $cat)
                <div class="mega-detail-panel {{ $loop->first ? 'active' : '' }}" data-panel="{{ $cat->category_id }}">
                    @if($cat->children->count())
                        <div class="mega-section mb-6">
                            <h4 class="mega-section-title">Dòng sản phẩm {{ $cat->name }}</h4>
                            <div class="mega-tags">
                                @foreach($cat->children as $child)
                                    <a href="{{ $child->slug ? route('products.category', $child->slug) : route('products.index') }}" class="mega-tag">{{ $child->name }}</a>
                                @endforeach
                                <a href="{{ $cat->slug ? route('products.category', $cat->slug) : route('products.index') }}" class="mega-tag see-all">Xem tất cả {{ $cat->name }}</a>
                            </div>
                        </div>
                    @endif
                    <div class="mega-section mb-6">
                        <h4 class="mega-section-title">Hãng sản xuất phổ biến</h4>
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
                    @if(Str::contains($cat->name, ['Laptop', 'Điện thoại']))
                        <div class="mega-section mb-6">
                            <h4 class="mega-section-title">Chọn theo nhu cầu</h4>
                            <div class="mega-tags">
                                <a href="{{ route('products.category', $cat->slug) }}?needs=gaming" class="mega-tag">🎮 Chơi game/Đồ họa</a>
                                <a href="{{ route('products.category', $cat->slug) }}?needs=student" class="mega-tag">🎓 Học tập/Văn phòng</a>
                                <a href="{{ route('products.category', $cat->slug) }}?eco_friendly=1" class="mega-tag">🌱 Thân thiện môi trường</a>
                            </div>
                        </div>
                    @endif
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('products.category', $cat->slug) }}" class="text-primary font-bold hover:underline">
                            <i class="fa-solid fa-arrow-right-long mr-2"></i> Xem tất cả {{ $cat->name }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

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
</style>