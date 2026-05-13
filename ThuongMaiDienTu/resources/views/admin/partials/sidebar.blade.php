{{--
|--------------------------------------------------------------------------
| ADMIN SIDEBAR - File riêng biệt để team dễ bảo trì
|--------------------------------------------------------------------------
| Sidebar điều hướng chính cho trang quản trị.
| Phân quyền hiển thị menu dựa theo role_id của user đang đăng nhập.
| - Admin (role_id = 1): Thấy tất cả menu.
| - Quản lý (role_id = 2): Thấy Đơn hàng, Sản phẩm.
| - Khách hàng (role_id = 3): Không thể truy cập admin.
--}}

<aside id="sidebar" class="w-64 bg-slate-900 text-white flex flex-col h-full shadow-2xl z-40 shrink-0
           fixed lg:static inset-y-0 left-0 transform -translate-x-full lg:translate-x-0
           transition-transform duration-300 ease-in-out">

    {{-- LOGO --}}
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-700">
        <a href="{{ route('admin.dashboard') }}"
            class="text-xl font-bold text-yellow-400 flex items-center gap-2 hover:text-white transition">
            <i class="fa-solid fa-bolt-lightning"></i> DIENMAYPRO
        </a>
        <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white text-lg p-1"
            title="Đóng menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- MENU ITEMS --}}
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">

        {{-- ===== NHÓM: TỔNG QUAN ===== --}}
        <div class="text-xs text-slate-400 font-bold mb-4 uppercase tracking-wider">Tổng quan</div>

        <a href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ (request()->routeIs('admin.dashboard') || request()->routeIs('dashboard') || request()->is('admin')) ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-gauge-high w-5"></i> Dashboard
        </a>

        <a href="{{ route('admin.kpi.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.kpi.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-chart-pie w-5"></i> Thống kê KPI
        </a>

        {{-- ===== NHÓM: QUẢN LÝ BÁN HÀNG ===== --}}
        <div class="text-xs text-slate-400 font-bold mb-4 uppercase tracking-wider">Quản lý Bán Hàng</div>

        <a href="{{ Route::has('admin.orders.index') ? route('admin.orders.index') : '#' }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->is('admin/orders*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-cart-shopping w-5"></i> Đơn hàng
        </a>

        <a href="{{ route('admin.cashbooks.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->is('admin/cashbooks*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-wallet w-5"></i> Sổ Quỹ
        </a>

        {{-- ===== NHÓM: SẢN PHẨM & NỘI DUNG ===== --}}
        <div class="text-xs text-slate-400 font-bold mt-6 mb-4 uppercase tracking-wider">Sản phẩm & Nội dung</div>

        <a href="{{ route('admin.products.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->is('admin/products*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-box w-5"></i> Sản phẩm
        </a>

        <a href="{{ route('admin.categories.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->is('admin/categories*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-list w-5"></i> Danh mục
        </a>

        <a href="{{ route('admin.articles.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->is('admin/articles*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-newspaper w-5"></i> Bài viết & CMS
        </a>

        {{-- ===== NHÓM: QUẢN LÝ KHO ===== --}}
        <div class="text-xs text-slate-400 font-bold mt-6 mb-4 uppercase tracking-wider">Quản lý Kho</div>

        <a href="{{ route('admin.suppliers.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->is('admin/suppliers*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-truck-field w-5"></i> Nhà cung cấp
        </a>

        <a href="{{ route('admin.purchase-orders.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->is('admin/purchase-orders*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-file-invoice-dollar w-5"></i> Nhập kho
        </a>

        <a href="{{ route('admin.inventory.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ (request()->is('admin/inventory*') || request()->routeIs('admin.inventory*')) ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-warehouse w-5"></i> Tồn kho (IMEI)
        </a>

        {{-- ===== NHÓM: GIAO DIỆN ===== --}}
        <div class="text-xs text-slate-400 font-bold mt-6 mb-4 uppercase tracking-wider">Giao diện</div>

        <a href="{{ Route::has('admin.settings.theme') ? route('admin.settings.theme') : '#' }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.settings.theme') ? 'bg-pink-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-wand-magic-sparkles w-5"></i> Tùy biến Giao diện
        </a>

        {{-- ===== NHÓM: HỆ THỐNG (Chỉ Admin thấy) ===== --}}
        @if(Auth::check() && Auth::user()->role_id == 1)
            <div class="text-xs text-slate-400 font-bold mt-6 mb-4 uppercase tracking-wider">Hệ thống</div>

            <a href="{{ route('admin.users.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                               {{ request()->is('admin/permissions*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                <i class="fa-solid fa-users w-5"></i> Tài khoản
            </a>

            <a href="{{ Route::has('admin.settings.index') ? route('admin.settings.index') : '#' }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                               {{ request()->routeIs('admin.settings*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                <i class="fa-solid fa-gear w-5"></i> Cài đặt
            </a>

            <a href="#"
                class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                               {{ request()->routeIs('admin.logs*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                <i class="fa-solid fa-clock-rotate-left w-5"></i> Nhật ký
            </a>
        @endif
    </nav>

    {{-- USER INFO & BACK BUTTON --}}
    <div class="p-4 border-t border-slate-700 text-sm shrink-0">
        <div class="flex items-center gap-3 mb-4 text-slate-300">
            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center">
                <i class="fa-solid fa-user"></i>
            </div>
            <div>
                <div class="font-bold text-white">{{ Auth::check() ? Auth::user()->full_name : 'Admin' }}</div>
                <div class="text-xs text-green-400">{{ Auth::check() && Auth::user()->role ? Auth::user()->role->name : 'Quản trị viên' }}</div>
            </div>
        </div>
        <a href="/" class="block w-full text-center py-2 bg-slate-800 hover:bg-slate-700 rounded transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Trở về Web
        </a>
    </div>
</aside>