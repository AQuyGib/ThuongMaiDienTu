{{-- TOPBAR ADMIN --}}
<header class="h-16 bg-white shadow-sm flex items-center justify-between px-4 sm:px-8 z-10 shrink-0">
    <div class="flex items-center gap-3">
        {{-- Nút Hamburger (hiện trên mọi màn hình) --}}
        <button onclick="toggleSidebar()" class="text-gray-600 hover:text-blue-600 text-xl p-1" title="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h2 class="text-base sm:text-xl font-bold text-gray-800 truncate">
            @yield('page-title', 'Dashboard')
        </h2>
    </div>
    {{-- Đã xóa ô Tìm kiếm chung --}}
</header>
