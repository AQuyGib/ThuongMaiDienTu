@extends('admin.layouts.master')

@section('title', 'Master Theme Editor')
@section('page-title', 'Tùy biến Giao diện Master')

@section('content')
<div id="admin-theme-settings" data-props='@json($props)'>
    {{-- Loading placeholder --}}
    <div class="flex items-center justify-center min-h-[600px] bg-slate-50 rounded-[2.5rem] border border-slate-200">
        <div class="flex flex-col items-center gap-4">
            <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-slate-500 font-bold animate-pulse uppercase tracking-widest text-xs">Đang khởi tạo Master Editor...</p>
        </div>
    </div>
</div>
@endsection
