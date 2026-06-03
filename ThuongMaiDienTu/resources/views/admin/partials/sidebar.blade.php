@php
    $isEn = app()->getLocale() === 'en';
    $menu = [
        [
            'label' => $isEn ? 'Dashboard' : 'Bảng điều khiển',
            'route' => route('admin.dashboard'),
            'icon' => 'fa-solid fa-house',
            'active' => request()->routeIs('admin.dashboard') || request()->is('admin'),
            'section' => $isEn ? 'Overview' : 'Tổng quan'
        ],
        [
            'label' => $isEn ? 'KPI Statistics' : 'Thống kê KPI',
            'route' => route('admin.kpi.index'),
            'icon' => 'fa-solid fa-chart-line',
            'active' => request()->routeIs('admin.kpi.*'),
            'section' => $isEn ? 'Overview' : 'Tổng quan'
        ],
        [
            'label' => $isEn ? 'Orders' : 'Đơn hàng',
            'route' => Route::has('admin.orders.index') ? route('admin.orders.index') : '#',
            'icon' => 'fa-solid fa-shopping-bag',
            'active' => request()->is('admin/orders*'),
            'section' => $isEn ? 'Business' : 'Kinh doanh'
        ],
        [
            'label' => $isEn ? 'Customers' : 'Khách hàng',
            'route' => route('admin.customers.index'),
            'icon' => 'fa-solid fa-user-group',
            'active' => request()->is('admin/customers*'),
            'section' => $isEn ? 'Business' : 'Kinh doanh'
        ],
        [
            'label' => $isEn ? 'Cashbook & Expenses' : 'Sổ Quỹ & Thu chi',
            'route' => route('admin.cashbooks.index'),
            'icon' => 'fa-solid fa-vault',
            'active' => request()->is('admin/cashbooks*'),
            'section' => $isEn ? 'Business' : 'Kinh doanh'
        ],
        [
            'label' => $isEn ? 'Service Invoices' : 'Hóa đơn dịch vụ',
            'route' => route('admin.service-invoices.index'),
            'icon' => 'fa-solid fa-file-invoice-dollar',
            'active' => request()->is('admin/service-invoices*'),
            'section' => $isEn ? 'Business' : 'Kinh doanh'
        ],
        [
            'label' => $isEn ? 'Installments' : 'Hợp đồng trả góp',
            'route' => route('admin.installments.index'),
            'icon' => 'fa-solid fa-credit-card',
            'active' => request()->is('admin/installments*'),
            'section' => $isEn ? 'Business' : 'Kinh doanh'
        ],
        [
            'label' => $isEn ? 'Repair Tickets' : 'Phiếu sửa chữa',
            'route' => route('admin.repair-tickets.index'),
            'icon' => 'fa-solid fa-wrench',
            'active' => request()->is('admin/repair-tickets*'),
            'section' => $isEn ? 'Business' : 'Kinh doanh'
        ],
        [
            'label' => $isEn ? 'Warranty Claims' : 'Bảo hành & Đổi trả',
            'route' => route('admin.warranty-claims.index'),
            'icon' => 'fa-solid fa-shield-halved',
            'active' => request()->is('admin/warranty-claims*'),
            'section' => $isEn ? 'Business' : 'Kinh doanh'
        ],
        [
            'label' => $isEn ? 'Flash Sale' : 'Flash Sale',
            'route' => route('admin.flash-sales.index'),
            'icon' => 'fa-solid fa-bolt',
            'active' => request()->is('admin/flash-sales*'),
            'section' => $isEn ? 'Business' : 'Kinh doanh'
        ],
        [
            'label' => $isEn ? 'Products' : 'Sản phẩm',
            'route' => route('admin.products.index'),
            'icon' => 'fa-solid fa-box-open',
            'active' => request()->is('admin/products*'),
            'section' => $isEn ? 'Products & Inventory' : 'Sản phẩm & Kho'
        ],
        [
            'label' => $isEn ? 'Articles & CMS' : 'Bài viết & CMS',
            'route' => route('admin.articles.index'),
            'icon' => 'fa-solid fa-newspaper',
            'active' => request()->is('admin/articles*'),
            'section' => $isEn ? 'Products & Inventory' : 'Sản phẩm & Kho'
        ],
        [
            'label' => $isEn ? 'Inventory Management' : 'Quản lý Kho',
            'route' => route('admin.inventory.index'),
            'icon' => 'fa-solid fa-warehouse',
            'active' => request()->is('admin/inventory*') || request()->is('admin/purchase-orders*'),
            'section' => $isEn ? 'Products & Inventory' : 'Sản phẩm & Kho'
        ],
        [
            'label' => $isEn ? 'Videos' : 'Video',
            'route' => route('admin.videos.index'),
            'icon' => 'fa-solid fa-video',
            'active' => request()->is('admin/videos*'),
            'section' => $isEn ? 'Products & Inventory' : 'Sản phẩm & Kho'
        ],
        [
            'label' => $isEn ? 'Comments & Reviews' : 'Bình luận & Đánh giá',
            'route' => route('admin.comments.index'),
            'icon' => 'fa-solid fa-comments',
            'active' => request()->is('admin/comments*'),
            'section' => $isEn ? 'Products & Inventory' : 'Sản phẩm & Kho'
        ],
        [
            'label' => $isEn ? 'Warehouse Transfer' : 'Điều chuyển kho',
            'route' => route('admin.warehouse-transfers.index'),
            'icon' => 'fa-solid fa-truck-ramp-box',
            'active' => request()->is('admin/warehouse-transfers*'),
            'section' => $isEn ? 'Products & Inventory' : 'Sản phẩm & Kho'
        ],
        [
            'label' => $isEn ? 'Suppliers' : 'Nhà cung cấp',
            'route' => route('admin.suppliers.index'),
            'icon' => 'fa-solid fa-truck-field',
            'active' => request()->is('admin/suppliers*'),
            'section' => $isEn ? 'Products & Inventory' : 'Sản phẩm & Kho'
        ],
        [
            'label' => $isEn ? 'Categories' : 'Danh mục',
            'route' => route('admin.categories.index'),
            'icon' => 'fa-solid fa-layer-group',
            'active' => request()->is('admin/categories*'),
            'section' => $isEn ? 'Products & Inventory' : 'Sản phẩm & Kho'
        ],
        [
            'label' => $isEn ? 'Rewards' : 'Đổi thưởng',
            'route' => route('admin.rewards.index'),
            'icon' => 'fa-solid fa-gift',
            'active' => request()->routeIs('admin.rewards.index'),
            'section' => $isEn ? 'Settings' : 'Thiết lập'
        ],
        [
            'label' => $isEn ? 'Vouchers' : 'Voucher',
            'route' => route('admin.vouchers.index'),
            'icon' => 'fa-solid fa-ticket',
            'active' => request()->routeIs('admin.vouchers.*'),
            'section' => $isEn ? 'Settings' : 'Thiết lập'
        ],
        [
            'label' => $isEn ? 'Theme Customization' : 'Tùy biến Giao diện',
            'route' => route('admin.settings.theme'),
            'icon' => 'fa-solid fa-paint-brush',
            'active' => request()->routeIs('admin.settings.theme'),
            'section' => $isEn ? 'Settings' : 'Thiết lập'
        ],
        [
            'label' => $isEn ? 'Notifications' : 'Thông báo',
            'route' => route('admin.notifications.index'),
            'icon' => 'fa-regular fa-bell',
            'active' => request()->is('admin/notifications*'),
            'section' => $isEn ? 'Settings' : 'Thiết lập'
        ],
        [
            'label' => $isEn ? 'Home Management' : 'Quản lý Trang chủ',
            'route' => route('admin.home-sections.index'),
            'icon' => 'fa-solid fa-house-laptop',
            'active' => request()->is('admin/home-sections*'),
            'section' => $isEn ? 'Settings' : 'Thiết lập'
        ]
    ];

    if(Auth::check() && Auth::user()->role_id == 1) {
        $menu[] = [
            'label' => $isEn ? 'Accounts' : 'Tài khoản',
            'route' => route('admin.users.index'),
            'icon' => 'fa-solid fa-user-gear',
            'active' => request()->is('admin/users*') || request()->is('admin/permissions*'),
            'section' => $isEn ? 'Settings' : 'Thiết lập'
        ];
        $menu[] = [
            'label' => $isEn ? 'System Settings' : 'Cài đặt hệ thống',
            'route' => Route::has('admin.settings.index') ? route('admin.settings.index') : '#',
            'icon' => 'fa-solid fa-cog',
            'active' => request()->routeIs('admin.settings.index'),
            'section' => $isEn ? 'Settings' : 'Thiết lập'
        ];
        $menu[] = [
            'label' => $isEn ? 'Activity Logs' : 'Nhật ký hoạt động',
            'route' => Route::has('admin.activity-logs.index') ? route('admin.activity-logs.index') : '#',
            'icon' => 'fa-solid fa-clock-rotate-left',
            'active' => request()->is('admin/activity-logs*'),
            'section' => $isEn ? 'Settings' : 'Thiết lập'
        ];
    }

    $props = [
        'user' => [
            'full_name' => Auth::user()->full_name ?? 'Admin',
            'role_name' => optional(Auth::user()->role)->name ?? 'Administrator',
            'email' => Auth::user()->email ?? ''
        ],
        'menu' => $menu,
        'homeRoute' => route('admin.dashboard'),
        'logoutRoute' => route('logout'),
        'csrfToken' => csrf_token()
    ];
@endphp

<div id="joly-admin-sidebar" data-props='{!! json_encode($props, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}' class="h-full">
    {{-- Static fallback or loader --}}
    <div class="w-72 bg-slate-900 h-full flex flex-col items-center justify-center gap-4">
        <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $isEn ? 'Initializing Sidebar...' : 'Khởi tạo Sidebar...' }}</p>
    </div>
</div>
