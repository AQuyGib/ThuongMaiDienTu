@extends('layouts.app')
@section('title', 'Lịch sử đổi thưởng')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@php
    $statusMap = [
        'issued' => ['bg-emerald-50 text-emerald-700', 'Đã phát hành'],
        'approved' => ['bg-blue-50 text-blue-700', 'Đã duyệt'],
        'pending' => ['bg-amber-50 text-amber-700', 'Đang chờ'],
        'cancelled' => ['bg-rose-50 text-rose-700', 'Đã hủy'],
        'won' => ['bg-violet-50 text-violet-700', 'Trúng thưởng'],
        'lost' => ['bg-slate-100 text-slate-600', 'Chưa trúng'],
    ];
@endphp

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-extrabold text-slate-900">Lịch sử đổi thưởng</h1>
        <p class="text-slate-600 mt-2">Xem lại voucher, quà tặng và lượt quay may mắn đã sử dụng.</p>
      </div>
      <a href="{{ route('rewards.index') }}" class="inline-flex px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold">Quay lại rewards</a>
    </div>

    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3 shadow-sm">
      <select name="type" class="rounded-xl border-slate-300">
        <option value="">Tất cả loại</option>
        <option value="redeem" @selected($type === 'redeem')>Đổi thưởng</option>
        <option value="spin" @selected($type === 'spin')>Vòng quay</option>
      </select>
      <select name="status" class="rounded-xl border-slate-300">
        <option value="">Tất cả trạng thái</option>
        @foreach(array_keys($statusMap) as $key)
          <option value="{{ $key }}" @selected($status === $key)>{{ $statusMap[$key][1] }}</option>
        @endforeach
      </select>
      <input type="text" name="search" value="{{ $search }}" placeholder="Tìm mã / tên reward" class="rounded-xl border-slate-300">
      <button class="rounded-xl bg-indigo-600 text-white font-semibold">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6 flex flex-wrap gap-2">
      <a href="?type=" class="px-4 py-2 rounded-full text-sm font-semibold {{ $type === '' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Tất cả</a>
      <a href="?type=redeem" class="px-4 py-2 rounded-full text-sm font-semibold {{ $type === 'redeem' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700' }}">Voucher / Quà tặng</a>
      <a href="?type=spin" class="px-4 py-2 rounded-full text-sm font-semibold {{ $type === 'spin' ? 'bg-fuchsia-600 text-white' : 'bg-slate-100 text-slate-700' }}">Vòng quay</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold">Lịch sử đổi thưởng</h2>
          <span class="text-sm text-slate-500">{{ $redemptions->total() }} giao dịch</span>
        </div>
        <div class="space-y-4">
          @forelse ($redemptions as $item)
            @php $badge = $statusMap[$item->status] ?? ['bg-slate-100 text-slate-600', ucfirst($item->status)]; @endphp
            <div class="relative pl-6 border-l-2 border-slate-200">
              <div class="absolute -left-2 top-1.5 w-4 h-4 rounded-full bg-indigo-600"></div>
              <div class="p-4 rounded-2xl border border-slate-200 hover:border-indigo-200 transition">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <p class="font-semibold text-slate-900">{{ $item->reward?->name ?? 'Phần thưởng' }}</p>
                      <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge[0] }}">{{ $badge[1] }}</span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">Mã: {{ $item->redemption_code }}</p>
                    <p class="text-sm text-slate-500">Ngày đổi: {{ optional($item->created_at)->format('d/m/Y H:i') }}</p>
                    @if($item->expires_at)
                      <p class="text-sm text-slate-500">HSD: {{ $item->expires_at->format('d/m/Y H:i') }}</p>
                    @endif
                  </div>
                  <div class="text-right">
                    <p class="font-black text-violet-600">-{{ number_format($item->points_spent) }} điểm</p>
                    <p class="text-xs text-slate-500">Đã trừ ngay</p>
                  </div>
                </div>
                <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: {{ $item->status === 'issued' ? 100 : ($item->status === 'pending' ? 50 : 100) }}%"></div>
                </div>
              </div>
            </div>
          @empty
            <p class="text-slate-500">Chưa có giao dịch đổi thưởng.</p>
          @endforelse
        </div>
        <div class="mt-4">{{ $redemptions->links() }}</div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold">Lịch sử quay may mắn</h2>
          <span class="text-sm text-slate-500">{{ $spins->total() }} lượt</span>
        </div>
        <div class="space-y-4">
          @forelse ($spins as $spin)
            @php $badge = $statusMap[$spin->status] ?? ['bg-slate-100 text-slate-600', ucfirst($spin->status)]; @endphp
            <div class="relative pl-6 border-l-2 border-slate-200">
              <div class="absolute -left-2 top-1.5 w-4 h-4 rounded-full bg-fuchsia-600"></div>
              <div class="p-4 rounded-2xl border border-slate-200 hover:border-fuchsia-200 transition">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <p class="font-semibold text-slate-900">{{ $spin->reward?->name ?? 'Lượt quay' }}</p>
                      <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge[0] }}">{{ $badge[1] }}</span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">Mã quay: {{ $spin->spin_code }}</p>
                    <p class="text-sm text-slate-500">Ngày quay: {{ optional($spin->spun_at ?? $spin->created_at)->format('d/m/Y H:i') }}</p>
                    @if($spin->expires_at)
                      <p class="text-sm text-slate-500">HSD: {{ $spin->expires_at->format('d/m/Y H:i') }}</p>
                    @endif
                  </div>
                  <div class="text-right">
                    <p class="font-black text-violet-600">-{{ number_format($spin->points_spent) }} điểm</p>
                    <p class="text-xs text-slate-500">Đã trừ ngay</p>
                  </div>
                </div>
                <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-fuchsia-500 to-violet-500" style="width: {{ $spin->status === 'won' ? 100 : ($spin->status === 'pending' ? 50 : 100) }}%"></div>
                </div>
              </div>
            </div>
          @empty
            <p class="text-slate-500">Chưa có lượt quay nào.</p>
          @endforelse
        </div>
        <div class="mt-4">{{ $spins->links() }}</div>
      </div>
    </div>
  </div>
</div>
@endsection
