@php
    $menu = [
        [
            'label' => 'Bảng điều khiển',
            'route' => route('admin.dashboard'),
            'icon' => 'fa-solid fa-house',
            'active' => request()->routeIs('admin.dashboard') || request()->is('admin'),
            'section' => 'Tổng quan'
        ],
        [
            'label' => 'Thống kê KPI',
            'route' => route('admin.kpi.index'),
            'icon' => 'fa-solid fa-chart-line',
            'active' => request()->routeIs('admin.kpi.*'),
            'section' => 'Tổng quan'
        ],
        [
            'label' => 'Đơn hàng',
            'route' => Route::has('admin.orders.index') ? route('admin.orders.index') : '#',
            'icon' => 'fa-solid fa-shopping-bag',
            'active' => request()->is('admin/orders*'),
            'section' => 'Kinh doanh'
        ],
        [
            'label' => 'Khách hàng',
            'route' => route('admin.customers.index'),
            'icon' => 'fa-solid fa-user-group',
            'active' => request()->is('admin/customers*'),
            'section' => 'Kinh doanh'
        ],
        [
            'label' => 'Sổ Quỹ & Thu chi',
            'route' => route('admin.cashbooks.index'),
            'icon' => 'fa-solid fa-vault',
            'active' => request()->is('admin/cashbooks*'),
            'section' => 'Kinh doanh'
        ],
        [
            'label' => 'Hóa đơn dịch vụ',
            'route' => route('admin.service-invoices.index'),
            'icon' => 'fa-solid fa-file-invoice-dollar',
            'active' => request()->is('admin/service-invoices*'),
            'section' => 'Kinh doanh'
        ],
        [
            'label' => 'Phiếu sửa chữa',
            'route' => route('admin.repair-tickets.index'),
            'icon' => 'fa-solid fa-wrench',
            'active' => request()->is('admin/repair-tickets*'),
            'section' => 'Kinh doanh'
        ],
        [
            'label' => 'Flash Sale',
            'route' => route('admin.flash-sales.index'),
            'icon' => 'fa-solid fa-bolt',
            'active' => request()->is('admin/flash-sales*'),
            'section' => 'Kinh doanh'
        ],
        [
            'label' => 'Sản phẩm',
            'route' => route('admin.products.index'),
            'icon' => 'fa-solid fa-box-open',
            'active' => request()->is('admin/products*'),
            'section' => 'Sản phẩm & Kho'
        ],
        [
            'label' => 'Bài viết & CMS',
            'route' => route('admin.articles.index'),
            'icon' => 'fa-solid fa-newspaper',
            'active' => request()->is('admin/articles*'),
            'section' => 'Sản phẩm & Kho'
        ],
        [
            'label' => 'Quản lý Kho',
            'route' => route('admin.inventory.index'),
            'icon' => 'fa-solid fa-warehouse',
            'active' => request()->is('admin/inventory*'),
            'section' => 'Sản phẩm & Kho'
        ],
        [
            'label' => 'Danh mục',
            'route' => route('admin.categories.index'),
            'icon' => 'fa-solid fa-layer-group',
            'active' => request()->is('admin/categories*'),
            'section' => 'Sản phẩm & Kho'
        ],
        [
            'label' => 'Tùy biến Giao diện',
            'route' => route('admin.settings.theme'),
            'icon' => 'fa-solid fa-paint-brush',
            'active' => request()->routeIs('admin.settings.theme'),
            'section' => 'Thiết lập'
        ],
        [
            'label' => 'Quản lý Trang chủ',
            'route' => route('admin.home-sections.index'),
            'icon' => 'fa-solid fa-house-laptop',
            'active' => request()->is('admin/home-sections*'),
            'section' => 'Thiết lập'
        ]
    ];

    if(Auth::check() && Auth::user()->role_id == 1) {
        $menu[] = [
            'label' => 'Tài khoản',
            'route' => route('admin.users.index'),
            'icon' => 'fa-solid fa-user-gear',
            'active' => request()->is('admin/users*') || request()->is('admin/permissions*'),
            'section' => 'Thiết lập'
        ];
        $menu[] = [
            'label' => 'Cài đặt hệ thống',
            'route' => Route::has('admin.settings.index') ? route('admin.settings.index') : '#',
            'icon' => 'fa-solid fa-cog',
            'active' => request()->routeIs('admin.settings.index'),
            'section' => 'Thiết lập'
        ];
        $menu[] = [
            'label' => 'Nhật ký hoạt động',
            'route' => Route::has('admin.activity-logs.index') ? route('admin.activity-logs.index') : '#',
            'icon' => 'fa-solid fa-clock-rotate-left',
            'active' => request()->is('admin/activity-logs*'),
            'section' => 'Thiết lập'
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
        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Khởi tạo Sidebar...</p>
    </div>
</div>
