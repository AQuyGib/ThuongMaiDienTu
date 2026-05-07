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
                   {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-gauge-high w-5"></i> Dashboard
        </a>

        {{-- ===== NHÓM: QUẢN LÝ BÁN HÀNG ===== --}}
        <div class="text-xs text-slate-400 font-bold mt-6 mb-4 uppercase tracking-wider">Quản lý Bán Hàng</div>

        <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.orders*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-clipboard-list w-5"></i> Đơn hàng
        </a>

        <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.warranties*') ? 'bg-yellow-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-wrench w-5 text-yellow-400"></i> Y/c Bảo hành
        </a>

        <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.returns*') ? 'bg-purple-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-right-left w-5 text-purple-400"></i> Y/c Đổi Trả
        </a>

        {{-- ===== NHÓM: SẢN PHẨM ===== --}}
        <div class="text-xs text-slate-400 font-bold mt-6 mb-4 uppercase tracking-wider">Sản phẩm</div>

        <a href="{{ route('admin.products.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.products*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-box w-5"></i> Sản phẩm
        </a>

        <a href="{{ route('admin.categories.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.categories*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-list w-5"></i> Danh mục
        </a>

        <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.brands*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-tags w-5"></i> Thương hiệu
        </a>

        <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.vouchers*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-ticket w-5"></i> Mã giảm giá
        </a>

        {{-- ===== NHÓM: KHO HÀNG ===== --}}
        <div class="text-xs text-slate-400 font-bold mt-6 mb-4 uppercase tracking-wider">Kho hàng</div>

        <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.suppliers*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-truck-field w-5"></i> Nhà cung cấp
        </a>

        <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.purchase-orders*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-file-invoice-dollar w-5"></i> Phiếu nhập kho
        </a>

        <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.inventory*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-warehouse w-5"></i> Tồn kho (IMEI)
        </a>

        {{-- ===== NHÓM: TRANG CHỦ & GIAO DIỆN ===== --}}
        <div class="text-xs text-slate-400 font-bold mt-6 mb-4 uppercase tracking-wider">Giao diện</div>

        <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ request()->routeIs('admin.homepage*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <i class="fa-solid fa-palette w-5"></i> Trang chủ
        </a>

        {{-- ===== NHÓM: HỆ THỐNG (Chỉ Admin thấy) ===== --}}
        @if(Auth::check() && Auth::user()->role_id == 1)
            <div class="text-xs text-slate-400 font-bold mt-6 mb-4 uppercase tracking-wider">Hệ thống</div>

            <a href="#"
                class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                               {{ request()->routeIs('admin.users*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                <i class="fa-solid fa-users w-5"></i> Tài khoản
            </a>

            <a href="#"
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