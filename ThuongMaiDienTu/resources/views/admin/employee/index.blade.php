@extends('admin.layouts.master')

@section('title', 'Quản lý Nhân viên')
@section('page-title', 'Quản lý Nhân viên')

@section('content')
<div class="container-fluid">
    {{-- React Mount Point cho Employee Manager --}}
    <div id="admin-employee-management" data-props="{{ json_encode(['employees' => $employees, 'roles' => $roles, 'stats' => $stats]) }}">
        {{-- Trạng thái chờ trong lúc React component tải --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 p-24 flex flex-col items-center justify-center text-center shadow-xl">
            <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-6"></div>
            <h4 class="text-slate-800 dark:text-slate-100 font-black uppercase text-xs tracking-[0.2em] mb-1">Đang tải phân hệ nhân sự</h4>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Đồng bộ dữ liệu thời gian thực từ POS...</p>
        </div>
    </div>
</div>

@push('scripts')
    @viteReactRefresh
    @vite(['resources/js/app.tsx'])
@endpush
@endsection
