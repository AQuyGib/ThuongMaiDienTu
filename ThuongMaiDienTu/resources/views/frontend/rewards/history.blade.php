@extends('layouts.app')
@section('title', 'Lịch sử đổi thưởng')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-extrabold text-slate-900 mb-2">Lịch sử đổi thưởng</h1>
        <p class="text-slate-600">Xem lại voucher, quà tặng và lượt quay may mắn đã sử dụng.</p>
      </div>
      <form method="GET" class="flex flex-wrap gap-3 bg-white p-3 rounded-2xl border border-slate-200 shadow-sm">
        <select name="type" class="px-4 py-2 rounded-xl border border-slate-200 text-sm">
          <option value="" @selected(empty($type))>Tất cả loại</option>
          <option value="earn" @selected($type === 'earn')>Tích điểm (Cộng)</option>
          <option value="use" @selected($type === 'use')>Sử dụng (Trừ)</option>
        </select>
        <select name="status" class="px-4 py-2 rounded-xl border border-slate-200 text-sm">
          <option value="" @selected(empty($status))>Tất cả trạng thái</option>
          <option value="issued" @selected($status === 'issued')>Đã phát hành</option>
          <option value="approved" @selected($status === 'approved')>Đã duyệt</option>
          <option value="pending" @selected($status === 'pending')>Chờ xử lý</option>
          <option value="cancelled" @selected($status === 'cancelled')>Đã hủy</option>
          <option value="won" @selected($status === 'won')>Trúng thưởng</option>
          <option value="lost" @selected($status === 'lost')>Không trúng</option>
        </select>
        <button class="px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold text-sm">Lọc</button>
      </form>
    </div>

    <div class="mb-8">
      <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-xl font-bold">Biến động số dư điểm</h2>
          <span class="text-sm text-slate-500">{{ $transactions->total() }} giao dịch</span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="text-slate-500 text-sm border-b">
                <th class="py-4 font-semibold">Thời gian</th>
                <th class="py-4 font-semibold">Loại</th>
                <th class="py-4 font-semibold">Nội dung</th>
                <th class="py-4 font-semibold text-right">Số điểm</th>
              </tr>
            </thead>
            <tbody class="text-sm">
              @forelse ($transactions as $tx)
                <tr class="border-b last:border-0 hover:bg-slate-50/50 transition">
                  <td class="py-4 text-slate-500">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                  <td class="py-4">
                    <span class="px-2 py-1 rounded-md text-xs font-bold {{ $tx->action === 'earn' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                      {{ $tx->action === 'earn' ? 'CỘNG' : 'TRỪ' }}
                    </span>
                  </td>
                  <td class="py-4">
                    <p class="font-medium text-slate-900">{{ $tx->description }}</p>
                    @if ($tx->point_type === 'rank')
                      <span class="text-[10px] bg-blue-50 text-blue-600 px-1.5 rounded">Rank Point</span>
                    @endif
                  </td>
                  <td class="py-4 text-right font-black {{ $tx->action === 'earn' ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $tx->action === 'earn' ? '+' : '-' }}{{ number_format($tx->points) }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="py-10 text-center text-slate-400">Chưa có giao dịch nào.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="mt-4">{{ $transactions->links() }}</div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-xl font-bold">Lịch sử đổi thưởng</h2>
          <span class="text-sm text-slate-500">{{ $redemptions->total() }} bản ghi</span>
        </div>
        <div class="relative pl-5 space-y-6 before:content-[''] before:absolute before:top-1.5 before:bottom-1.5 before:left-2 before:w-px before:bg-slate-200">
          @forelse ($redemptions as $item)
            <div class="relative">
              <div class="absolute -left-[1.05rem] top-1 w-4 h-4 rounded-full border-4 border-white shadow {{ $item->status === 'issued' ? 'bg-emerald-500' : ($item->status === 'approved' ? 'bg-blue-500' : 'bg-slate-400') }}"></div>
              <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60">
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <p class="font-semibold text-slate-900">{{ $item->reward?->name ?? 'Phần thưởng' }}</p>
                      <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $item->status === 'issued' ? 'bg-emerald-100 text-emerald-700' : ($item->status === 'approved' ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-700') }}">{{ strtoupper($item->status) }}</span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">Mã: {{ $item->redemption_code }}</p>
                    <p class="text-sm text-slate-500">HSD: {{ optional($item->expires_at)->format('d/m/Y H:i') ?? 'N/A' }}</p>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="font-black text-violet-600">-{{ number_format($item->points_spent) }} điểm</p>
                    <p class="text-xs text-slate-500">{{ optional($item->issued_at)->diffForHumans() }}</p>
                  </div>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-3 text-xs text-slate-600">
                  <div class="p-3 rounded-xl bg-white border">
                    <div class="font-semibold text-slate-900">Giảm giá</div>
                    <div>{{ number_format($item->discount_amount) }}đ</div>
                  </div>
                  <div class="p-3 rounded-xl bg-white border">
                    <div class="font-semibold text-slate-900">Free ship</div>
                    <div>{{ number_format($item->shipping_discount_amount) }}đ</div>
                  </div>
                  <div class="p-3 rounded-xl bg-white border">
                    <div class="font-semibold text-slate-900">Mã</div>
                    <div class="truncate">{{ $item->redemption_code }}</div>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <p class="text-slate-500">Chưa có giao dịch đổi thưởng.</p>
          @endforelse
        </div>
        <div class="mt-6">{{ $redemptions->links() }}</div>
      </div>

      <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-xl font-bold">Lịch sử quay may mắn</h2>
          <span class="text-sm text-slate-500">{{ $spins->total() }} bản ghi</span>
        </div>
        <div class="relative pl-5 space-y-6 before:content-[''] before:absolute before:top-1.5 before:bottom-1.5 before:left-2 before:w-px before:bg-slate-200">
          @forelse ($spins as $spin)
            <div class="relative">
              <div class="absolute -left-[1.05rem] top-1 w-4 h-4 rounded-full border-4 border-white shadow {{ $spin->status === 'won' ? 'bg-fuchsia-500' : ($spin->status === 'lost' ? 'bg-slate-400' : 'bg-blue-500') }}"></div>
              <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60">
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <p class="font-semibold text-slate-900">{{ $spin->reward?->name ?? 'Lượt quay' }}</p>
                      <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $spin->status === 'won' ? 'bg-fuchsia-100 text-fuchsia-700' : ($spin->status === 'lost' ? 'bg-slate-200 text-slate-700' : 'bg-blue-100 text-blue-700') }}">{{ strtoupper($spin->status) }}</span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">Mã quay: {{ $spin->spin_code }}</p>
                    <p class="text-sm text-slate-500">HSD: {{ optional($spin->expires_at)->format('d/m/Y H:i') ?? 'N/A' }}</p>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="font-black text-violet-600">-{{ number_format($spin->points_spent) }} điểm</p>
                    <p class="text-xs text-slate-500">{{ optional($spin->spun_at)->diffForHumans() }}</p>
                  </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 text-xs text-slate-600">
                  <div class="p-3 rounded-xl bg-white border">
                    <div class="font-semibold text-slate-900">Loại</div>
                    <div>{{ $spin->reward?->reward_category ?? 'wheel' }}</div>
                  </div>
                  <div class="p-3 rounded-xl bg-white border">
                    <div class="font-semibold text-slate-900">Phần thưởng</div>
                    <div class="truncate">{{ $spin->reward?->name ?? 'N/A' }}</div>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <p class="text-slate-500">Chưa có lượt quay nào.</p>
          @endforelse
        </div>
        <div class="mt-6">{{ $spins->links() }}</div>
      </div>
    </div>
  </div>
</div>
@endsection
