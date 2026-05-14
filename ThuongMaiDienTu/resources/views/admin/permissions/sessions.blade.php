@extends('admin.layouts.master')

@section('title', 'Quản lý thiết bị - ' . $user->full_name)
@section('page-title', 'Quản lý thiết bị: ' . $user->full_name)

@section('content')
<div class="container-fluid">
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="group inline-flex items-center text-slate-500 hover:text-blue-600 transition-colors font-medium text-sm">
            <i class="fa-solid fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> 
            Quay lại danh sách tài khoản
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mt-4 tracking-tight">
            Quản lý thiết bị
            <span class="text-slate-400 font-medium text-lg ml-2">/ {{ $user->full_name }}</span>
        </h1>
    </div>

    @php
        $sessionData = [
            'sessions' => $sessions,
            'userName' => $user->full_name,
            'revokeAllUrl' => route('admin.users.revoke', $user->user_id),
            'revokeUrl' => url('/admin/permissions/sessions'), 
            'csrfToken' => csrf_token(),
            'currentSessionId' => session()->getId()
        ];
    @endphp

    <div id="admin-session-management" data-props="{{ json_encode($sessionData) }}">
        {{-- Loading state --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-20 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
            <p class="text-slate-500 font-medium">Đang tải danh sách thiết bị...</p>
        </div>
    </div>
</div>
@endsection
