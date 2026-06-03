@extends('admin.layouts.master')

@section('title', 'Sổ Quỹ – Điện Máy PRO')

@section('page-title', 'Sổ Quỹ Tài Chính')

@push('styles')
<style>
    .chart-container { position: relative; height: 300px; width: 100%; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    /* Animation cho thanh bulk actions */
    @keyframes slideUp {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .animate-slide-up { animation: slideUp 0.3s ease-out forwards; }

    /* Modal Glassmorphism & Animations */
    .custom-modal-backdrop {
        transition: all 0.3s ease-in-out;
    }
    .modal-content-anim {
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        transform: scale(0.95) translateY(10px);
        opacity: 0;
    }
    .modal-active .modal-content-anim {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
    .modal-active {
        display: flex !important;
    }

    /* Remove number arrows */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }

    /* Toast Animation */
    @keyframes slideLeft {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .animate-slide-left {
        animation: slideLeft 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

</style>
@endpush

@section('content')

{{-- Toast Notifications Container --}}
<div id="toast-container" class="fixed top-24 right-8 z-[200] flex flex-col gap-3 pointer-events-none">
    @if(session('success'))
        <div class="bg-white border-l-4 border-emerald-500 shadow-[0_10px_40px_rgba(0,0,0,0.1)] rounded-xl p-4 flex items-center gap-4 animate-slide-left toast-item transition-all duration-300 w-80">
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-check text-lg"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-800 tracking-tight">Thành công!</h4>
                <p class="text-[11px] font-bold text-slate-500 mt-0.5 leading-tight">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-white border-l-4 border-rose-500 shadow-[0_10px_40px_rgba(0,0,0,0.1)] rounded-xl p-4 flex items-center gap-4 animate-slide-left toast-item transition-all duration-300 w-80">
            <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-xmark text-lg"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-800 tracking-tight">Thất bại!</h4>
                <p class="text-[11px] font-bold text-slate-500 mt-0.5 leading-tight">{{ session('error') }}</p>
            </div>
        </div>
    @endif
</div>
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 pb-20">

    {{-- Top Action Bar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Quản lý Dòng tiền</h1>
            <p class="text-sm text-slate-500 font-medium">Theo dõi, kiểm soát thu chi và tối ưu hóa tài chính cửa hàng.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden sm:flex bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm items-center gap-2">
                <i class="fa-solid fa-calendar-day text-indigo-500"></i>
                <span class="text-xs font-bold text-slate-600">{{ now('Asia/Ho_Chi_Minh')->locale('vi')->isoFormat('dddd, D/MM/YYYY') }}</span>
            </div>
            <button onclick="openModal('modal-add')"
                    class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-6 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-indigo-100 hover:scale-[1.02] active:scale-95">
                <i class="fa-solid fa-plus"></i> Thêm giao dịch
            </button>
        </div>
    </div>

    {{-- Stats & Chart Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-4">
            <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm flex items-center justify-between group hover:border-emerald-200 transition-all hover:shadow-emerald-100/50 hover:shadow-xl">
                <div class="space-y-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Tổng thu nhập</p>
                    <p class="text-3xl font-black text-emerald-600 tabular-nums">{{ number_format($totalIncome) }}<span class="text-base ml-1">đ</span></p>
                </div>
                <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
                    <i class="fa-solid fa-arrow-trend-up text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm flex items-center justify-between group hover:border-rose-200 transition-all hover:shadow-rose-100/50 hover:shadow-xl">
                <div class="space-y-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Tổng chi phí</p>
                    <p class="text-3xl font-black text-rose-500 tabular-nums">{{ number_format($totalExpense) }}<span class="text-base ml-1">đ</span></p>
                </div>
                <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center group-hover:bg-rose-500 group-hover:text-white transition-all duration-300">
                    <i class="fa-solid fa-arrow-trend-down text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm flex items-center justify-between group {{ $balance >= 0 ? 'hover:border-blue-200 hover:shadow-indigo-100/50' : 'hover:border-rose-200 hover:shadow-rose-100/50' }} transition-all hover:shadow-xl">
                <div class="space-y-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Số dư hiện tại</p>
                    <p class="text-3xl font-black tabular-nums {{ $balance >= 0 ? 'text-indigo-600' : 'text-rose-600' }}">
                        {{ $balance < 0 ? '-' : '' }}{{ number_format(abs($balance)) }}<span class="text-base ml-1">đ</span>
                    </p>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 {{ $balance >= 0 ? 'bg-indigo-50 text-indigo-500 group-hover:bg-indigo-600 group-hover:text-white' : 'bg-rose-50 text-rose-500 group-hover:bg-rose-600 group-hover:text-white' }}">
                    <i class="fa-solid {{ $balance >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="lg:col-span-2 bg-white rounded-[2rem] p-8 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="font-black text-slate-800 text-lg">Xu hướng tài chính</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Biến động trong 7 ngày gần nhất</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm shadow-emerald-200"></span>
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Thu</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500 shadow-sm shadow-rose-200"></span>
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Chi</span>
                    </div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Filter & List Section --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden relative">
        
        {{-- List Header / Filter --}}
        <div class="px-8 py-6 border-b border-slate-200 bg-slate-50/50 relative">
            <form method="GET" action="{{ route('admin.cashbooks.index') }}" onsubmit="return validateFilter(this)" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; width: 100%;">
                
                {{-- Ô Tìm Kiếm --}}
                <div style="flex: 1 1 300px; position: relative;">
                    <div style="position: absolute; top: 50%; left: 1.25rem; transform: translateY(-50%); pointer-events: none; color: #94a3b8;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input name="search" value="{{ request('search') }}" type="text"
                           placeholder="Nhập mã giao dịch hoặc nội dung để tìm..."
                           style="width: 100%; padding: 1rem 1.5rem 1rem 3rem; background-color: white; border: 2px solid #e2e8f0; border-radius: 1rem; font-size: 0.875rem; font-weight: 700; color: #334155; outline: none; transition: all 0.3s;"
                           onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 4px #e0e7ff';"
                           onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                </div>

                {{-- Nhóm Bộ Lọc & Nút --}}
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    
                    {{-- Nút Tìm --}}
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white transition-all shadow-xl shadow-indigo-100 active:scale-95 flex items-center justify-center gap-2"
                            style="padding: 1rem 2rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 900; min-width: max-content;">
                        <i class="fa-solid fa-magnifying-glass text-indigo-200"></i> Tìm
                    </button>
                    
                    {{-- Dropdown Phân Loại --}}
                    <div style="position: relative; width: 200px;">
                        <select name="type" onchange="this.form.submit()"
                                style="width: 100%; padding: 1rem 2.5rem 1rem 1.25rem; background-color: white; border: 2px solid #e2e8f0; border-radius: 1rem; font-size: 0.875rem; font-weight: 700; color: #475569; outline: none; appearance: none; cursor: pointer; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#6366f1';"
                                onblur="this.style.borderColor='#e2e8f0';">
                            <option value="">Tất cả loại giao dịch</option>
                            <option value="Income"  {{ request('type') === 'Income'  ? 'selected' : '' }}>Khoản Thu (+)</option>
                            <option value="Expense" {{ request('type') === 'Expense' ? 'selected' : '' }}>Khoản Chi (-)</option>
                        </select>
                        <div style="position: absolute; top: 50%; right: 1.25rem; transform: translateY(-50%); pointer-events: none; color: #94a3b8;">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>

                    {{-- Nút Xóa Lọc --}}
                    @if(request()->hasAny(['search','type']))
                        <a href="{{ route('admin.cashbooks.index') }}"
                           class="bg-rose-50 hover:bg-rose-500 text-rose-500 hover:text-white transition-all shadow-sm flex items-center justify-center"
                           title="Xóa bộ lọc"
                           style="width: 52px; height: 52px; border-radius: 1rem;">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </a>
                    @endif
                </div>
            </form>

        </div>

        {{-- Table Content --}}
        <form id="bulk-delete-form" action="{{ route('admin.cashbooks.bulkDestroy') }}" method="POST">
            @csrf
            <input type="hidden" name="select_all_matching" id="select-all-matching" value="0">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="type" value="{{ request('type') }}">
            <div class="overflow-x-auto custom-scrollbar">
                @if($cashbooks->isEmpty())
                    <div class="py-32 text-center">
                        @if(request()->filled('search'))
                            <div class="w-24 h-24 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-500">
                                <i class="fa-solid fa-search text-4xl"></i>
                            </div>
                            <h3 class="text-slate-900 font-black text-2xl tracking-tight">Không tìm thấy kết quả!</h3>
                            <p class="text-slate-500 font-medium text-sm mt-3 max-w-md mx-auto leading-relaxed">
                                Rất tiếc, không có giao dịch nào khớp với từ khóa <strong class="text-slate-800">"{{ request('search') }}"</strong>.
                                <br><br>
                                <span class="text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg inline-block text-xs font-bold border border-indigo-100">💡 Gợi ý: Vui lòng nhập lại <b>Mã giao dịch</b> hoặc một phần <b>Nội dung chi tiết</b> để tìm.</span>
                            </p>
                        @else
                            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fa-solid fa-inbox text-slate-200 text-4xl"></i>
                            </div>
                            <h3 class="text-slate-900 font-black text-xl">Dữ liệu trống</h3>
                            <p class="text-slate-400 font-medium text-sm mt-2">Thử thay đổi bộ lọc hoặc thêm giao dịch mới.</p>
                        @endif
                    </div>
                @else
                    <table class="w-full text-left min-w-[768px]">
                        <thead>
                            <tr class="text-slate-400 text-[10px] uppercase font-black tracking-[0.2em] bg-white border-b border-slate-100">
                                <th class="px-4 py-4 w-12">
                                    <div class="flex items-center justify-center">
                                        <input type="checkbox" id="select-all" class="w-5 h-5 rounded-lg border-2 border-slate-200 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-all">
                                    </div>
                                </th>
                                <th class="px-4 py-4">Mã GD</th>
                                <th class="px-4 py-4">Thời gian</th>
                                <th class="px-4 py-4">Phân loại</th>
                                <th class="px-4 py-4">Nội dung chi tiết</th>
                                <th class="px-4 py-4 text-right">Số tiền</th>
                                <th class="px-4 py-4 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($cashbooks as $cb)
                            <tr class="hover:bg-indigo-50/20 transition-all group {{ request('highlight') == $cb->cashbook_id ? 'bg-indigo-50/50 animate-pulse' : '' }}">
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center">
                                        <input type="checkbox" name="ids[]" value="{{ $cb->cashbook_id }}" class="item-checkbox w-5 h-5 rounded-lg border-2 border-slate-200 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-all">
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    @if($cb->reference_id)
                                        @php
                                            $link = '#';
                                            $title = 'Mã giao dịch';
                                            $colorClass = 'hover:bg-slate-100 hover:text-slate-700';
                                            $icon = '<i class="fa-solid fa-hashtag mr-1.5 text-xs text-slate-400"></i>';
                                            $code = '#' . $cb->reference_id;
                                            $exists = false;
                                            
                                            $refType = $cb->reference_type;
                                            if (!$refType) {
                                                if (str_contains(strtolower($cb->description), 'đơn hàng')) {
                                                    $refType = 'order';
                                                } elseif (str_contains(strtolower($cb->description), 'dịch vụ')) {
                                                    $refType = 'service_invoice';
                                                } elseif (str_contains(strtolower($cb->description), 'nhập hàng')) {
                                                    $refType = 'purchase_order';
                                                } elseif (str_contains(strtolower($cb->description), 'trả góp')) {
                                                    $refType = 'installment';
                                                }
                                            }

                                            if ($refType === 'order') {
                                                $title = 'Đơn hàng';
                                                $colorClass = 'hover:bg-indigo-50/80 hover:text-indigo-600';
                                                $icon = '<i class="fa-solid fa-cart-shopping mr-1.5 text-xs text-indigo-500"></i>';
                                                $exists = isset($existingRefs['order'][$cb->reference_id]);
                                                if ($exists) {
                                                    $link = route('admin.orders.show', $cb->reference_id);
                                                    $code = $existingRefs['order'][$cb->reference_id];
                                                }
                                            } elseif ($refType === 'service_invoice') {
                                                $title = 'Hóa đơn dịch vụ';
                                                $colorClass = 'hover:bg-emerald-50/80 hover:text-emerald-700';
                                                $icon = '<i class="fa-solid fa-wrench mr-1.5 text-xs text-emerald-500"></i>';
                                                $exists = isset($existingRefs['service_invoice'][$cb->reference_id]);
                                                if ($exists) {
                                                    $link = route('admin.service-invoices.show', $cb->reference_id);
                                                    $code = $existingRefs['service_invoice'][$cb->reference_id];
                                                }
                                            } elseif ($refType === 'purchase_order') {
                                                $title = 'Phiếu nhập kho';
                                                $colorClass = 'hover:bg-amber-50/80 hover:text-amber-700';
                                                $icon = '<i class="fa-solid fa-truck mr-1.5 text-xs text-amber-500"></i>';
                                                $exists = isset($existingRefs['purchase_order'][$cb->reference_id]);
                                                if ($exists) {
                                                    $link = route('admin.purchase-orders.show', $cb->reference_id);
                                                    $code = $existingRefs['purchase_order'][$cb->reference_id];
                                                }
                                            } elseif ($refType === 'installment') {
                                                $title = 'Hợp đồng trả góp';
                                                $colorClass = 'hover:bg-violet-50/80 hover:text-violet-700';
                                                $icon = '<i class="fa-solid fa-credit-card mr-1.5 text-xs text-violet-500"></i>';
                                                $exists = isset($existingRefs['installment'][$cb->reference_id]);
                                                if ($exists) {
                                                    $link = route('admin.installments.show', $cb->reference_id);
                                                    $code = $existingRefs['installment'][$cb->reference_id];
                                                }
                                            }
                                        @endphp
                                        @if($link !== '#')
                                            <a href="{{ $link }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-white text-slate-900 text-sm font-black font-mono uppercase tracking-widest border border-slate-200 shadow-[0_2px_8px_rgba(0,0,0,0.04)] ring-1 ring-slate-900/5 transition-all {{ $colorClass }}" title="Xem chi tiết {{ $title }}">
                                                {!! $icon !!}{{ $code }}
                                            </a>
                                        @else
                                            <div class="inline-flex items-center px-4 py-2 rounded-lg bg-slate-50 text-slate-400 text-sm font-black font-mono uppercase tracking-widest border border-slate-200 shadow-sm cursor-not-allowed select-none" title="Không có tài liệu {{ $title }} thực tế (Dữ liệu Seeder mẫu)">
                                                {!! $icon !!}{{ $code }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-[12px] text-slate-300 font-bold uppercase tracking-widest pl-4">---</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $cb->type === 'Income' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                            <i class="fa-solid {{ $cb->type === 'Income' ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-900 text-xs tracking-tight">{{ $cb->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}</div>
                                            <div class="text-[9px] text-slate-400 font-bold uppercase mt-0.5 tracking-wider">{{ $cb->created_at->timezone('Asia/Ho_Chi_Minh')->format('H:i') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    @if($cb->type === 'Income')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-widest border border-emerald-100 shadow-sm">Khoản Thu</span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-50 text-rose-600 text-[9px] font-black uppercase tracking-widest border border-rose-100 shadow-sm">Khoản Chi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-xs font-bold text-slate-700 max-w-[200px] truncate leading-relaxed" title="{{ $cb->description ?? '' }}">{{ $cb->description ?? 'Chưa có mô tả' }}</p>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="font-black text-sm tabular-nums {{ $cb->type === 'Income' ? 'text-emerald-600' : 'text-rose-500' }}">
                                        {{ $cb->type === 'Income' ? '+' : '-' }}{{ number_format($cb->amount) }}đ
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" onclick="openEditModal(this)"
                                           data-id="{{ $cb->cashbook_id }}"
                                           data-type="{{ $cb->type }}"
                                           data-amount="{{ $cb->amount }}"
                                           data-desc="{{ $cb->description }}"
                                           data-ref="{{ ltrim($code, '#') }}"
                                           data-ref-type="{{ $cb->reference_type }}"
                                           data-date="{{ $cb->created_at->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') }}"
                                           class="group w-8 h-8 flex items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-300 active:scale-90 shadow-sm" title="Chỉnh sửa">
                                             <i class="fa-solid fa-pen-to-square text-xs group-hover:scale-110 transition-transform"></i>
                                        </button>
                                        <button type="button" onclick="deleteItem({{ $cb->cashbook_id }})"
                                                class="group w-8 h-8 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all duration-300 active:scale-90 shadow-sm" title="Xóa">
                                            <i class="fa-solid fa-trash-alt text-xs group-hover:scale-110 transition-transform"></i>
                                        </button>
                                     </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </form>

        {{-- Hidden Delete Forms (Must be outside the bulk-delete-form) --}}
        @foreach($cashbooks as $cb)
            <form id="delete-form-{{ $cb->cashbook_id }}" action="{{ route('admin.cashbooks.destroy', $cb->cashbook_id) }}" method="POST" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach

        {{-- Pagination Footer --}}
        @if($cashbooks->hasPages())
            <div class="px-8 py-6 border-t border-slate-50 bg-slate-50/20">
                {{ $cashbooks->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL THÊM GIAO DỊCH --}}
<div id="modal-add" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 custom-modal-backdrop">
    <div class="absolute inset-0 bg-slate-900/40 transition-opacity duration-300"></div>
    <div class="bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] w-full max-w-md overflow-hidden modal-content-anim relative z-10">
        {{-- Close Button --}}
        <button onclick="closeModal('modal-add')" class="absolute top-5 right-5 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-rose-500 hover:text-white text-slate-400 transition-all z-20 active:scale-90">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>

        <div class="px-6 pt-6 pb-2">
            <h3 class="text-xl font-black text-slate-900 tracking-tight">Thêm Giao Dịch</h3>
            <p class="text-slate-400 text-xs font-semibold mt-0.5">Lưu trữ thông tin thu chi mới vào hệ thống</p>
        </div>

        <form id="form-add" action="{{ route('admin.cashbooks.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.12em] mb-2">Loại giao dịch <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="type" value="Income" class="sr-only peer" required>
                        <div class="flex items-center justify-center gap-2 border-2 border-slate-200 rounded-xl p-3 text-slate-500 font-bold transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 hover:border-slate-300 text-xs">
                            <i class="fa-solid fa-arrow-trend-up text-lg"></i>
                            Thu tiền
                        </div>
                    </label>
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="type" value="Expense" class="sr-only peer">
                        <div class="flex items-center justify-center gap-2 border-2 border-slate-200 rounded-xl p-3 text-slate-500 font-bold transition-all peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 hover:border-slate-300 text-xs">
                            <i class="fa-solid fa-arrow-trend-down text-lg"></i>
                            Chi tiền
                        </div>
                    </label>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.12em]">Số tiền <span class="text-rose-500">*</span></label>
                <div class="relative group">
                    <input type="number" name="amount" min="1000" step="1000" placeholder="0" style="padding-left: 1.25rem; padding-right: 2.5rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 text-base font-black text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm" required>
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-sm">đ</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Hỗ trợ nhập mã tài liệu thực tế (VD: PO-00001, HD0001, TGO...) thay vì ID thô --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.12em]">Mã tài liệu liên kết</label>
                    <div class="relative group">
                        <i class="fa-solid fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="reference_id" placeholder="VD: PO-00001, POS..., hoặc ID số" style="padding-left: 2.25rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl pr-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm">
                    </div>
                </div>
                {{-- Hỗ trợ nhập mã tài liệu thực tế (VD: PO-00001, HD0001, TGO...) thay vì ID thô --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.12em]">Loại liên kết</label>
                    <div class="relative group">
                        <select name="reference_type" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm appearance-none" style="padding-left: 1rem; padding-right: 2rem;">
                            <option value="">Không có liên kết</option>
                            <option value="order">Đơn hàng (Order)</option>
                            <option value="service_invoice">Hóa đơn dịch vụ (Service Invoice)</option>
                            <option value="purchase_order">Phiếu nhập kho (Purchase PO)</option>
                            <option value="installment">Trả góp (Installment)</option>
                        </select>
                        <div style="position: absolute; top: 50%; right: 1rem; transform: translateY(-50%); pointer-events: none; color: #94a3b8;">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.12em]">Thời gian ghi nhận</label>
                <div class="relative group">
                    <i class="fa-solid fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="datetime-local" name="created_at" value="{{ now('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') }}" style="padding-left: 2.25rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl pr-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.12em]">Mô tả giao dịch <span class="text-rose-500">*</span></label>
                <textarea name="description" rows="2" maxlength="500" placeholder="Nhập nội dung chi tiết..." class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all resize-none shadow-sm" required></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('modal-add')" class="flex-1 bg-slate-100 text-slate-600 py-3 rounded-xl font-black text-xs hover:bg-slate-200 transition-all active:scale-95">Hủy bỏ</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-black text-xs transition-all shadow-xl shadow-indigo-100 active:scale-95">Xác nhận Lưu</button>
            </div>
        </form>
    </div>
</div>


{{-- MODAL SỬA GIAO DỊCH --}}
<div id="modal-edit" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 custom-modal-backdrop">
    <div class="absolute inset-0 bg-slate-900/40 transition-opacity duration-300"></div>
    <div class="bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] w-full max-w-md overflow-hidden modal-content-anim relative z-10">
        {{-- Close Button --}}
        <button onclick="closeModal('modal-edit')" class="absolute top-5 right-5 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-rose-500 hover:text-white text-slate-400 transition-all z-20 active:scale-90">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>

        <div class="px-6 pt-6 pb-2">
            <h3 class="text-xl font-black text-slate-900 tracking-tight">Cập Nhật</h3>
            <p class="text-slate-400 text-xs font-semibold mt-0.5">Cập nhật thông tin thu chi cửa hàng</p>
            <span id="edit-id-label" class="hidden"></span>
        </div>

        <form id="form-edit" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.12em] mb-2">Phân loại</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="type" id="edit-type-income" value="Income" class="sr-only peer" required>
                        <div class="flex items-center justify-center gap-2 border-2 border-slate-200 rounded-xl p-3 text-slate-500 font-bold transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 hover:border-slate-300 text-xs">
                            <i class="fa-solid fa-arrow-trend-up text-lg"></i>
                            Khoản thu
                        </div>
                    </label>
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="type" id="edit-type-expense" value="Expense" class="sr-only peer">
                        <div class="flex items-center justify-center gap-2 border-2 border-slate-200 rounded-xl p-3 text-slate-500 font-bold transition-all peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 hover:border-slate-300 text-xs">
                            <i class="fa-solid fa-arrow-trend-down text-lg"></i>
                            Khoản chi
                        </div>
                    </label>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.12em]">Số tiền (VNĐ) <span class="text-rose-500">*</span></label>
                <div class="relative group">
                    <input type="number" name="amount" id="edit-amount" min="1000" step="1000" style="padding-left: 1.25rem; padding-right: 2.5rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-2.5 text-base font-black text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm" required>
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-sm">đ</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Khóa sửa thông tin liên kết tài liệu trong Form Sửa để đảm bảo tính toàn vẹn của sổ quỹ --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.12em]">Mã tài liệu liên kết <i class="fa-solid fa-lock ml-1 text-slate-400/60"></i></label>
                    <div class="relative group">
                        <i class="fa-solid fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="reference_id" id="edit-ref" placeholder="Không có liên kết" style="padding-left: 2.25rem;" class="w-full bg-slate-100/80 border-2 border-slate-200/60 rounded-xl pr-4 py-2.5 text-xs font-bold text-slate-400 cursor-not-allowed select-none outline-none focus:outline-none" readonly>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.12em]">Loại liên kết <i class="fa-solid fa-lock ml-1 text-slate-400/60"></i></label>
                    <div class="relative group">
                        <select name="reference_type" id="edit-ref-type" class="w-full bg-slate-100/80 border-2 border-slate-200/60 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-400 cursor-not-allowed select-none appearance-none outline-none focus:outline-none pointer-events-none" style="padding-left: 1rem; padding-right: 2rem;" disabled>
                            <option value="">Không có liên kết</option>
                            <option value="order">Đơn hàng (Order)</option>
                            <option value="service_invoice">Hóa đơn dịch vụ (Service Invoice)</option>
                            <option value="purchase_order">Phiếu nhập kho (Purchase PO)</option>
                            <option value="installment">Trả góp (Installment)</option>
                        </select>
                        <div style="position: absolute; top: 50%; right: 1rem; transform: translateY(-50%); pointer-events: none; color: #cbd5e1;">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.12em]">Thời gian ghi nhận</label>
                <div class="relative group">
                    <i class="fa-solid fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="datetime-local" name="created_at" id="edit-date" style="padding-left: 2.25rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl pr-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.12em]">Mô tả chi tiết <span class="text-rose-500">*</span></label>
                <textarea name="description" id="edit-description" rows="2" maxlength="500" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all resize-none shadow-sm" required></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('modal-edit')" class="flex-1 bg-slate-100 text-slate-600 py-3 rounded-xl font-black text-xs hover:bg-slate-200 transition-all active:scale-95">Hủy bỏ</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-black text-xs transition-all shadow-xl shadow-indigo-100 active:scale-95">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL XÓA GIAO DỊCH (ĐƠN) --}}
<div id="modal-delete" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 custom-modal-backdrop">
    <div class="absolute inset-0 transition-opacity duration-300" style="background-color: rgba(15, 23, 42, 0.4);"></div>
    <div class="bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] w-full max-w-sm overflow-hidden modal-content-anim relative z-10 text-center p-8">
        <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-5 text-rose-500 shadow-inner">
            <i class="fa-solid fa-trash-alt text-3xl"></i>
        </div>
        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Xóa giao dịch này?</h3>
        <p class="text-slate-500 text-[13px] font-bold mt-2">Giao dịch này sẽ bị xóa vĩnh viễn khỏi sổ quỹ và không thể phục hồi.</p>
        
        <div class="flex gap-3 mt-8">
            <button type="button" onclick="closeModal('modal-delete')" class="flex-1 bg-slate-100 text-slate-600 py-3.5 rounded-xl font-black text-sm hover:bg-slate-200 transition-all active:scale-95">Hủy bỏ</button>
            <button type="button" onclick="confirmDelete()" class="flex-1 transition-all shadow-xl shadow-rose-100 active:scale-95 py-3.5 rounded-xl font-black text-sm" style="background-color: #e11d48; color: white;">Xóa ngay</button>
        </div>
    </div>
</div>

{{-- MODAL XÓA GIAO DỊCH (NHIỀU) --}}
<div id="modal-bulk-delete" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 custom-modal-backdrop">
    <div class="absolute inset-0 transition-opacity duration-300" style="background-color: rgba(15, 23, 42, 0.4);"></div>
    <div class="bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] w-full max-w-sm overflow-hidden modal-content-anim relative z-10 text-center p-8">
        <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-5 text-rose-500 shadow-inner">
            <i class="fa-solid fa-exclamation-triangle text-3xl"></i>
        </div>
        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Xóa hàng loạt?</h3>
        <p class="text-slate-500 text-[13px] font-bold mt-2">Toàn bộ dữ liệu được chọn sẽ bị xóa vĩnh viễn và không thể phục hồi.</p>
        
        <div class="flex gap-3 mt-8">
            <button type="button" onclick="closeModal('modal-bulk-delete')" class="flex-1 bg-slate-100 text-slate-600 py-3.5 rounded-xl font-black text-sm hover:bg-slate-200 transition-all active:scale-95">Hủy bỏ</button>
            <button type="button" onclick="confirmBulkDelete()" class="flex-1 transition-all shadow-xl shadow-rose-100 active:scale-95 py-3.5 rounded-xl font-black text-sm" style="background-color: #e11d48; color: white;">Xóa ngay</button>
        </div>
    </div>
</div>

{{-- BULK ACTION BAR (FLOATING PILL AT BOTTOM) --}}
<div id="bulk-action-bar" class="px-6 py-4 rounded-full shadow-[0_20px_50px_rgba(0,0,0,0.2)] animate-slide-up" style="display: none; align-items: center; gap: 1.5rem; background-color: white; border: 1px solid #e2e8f0; width: max-content; position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%); z-index: 9999;">
    <div class="flex items-center gap-3">
        <span class="w-auto px-2.5 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-black text-xs" id="selected-count">0</span>
        <span class="font-bold text-slate-800 text-sm">giao dịch đã chọn</span>
    </div>
    
    @if ($cashbooks->total() > $cashbooks->count())
    <div style="width: 1px; height: 24px; background-color: #e2e8f0;" class="bulk-divider"></div>
    <div id="bulk-select-all-matching-container" style="display: none;" class="items-center gap-2 text-xs font-bold">
        <span class="text-slate-500" id="bulk-matching-text">Đã chọn {{ $cashbooks->count() }} giao dịch trên trang này.</span>
        <button type="button" onclick="selectAllMatching()" id="btn-select-all-matching" class="text-indigo-600 hover:text-indigo-800 underline transition-all">Chọn tất cả {{ $cashbooks->total() }} giao dịch</button>
        <button type="button" onclick="clearSelectAllMatching()" id="btn-clear-select-all-matching" style="display: none;" class="text-rose-600 hover:text-rose-800 underline transition-all">Bỏ chọn toàn bộ</button>
    </div>
    @endif

    <div style="width: 1px; height: 24px; background-color: #e2e8f0;"></div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="cancelSelection()" class="px-4 py-2 rounded-xl text-slate-500 font-bold text-sm hover:bg-slate-100 transition-all">Hủy</button>
        <button type="button" onclick="bulkDelete()" class="px-5 py-2 rounded-xl text-white font-black text-sm transition-all shadow-md" style="background-color: #e11d48;" onmouseover="this.style.backgroundColor='#be123c'" onmouseout="this.style.backgroundColor='#e11d48'">Xóa tất cả</button>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ══════════════════════════════════════════════════════════
    // QUẢN LÝ MODAL (HIỂN THỊ HỘP THOẠI POPUP)
    // ══════════════════════════════════════════════════════════
    
    // Hàm mở modal popup bằng ID phần tử html
    function openModal(id) {
        // Tìm element modal trong DOM qua ID
        const modal = document.getElementById(id);
        // Thêm class hiển thị modal (thường là display: flex)
        modal.classList.add('modal-active');
        // Trì hoãn 10ms trước khi thêm opacity để tạo hiệu ứng chuyển động mượt mà
        setTimeout(() => {
            modal.classList.add('opacity-100');
        }, 10);
    }

    // Hàm đóng modal popup bằng ID phần tử html
    function closeModal(id) {
        // Tìm element modal trong DOM qua ID
        const modal = document.getElementById(id);
        // Gỡ bỏ opacity để kích hoạt hiệu ứng fade-out ẩn dần
        modal.classList.remove('opacity-100');
        // Đợi 300ms cho hiệu ứng CSS kết thúc rồi mới tắt hoàn toàn display
        setTimeout(() => {
            modal.classList.remove('modal-active');
        }, 300);
    }

    // Lắng nghe sự kiện click trên cửa sổ trình duyệt
    window.onclick = function(event) {
        // Nếu người dùng click vào vùng làm mờ xung quanh modal (backdrop)
        if (event.target.classList.contains('custom-modal-backdrop')) {
            // Tự động đóng modal tương ứng
            closeModal(event.target.id);
        }
    }

    // ══════════════════════════════════════════════════════════
    // VẼ BIỂU ĐỒ THU CHI BẰNG CHART.JS
    // ══════════════════════════════════════════════════════════
    
    // Lấy ngữ cảnh vẽ 2D trên thẻ canvas biểu đồ
    const ctx = document.getElementById('cashflowChart').getContext('2d');
    // Khởi tạo một đối tượng biểu đồ đường (Line Chart)
    new Chart(ctx, {
        type: 'line', // Kiểu vẽ dạng đường nối các điểm dữ liệu
        data: {
            // Danh sách ngày hiển thị trên trục hoành (X) nhận từ biến PHP $chartData['labels']
            labels: @json($chartData['labels']),
            datasets: [
                {
                    label: 'Thu', // Tên nhãn của đường biểu diễn khoản thu
                    data: @json($chartData['income']), // Mảng số liệu doanh thu từ PHP
                    borderColor: '#10b981', // Màu viền xanh lá của đường thu
                    backgroundColor: 'rgba(16, 185, 129, 0.1)', // Màu tô mờ nhẹ vùng dưới đường thu
                    fill: true, // Cho phép tô màu phủ vùng dưới đường
                    tension: 0.4, // Độ cong mượt của các đường nối điểm (0.4 là tối ưu)
                    borderWidth: 4, // Độ dày của đường vẽ
                    pointRadius: 4, // Bán kính nút tròn hiển thị điểm mốc dữ liệu
                    pointBackgroundColor: '#fff', // Màu nền nút tròn trắng
                    pointBorderColor: '#10b981', // Viền nút tròn màu xanh
                    pointBorderWidth: 2, // Độ dày viền nút tròn
                },
                {
                    label: 'Chi', // Tên nhãn của đường biểu diễn khoản chi
                    data: @json($chartData['expense']), // Mảng số liệu chi phí từ PHP
                    borderColor: '#f43f5e', // Màu viền đỏ hồng của đường chi
                    backgroundColor: 'rgba(244, 63, 94, 0.1)', // Màu tô mờ hồng nhẹ vùng dưới đường chi
                    fill: true, // Cho phép tô phủ vùng
                    tension: 0.4, // Độ cong của đường chi
                    borderWidth: 4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#f43f5e',
                    pointBorderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true, // Tự động co giãn biểu đồ theo độ rộng của màn hình thiết bị
            maintainAspectRatio: false, // Không giữ nguyên tỷ lệ cũ để lấp đầy thẻ div cha
            plugins: {
                legend: { display: false }, // Ẩn thanh chú giải mặc định ở trên đầu biểu đồ
                tooltip: { // Cấu hình hộp thoại hiển thị số liệu khi di chuột vào điểm mốc
                    backgroundColor: '#1e293b', // Màu nền xám tối sang trọng
                    padding: 12, // Khoảng cách đệm bên trong tooltip
                    usePointStyle: true, // Sử dụng kiểu nút tròn đại diện màu sắc bên trong tooltip
                    callbacks: {
                        // Hàm định dạng hiển thị số tiền có dấu phân tách hàng nghìn và đuôi đ
                        label: (ctx) => `${ctx.dataset.label}: ${new Intl.NumberFormat('vi-VN').format(ctx.parsed.y)}đ`
                    }
                }
            },
            scales: {
                // Trục đứng Y hiển thị số tiền
                y: { 
                    beginAtZero: true, // Luôn bắt đầu đồ thị từ mốc số 0
                    grid: { color: 'rgba(0,0,0,0.03)' }, // Đường lưới mờ nhạt ngang qua màn hình
                    ticks: { font: { size: 10 }, color: '#94a3b8' } // Cỡ chữ và màu sắc nhãn trục Y
                },
                // Trục ngang X hiển thị ngày tháng
                x: { 
                    grid: { display: false }, // Ẩn đường lưới trục X để tránh gây rối mắt
                    ticks: { font: { size: 10 }, color: '#94a3b8' } // Cỡ chữ nhãn trục X
                }
            }
        }
    });

    // ══════════════════════════════════════════════════════════
    // CHỨC NĂNG XỬ LÝ HÀNG LOẠT (BULK SELECTION)
    // ══════════════════════════════════════════════════════════
    
    // Nút checkbox chính dùng để tích chọn tất cả các dòng
    const selectAll = document.getElementById('select-all');
    // Danh sách tất cả các nút checkbox đơn của từng giao dịch
    const checkboxes = document.querySelectorAll('.item-checkbox');
    // Thanh công cụ nổi phía dưới hiển thị khi có dòng được chọn
    const bulkBar = document.getElementById('bulk-action-bar');
    // Thẻ span hiển thị số lượng giao dịch đã được tích chọn
    const selectedCount = document.getElementById('selected-count');

    // Hàm cập nhật trạng thái hiển thị của thanh bulk action bar
    function updateBulkBar() {
        // Đếm tổng số lượng checkbox đang được chọn
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        const matchingContainer = document.getElementById('bulk-select-all-matching-container');
        // Nếu số lượng chọn lớn hơn 0, hiển thị thanh công cụ
        if (checkedCount > 0) {
            bulkBar.style.display = 'flex'; // Đặt kiểu hiển thị là flex để căn ngang
            
            // Nếu đang chọn toàn bộ trên tất cả các trang
            if (document.getElementById('select-all-matching').value === '1') {
                selectedCount.innerText = 'Tất cả {{ $cashbooks->total() }}';
                if (matchingContainer) {
                    matchingContainer.style.display = 'flex';
                    document.getElementById('bulk-matching-text').innerText = 'Đang chọn tất cả {{ $cashbooks->total() }} giao dịch.';
                    document.getElementById('btn-select-all-matching').style.display = 'none';
                    document.getElementById('btn-clear-select-all-matching').style.display = 'inline-block';
                }
            } else {
                selectedCount.innerText = checkedCount; // Ghi đè số lượng lên nhãn
                if (matchingContainer) {
                    // Nếu chọn tất cả dòng của trang này và tổng số bản ghi lớn hơn số lượng dòng hiện tại
                    if (checkedCount === checkboxes.length && {{ $cashbooks->total() }} > checkedCount) {
                        matchingContainer.style.display = 'flex';
                        document.getElementById('bulk-matching-text').innerText = `Đã chọn ${checkedCount} giao dịch trên trang này.`;
                        document.getElementById('btn-select-all-matching').style.display = 'inline-block';
                        document.getElementById('btn-clear-select-all-matching').style.display = 'none';
                    } else {
                        matchingContainer.style.display = 'none';
                    }
                }
            }
        } else {
            bulkBar.style.display = 'none'; // Ẩn thanh công cụ đi khi không có giao dịch nào được chọn
            if (matchingContainer) {
                matchingContainer.style.display = 'none';
            }
            document.getElementById('select-all-matching').value = '0';
        }
    }

    // Chọn tất cả các bản ghi khớp bộ lọc trên mọi trang
    function selectAllMatching() {
        document.getElementById('select-all-matching').value = '1';
        updateBulkBar();
    }

    // Quay lại chỉ chọn các bản ghi trên trang hiện tại
    function clearSelectAllMatching() {
        document.getElementById('select-all-matching').value = '0';
        updateBulkBar();
    }

    // Lắng nghe thay đổi của nút checkbox tổng (chọn tất cả)
    selectAll.addEventListener('change', () => {
        // Đặt thuộc tính checked của tất cả checkbox con bằng trạng thái của checkbox tổng
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        if (!selectAll.checked) {
            document.getElementById('select-all-matching').value = '0';
        }
        // Cập nhật lại thanh công cụ bulk bar
        updateBulkBar();
    });

    // Lắng nghe thay đổi của từng checkbox con
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            // Kiểm tra xem tất cả checkbox con đã được tích chọn hết chưa để tự động tích nút tổng
            selectAll.checked = [...checkboxes].every(c => c.checked);
            // Nếu uncheck bất kỳ con nào, hủy bỏ chọn tất cả trang
            if (!cb.checked) {
                document.getElementById('select-all-matching').value = '0';
            }
            // Cập nhật lại thanh công cụ bulk bar
            updateBulkBar();
        });
    });

    // Hàm hủy bỏ toàn bộ các tích chọn hiện có
    function cancelSelection() {
        // Duyệt và bỏ tích tất cả checkbox con
        checkboxes.forEach(cb => cb.checked = false);
        // Bỏ tích checkbox tổng
        selectAll.checked = false;
        document.getElementById('select-all-matching').value = '0';
        // Cập nhật lại ẩn thanh công cụ bulk bar
        updateBulkBar();
    }

    // Hàm kích hoạt mở modal xác nhận xóa hàng loạt
    function bulkDelete() {
        openModal('modal-bulk-delete');
    }

    // Hàm xác nhận gửi biểu mẫu xóa hàng loạt lên server
    function confirmBulkDelete() {
        document.getElementById('bulk-delete-form').submit();
    }

    // ══════════════════════════════════════════════════════════
    // CHỨC NĂNG XÓA ĐƠN LẺ GIAO DỊCH
    // ══════════════════════════════════════════════════════════
    
    // Biến toàn cục lưu giữ ID của giao dịch đơn lẻ chuẩn bị xóa
    let deleteIdToSubmit = null;
    
    // Hàm chuẩn bị xóa giao dịch
    function deleteItem(id) {
        deleteIdToSubmit = id; // Lưu ID vào biến toàn cục
        openModal('modal-delete'); // Mở hộp thoại xác nhận xóa đơn lẻ
    }

    // Hàm gửi yêu cầu xóa lên server sau khi nhấn xác nhận
    function confirmDelete() {
        if(deleteIdToSubmit) {
            // Lấy biểu mẫu form ẩn tương ứng với ID giao dịch
            const form = document.getElementById(`delete-form-${deleteIdToSubmit}`);
            if (form) {
                form.submit(); // Thực hiện submit form xóa
            } else {
                alert('Không tìm thấy dữ liệu để xóa. Vui lòng tải lại trang.');
            }
        }
    }

    // ══════════════════════════════════════════════════════════
    // CHỨC NĂNG SỬA GIAO DỊCH (ĐỒNG BỘ FORM CHỈNH SỬA)
    // ══════════════════════════════════════════════════════════
    
    // Hàm mở form sửa và nạp dữ liệu hiện tại của dòng vào các trường nhập liệu
    function openEditModal(btn) {
        // Lấy ID giao dịch được gắn trong nút nhấn sửa
        const id = btn.getAttribute('data-id');
        // Thiết lập địa chỉ URL hành động gửi form sửa của thẻ form khớp với ID giao dịch
        document.getElementById('form-edit').action = `{{ route('admin.cashbooks.index') }}/${id}`;
        // Gán ID cho nhãn ẩn trong DOM
        document.getElementById('edit-id-label').innerText = id;
        // Nạp số tiền giao dịch hiện tại vào ô nhập tiền
        document.getElementById('edit-amount').value = btn.getAttribute('data-amount');
        // Nạp nội dung mô tả hiện tại vào ô mô tả
        document.getElementById('edit-description').value = btn.getAttribute('data-desc');
        // Điền mã tài liệu dạng chữ (đã làm sạch ký tự #) để hiển thị trong Form Sửa chỉ đọc
        document.getElementById('edit-ref').value = btn.getAttribute('data-ref') || '';
        // Nạp loại tài liệu vào dropdown loại liên kết (đã bị vô hiệu hóa)
        document.getElementById('edit-ref-type').value = btn.getAttribute('data-ref-type') || '';
        // Nạp ngày ghi nhận vào ô datetime-local
        document.getElementById('edit-date').value = btn.getAttribute('data-date');
        
        // Tự động tích chọn phân loại khoản thu hoặc chi khớp với giá trị dòng
        if(btn.getAttribute('data-type') === 'Income') {
            document.getElementById('edit-type-income').checked = true;
        } else {
            document.getElementById('edit-type-expense').checked = true;
        }

        // Mở hộp thoại sửa sau khi đã nạp đầy đủ dữ liệu
        openModal('modal-edit');
    }

    // Nếu server trả về lỗi validate, tự động bật lại modal thêm mới để hiển thị lỗi cho người dùng
    @if($errors->any()) openModal('modal-add'); @endif

    // Hàm xác thực đầu vào bộ lọc tìm kiếm nâng cao ở đầu trang
    function validateFilter(form) {
        const search = form.search.value.trim(); // Nhận từ khóa tìm kiếm
        const type = form.type.value; // Nhận loại giao dịch cần tìm
        // Nếu người dùng không nhập gì cả mà nhấn tìm kiếm
        if (!search && !type) {
            showErrorToast('Vui lòng nhập nội dung hoặc chọn loại giao dịch cần tìm.'); // Hiển thị cảnh báo lỗi
            return false; // Ngăn chặn gửi form tìm kiếm trống
        }
        return true; // Cho phép gửi form
    }

    // Hàm tạo và hiển thị thông báo nhắc nhở dạng Toast trượt từ bên phải màn hình
    function showErrorToast(message) {
        // Lấy thẻ div thùng chứa các thông báo toast ở góc màn hình
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        // Khởi tạo thẻ div mới đại diện cho 1 toast thông báo
        const toast = document.createElement('div');
        toast.className = 'bg-white border-l-4 border-rose-500 shadow-[0_10px_40px_rgba(0,0,0,0.1)] rounded-xl p-4 flex items-center gap-4 animate-slide-left toast-item transition-all duration-300 w-80';
        // Cấu trúc HTML nội dung thông báo
        toast.innerHTML = `
            <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-800 tracking-tight">Nhắc nhở</h4>
                <p class="text-[11px] font-bold text-slate-500 mt-0.5 leading-tight">${message}</p>
            </div>
        `;
        
        // Thêm toast mới vào trong thùng chứa thông báo
        container.appendChild(toast);
        
        // Tự động đóng và biến mất thông báo sau 3 giây hiển thị
        setTimeout(() => {
            toast.style.opacity = '0'; // Hiệu ứng làm mờ
            setTimeout(() => toast.remove(), 400); // Gỡ bỏ thẻ html khỏi DOM
        }, 3000);
    }

    // Tự động ẩn Toast thông báo phản hồi từ server (ví dụ: Thêm thành công, cập nhật thành công) sau 3 giây
    setTimeout(() => {
        document.querySelectorAll('.toast-item').forEach(toast => {
            toast.style.opacity = '0'; // Thực hiện mờ đi
            setTimeout(() => toast.remove(), 400); // Gỡ khỏi giao diện sau khi mờ hoàn toàn
        });
    }, 3000);

</script>
@endpush
