@extends('admin.layouts.master')
@php $locale = app()->getLocale(); @endphp
@section('title', $locale === 'en' ? 'Reward & Spin History' : 'Lịch sử đổi thưởng & Trúng giải')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">{{ $locale === 'en' ? 'Reward & Spin History' : 'Lịch sử đổi thưởng & Trúng giải' }}</h1>
                <p class="text-slate-500 mt-1">{{ $locale === 'en' ? 'View and track all customer reward redemptions and lucky wheel spins.' : 'Xem và theo dõi toàn bộ lịch sử đổi voucher, quà tặng và quay thưởng của khách hàng.' }}</p>
            </div>
            <a href="{{ route('admin.rewards.index') }}" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold flex items-center gap-1.5 transition">
                <i class="fa-solid fa-arrow-left"></i> {{ $locale === 'en' ? 'Back to Rewards' : 'Quay lại quản lý' }}
            </a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Search Keyword' : 'Từ khóa tìm kiếm' }}</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ $locale === 'en' ? 'Customer name, email, code...' : 'Tên khách hàng, email, mã code...' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Reward Type' : 'Phân loại' }}</label>
            <select name="type" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-semibold text-slate-700">
                <option value="">{{ $locale === 'en' ? 'All Types' : 'Tất cả loại' }}</option>
                <option value="redeem" @selected($type === 'redeem')>{{ $locale === 'en' ? 'Redemptions Only' : 'Chỉ đổi thưởng' }}</option>
                <option value="spin" @selected($type === 'spin')>{{ $locale === 'en' ? 'Wheel Spins Only' : 'Chỉ lượt quay' }}</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Status' : 'Trạng thái' }}</label>
            <select name="status" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-semibold text-slate-700">
                <option value="">{{ $locale === 'en' ? 'All Status' : 'Tất cả trạng thái' }}</option>
                <option value="pending" @selected($status === 'pending')>{{ $locale === 'en' ? 'Pending' : 'Đang chờ' }}</option>
                <option value="approved" @selected($status === 'approved')>{{ $locale === 'en' ? 'Approved' : 'Đã duyệt' }}</option>
                <option value="issued" @selected($status === 'issued')>{{ $locale === 'en' ? 'Issued' : 'Đã phát hành' }}</option>
                <option value="cancelled" @selected($status === 'cancelled')>{{ $locale === 'en' ? 'Cancelled' : 'Đã hủy' }}</option>
                <option value="won" @selected($status === 'won')>{{ $locale === 'en' ? 'Won' : 'Trúng thưởng' }}</option>
                <option value="lost" @selected($status === 'lost')>{{ $locale === 'en' ? 'Lost' : 'Không trúng' }}</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> {{ $locale === 'en' ? 'Filter History' : 'Lọc lịch sử' }}
            </button>
        </div>
    </form>

    @php
        $statusMap = [
            'issued' => ['bg-emerald-50 text-emerald-700 border-emerald-100', $locale === 'en' ? 'Issued' : 'Đã phát hành'],
            'approved' => ['bg-blue-50 text-blue-700 border-blue-100', $locale === 'en' ? 'Approved' : 'Đã duyệt'],
            'pending' => ['bg-amber-50 text-amber-700 border-amber-100', $locale === 'en' ? 'Pending' : 'Đang chờ'],
            'cancelled' => ['bg-rose-50 text-rose-700 border-rose-100', $locale === 'en' ? 'Cancelled' : 'Đã hủy'],
            'won' => ['bg-violet-50 text-violet-700 border-violet-100', $locale === 'en' ? 'Won' : 'Trúng thưởng'],
            'lost' => ['bg-slate-50 text-slate-600 border-slate-100', $locale === 'en' ? 'Lost' : 'Chưa trúng'],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-6">
        <!-- 1. Lịch sử đổi thưởng -->
        @if(!$type || $type === 'redeem')
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="w-3.5 h-3.5 rounded-xl bg-indigo-600 shadow-lg shadow-indigo-200"></span>
                    {{ $locale === 'en' ? 'Reward Redemptions List' : 'Danh sách đổi thưởng (Voucher / Quà tặng)' }}
                </h2>
                <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 font-extrabold text-xs border border-indigo-100/50 shadow-sm">{{ $redemptions->total() }} {{ $locale === 'en' ? 'records' : 'bản ghi' }}</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-600 border-b border-slate-100 bg-slate-50/50">
                        <tr>
                            <th class="py-3.5 px-4 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Customer' : 'Khách hàng' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Reward' : 'Phần thưởng' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Code' : 'Mã Code' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500 text-center">{{ $locale === 'en' ? 'Points Spent' : 'Điểm tiêu' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500 text-center">{{ $locale === 'en' ? 'Status' : 'Trạng thái' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Redeemed At' : 'Thời gian đổi' }}</th>
                            <th class="py-3.5 px-4 font-bold text-xs uppercase tracking-wider text-slate-500 text-right">{{ $locale === 'en' ? 'Expires At' : 'Hạn sử dụng' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($redemptions as $item)
                            @php $badge = $statusMap[$item->status] ?? ['bg-slate-50 text-slate-600', $item->status]; @endphp
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-extrabold text-xs uppercase shadow-sm border border-white/20">
                                            {{ substr($item->user?->name ?? 'U', 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-xs leading-snug">{{ $item->user?->name ?? 'Guest' }}</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $item->user?->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-2">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center border border-slate-200/50 shadow-sm">
                                            @if($item->reward?->display_image)
                                                <img src="{{ asset('storage/'.$item->reward->display_image) }}" class="w-full h-full object-cover">
                                            @else
                                                @if($item->reward?->reward_type === 'voucher')
                                                    <div class="w-full h-full bg-gradient-to-br from-indigo-50 to-blue-100 flex items-center justify-center">
                                                        <i class="fa-solid fa-ticket text-indigo-500 text-sm"></i>
                                                    </div>
                                                @elseif($item->reward?->reward_type === 'shipping')
                                                    <div class="w-full h-full bg-gradient-to-br from-emerald-50 to-teal-100 flex items-center justify-center">
                                                        <i class="fa-solid fa-truck text-emerald-500 text-sm"></i>
                                                    </div>
                                                @else
                                                    <div class="w-full h-full bg-gradient-to-br from-amber-50 to-orange-100 flex items-center justify-center">
                                                        <i class="fa-solid fa-box-open text-amber-500 text-sm"></i>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-700 text-xs leading-tight">{{ $item->reward?->name ?? 'Phần thưởng' }}</div>
                                            <div class="text-[9px] text-indigo-600 font-extrabold uppercase mt-0.5 tracking-wide">{{ $item->reward?->reward_type ?? 'voucher' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-2">
                                    <span class="font-mono text-xs font-bold text-slate-600 bg-slate-100/80 px-2 py-0.5 rounded border border-slate-200/20 uppercase tracking-wide">{{ $item->redemption_code }}</span>
                                </td>
                                <td class="py-4 px-2 text-center">
                                    <span class="inline-flex items-center font-black text-rose-600 text-xs gap-0.5 bg-rose-50/50 px-2 py-0.5 rounded">
                                        <i class="fa-solid fa-coins text-[10px]"></i> -{{ number_format($item->points_spent) }}
                                    </span>
                                </td>
                                <td class="py-4 px-2 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badge[0] }}">{{ $badge[1] }}</span>
                                </td>
                                <td class="py-4 px-2 text-xs text-slate-500 font-medium">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-regular fa-clock text-slate-400 text-[10px]"></i>
                                        {{ optional($item->created_at)->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-500 font-medium text-right">
                                    @if($item->expires_at)
                                        <div class="inline-flex items-center gap-1 bg-amber-50/40 text-amber-700 border border-amber-100/30 px-2 py-0.5 rounded">
                                            <i class="fa-regular fa-calendar-check text-[10px]"></i>
                                            {{ $item->expires_at->format('d/m/Y H:i') }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 font-medium italic">{{ $locale === 'en' ? 'No redemption records found.' : 'Không tìm thấy lịch sử đổi thưởng.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $redemptions->links() }}
            </div>
        </div>
        @endif
 
        <!-- 2. Lịch sử quay may mắn -->
        @if(!$type || $type === 'spin')
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="w-3.5 h-3.5 rounded-xl bg-fuchsia-600 shadow-lg shadow-fuchsia-200"></span>
                    {{ $locale === 'en' ? 'Lucky Wheel Spins List' : 'Danh sách lượt quay may mắn' }}
                </h2>
                <span class="px-3 py-1 rounded-full bg-fuchsia-50 text-fuchsia-700 font-extrabold text-xs border border-fuchsia-100/50 shadow-sm">{{ $spins->total() }} {{ $locale === 'en' ? 'records' : 'bản ghi' }}</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-600 border-b border-slate-100 bg-slate-50/50">
                        <tr>
                            <th class="py-3.5 px-4 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Customer' : 'Khách hàng' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Prize' : 'Ô quà trúng' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Spin Code' : 'Mã Quay' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500 text-center">{{ $locale === 'en' ? 'Points Cost' : 'Giá lượt' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500 text-center">{{ $locale === 'en' ? 'Status' : 'Trạng thái' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Spun At' : 'Thời gian quay' }}</th>
                            <th class="py-3.5 px-4 font-bold text-xs uppercase tracking-wider text-slate-500 text-right">{{ $locale === 'en' ? 'Expires At' : 'Hạn sử dụng' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($spins as $spin)
                            @php $badge = $statusMap[$spin->status] ?? ['bg-slate-50 text-slate-600', $spin->status]; @endphp
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-fuchsia-500 to-fuchsia-600 flex items-center justify-center text-white font-extrabold text-xs uppercase shadow-sm border border-white/20">
                                            {{ substr($spin->user?->name ?? 'U', 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-xs leading-snug">{{ $spin->user?->name ?? 'Guest' }}</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $spin->user?->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-2">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center border border-slate-200/50 shadow-sm">
                                            @if($spin->reward?->display_image)
                                                <img src="{{ asset('storage/'.$spin->reward->display_image) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-gradient-to-br from-violet-50 to-purple-100 flex items-center justify-center">
                                                    <i class="fa-solid fa-dharmachakra text-violet-500 text-sm"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-700 text-xs leading-tight">{{ $spin->reward?->name ?? 'Lượt quay' }}</div>
                                            <div class="text-[9px] text-fuchsia-600 font-extrabold uppercase mt-0.5 tracking-wide">{{ $spin->reward?->reward_type ?? 'wheel_prize' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-2">
                                    <span class="font-mono text-xs font-bold text-slate-600 bg-slate-100/80 px-2 py-0.5 rounded border border-slate-200/20 uppercase tracking-wide">{{ $spin->spin_code }}</span>
                                </td>
                                <td class="py-4 px-2 text-center">
                                    <span class="inline-flex items-center font-black text-rose-600 text-xs gap-0.5 bg-rose-50/50 px-2 py-0.5 rounded">
                                        <i class="fa-solid fa-coins text-[10px]"></i> -{{ number_format($spin->points_spent) }}
                                    </span>
                                </td>
                                <td class="py-4 px-2 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badge[0] }}">{{ $badge[1] }}</span>
                                </td>
                                <td class="py-4 px-2 text-xs text-slate-500 font-medium">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-regular fa-clock text-slate-400 text-[10px]"></i>
                                        {{ optional($spin->spun_at ?? $spin->created_at)->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-500 font-medium text-right">
                                    @if($spin->expires_at)
                                        <div class="inline-flex items-center gap-1 bg-amber-50/40 text-amber-700 border border-amber-100/30 px-2 py-0.5 rounded">
                                            <i class="fa-regular fa-calendar-check text-[10px]"></i>
                                            {{ $spin->expires_at->format('d/m/Y H:i') }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 font-medium italic">{{ $locale === 'en' ? 'No spin records found.' : 'Không tìm thấy lịch sử quay thưởng.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $spins->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
