{{-- TOPBAR ADMIN --}}
<header class="h-16 bg-white shadow-sm flex items-center justify-between px-4 sm:px-8 z-10 shrink-0">
    <div class="flex items-center gap-3">
        {{-- Nút Hamburger (chỉ hiện trên mobile/tablet) --}}
        <button onclick="toggleSidebar()" class="lg:hidden text-gray-600 hover:text-blue-600 text-xl p-1" title="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h2 class="text-base sm:text-xl font-bold text-gray-800 truncate">
            @yield('page-title', 'Dashboard')
        </h2>
    </div>
    <form method="GET" action="" class="flex bg-gray-100 rounded-lg border border-gray-200 overflow-hidden shrink-0">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm kiếm..."
            class="px-3 sm:px-4 py-2 bg-transparent focus:outline-none text-sm w-32 sm:w-48 md:w-64">
        <button type="submit" class="px-3 sm:px-4 text-gray-500 hover:text-blue-600">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>
</header>
