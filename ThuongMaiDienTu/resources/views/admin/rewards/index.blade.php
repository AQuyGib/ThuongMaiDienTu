@extends('admin.layouts.master')
@php $locale = app()->getLocale(); @endphp
@section('title', $locale === 'en' ? 'Manage Rewards' : 'Quản lý đổi thưởng')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">{{ $locale === 'en' ? 'Manage Rewards' : 'Quản lý đổi thưởng' }}</h1>
                <p class="text-slate-500 mt-1">{{ $locale === 'en' ? 'Manage vouchers, gifts and lucky wheel' : 'Quản lý voucher, quà tặng và vòng quay may mắn' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.rewards.history') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-history"></i> {{ $locale === 'en' ? 'Redemption & Spin History' : 'Lịch sử đổi & trúng giải' }}
                </a>
                <a href="{{ route('rewards.index') }}" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold transition">{{ $locale === 'en' ? 'View Client Page' : 'Xem trang người dùng' }}</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2 border-b border-slate-100 pb-3">
                <h2 class="text-lg font-extrabold text-slate-800">{{ $locale === 'en' ? 'Rewards & Prizes' : 'Quản lý danh sách' }}</h2>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openWheelSetupModal()" class="px-4 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 text-white font-extrabold transition shadow-lg shadow-violet-100 flex items-center gap-1.5 text-xs md:text-sm">
                        <i class="fa-solid fa-dharmachakra animate-spin-slow"></i> {{ $locale === 'en' ? 'Wheel Settings' : 'Thiết lập Vòng quay' }}
                    </button>
                    <button type="button" id="btn-add-exchange" onclick="openCreateModal('exchange')" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition flex items-center gap-1 text-xs md:text-sm">
                        <i class="fa-solid fa-plus"></i> {{ $locale === 'en' ? 'Add Reward' : 'Thêm reward' }}
                    </button>
                    <button type="button" id="btn-add-wheel-prize" onclick="openCreateModal('wheel')" class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold transition hidden flex items-center gap-1 text-xs md:text-sm">
                        <i class="fa-solid fa-plus"></i> {{ $locale === 'en' ? 'Add Wheel Prize' : 'Thêm quà vòng quay' }}
                    </button>
                </div>
            </div>

            <!-- Main Tabs -->
            <div class="flex border-b border-slate-200 mb-4 bg-slate-50 p-1 rounded-xl">
                <button type="button" onclick="switchMainTab('exchange')" id="tab-btn-exchange" class="flex-1 py-2 rounded-lg font-bold text-xs md:text-sm transition flex items-center justify-center gap-1.5 bg-white text-indigo-600 shadow-sm border border-slate-200/50">
                    <i class="fa-solid fa-gift"></i>
                    {{ $locale === 'en' ? 'Points Exchange Vouchers' : 'Phần thưởng đổi điểm' }}
                </button>
                <button type="button" onclick="switchMainTab('wheel')" id="tab-btn-wheel" class="flex-1 py-2 rounded-lg font-bold text-xs md:text-sm transition flex items-center justify-center gap-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100">
                    <i class="fa-solid fa-dharmachakra"></i>
                    {{ $locale === 'en' ? 'Lucky Wheel Prizes' : 'Quà vòng quay may mắn' }}
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-600 border-b border-slate-100 bg-slate-50/50">
                        <tr>
                            <th class="py-3.5 px-4 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Name' : 'Tên' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Type' : 'Loại' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500" id="th-cost-or-rate">{{ $locale === 'en' ? 'Points / Rate' : 'Giá điểm / Tỷ lệ' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Stock' : 'Tồn' }}</th>
                            <th class="py-3.5 px-2 font-bold text-xs uppercase tracking-wider text-slate-500">{{ $locale === 'en' ? 'Status' : 'Trạng thái' }}</th>
                            <th class="py-3.5 px-4 font-bold text-xs uppercase tracking-wider text-slate-500 text-right">{{ $locale === 'en' ? 'Action' : 'Hành động' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($catalog as $item)
                        @php
                            $isWheelPrize = $item->reward_type === 'wheel_prize';
                        @endphp
                        <tr class="reward-row hover:bg-slate-50/40 transition-colors" data-main-tab="{{ $isWheelPrize ? 'wheel' : 'exchange' }}">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center border border-slate-200/50 shadow-sm">
                                        @if($item->display_image)
                                            <img src="{{ asset('storage/'.$item->display_image) }}" class="w-full h-full object-cover">
                                        @else
                                            @if($isWheelPrize)
                                                <div class="w-full h-full bg-gradient-to-br from-violet-50 to-purple-100 flex items-center justify-center">
                                                    <i class="fa-solid fa-dharmachakra text-violet-500 text-lg"></i>
                                                </div>
                                            @elseif($item->reward_type === 'voucher')
                                                <div class="w-full h-full bg-gradient-to-br from-indigo-50 to-blue-100 flex items-center justify-center">
                                                    <i class="fa-solid fa-ticket text-indigo-500 text-lg"></i>
                                                </div>
                                            @elseif($item->reward_type === 'shipping')
                                                <div class="w-full h-full bg-gradient-to-br from-emerald-50 to-teal-100 flex items-center justify-center">
                                                    <i class="fa-solid fa-truck text-emerald-500 text-lg"></i>
                                                </div>
                                            @else
                                                <div class="w-full h-full bg-gradient-to-br from-amber-50 to-orange-100 flex items-center justify-center">
                                                    <i class="fa-solid fa-box-open text-amber-500 text-lg"></i>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm leading-snug">{{ $item->name }}</div>
                                        <div class="text-[10px] font-mono text-slate-400 mt-0.5 tracking-wider uppercase bg-slate-100 px-1.5 py-0.5 rounded w-fit">{{ $item->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-2">
                                @if($isWheelPrize)
                                    @php
                                        $wheelKey = $item->metadata['wheel_type'] ?? 'standard';
                                        $targetWheel = collect($wheels)->firstWhere('key', $wheelKey);
                                        $wheelName = $targetWheel ? ($locale === 'en' ? $targetWheel['name_en'] : $targetWheel['name']) : 'Vòng quay';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-violet-50 text-violet-700 font-extrabold text-[10px] border border-violet-100 uppercase tracking-wide">
                                        <i class="fa-solid fa-dharmachakra mr-1 text-[9px]"></i> {{ $wheelName }}
                                    </span>
                                @else
                                    @if($item->reward_type === 'voucher')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-extrabold text-[10px] border border-indigo-100 uppercase tracking-wide">
                                            <i class="fa-solid fa-ticket mr-1 text-[9px]"></i> Voucher
                                        </span>
                                    @elseif($item->reward_type === 'shipping')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-extrabold text-[10px] border border-emerald-100 uppercase tracking-wide">
                                            <i class="fa-solid fa-truck mr-1 text-[9px]"></i> Freeship
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-extrabold text-[10px] border border-amber-100 uppercase tracking-wide">
                                            <i class="fa-solid fa-box mr-1 text-[9px]"></i> Quà Tặng
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="py-4 px-2 font-bold text-slate-700">
                                @if($isWheelPrize)
                                    <span class="text-indigo-600 font-black text-sm"><i class="fa-solid fa-percent mr-0.5"></i>{{ $item->metadata['winning_rate'] ?? 10 }}%</span>
                                @else
                                    <span class="text-slate-800 font-black text-sm">{{ number_format($item->points_cost) }}<span class="text-[9px] text-slate-400 font-bold uppercase ml-1">pts</span></span>
                                @endif
                            </td>
                            <td class="py-4 px-2 text-slate-600 font-semibold text-xs">
                                {{ is_null($item->stock) ? ($locale === 'en' ? 'Unlimited' : 'Không giới hạn') : number_format($item->stock) }}
                            </td>
                            <td class="py-4 px-2">
                                <div class="flex flex-col gap-1.5 w-fit">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }} w-fit flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                        {{ $locale === 'en' ? ($item->is_active ? 'Active' : 'Inactive') : ($item->is_active ? 'Đang bật' : 'Đang tắt') }}
                                    </span>
                                    <div class="w-24 h-1.5 rounded-full bg-slate-100 overflow-hidden relative" title="{{ $locale === 'en' ? 'Stock progress' : 'Tiến độ tồn kho' }}">
                                        <div class="h-full bg-gradient-to-r from-indigo-500 to-violet-500 rounded-full" style="width: {{ $item->progress_percent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200/50 flex items-center justify-center transition shadow-sm" onclick='openEditModal(@json($item))' title="{{ $locale === 'en' ? 'Edit' : 'Sửa' }}">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <button class="w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-800 border border-indigo-100/50 flex items-center justify-center transition shadow-sm" onclick='openImageModal(@json($item))' title="{{ $locale === 'en' ? 'Change Image' : 'Đổi ảnh' }}">
                                        <i class="fa-solid fa-image text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.rewards.destroy', $item->reward_id) }}" method="POST" class="inline" onsubmit="return confirm('{{ $locale === 'en' ? 'Delete this reward?' : 'Xóa reward này?' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 border border-rose-100/50 flex items-center justify-center transition shadow-sm" title="{{ $locale === 'en' ? 'Delete' : 'Xóa' }}">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fa-solid fa-chart-pie text-indigo-500"></i>
                {{ $locale === 'en' ? 'Quick Stats' : 'Cài đặt nhanh' }}
            </h2>
            
            <div class="grid grid-cols-1 gap-3.5">
                <div class="p-4 rounded-2xl bg-gradient-to-r from-fuchsia-500/5 to-pink-500/5 border border-fuchsia-100/30 flex items-center justify-between shadow-sm">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $locale === 'en' ? 'Total Redemptions' : 'Tổng lịch sử đổi thưởng' }}</div>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['redemptions'] }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-fuchsia-50 text-fuchsia-600 flex items-center justify-center border border-fuchsia-100/50 shadow-sm">
                        <i class="fa-solid fa-gift text-base"></i>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-gradient-to-r from-violet-500/5 to-purple-500/5 border border-violet-100/30 flex items-center justify-between shadow-sm">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $locale === 'en' ? 'Total Spins' : 'Tổng lượt quay' }}</div>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['spins'] }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center border border-violet-100/50 shadow-sm">
                        <i class="fa-solid fa-dharmachakra text-base"></i>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-500/5 to-teal-500/5 border border-emerald-100/30 flex items-center justify-between shadow-sm">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $locale === 'en' ? 'Total Points Spent' : 'Tổng điểm đã tiêu' }}</div>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['points_spent']) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100/50 shadow-sm">
                        <i class="fa-solid fa-coins text-base"></i>
                    </div>
                </div>
            </div>
            
            <div class="p-4 rounded-2xl bg-gradient-to-r from-indigo-50 to-violet-50 border border-indigo-100 space-y-3 mt-2 shadow-sm">
                <div class="text-xs font-extrabold text-indigo-950 flex items-center gap-1.5">
                    <i class="fa-solid fa-eye text-indigo-600"></i>
                    {{ $locale === 'en' ? 'Wheel Visibility' : 'Hiển thị Vòng quay' }}
                </div>
                <label class="relative inline-flex items-center cursor-pointer select-none">
                    @php
                        $showWheel = \App\Models\Setting::where('setting_key', 'show_lucky_wheel')->value('setting_value') ?? '1';
                    @endphp
                    <input type="checkbox" id="toggle-wheel-visibility" class="sr-only peer" {{ $showWheel === '1' ? 'checked' : '' }} onchange="updateWheelVisibility(this.checked)">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 shadow-inner"></div>
                    <span class="ml-3 text-xs font-bold text-slate-700" id="wheel-status-text">
                        {{ $showWheel === '1' ? ($locale === 'en' ? 'Showing on Frontend' : 'Đang hiện ở trang chủ') : ($locale === 'en' ? 'Hidden on Frontend' : 'Đang ẩn ở trang chủ') }}
                    </span>
                </label>
            </div>
        </div>
    </div>
</div>

@include('admin.rewards.partials.image-modal')

<div id="reward-modal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center z-[9999] p-4 transition-all">
    <div class="bg-white rounded-3xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto flex flex-col shadow-2xl animate-in zoom-in-95 duration-200">
        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
            <h3 id="modal-title" class="text-xl font-extrabold text-slate-800">{{ $locale === 'en' ? 'Create Reward' : 'Tạo reward' }}</h3>
            <button type="button" onclick="closeRewardModal()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="reward-form" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div id="method-field"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Reward Code' : 'Mã Reward' }}</label>
                    <input name="code" placeholder="{{ $locale === 'en' ? 'Code (E.g. VOUCHER20K)' : 'Mã (Ví dụ: VOUCHER20K)' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 uppercase font-bold text-slate-700" pattern="[A-Z0-9_\-]+" title="Chỉ dùng A-Z, 0-9, _ hoặc -" maxlength="50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Reward Name' : 'Tên Reward' }}</label>
                    <input name="name" placeholder="{{ $locale === 'en' ? 'Reward name' : 'Tên phần thưởng' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-semibold text-slate-700" minlength="3" maxlength="150">
                </div>
                <div id="div-reward-type" class="exchange-only-field">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Reward Type' : 'Loại phần thưởng' }}</label>
                    <select name="reward_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-bold text-slate-700">
                        <option value="voucher">{{ $locale === 'en' ? 'Voucher' : 'Voucher (Giảm giá)' }}</option>
                        <option value="shipping">{{ $locale === 'en' ? 'Shipping' : 'Shipping (Vận chuyển)' }}</option>
                        <option value="product">{{ $locale === 'en' ? 'Product' : 'Product (Sản phẩm)' }}</option>
                        <option value="wheel_prize" class="hidden">{{ $locale === 'en' ? 'Wheel Prize' : 'Quà vòng quay' }}</option>
                    </select>
                </div>
                <div id="div-reward-category" class="exchange-only-field">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Display Category' : 'Danh mục hiển thị' }}</label>
                    <select name="reward_category" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-bold text-slate-700">
                        <option value="free_ship">{{ $locale === 'en' ? 'Free Ship' : 'Miễn phí vận chuyển' }}</option>
                        <option value="discount">{{ $locale === 'en' ? 'Discount' : 'Giảm giá hóa đơn' }}</option>
                        <option value="gift">{{ $locale === 'en' ? 'Gift' : 'Quà tặng' }}</option>
                        <option value="wheel" class="hidden">{{ $locale === 'en' ? 'Wheel' : 'Vòng quay may mắn' }}</option>
                    </select>
                </div>
                <div class="wheel-only-field">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Wheel Prize Type' : 'Loại quà vòng quay' }}</label>
                    <select name="wheel_prize_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-bold text-slate-700">
                        <option value="voucher">{{ $locale === 'en' ? 'Voucher' : 'Voucher (Giảm giá)' }}</option>
                        <option value="shipping">{{ $locale === 'en' ? 'Shipping' : 'Shipping (Vận chuyển)' }}</option>
                        <option value="product">{{ $locale === 'en' ? 'Product' : 'Product (Sản phẩm)' }}</option>
                    </select>
                </div>
                <div class="exchange-only-field">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Points Cost' : 'Điểm quy đổi' }}</label>
                    <input name="points_cost" type="number" min="0" max="1000000" placeholder="{{ $locale === 'en' ? 'Points to redeem' : 'Số điểm đổi' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-semibold text-slate-700">
                </div>
                <div id="div-discount-amount">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Discount Amount (VND)' : 'Số tiền giảm (VND)' }}</label>
                    <input name="discount_amount" type="number" min="0" max="50000000" placeholder="{{ $locale === 'en' ? 'Enter discount amount' : 'Nhập số tiền giảm' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-semibold text-slate-700">
                </div>
                <div id="div-shipping-discount-amount">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Shipping Discount (VND)' : 'Số tiền giảm ship (VND)' }}</label>
                    <input name="shipping_discount_amount" type="number" min="0" max="5000000" placeholder="{{ $locale === 'en' ? 'Enter shipping discount amount' : 'Nhập số tiền giảm ship' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-semibold text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Stock Quantity' : 'Số lượng tồn kho' }}</label>
                    <input name="stock" type="number" min="0" max="100000" placeholder="{{ $locale === 'en' ? 'Leave blank for unlimited' : 'Để trống nếu không giới hạn' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-semibold text-slate-700">
                </div>
                <div class="exchange-only-field">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Limit per user' : 'Giới hạn nhận / người dùng' }}</label>
                    <input name="max_per_user" type="number" min="1" max="100" placeholder="{{ $locale === 'en' ? 'Default: 1' : 'Mặc định: 1' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-semibold text-slate-700">
                </div>
                <div id="min-rank-points-container" class="exchange-only-field">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Minimum Rank Required' : 'Hạng yêu cầu tối thiểu' }}</label>
                    <select name="min_rank" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-bold text-slate-700">
                        <option value="none">{{ $locale === 'en' ? 'No rank required' : 'Không yêu cầu (Bất kỳ ai)' }}</option>
                        <option value="Dong">{{ $locale === 'en' ? 'Bronze' : 'Hạng Đồng' }}</option>
                        <option value="Bac">{{ $locale === 'en' ? 'Silver' : 'Hạng Bạc' }}</option>
                        <option value="Vang">{{ $locale === 'en' ? 'Gold' : 'Hạng Vàng' }}</option>
                        <option value="KimCuong">{{ $locale === 'en' ? 'Diamond' : 'Hạng Kim Cương' }}</option>
                    </select>
                </div>
                <div class="exchange-only-field">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Requires Rank Check' : 'Ràng buộc hạng thành viên' }}</label>
                    <select name="requires_rank_check" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-bold text-slate-700">
                        <option value="0">{{ $locale === 'en' ? 'No rank required' : 'Không bắt buộc rank' }}</option>
                        <option value="1">{{ $locale === 'en' ? 'Rank required' : 'Bắt buộc rank' }}</option>
                    </select>
                </div>
                <div class="wheel-only-field">
                    <label class="block text-xs font-bold text-indigo-500 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Winning Rate (%)' : 'Tỷ lệ trúng vòng quay (%)' }}</label>
                    <input name="winning_rate" type="number" min="1" max="100" placeholder="{{ $locale === 'en' ? 'Only applicable to wheel prizes. Default: 10' : 'Chỉ áp dụng với quà Vòng quay. Mặc định: 10' }}" class="w-full px-4 py-3 rounded-xl border border-indigo-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-indigo-50/20 font-semibold text-slate-700">
                </div>
                <div class="wheel-only-field">
                    <label class="block text-xs font-bold text-indigo-500 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Belongs to Wheel' : 'Thuộc vòng quay' }}</label>
                    <select name="wheel_type" id="select-wheel-type" class="w-full px-4 py-3 rounded-xl border border-indigo-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-indigo-50/20 font-bold text-slate-700">
                        @foreach($wheels as $w)
                            <option value="{{ $w['key'] }}">{{ $locale === 'en' ? $w['name_en'] : $w['name'] }} ({{ $w['points_cost'] }}đ)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Starts At' : 'Thời gian bắt đầu' }}</label>
                    <input name="starts_at" type="datetime-local" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 text-slate-500 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $locale === 'en' ? 'Ends At' : 'Thời gian kết thúc' }}</label>
                    <input name="ends_at" type="datetime-local" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 text-slate-500 font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">{{ $locale === 'en' ? 'Reward Image' : 'Ảnh phần thưởng' }}</label>
                    <input name="image" id="reward-image-input" type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 text-slate-600 font-semibold file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 file:cursor-pointer mb-3">
                    <div id="image-preview" class="w-full h-32 rounded-2xl bg-slate-50 border border-dashed border-slate-200 overflow-hidden flex items-center justify-center text-slate-400 text-sm">{{ $locale === 'en' ? 'No image' : 'Chưa có ảnh' }}</div>
                </div>
                <div class="flex flex-col">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">{{ $locale === 'en' ? 'Description' : 'Mô tả' }}</label>
                    <textarea name="description" rows="6" placeholder="{{ $locale === 'en' ? 'Detailed description of the reward...' : 'Mô tả chi tiết phần thưởng...' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-medium text-slate-700 resize-none flex-1"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100 mt-5">
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 select-none cursor-pointer">
                    <input type="checkbox" name="is_active" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500" checked>
                    {{ $locale === 'en' ? 'Active display' : 'Kích hoạt hiển thị' }}
                </label>
                <div class="flex gap-2">
                    <button type="button" onclick="closeRewardModal()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition">{{ $locale === 'en' ? 'Close' : 'Đóng' }}</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition shadow-lg shadow-indigo-100">{{ $locale === 'en' ? 'Save Changes' : 'Lưu thông tin' }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Xem trước & Thiết lập Vòng quay may mắn -->
<div id="wheel-setup-modal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center z-[9999] p-4 transition-all">
    <div class="bg-white rounded-3xl p-6 max-w-4xl w-full max-h-[90vh] overflow-y-auto flex flex-col shadow-2xl animate-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-dharmachakra text-violet-600"></i>
                    {{ $locale === 'en' ? 'Wheel Settings & Preview' : 'Thiết lập & Xem trước Vòng quay' }}
                </h3>
                <p class="text-xs text-slate-500 mt-1">{{ $locale === 'en' ? 'Configure prize segments and test spin the wheel in real-time.' : 'Cấu hình các ô quà tặng và trải nghiệm xoay thử vòng quay thực tế.' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="openLuckyWheelsManagerModal()" class="px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-gear"></i> {{ $locale === 'en' ? 'Manage Wheels List' : 'Quản lý Vòng quay' }}
                </button>
                <button type="button" onclick="closeWheelSetupModal()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Body -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Cột trái: Đĩa quay (lg:col-span-5) -->
            <div class="lg:col-span-5 flex flex-col items-center justify-center p-4 bg-slate-50 rounded-2xl border border-slate-100 min-h-[380px]">
                <!-- Tabs chuyển vòng quay cho Admin -->
                <div id="admin-wheel-tabs-container" class="flex items-center gap-1.5 mb-4 bg-slate-200/60 p-1 rounded-xl w-full flex-wrap">
                    @foreach($wheels as $idx => $w)
                        <button type="button" onclick="switchAdminWheelTab('{{ $w['key'] }}')" id="admin-tab-{{ $w['key'] }}" class="flex-1 min-w-[80px] py-1.5 rounded-lg text-xs font-bold transition {{ $idx === 0 ? 'bg-white text-slate-800 shadow-sm border border-slate-200/50' : 'text-slate-600 hover:text-slate-800 hover:bg-slate-50' }}">{{ $locale === 'en' ? $w['name_en'] : $w['name'] }}</button>
                    @endforeach
                </div>
                <div class="relative w-[280px] h-[280px] flex items-center justify-center shrink-0 select-none">
                    <!-- Kim chỉ ở đỉnh -->
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1.5 z-10 w-0 h-0 border-l-[12px] border-l-transparent border-r-[12px] border-r-transparent border-t-[20px] border-t-rose-600 filter drop-shadow"></div>
                    
                    <!-- Canvas vẽ đĩa -->
                    <canvas id="admin-wheel-canvas" width="260" height="260" class="rounded-full shadow-lg border-4 border-slate-800 transition-transform duration-[4000ms] ease-[cubic-bezier(0.1,0.8,0.2,1)] bg-white"></canvas>
                    
                    <!-- Nút bấm quay thử -->
                    <button id="admin-wheel-btn" onclick="spinAdminWheel()" class="absolute w-12 h-12 bg-slate-900 text-white rounded-full flex items-center justify-center font-extrabold text-[9px] shadow-md border-4 border-white hover:bg-slate-800 transition active:scale-95 z-20">
                        TEST
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-4 italic text-center"><i class="fa-solid fa-circle-info mr-1"></i>{{ $locale === 'en' ? 'Click "TEST" or center button to test spin the wheel offline.' : 'Bấm nút "TEST" hoặc bấm vào tâm để chạy thử hiệu ứng xoay ngẫu nhiên offline.' }}</p>
            </div>
            
            <!-- Cột phải: Danh sách ô quà (lg:col-span-7) -->
            <div class="lg:col-span-7 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-800 text-sm flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-violet-600 animate-pulse"></span>
                            {{ $locale === 'en' ? 'Current Prizes List' : 'Danh sách ô quà hiện tại' }}
                        </h4>
                        <button type="button" onclick="openQuickCreateWheelPrize()" class="px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition flex items-center gap-1">
                            <i class="fa-solid fa-plus"></i> {{ $locale === 'en' ? 'Add Prize' : 'Thêm ô quà' }}
                        </button>
                    </div>
                    
                    <div class="border border-slate-100 rounded-2xl overflow-hidden max-h-[260px] overflow-y-auto shadow-sm">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold">
                                    <th class="p-3">{{ $locale === 'en' ? 'Prize name' : 'Tên quà' }}</th>
                                    <th class="p-3">{{ $locale === 'en' ? 'Code' : 'Mã Code' }}</th>
                                    <th class="p-3 text-center">{{ $locale === 'en' ? 'Winning rate' : 'Tỷ lệ trúng' }}</th>
                                    <th class="p-3 text-center">{{ $locale === 'en' ? 'Stock' : 'Tồn kho' }}</th>
                                    <th class="p-3 text-center">{{ $locale === 'en' ? 'Status' : 'Trạng thái' }}</th>
                                    <th class="p-3 text-right">{{ $locale === 'en' ? 'Action' : 'Thao tác' }}</th>
                                </tr>
                            </thead>
                            <tbody id="admin-wheel-prizes-list">
                                <!-- JS render danh sách quà -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeWheelSetupModal()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition">{{ $locale === 'en' ? 'Close' : 'Đóng' }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Quản lý danh sách Vòng quay -->
<div id="lucky-wheels-manager-modal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center z-[10000] p-4 transition-all">
    <div class="bg-white rounded-3xl p-6 max-w-2xl w-full max-h-[85vh] overflow-y-auto flex flex-col shadow-2xl animate-in zoom-in-95 duration-200">
        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-gear text-indigo-600"></i>
                    {{ $locale === 'en' ? 'Manage Lucky Wheels List' : 'Quản lý danh sách Vòng quay' }}
                </h3>
                <p class="text-xs text-slate-500 mt-1">{{ $locale === 'en' ? 'Add, edit or delete lucky wheel tiers and points cost.' : 'Thêm, sửa hoặc xóa các cấp độ vòng quay và số điểm quy định.' }}</p>
            </div>
            <button type="button" onclick="closeLuckyWheelsManagerModal()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <!-- Danh sách Vòng quay -->
        <div class="space-y-4 flex-1">
            <div class="overflow-x-auto border border-slate-100 rounded-2xl shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold">
                            <th class="p-3">{{ $locale === 'en' ? 'Key ID' : 'Mã Key (Định danh)' }}</th>
                            <th class="p-3">{{ $locale === 'en' ? 'Name (VI)' : 'Tên (Tiếng Việt)' }}</th>
                            <th class="p-3">{{ $locale === 'en' ? 'Name (EN)' : 'Tên (Tiếng Anh)' }}</th>
                            <th class="p-3">{{ $locale === 'en' ? 'Points Cost' : 'Chi phí (Điểm)' }}</th>
                            <th class="p-3">{{ $locale === 'en' ? 'Required Rank' : 'Hạng yêu cầu' }}</th>
                            <th class="p-3 text-right">{{ $locale === 'en' ? 'Action' : 'Thao tác' }}</th>
                        </tr>
                    </thead>
                    <tbody id="lucky-wheels-list-body">
                        <!-- Render động từ JS -->
                    </tbody>
                </table>
            </div>
            
            <button type="button" onclick="addNewLuckyWheelRow()" class="px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold transition flex items-center gap-1.5 text-xs">
                <i class="fa-solid fa-plus"></i> {{ $locale === 'en' ? 'Add New Wheel' : 'Thêm Vòng quay mới' }}
            </button>
        </div>
        
        <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end gap-2">
            <button type="button" onclick="closeLuckyWheelsManagerModal()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition">{{ $locale === 'en' ? 'Cancel' : 'Hủy bỏ' }}</button>
            <button type="button" onclick="saveLuckyWheelsList()" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition shadow-lg shadow-indigo-100">{{ $locale === 'en' ? 'Save Settings' : 'Lưu cài đặt' }}</button>
        </div>
    </div>
</div>

<script>
const locale = '{{ $locale }}';
const modal = document.getElementById('reward-modal');
const form = document.getElementById('reward-form');
const imageModal = document.getElementById('reward-image-modal');
const imageForm = document.getElementById('reward-image-form');

function toggleAdminTopbar(show) {
  const topbar = document.getElementById('joly-admin-topbar');
  if (topbar) {
    if (show) {
      topbar.style.visibility = '';
    } else {
      topbar.style.visibility = 'hidden';
    }
  }
}

function updateTopbarVisibilityBasedOnModals() {
  const modalSetup = document.getElementById('wheel-setup-modal');
  const managerModal = document.getElementById('lucky-wheels-manager-modal');
  const rewardModal = document.getElementById('reward-modal');
  const imageModal = document.getElementById('reward-image-modal');
  
  const isAnyModalOpen = 
    (modalSetup && modalSetup.classList.contains('flex')) ||
    (managerModal && managerModal.classList.contains('flex')) ||
    (rewardModal && rewardModal.classList.contains('flex')) ||
    (imageModal && imageModal.classList.contains('flex'));
    
  toggleAdminTopbar(!isAnyModalOpen);
}

function toggleMinRankPoints() {
  const select = form.querySelector('[name="requires_rank_check"]');
  const container = document.getElementById('min-rank-points-container');
  if (select && container) {
    if (select.value === '1') {
      container.style.display = '';
    } else {
      container.style.display = 'none';
      const selectRank = container.querySelector('select');
      if (selectRank) selectRank.value = 'none';
    }
  }
}

// Lắng nghe sự kiện thay đổi của select
form.querySelector('[name="requires_rank_check"]')?.addEventListener('change', toggleMinRankPoints);
form.querySelector('[name="reward_type"]')?.addEventListener('change', adjustExchangeFieldsByType);
form.querySelector('[name="wheel_prize_type"]')?.addEventListener('change', adjustExchangeFieldsByType);

let currentMainTab = 'exchange';

function switchMainTab(tab) {
  currentMainTab = tab;
  
  const btnExchange = document.getElementById('tab-btn-exchange');
  const btnWheel = document.getElementById('tab-btn-wheel');
  const btnAddExchange = document.getElementById('btn-add-exchange');
  const btnAddWheelPrize = document.getElementById('btn-add-wheel-prize');
  
  if (tab === 'exchange') {
    if (btnExchange) {
      btnExchange.className = "flex-1 py-2 rounded-lg font-bold text-xs md:text-sm transition flex items-center justify-center gap-1.5 bg-white text-indigo-600 shadow-md shadow-indigo-100/50 border border-slate-200/30";
    }
    if (btnWheel) {
      btnWheel.className = "flex-1 py-2 rounded-lg font-bold text-xs md:text-sm transition flex items-center justify-center gap-1.5 text-slate-500 hover:text-slate-800 hover:bg-white/40";
    }
    if (btnAddExchange) btnAddExchange.classList.remove('hidden');
    if (btnAddWheelPrize) btnAddWheelPrize.classList.add('hidden');
  } else {
    if (btnExchange) {
      btnExchange.className = "flex-1 py-2 rounded-lg font-bold text-xs md:text-sm transition flex items-center justify-center gap-1.5 text-slate-500 hover:text-slate-800 hover:bg-white/40";
    }
    if (btnWheel) {
      btnWheel.className = "flex-1 py-2 rounded-lg font-bold text-xs md:text-sm transition flex items-center justify-center gap-1.5 bg-white text-indigo-600 shadow-md shadow-indigo-100/50 border border-slate-200/30";
    }
    if (btnAddExchange) btnAddExchange.classList.add('hidden');
    if (btnAddWheelPrize) btnAddWheelPrize.classList.remove('hidden');
  }
  
  document.querySelectorAll('.reward-row').forEach(row => {
    if (row.getAttribute('data-main-tab') === tab) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

function adjustExchangeFieldsByType() {
  const rewardTypeSelect = form.querySelector('[name="reward_type"]');
  const isExchange = rewardTypeSelect && rewardTypeSelect.closest('.exchange-only-field')?.style.display !== 'none';
  
  const divDiscount = document.getElementById('div-discount-amount');
  const divShipping = document.getElementById('div-shipping-discount-amount');
  
  const type = isExchange 
    ? (rewardTypeSelect?.value ?? 'voucher') 
    : (form.querySelector('[name="wheel_prize_type"]')?.value ?? 'voucher');
  
  const rewardCategorySelect = form.querySelector('[name="reward_category"]');
  
  if (type === 'voucher') {
    if (divDiscount) divDiscount.style.display = '';
    if (divShipping) divShipping.style.display = 'none';
    if (isExchange && rewardCategorySelect) rewardCategorySelect.value = 'discount';
  } else if (type === 'shipping') {
    if (divDiscount) divDiscount.style.display = 'none';
    if (divShipping) divShipping.style.display = '';
    if (isExchange && rewardCategorySelect) rewardCategorySelect.value = 'free_ship';
  } else if (type === 'product') {
    if (divDiscount) divDiscount.style.display = 'none';
    if (divShipping) divShipping.style.display = 'none';
    if (isExchange && rewardCategorySelect) rewardCategorySelect.value = 'gift';
  }
}

function adjustFormFieldsByMode(mode) {
  document.querySelectorAll('.exchange-only-field').forEach(el => {
    el.style.display = mode === 'exchange' ? '' : 'none';
  });
  
  document.querySelectorAll('.wheel-only-field').forEach(el => {
    el.style.display = mode === 'wheel' ? '' : 'none';
  });

  const rewardTypeSelect = form.querySelector('[name="reward_type"]');
  const rewardCategorySelect = form.querySelector('[name="reward_category"]');
  const pointsCostInput = form.querySelector('[name="points_cost"]');

  if (mode === 'wheel') {
    if (rewardTypeSelect) rewardTypeSelect.value = 'wheel_prize';
    if (rewardCategorySelect) rewardCategorySelect.value = 'wheel';
    if (pointsCostInput) pointsCostInput.value = 0;
  } else {
    if (rewardTypeSelect && rewardTypeSelect.value === 'wheel_prize') {
      rewardTypeSelect.value = 'voucher';
    }
    if (rewardCategorySelect && rewardCategorySelect.value === 'wheel') {
      rewardCategorySelect.value = 'discount';
    }
  }
  
  adjustExchangeFieldsByType();
}

function openCreateModal(mode = null) {
  if (!mode) mode = currentMainTab;
  
  if (mode === 'wheel') {
    document.getElementById('modal-title').textContent = '{{ $locale === "en" ? "Create Lucky Wheel Prize" : "Tạo quà vòng quay may mắn" }}';
  } else {
    document.getElementById('modal-title').textContent = '{{ $locale === "en" ? "Create Reward" : "Tạo phần thưởng đổi điểm" }}';
  }
  
  form.action = '{{ route('admin.rewards.store') }}';
  document.getElementById('method-field').innerHTML = '';
  form.reset();
  
  adjustFormFieldsByMode(mode);
  
  if (mode === 'wheel') {
    form.querySelector('[name="code"]').value = 'WHEEL_' + Date.now().toString().slice(-6);
    form.querySelector('[name="name"]').value = 'Quà Vòng Quay - ';
    form.querySelector('[name="winning_rate"]').value = 10;
    if (luckyWheels && luckyWheels.length > 0) {
      form.querySelector('[name="wheel_type"]').value = luckyWheels[0].key;
    } else {
      form.querySelector('[name="wheel_type"]').value = 'standard';
    }
    const wheelPrizeTypeSelect = form.querySelector('[name="wheel_prize_type"]');
    if (wheelPrizeTypeSelect) {
      wheelPrizeTypeSelect.value = 'voucher';
    }
  } else {
    form.querySelector('[name="code"]').value = '';
    form.querySelector('[name="name"]').value = '';
    const minRankSelect = form.querySelector('[name="min_rank"]');
    if (minRankSelect) minRankSelect.value = 'none';
    toggleMinRankPoints();
  }
  
  adjustExchangeFieldsByType();
  
  const imgInput = document.getElementById('reward-image-input');
  if (imgInput) imgInput.value = '';
  document.getElementById('image-preview').textContent = '{{ $locale === "en" ? "No image" : "Chưa có ảnh" }}';
  
  modal.classList.remove('hidden'); 
  modal.classList.add('flex');
  updateTopbarVisibilityBasedOnModals();
}

function openEditModal(item) {
  const mode = item.reward_type === 'wheel_prize' ? 'wheel' : 'exchange';
  
  if (mode === 'wheel') {
    document.getElementById('modal-title').textContent = '{{ $locale === "en" ? "Edit Lucky Wheel Prize" : "Sửa quà vòng quay may mắn" }}';
  } else {
    document.getElementById('modal-title').textContent = '{{ $locale === "en" ? "Edit Reward" : "Sửa phần thưởng đổi điểm" }}';
  }
  
  form.action = `/admin/rewards/${item.reward_id}`;
  document.getElementById('method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
  
  adjustFormFieldsByMode(mode);
  
  for (const [k,v] of Object.entries(item)) {
    const el = form.querySelector(`[name="${k}"]`);
    if (el) el.value = v ?? '';
  }
  
  // Gán winning_rate & wheel_type & min_rank từ metadata
  const winningRateInput = form.querySelector('[name="winning_rate"]');
  if (winningRateInput) {
    winningRateInput.value = item.metadata?.winning_rate ?? 10;
  }
  const wheelTypeSelect = form.querySelector('[name="wheel_type"]');
  if (wheelTypeSelect) {
    wheelTypeSelect.value = item.metadata?.wheel_type ?? 'standard';
  }
  const minRankSelect = form.querySelector('[name="min_rank"]');
  if (minRankSelect) {
    minRankSelect.value = item.metadata?.min_rank ?? 'none';
  }
  const wheelPrizeTypeSelect = form.querySelector('[name="wheel_prize_type"]');
  if (wheelPrizeTypeSelect) {
    wheelPrizeTypeSelect.value = item.metadata?.wheel_prize_type ?? 'voucher';
  }
  
  const imgInput = document.getElementById('reward-image-input');
  if (imgInput) imgInput.value = '';
  
  if (mode === 'exchange') {
    toggleMinRankPoints();
  }
  
  adjustExchangeFieldsByType();
  
  const preview = document.getElementById('image-preview');
  if (item.display_image) {
    preview.innerHTML = `<img src="/storage/${item.display_image}" class="w-full h-full object-cover" alt="preview">`;
  } else {
    preview.textContent = '{{ $locale === "en" ? "No image" : "Chưa có ảnh" }}';
  }
  
  modal.classList.remove('hidden'); 
  modal.classList.add('flex');
  updateTopbarVisibilityBasedOnModals();
}

function closeRewardModal(){ modal.classList.add('hidden'); modal.classList.remove('flex'); updateTopbarVisibilityBasedOnModals(); }
function openImageModal(item){
  imageForm.action = `/admin/rewards/${item.reward_id}/image`;
  const preview = document.getElementById('reward-image-preview');
  const placeholder = document.getElementById('reward-image-placeholder');
  if (item.display_image) {
    preview.src = `/storage/${item.display_image}`;
    preview.classList.remove('hidden');
    placeholder.classList.add('hidden');
  } else {
    preview.src = '';
    preview.classList.add('hidden');
    placeholder.classList.remove('hidden');
  }
  imageModal.classList.remove('hidden'); imageModal.classList.add('flex');
  updateTopbarVisibilityBasedOnModals();
}
function closeImageModal(){ imageModal.classList.add('hidden'); imageModal.classList.remove('flex'); updateTopbarVisibilityBasedOnModals(); }

document.getElementById('reward-image-form')?.addEventListener('change', (e) => {
  const file = e.target.files?.[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = () => {
    const preview = document.getElementById('reward-image-preview');
    const placeholder = document.getElementById('reward-image-placeholder');
    preview.src = reader.result;
    preview.classList.remove('hidden');
    placeholder.classList.add('hidden');
  };
  reader.readAsDataURL(file);
});

document.getElementById('reward-image-input')?.addEventListener('change', (e) => {
  const file = e.target.files?.[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = () => {
    const preview = document.getElementById('image-preview');
    if (preview) {
      preview.innerHTML = `<img src="${reader.result}" class="w-full h-full object-cover" alt="preview">`;
    }
  };
  reader.readAsDataURL(file);
});

// Vòng quay may mắn Admin
let luckyWheels = @json($wheels);
const allWheelPrizes = @json($catalog->where('reward_type', 'wheel_prize')->values());
const wheelColors = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#14b8a6'];
let adminCurrentRotation = 0;
let adminIsSpinning = false;
let wheelItems = [];
let adminLedTick = 0;
let currentAdminWheelTab = luckyWheels.length > 0 ? luckyWheels[0].key : 'standard';
let activeWheelPrizes = [];

function switchAdminWheelTab(tab) {
  currentAdminWheelTab = tab;
  
  // Cập nhật CSS cho các button tab
  luckyWheels.forEach(w => {
    const btn = document.getElementById(`admin-tab-${w.key}`);
    if (btn) {
      if (w.key === tab) {
        btn.className = "flex-1 py-1.5 rounded-lg text-xs font-bold transition bg-white text-slate-800 shadow-sm border border-slate-200/50";
      } else {
        btn.className = "flex-1 py-1.5 rounded-lg text-xs font-bold transition text-slate-500 hover:text-slate-700 hover:bg-slate-50";
      }
    }
  });
  
  // Reset trạng thái xoay của Canvas
  adminCurrentRotation = 0;
  if (adminCanvas) adminCanvas.style.transform = `rotate(0deg)`;
  
  openWheelSetupModal();
}

setInterval(() => {
  adminLedTick = (adminLedTick + 1) % 2;
  const modalSetup = document.getElementById('wheel-setup-modal');
  if (modalSetup && !modalSetup.classList.contains('hidden')) {
    drawAdminWheel();
  }
}, 280);

const adminCanvas = document.getElementById('admin-wheel-canvas');
const adminCtx = adminCanvas ? adminCanvas.getContext('2d') : null;

function buildWheelItems() {
  activeWheelPrizes = allWheelPrizes.filter(prize => {
    const meta = prize.metadata ?? {};
    const type = meta.wheel_type ?? 'standard';
    return type === currentAdminWheelTab;
  });

  if (activeWheelPrizes.length === 0) {
    wheelItems = [];
    return;
  }
  let temp = [...activeWheelPrizes];
  while (temp.length < 6) {
    temp = temp.concat(activeWheelPrizes);
  }
  wheelItems = temp.map((prize, idx) => ({
    id: prize.reward_id,
    name: prize.name.replace('Vòng quay - ', '').substring(0, 12),
    color: wheelColors[idx % wheelColors.length],
    textColor: '#ffffff'
  }));
}

function drawAdminWheel() {
  if (!adminCanvas || !adminCtx) return;
  const size = adminCanvas.width;
  const center = size / 2;
  const radius = center - 8;

  adminCtx.clearRect(0, 0, size, size);

  if (wheelItems.length === 0) {
    adminCtx.beginPath();
    adminCtx.arc(center, center, radius, 0, 2 * Math.PI);
    adminCtx.fillStyle = '#f8fafc';
    adminCtx.fill();
    adminCtx.strokeStyle = '#e2e8f0';
    adminCtx.lineWidth = 4;
    adminCtx.stroke();

    adminCtx.fillStyle = '#94a3b8';
    adminCtx.font = 'bold 11px sans-serif';
    adminCtx.textAlign = 'center';
    adminCtx.textBaseline = 'middle';
    adminCtx.fillText('{{ $locale === "en" ? "No prizes configured" : "Chưa cấu hình quà" }}', center, center);
    return;
  }

  const anglePerSeg = (2 * Math.PI) / wheelItems.length;

  wheelItems.forEach((seg, i) => {
    const startAngle = i * anglePerSeg;
    const endAngle = startAngle + anglePerSeg;

    adminCtx.beginPath();
    adminCtx.moveTo(center, center);
    adminCtx.arc(center, center, radius - 6, startAngle, endAngle);
    adminCtx.closePath();

    // Tạo Radial Gradient 3D sang trọng đồng bộ với frontend
    const grad = adminCtx.createRadialGradient(center, center, 10, center, center, radius - 6);
    // Nhận diện màu để chuyển sắc cho đẹp mắt
    if (seg.color === '#ef4444') {
      grad.addColorStop(0, '#ff8787');
      grad.addColorStop(1, '#c92a2a');
    } else if (seg.color === '#3b82f6') {
      grad.addColorStop(0, '#74c0fc');
      grad.addColorStop(1, '#1c7ed6');
    } else if (seg.color === '#10b981') {
      grad.addColorStop(0, '#8ce99a');
      grad.addColorStop(1, '#2b8a3e');
    } else if (seg.color === '#f59e0b') {
      grad.addColorStop(0, '#ffe066');
      grad.addColorStop(1, '#d9480f');
    } else if (seg.color === '#8b5cf6') {
      grad.addColorStop(0, '#b197fc');
      grad.addColorStop(1, '#6741d9');
    } else if (seg.color === '#ec4899') {
      grad.addColorStop(0, '#faa2c1');
      grad.addColorStop(1, '#c2255c');
    } else if (seg.color === '#06b6d4') {
      grad.addColorStop(0, '#66d9e8');
      grad.addColorStop(1, '#0b7285');
    } else if (seg.color === '#14b8a6') {
      grad.addColorStop(0, '#63e6be');
      grad.addColorStop(1, '#0ca678');
    } else {
      grad.addColorStop(0, '#f8fafc');
      grad.addColorStop(1, '#cbd5e1');
    }

    adminCtx.fillStyle = grad;
    adminCtx.fill();

    adminCtx.strokeStyle = '#ffffff';
    adminCtx.lineWidth = 1.2;
    adminCtx.stroke();

    adminCtx.save();
    adminCtx.translate(center, center);
    adminCtx.rotate(startAngle + anglePerSeg / 2);
    adminCtx.textAlign = 'right';
    adminCtx.textBaseline = 'middle';
    
    adminCtx.shadowColor = 'rgba(0, 0, 0, 0.4)';
    adminCtx.shadowBlur = 3;
    adminCtx.fillStyle = seg.textColor;
    adminCtx.font = 'bold 9px sans-serif';
    adminCtx.fillText(seg.name, radius - 18, 0);
    adminCtx.restore();
  });

  // Vòng tròn viền ngoài mạ vàng kim (Gold Metallic Rim)
  const rimGrad = adminCtx.createRadialGradient(center, center, radius - 10, center, center, radius);
  rimGrad.addColorStop(0, '#b58928');
  rimGrad.addColorStop(0.3, '#f9d976');
  rimGrad.addColorStop(0.7, '#e9b646');
  rimGrad.addColorStop(1, '#8a5a00');

  adminCtx.beginPath();
  adminCtx.arc(center, center, radius - 5, 0, 2 * Math.PI);
  adminCtx.strokeStyle = rimGrad;
  adminCtx.lineWidth = 10;
  adminCtx.stroke();

  // Khuyên tròn bảo vệ viền ngoài
  adminCtx.beginPath();
  adminCtx.arc(center, center, radius, 0, 2 * Math.PI);
  adminCtx.strokeStyle = '#2d1e03';
  adminCtx.lineWidth = 1.5;
  adminCtx.stroke();

  // Vẽ các chấm LED lấp lánh chạy xung quanh viền
  const numLeds = 24;
  const ledAngle = (2 * Math.PI) / numLeds;
  for (let j = 0; j < numLeds; j++) {
    const angle = j * ledAngle;
    const x = center + (radius - 5) * Math.cos(angle);
    const y = center + (radius - 5) * Math.sin(angle);

    adminCtx.beginPath();
    adminCtx.arc(x, y, 3.5, 0, 2 * Math.PI);

    if ((j + adminLedTick) % 2 === 0) {
      adminCtx.fillStyle = '#ffffb3'; // Vàng neon sáng
      adminCtx.shadowColor = '#f59e0b';
      adminCtx.shadowBlur = 5;
    } else {
      adminCtx.fillStyle = '#d9480f'; // Đỏ cam tối
      adminCtx.shadowBlur = 0;
    }
    adminCtx.fill();
    adminCtx.shadowBlur = 0;
  }

  // Vòng tròn trung tâm 3D Gold
  const centerGrad = adminCtx.createRadialGradient(center, center, 0, center, center, 20);
  centerGrad.addColorStop(0, '#ffffff');
  centerGrad.addColorStop(0.4, '#f9d976');
  centerGrad.addColorStop(1, '#b58928');

  adminCtx.beginPath();
  adminCtx.arc(center, center, 18, 0, 2 * Math.PI);
  adminCtx.fillStyle = centerGrad;
  adminCtx.fill();

  adminCtx.strokeStyle = '#8a5a00';
  adminCtx.lineWidth = 1.5;
  adminCtx.stroke();
}

function spinAdminWheel() {
  if (adminIsSpinning || wheelItems.length === 0) return;

  const btn = document.getElementById('admin-wheel-btn');
  adminIsSpinning = true;
  if (btn) btn.disabled = true;

  const targetIndex = Math.floor(Math.random() * wheelItems.length);
  const anglePerSeg = (2 * Math.PI) / wheelItems.length;

  const centerAngleRad = (targetIndex * anglePerSeg) + (anglePerSeg / 2);
  const centerAngleDeg = (centerAngleRad * 180) / Math.PI;

  let targetRotationDeg = 270 - centerAngleDeg;
  const extraSpins = 5;
  const additionalDeg = (extraSpins * 360) + (targetRotationDeg - (adminCurrentRotation % 360) + 360) % 360;
  adminCurrentRotation += additionalDeg;

  adminCanvas.style.transform = `rotate(${adminCurrentRotation}deg)`;

  setTimeout(() => {
    alert(`${locale === 'en' ? '[Test Spin] Stopped at: ' : '[Quay thử] Dừng ở ô: '}"${wheelItems[targetIndex].name}"`);
    adminIsSpinning = false;
    if (btn) btn.disabled = false;
  }, 4100);
}

function openWheelSetupModal() {
  buildWheelItems();
  
  const tbody = document.getElementById('admin-wheel-prizes-list');
  tbody.innerHTML = '';
  
  if (activeWheelPrizes.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-slate-400 italic">${locale === 'en' ? 'No prizes configured. Please add new segments!' : 'Chưa cấu hình quà. Hãy thêm ô quà tặng mới!'}</td></tr>`;
  } else {
    activeWheelPrizes.forEach(prize => {
      const rate = prize.metadata?.winning_rate ?? 10;
      const tr = document.createElement('tr');
      tr.className = 'border-b border-slate-100 last:border-0 hover:bg-slate-50 transition';
      tr.innerHTML = `
        <td class="p-3 font-semibold text-slate-800">${prize.name}</td>
        <td class="p-3 text-slate-500 font-mono text-[10px]">${prize.code}</td>
        <td class="p-3 text-center text-indigo-600 font-bold bg-indigo-50/30 rounded-lg">${rate}%</td>
        <td class="p-3 text-center text-slate-600">${prize.stock !== null ? prize.stock : (locale === 'en' ? 'Unlimited' : 'Vô hạn')}</td>
        <td class="p-3 text-center">
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${prize.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}">
            ${prize.is_active ? (locale === 'en' ? 'Active' : 'Bật') : (locale === 'en' ? 'Inactive' : 'Tắt')}
          </span>
        </td>
        <td class="p-3 text-right">
          <button type="button" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold transition" onclick='openEditModalFromWheel(${JSON.stringify(prize)})'>${locale === 'en' ? 'Edit' : 'Sửa'}</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  setTimeout(drawAdminWheel, 100);

  const modalSetup = document.getElementById('wheel-setup-modal');
  modalSetup.classList.remove('hidden');
  modalSetup.classList.add('flex');
  updateTopbarVisibilityBasedOnModals();
}

function closeWheelSetupModal() {
  const modalSetup = document.getElementById('wheel-setup-modal');
  modalSetup.classList.add('hidden');
  modalSetup.classList.remove('flex');
  updateTopbarVisibilityBasedOnModals();
}

function openQuickCreateWheelPrize() {
  closeWheelSetupModal();
  openCreateModal('wheel');
  
  form.querySelector('[name="wheel_type"]').value = currentAdminWheelTab;
  form.querySelector('[name="code"]').value = 'WHEEL_' + Date.now().toString().slice(-6);
  form.querySelector('[name="name"]').value = 'Vòng quay - ';
}

function openEditModalFromWheel(prize) {
  closeWheelSetupModal();
  openEditModal(prize);
}

function updateWheelVisibility(checked) {
  const statusText = document.getElementById('wheel-status-text');
  if (statusText) {
    statusText.textContent = checked ? (locale === 'en' ? 'Showing...' : 'Đang hiện...') : (locale === 'en' ? 'Hidden...' : 'Đang ẩn...');
  }
  
  fetch('{{ route('admin.rewards.update-setting') }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ key: 'show_lucky_wheel', value: checked ? '1' : '0' })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      if (statusText) {
        statusText.textContent = checked ? (locale === 'en' ? 'Showing on Frontend' : 'Đang hiện ở trang chủ') : (locale === 'en' ? 'Hidden on Frontend' : 'Đang ẩn ở trang chủ');
      }
    } else {
      alert(data.message || 'Lỗi cập nhật cấu hình');
    }
  })
  .catch(err => {
    console.error(err);
    alert('Lỗi kết nối máy chủ');
  });
}

let tempLuckyWheels = [];

function openLuckyWheelsManagerModal() {
  tempLuckyWheels = JSON.parse(JSON.stringify(luckyWheels));
  renderLuckyWheelsList();
  closeWheelSetupModal();
  
  const managerModal = document.getElementById('lucky-wheels-manager-modal');
  if (managerModal) {
    managerModal.classList.remove('hidden');
    managerModal.classList.add('flex');
  }
  updateTopbarVisibilityBasedOnModals();
}

function closeLuckyWheelsManagerModal() {
  const managerModal = document.getElementById('lucky-wheels-manager-modal');
  if (managerModal) {
    managerModal.classList.add('hidden');
    managerModal.classList.remove('flex');
  }
  updateTopbarVisibilityBasedOnModals();
  openWheelSetupModal();
}

function renderLuckyWheelsList() {
  const tbody = document.getElementById('lucky-wheels-list-body');
  if (!tbody) return;
  tbody.innerHTML = '';
  
  if (tempLuckyWheels.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-slate-400 italic">${locale === 'en' ? 'No lucky wheels configured. Click button below to add one!' : 'Chưa cấu hình vòng quay nào. Hãy bấm nút phía dưới để thêm mới!'}</td></tr>`;
    return;
  }
  
  tempLuckyWheels.forEach((w, index) => {
    const minRankVal = w.min_rank || 'none';
    const tr = document.createElement('tr');
    tr.className = 'border-b border-slate-100 last:border-0 hover:bg-slate-50 transition';
    tr.innerHTML = `
      <td class="p-3">
        <input type="text" class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs font-mono font-bold uppercase text-slate-700" value="${w.key}" onchange="updateTempLuckyWheelValue(${index}, 'key', this.value)" ${['standard', 'silver', 'gold'].includes(w.key) ? 'disabled' : ''}>
      </td>
      <td class="p-3">
        <input type="text" class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700" value="${w.name}" onchange="updateTempLuckyWheelValue(${index}, 'name', this.value)">
      </td>
      <td class="p-3">
        <input type="text" class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700" value="${w.name_en}" onchange="updateTempLuckyWheelValue(${index}, 'name_en', this.value)">
      </td>
      <td class="p-3">
        <input type="number" min="0" class="w-20 px-2 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700" value="${w.points_cost}" onchange="updateTempLuckyWheelValue(${index}, 'points_cost', this.value)">
      </td>
      <td class="p-3">
        <select class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 bg-white" onchange="updateTempLuckyWheelValue(${index}, 'min_rank', this.value)">
          <option value="none" ${minRankVal === 'none' ? 'selected' : ''}>${locale === 'en' ? 'No requirement' : 'Không yêu cầu'}</option>
          <option value="Dong" ${minRankVal === 'Dong' ? 'selected' : ''}>${locale === 'en' ? 'Bronze' : 'Đồng'}</option>
          <option value="Bac" ${minRankVal === 'Bac' ? 'selected' : ''}>${locale === 'en' ? 'Silver' : 'Bạc'}</option>
          <option value="Vang" ${minRankVal === 'Vang' ? 'selected' : ''}>${locale === 'en' ? 'Gold' : 'Vàng'}</option>
          <option value="KimCuong" ${minRankVal === 'KimCuong' ? 'selected' : ''}>${locale === 'en' ? 'Diamond' : 'Kim Cương'}</option>
        </select>
      </td>
      <td class="p-3 text-right">
        <button type="button" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition" onclick="deleteLuckyWheelRow(${index})">
          <i class="fa-solid fa-trash"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function updateTempLuckyWheelValue(index, field, value) {
  if (field === 'key') {
    value = value.toLowerCase().replace(/[^a-z0-9_]/g, '');
  }
  if (field === 'points_cost') {
    value = parseInt(value) || 0;
  }
  tempLuckyWheels[index][field] = value;
}

function addNewLuckyWheelRow() {
  const newKey = 'wheel_' + Date.now().toString().slice(-4);
  tempLuckyWheels.push({
    key: newKey,
    name: 'Vòng quay mới',
    name_en: 'New Lucky Wheel',
    points_cost: 10,
    min_rank: 'none'
  });
  renderLuckyWheelsList();
}

function deleteLuckyWheelRow(index) {
  const w = tempLuckyWheels[index];
  if (['standard', 'silver', 'gold'].includes(w.key)) {
    if (!confirm(locale === 'en' ? 'This is a default system wheel. Are you sure you want to delete it?' : 'Đây là vòng quay mặc định của hệ thống. Bạn có chắc chắn muốn xóa không?')) {
      return;
    }
  } else {
    if (!confirm(locale === 'en' ? 'Are you sure you want to delete this lucky wheel?' : 'Bạn có chắc chắn muốn xóa vòng quay này không?')) {
      return;
    }
  }
  tempLuckyWheels.splice(index, 1);
  renderLuckyWheelsList();
}

function saveLuckyWheelsList() {
  let keys = [];
  for (let i = 0; i < tempLuckyWheels.length; i++) {
    const w = tempLuckyWheels[i];
    if (!w.key || w.key.trim() === '') {
      alert(locale === 'en' ? 'Key ID cannot be empty!' : 'Mã Key định danh không được để trống!');
      return;
    }
    if (keys.includes(w.key)) {
      alert((locale === 'en' ? 'Duplicate Key ID found: ' : 'Phát hiện trùng mã định danh: ') + w.key);
      return;
    }
    keys.push(w.key);
    if (!w.name || w.name.trim() === '') {
      alert(locale === 'en' ? 'Name (VI) cannot be empty!' : 'Tên (Tiếng Việt) không được để trống!');
      return;
    }
    if (!w.name_en || w.name_en.trim() === '') {
      alert(locale === 'en' ? 'Name (EN) cannot be empty!' : 'Tên (Tiếng Anh) không được để trống!');
      return;
    }
  }

  fetch('{{ route('admin.rewards.update-lucky-wheels') }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ wheels: tempLuckyWheels })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(locale === 'en' ? 'Lucky wheels saved successfully!' : 'Đã lưu danh sách vòng quay thành công!');
      luckyWheels = data.wheels;
      
      updateAdminWheelTabsUI();
      updateRewardFormSelectWheels();
      
      // Update tab hiện tại nếu tab đó bị xóa
      if (!luckyWheels.some(x => x.key === currentAdminWheelTab)) {
        currentAdminWheelTab = luckyWheels.length > 0 ? luckyWheels[0].key : 'standard';
      }

      closeLuckyWheelsManagerModal();
    } else {
      alert(data.message || 'Lỗi lưu thông tin');
    }
  })
  .catch(err => {
    console.error(err);
    alert('Lỗi kết nối máy chủ');
  });
}

function updateAdminWheelTabsUI() {
  const container = document.getElementById('admin-wheel-tabs-container');
  if (!container) return;
  
  container.innerHTML = '';
  luckyWheels.forEach((w, idx) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.onclick = () => switchAdminWheelTab(w.key);
    btn.id = `admin-tab-${w.key}`;
    if (w.key === currentAdminWheelTab || (idx === 0 && !luckyWheels.some(x => x.key === currentAdminWheelTab))) {
      currentAdminWheelTab = w.key;
      btn.className = "flex-1 min-w-[80px] py-1.5 rounded-lg text-xs font-bold transition bg-white text-slate-800 shadow-sm border border-slate-200/50";
    } else {
      btn.className = "flex-1 min-w-[80px] py-1.5 rounded-lg text-xs font-bold transition text-slate-600 hover:text-slate-800 hover:bg-slate-50";
    }
    btn.textContent = locale === 'en' ? w.name_en : w.name;
    container.appendChild(btn);
  });
}

function updateRewardFormSelectWheels() {
  const select = document.getElementById('select-wheel-type');
  if (!select) return;
  
  const currentVal = select.value;
  select.innerHTML = '';
  luckyWheels.forEach(w => {
    const opt = document.createElement('option');
    opt.value = w.key;
    opt.textContent = `${locale === 'en' ? w.name_en : w.name} (${w.points_cost}đ)`;
    select.appendChild(opt);
  });
  
  if (luckyWheels.some(x => x.key === currentVal)) {
    select.value = currentVal;
  }
}

// Khởi tạo tab ban đầu khi tải trang
document.addEventListener('DOMContentLoaded', () => {
  switchMainTab('exchange');
});
</script>
@endsection
