@extends('admin.layouts.master')

@section('title', 'Chi tiết thông báo')
@section('page-title', 'Chi tiết thông báo')

@section('content')
<!-- TRANG HIỂN THỊ CHI TIẾT THÔNG BÁO HỆ THỐNG
     Cho phép xem tiêu đề, người nhận, trạng thái đã đọc/chưa đọc, nội dung đầy đủ
     cũng như siêu dữ liệu liên quan dưới dạng JSON đẹp mắt.
-->
<div class="max-w-4xl mx-auto space-y-6">
    <!-- KHỐI TIÊU ĐỀ & ĐIỀU HƯỚNG QUAY LẠI -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Chi tiết thông báo</h1>
            <p class="text-slate-500 mt-1">Xem đầy đủ nội dung và trạng thái thông báo.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Quay lại</a>
            <!-- Nếu chưa đọc, cung cấp nút đánh dấu đã đọc trực tiếp -->
            @unless($notification->read_at)
                <form method="POST" action="{{ route('admin.notifications.read', $notification->notification_id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">Đánh dấu đã đọc</button>
                </form>
            @endunless
        </div>
    </div>

    <!-- KHỐI CHI TIẾT THÔNG TIN THÔNG BÁO -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-8 space-y-6">
        <!-- Huy hiệu phân loại trạng thái đọc và loại thông báo -->
        <div class="flex items-center gap-3 flex-wrap">
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $notification->read_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                {{ $notification->read_at ? 'Đã đọc' : 'Chưa đọc' }}
            </span>
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-indigo-100 text-indigo-700">
                {{ $notification->type }}
            </span>
        </div>

        <div>
            <h2 class="text-2xl font-black text-slate-900">{{ $notification->title }}</h2>
            <div class="text-sm text-slate-500 mt-2">Gửi tới: <span class="font-semibold text-slate-700">{{ $notification->user->full_name ?? 'N/A' }}</span></div>
            <div class="text-sm text-slate-500">Thời gian: {{ $notification->created_at?->format('d/m/Y H:i:s') }}</div>
        </div>

        <!-- Khung hiển thị nội dung thông báo -->
        <div class="rounded-2xl bg-slate-50 p-5 text-slate-700 leading-7 whitespace-pre-line">{{ $notification->content }}</div>

        <!-- Đường dẫn hành động (Nếu có cấu hình) -->
        @if($notification->action_url)
            <div>
                <div class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Đường dẫn hành động</div>
                <a href="{{ $notification->action_url }}" class="text-indigo-600 font-bold hover:underline break-all">{{ $notification->action_url }}</a>
            </div>
        @endif

        <!-- Dữ liệu JSON metadata đính kèm (Thông tin sản phẩm, coupon...) -->
        @if($notification->data)
            <div>
                <div class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Dữ liệu bổ sung</div>
                <pre class="bg-slate-900 text-slate-100 rounded-2xl p-5 overflow-auto text-sm">{{ json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif
    </div>
</div>
@endsection

