@extends('admin.layouts.master')

@section('title', 'Dashboard thông báo')
@section('page-title', 'Dashboard thông báo')

@section('content')
<!-- TRANG DASHBOARD PHÂN TÍCH VÀ THỐNG KÊ THÔNG BÁO CHO QUẢN TRỊ VIÊN
     Cung cấp cái nhìn trực quan về tổng số lượng thông báo được phát hành, tỷ lệ chưa đọc, 
     biểu đồ biến động theo ngày/tháng và cơ cấu phân loại thông báo.
-->
<div class="space-y-6">
    <!-- KHỐI TIÊU ĐỀ TRANG VÀ CÁC NÚT ĐIỀU HƯỚNG NHANH -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard thông báo</h1>
            <p class="text-slate-500 mt-1">Tổng quan nhanh về hoạt động thông báo trong hệ thống.</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                <i class="fa-regular fa-list-alt mr-2"></i> Danh sách thông báo
            </a>
            <a href="{{ route('admin.notifications.create') }}" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition">
                <i class="fa-solid fa-paper-plane mr-2"></i> Tạo thông báo
            </a>
        </div>
    </div>

    <!-- KHỐI HỘP THỐNG KÊ NHANH (STATS CARDS) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Tổng</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Chưa đọc</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($stats['unread']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Hôm nay</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($stats['today']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Tháng này</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($stats['month']) }}</div>
        </div>
    </div>

    <!-- NẠP THÀNH PHẦN BIỂU ĐỒ DÙNG CHUNG (CHARTS COMPONENT) -->
    @include('admin.notifications.partials.charts')

    <!-- BỐ CỤC PHÂN TÍCH CƠ CẤU VÀ GỢI Ý VẬN HÀNH -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Top các loại thông báo phổ biến nhất trong hệ thống -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h2 class="font-black text-slate-900 text-lg mb-2">Top loại thông báo</h2>
            <div class="space-y-3">
                @foreach($topTypes as $item)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <div class="font-semibold text-slate-700">{{ $typeOptions[$item->type] ?? $item->type }}</div>
                        <div class="font-black text-slate-900">{{ $item->total }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Gợi ý vận hành và các mẹo quản trị -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h2 class="font-black text-slate-900 text-lg mb-2">Gợi ý vận hành</h2>
            <ul class="space-y-3 text-slate-600 text-sm leading-6">
                <li>• Theo dõi tồn kho thấp để tránh hết hàng đột ngột.</li>
                <li>• Gửi khuyến mãi theo nhóm người dùng để tăng chuyển đổi.</li>
                <li>• Ưu tiên review và bài viết mới để tăng tương tác nội dung.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

