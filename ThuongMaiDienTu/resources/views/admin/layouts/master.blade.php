<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị hệ thống') - DIENMAYPRO Admin</title>
    <!-- Favicon (Logo Sét của Web) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 384 512' fill='%230046ab'><path d='M0 256L0 288c0 17.7 14.3 32 32 32l104.7 0L88.9 455c-6.8 17.1 5.8 36 24.2 36c11.3 0 21.6-6 26.8-15.6l176-320c9-16.3-.2-36.4-18.9-36.4l-123.8 0L222.1 57c6.8-17.1-5.8-36-24.2-36c-11.3 0-21.6 6-26.8 15.6L1.1 228.3C-.2 230.9 0 233.9 0 236.9v19.1z'/></svg>">
    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Bootstrap JS bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Vite: Tailwind CSS + JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @stack('styles')
</head>

<body class="bg-[#f8fafc] font-sans flex h-screen overflow-hidden text-slate-900">

    {{-- MOBILE OVERLAY --}}
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-30 hidden lg:hidden transition-all duration-500" onclick="toggleSidebar()"></div>

    {{-- SIDEBAR (file riêng) --}}
    @include('admin.partials.sidebar')

    {{-- MAIN CONTENT --}}
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        {{-- TOPBAR --}}
        @include('admin.partials.topbar')

        {{-- NỘI DUNG CHÍNH --}}
        <div id="joly-main-container" class="flex-1 p-6 sm:p-10 overflow-y-auto custom-scrollbar bg-slate-50/50">
            {{-- Thông báo Flash Message --}}
            @if(session('success'))
                <div class="mb-8 p-6 bg-white border-l-4 border-emerald-500 shadow-xl shadow-emerald-500/5 rounded-2xl flex items-center gap-4 animate-in slide-in-from-top duration-500">
                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-emerald-600 font-black uppercase tracking-widest leading-none mb-1">Thành công</p>
                        <p class="text-sm font-bold text-slate-700 leading-none">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-8 p-6 bg-white border-l-4 border-rose-500 shadow-xl shadow-rose-500/5 rounded-2xl flex items-center gap-4 animate-in slide-in-from-top duration-500">
                    <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-rose-500/20">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-rose-600 font-black uppercase tracking-widest leading-none mb-1">Lỗi hệ thống</p>
                        <p class="text-sm font-bold text-slate-700 leading-none">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                @yield('content')
            </div>
        </div>
    </main>

    {{-- SCRIPT chung --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            window.dispatchEvent(new CustomEvent('admin-sidebar-toggle'));
        }

        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: '{{ session('error') }}',
                    showConfirmButton: false,
                    timer: 4500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            @endif
        });
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
    @stack('scripts')
</body>
</html>
