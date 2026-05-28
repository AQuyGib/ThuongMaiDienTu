@extends('admin.layouts.master')

@section('title', 'Bảng điều khiển')
@section('page-title', 'Bảng điều khiển')

@section('content')
<div class="space-y-8 animate-in fade-in duration-500">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Bảng điều khiển hệ thống</h1>
            <p class="text-slate-500 mt-1">Tổng quan tình hình kinh doanh và quản trị hệ thống.</p>
        </div>
        <div class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
            <div class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-calendar-day"></i>
                {{ now()->format('d/m/Y') }}
            </div>
            <div class="h-8 w-[1px] bg-slate-200"></div>
            <div class="text-sm text-slate-600 px-2">
                Chào, <span class="font-bold text-slate-900">{{ Auth::user()->full_name ?? 'Admin' }}</span>
            </div>
        </div>
    </div>

    {{-- Stats Grid - Cân bằng lại 4 mục --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Revenue Card --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-green-200 transition-all">
            <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tổng thu nhập</div>
                <div class="text-xl font-black text-slate-900">{{ number_format($stats['total_income']) }}đ</div>
            </div>
        </div>

        {{-- Total Expense Card --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-red-200 transition-all">
            <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-credit-card"></i>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tổng chi phí</div>
                <div class="text-xl font-black text-slate-900">{{ number_format($stats['total_expense']) }}đ</div>
            </div>
        </div>

        {{-- Products Card --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-blue-200 transition-all">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sản phẩm</div>
                <div class="text-xl font-black text-slate-900">{{ number_format($stats['total_products']) }}</div>
            </div>
        </div>

        {{-- Customers Card --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-purple-200 transition-all">
            <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-group"></i>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Khách hàng</div>
                <div class="text-xl font-black text-slate-900">{{ number_format($stats['total_users']) }}</div>
            </div>
        </div>
    </div>

    {{-- Thao tác nhanh - Làm đơn giản lại --}}
    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-wrap items-center gap-4">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider px-2">Thao tác nhanh:</span>
        <a href="{{ route('admin.products.index') }}" class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all">
            <i class="fa-solid fa-plus-circle text-indigo-500 group-hover:text-white"></i> Thêm sản phẩm
        </a>
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all">
            <i class="fa-solid fa-user-plus text-indigo-500 group-hover:text-white"></i> Quản lý User
        </a>
        <a href="{{ route('admin.purchase-orders.index') }}" class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all">
            <i class="fa-solid fa-file-invoice text-indigo-500 group-hover:text-white"></i> Nhập kho
        </a>
        <a href="{{ route('admin.cashbooks.index') }}" class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all">
            <i class="fa-solid fa-wallet text-indigo-500 group-hover:text-white"></i> Sổ quỹ
        </a>
    </div>

    {{-- Recent Orders Section - Full Width để dễ nhìn --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-black text-slate-900 text-lg">Đơn hàng mới nhất</h2>
                <p class="text-xs text-slate-500">Danh sách 5 giao dịch gần đây nhất trên hệ thống</p>
            </div>
            <a href="{{ route('admin.cart.index') }}" class="flex items-center gap-2 text-sm font-bold text-indigo-600 hover:underline">
                Xem tất cả đơn hàng
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-4">Mã Đơn</th>
                        <th class="px-8 py-4">Khách hàng</th>
                        <th class="px-8 py-4 text-right">Tổng thanh toán</th>
                        <th class="px-8 py-4 text-center">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stats['recent_orders'] as $order)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-5">
                            <span class="font-bold text-slate-900">#{{ $order->order_id }}</span>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs">
                                    {{ substr($order->user->full_name ?? 'K', 0, 1) }}
                                </div>
                                <span class="font-bold text-slate-700">{{ $order->user->full_name ?? 'Khách lẻ' }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-right font-black text-slate-900">{{ number_format($order->total_amount) }}đ</td>
                        <td class="px-8 py-5 text-center">
                            @php
                                $statusClasses = [
                                    'completed' => 'bg-green-100 text-green-700',
                                    'pending'   => 'bg-yellow-100 text-yellow-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                ];
                                $class = $statusClasses[$order->status] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $class }}">
                                {{ $order->status ?? 'Đang xử lý' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-4xl mb-2 opacity-20"></i>
                            <p class="font-medium text-sm">Chưa có đơn hàng nào.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
