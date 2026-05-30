@extends('admin.layouts.master')

@section('title', 'Quản lý Tài khoản')
@section('page-title', 'Quản lý Tài khoản')

@section('content')
<div class="container-fluid">
    {{-- React Mount Point --}}
    <div id="admin-user-management" data-props="{{ json_encode(['users' => $users, 'roles' => $roles, 'stats' => $stats]) }}">
        {{-- Fallback content while React loads --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-20 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
            <p class="text-slate-500 font-medium">Đang tải hệ thống quản trị...</p>
        </div>
    </div>
</div>

@push('scripts')
    @viteReactRefresh
    @vite(['resources/js/app.tsx'])
@endpush
@endsection