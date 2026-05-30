@extends('admin.layouts.master')

@section('title', app()->getLocale() === 'en' ? 'Staff KPI Statistics' : 'Thống kê KPI Nhân sự')
@section('page-title', app()->getLocale() === 'en' ? 'Measure Employee Performance' : 'Đo lường Hiệu suất Nhân sự')

@section('content')
<div id="admin-kpi-dashboard" data-props='@json($props)'>
    {{-- Loading placeholder --}}
    <div class="flex items-center justify-center min-h-[600px]">
        <div class="flex flex-col items-center gap-4">
            <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-slate-500 font-bold animate-pulse">{{ app()->getLocale() === 'en' ? 'Loading KPI data...' : 'Đang tải dữ liệu KPI...' }}</p>
        </div>
    </div>
</div>
@endsection
