<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị hệ thống') - TechZone Admin</title>
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Vite: Tailwind CSS + JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-gray-100 font-sans flex h-screen overflow-hidden">

    {{-- MOBILE OVERLAY --}}
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- SIDEBAR (file riêng) --}}
    @include('admin.partials.sidebar')

    {{-- MAIN CONTENT --}}
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        {{-- TOPBAR --}}
        @include('admin.partials.topbar')

        {{-- NỘI DUNG CHÍNH --}}
        <div class="flex-1 p-3 sm:p-4 md:p-8 overflow-y-auto">
            {{-- Thông báo Flash Message --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- SCRIPT chung --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (window.innerWidth >= 1024) {
                // Desktop: Ẩn/Hiện sidebar hoàn toàn
                sidebar.classList.toggle('lg:hidden');
            } else {
                // Mobile: Trượt sidebar và overlay
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function confirmDelete(event) {
            event.preventDefault();
            const form = event.target;
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: 'Bạn sẽ không thể hoàn tác thao tác này!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
            return false;
        }
    </script>
    @stack('scripts')
</body>

</html>
