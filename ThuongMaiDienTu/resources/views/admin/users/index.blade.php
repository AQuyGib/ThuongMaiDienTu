@extends('admin.layouts.master')

@section('title', 'Quản lý tài khoản')

@section('content')
<div class="container-fluid">
    {{-- Header Section --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Hệ thống Tài khoản</h1>
            <nav class="flex text-sm text-slate-500 mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Admin</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                            <span class="font-medium">Tài khoản</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- React Mount Point --}}
    <div id="admin-user-management">
        {{-- Fallback content while React loads --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
            <p class="text-slate-500 font-medium">Đang tải giao diện quản lý...</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom styles for the user management page if needed */
</style>
@endpush
