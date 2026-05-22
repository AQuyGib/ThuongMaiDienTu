@extends('layouts.app')
@section('title', $reward->name)

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 py-10">
  <div class="max-w-5xl mx-auto px-4">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
      <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        @if($reward->display_image)
          <div class="relative">
            <img src="{{ asset('storage/'.$reward->display_image) }}" class="w-full h-[420px] object-cover" alt="{{ $reward->name }}">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-transparent"></div>
          </div>
        @else
          <div class="relative h-[420px] bg-gradient-to-br from-indigo-500 via-violet-600 to-fuchsia-600">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,.25),transparent_35%)]"></div>
          </div>
        @endif
      </div>
      <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">{{ $reward->reward_category }}</span>
        <h1 class="text-3xl font-black text-slate-900 mt-3">{{ $reward->name }}</h1>
        <p class="text-slate-600 mt-3">{{ $reward->description }}</p>
        <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
          <div class="p-4 rounded-2xl bg-slate-50">
            <div class="text-slate-500">Giá điểm</div>
            <div class="text-2xl font-black text-violet-600">{{ number_format($reward->points_cost) }}</div>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50">
            <div class="text-slate-500">Loại</div>
            <div class="text-lg font-bold text-slate-900">{{ $reward->reward_type }}</div>
          </div>
        </div>
        <div class="mt-6 space-y-2 text-sm text-slate-600">
          @if($reward->discount_amount)
            <p>Giảm tiền: {{ number_format($reward->discount_amount) }}đ</p>
          @endif
          @if($reward->shipping_discount_amount)
            <p>Giảm ship: {{ number_format($reward->shipping_discount_amount) }}đ</p>
          @endif
          @if(!is_null($reward->stock))
            <p>Tồn kho: {{ $reward->stock }}</p>
          @endif
        </div>
        <div class="mt-8 flex gap-3">
          <a href="{{ route('rewards.index') }}" class="px-5 py-3 rounded-xl bg-slate-900 text-white font-semibold">Quay lại</a>
          <button class="px-5 py-3 rounded-xl bg-indigo-600 text-white font-semibold" onclick="document.querySelector('[data-id=\'{{ $reward->reward_id }}\']')?.click()">Đổi ngay</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
