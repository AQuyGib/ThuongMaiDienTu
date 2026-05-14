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
    .modal-backdrop {
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
                    <table class="w-full text-left min-w-[900px]">
                        <thead>
                            <tr class="text-slate-400 text-[10px] uppercase font-black tracking-[0.2em] bg-white border-b border-slate-100">
                                <th class="px-8 py-6 w-14">
                                    <div class="flex items-center justify-center">
                                        <input type="checkbox" id="select-all" class="w-5 h-5 rounded-lg border-2 border-slate-200 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-all">
                                    </div>
                                </th>
                                <th class="px-6 py-6">Mã GD</th>
                                <th class="px-4 py-6">Thời gian</th>
                                <th class="px-6 py-6">Phân loại</th>
                                <th class="px-6 py-6">Nội dung chi tiết</th>
                                <th class="px-8 py-6 text-right">Số tiền</th>
                                <th class="px-8 py-6 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($cashbooks as $cb)
                            <tr class="hover:bg-indigo-50/20 transition-all group {{ request('highlight') == $cb->cashbook_id ? 'bg-indigo-50/50 animate-pulse' : '' }}">
                                <td class="px-8 py-6">
                                    <div class="flex items-center justify-center">
                                        <input type="checkbox" name="ids[]" value="{{ $cb->cashbook_id }}" class="item-checkbox w-5 h-5 rounded-lg border-2 border-slate-200 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-all">
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    @if($cb->reference_id)
                                        <div class="inline-flex items-center px-4 py-2 rounded-lg bg-white text-slate-900 text-sm font-black font-mono uppercase tracking-widest border border-slate-200 shadow-[0_2px_8px_rgba(0,0,0,0.04)] ring-1 ring-slate-900/5" title="Mã giao dịch">
                                            #{{ $cb->reference_id }}
                                        </div>
                                    @else
                                        <span class="text-[12px] text-slate-300 font-bold uppercase tracking-widest pl-4">---</span>
                                    @endif
                                </td>
                                <td class="px-4 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $cb->type === 'Income' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                            <i class="fa-solid {{ $cb->type === 'Income' ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-900 text-sm tracking-tight">{{ $cb->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}</div>
                                            <div class="text-[10px] text-slate-400 font-bold uppercase mt-0.5 tracking-wider">{{ $cb->created_at->timezone('Asia/Ho_Chi_Minh')->format('H:i') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    @if($cb->type === 'Income')
                                        <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest border border-emerald-100 shadow-sm shadow-emerald-100">Khoản Thu</span>
                                    @else
                                        <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest border border-rose-100 shadow-sm shadow-rose-100">Khoản Chi</span>
                                    @endif
                                </td>
                                <td class="px-6 py-6">
                                    <p class="text-sm font-bold text-slate-700 max-w-[300px] truncate leading-relaxed">{{ $cb->description ?? 'Chưa có mô tả' }}</p>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="font-black text-lg tabular-nums {{ $cb->type === 'Income' ? 'text-emerald-600' : 'text-rose-500' }}">
                                        {{ $cb->type === 'Income' ? '+' : '-' }}{{ number_format($cb->amount) }}đ
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center justify-center gap-3">
                                        <button type="button" onclick="openEditModal(this)"
                                           data-id="{{ $cb->cashbook_id }}"
                                           data-type="{{ $cb->type }}"
                                           data-amount="{{ $cb->amount }}"
                                           data-desc="{{ $cb->description }}"
                                           data-ref="{{ $cb->reference_id }}"
                                           data-date="{{ $cb->created_at->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') }}"
                                           class="group w-10 h-10 flex items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-300 active:scale-90 shadow-sm" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen-to-square text-sm group-hover:scale-110 transition-transform"></i>
                                        </button>
                                        <button type="button" onclick="deleteItem({{ $cb->cashbook_id }})"
                                                class="group w-10 h-10 flex items-center justify-center rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all duration-300 active:scale-90 shadow-sm" title="Xóa">
                                            <i class="fa-solid fa-trash-alt text-sm group-hover:scale-110 transition-transform"></i>
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
<div id="modal-add" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 modal-backdrop">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"></div>
    <div class="bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] w-full max-w-lg overflow-hidden modal-content-anim relative z-10">
        {{-- Close Button --}}
        <button onclick="closeModal('modal-add')" class="absolute top-6 right-6 w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 hover:bg-rose-500 hover:text-white text-slate-400 transition-all z-20 active:scale-90">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="px-10 pt-10 pb-2">
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Thêm Giao Dịch</h3>
            <p class="text-slate-400 text-sm font-medium mt-1">Lưu trữ thông tin thu chi mới vào hệ thống</p>
        </div>


        
        <form id="form-add" action="{{ route('admin.cashbooks.store') }}" method="POST" class="p-8 space-y-5">
            @csrf
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-2.5">Loại giao dịch <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="type" value="Income" class="sr-only peer" required>
                        <div class="flex items-center justify-center gap-3 border-2 border-slate-200 rounded-xl p-4 text-slate-500 font-bold transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 hover:border-slate-300 text-sm">
                            <i class="fa-solid fa-arrow-trend-up text-xl"></i>
                            Thu tiền
                        </div>
                    </label>
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="type" value="Expense" class="sr-only peer">
                        <div class="flex items-center justify-center gap-3 border-2 border-slate-200 rounded-xl p-4 text-slate-500 font-bold transition-all peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 hover:border-slate-300 text-sm">
                            <i class="fa-solid fa-arrow-trend-down text-xl"></i>
                            Chi tiền
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Số tiền <span class="text-rose-500">*</span></label>
                    <div class="relative group">
                        <input type="number" name="amount" min="1000" step="1000" placeholder="0" style="padding-left: 1.5rem; padding-right: 3rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3.5 text-lg font-black text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm" required>
                        <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-base">đ</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Mã giao dịch</label>
                    <div class="relative group">
                        <i class="fa-solid fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="number" name="reference_id" placeholder="VD: 9999" style="padding-left: 2.5rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl pr-5 py-3.5 text-sm font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm">
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Thời gian ghi nhận</label>
                <div class="relative group">
                    <i class="fa-solid fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="datetime-local" name="created_at" value="{{ now('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') }}" style="padding-left: 2.5rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl pr-5 py-3.5 text-sm font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Mô tả giao dịch <span class="text-rose-500">*</span></label>
                <textarea name="description" rows="2" maxlength="500" placeholder="Nhập nội dung chi tiết..." class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-6 py-4 text-sm font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all resize-none shadow-sm" required></textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="button" onclick="closeModal('modal-add')" class="flex-1 bg-slate-100 text-slate-600 py-4 rounded-xl font-black text-sm hover:bg-slate-200 transition-all active:scale-95">Hủy bỏ</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-xl font-black text-sm transition-all shadow-xl shadow-indigo-100 active:scale-95">Xác nhận Lưu</button>
            </div>
        </form>
    </div>
</div>


{{-- MODAL SỬA GIAO DỊCH --}}
<div id="modal-edit" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 modal-backdrop">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"></div>
    <div class="bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] w-full max-w-lg overflow-hidden modal-content-anim relative z-10">
        {{-- Close Button --}}
        <button onclick="closeModal('modal-edit')" class="absolute top-6 right-6 w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 hover:bg-rose-500 hover:text-white text-slate-400 transition-all z-20 active:scale-90">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="px-10 pt-10 pb-2">
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Cập Nhật</h3>
            <p class="text-slate-400 text-sm font-medium mt-1">Chỉnh sửa giao dịch #<span id="edit-id-label"></span></p>
        </div>


        
        <form id="form-edit" method="POST" class="p-8 space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-2.5">Phân loại</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="type" id="edit-type-income" value="Income" class="sr-only peer" required>
                        <div class="flex items-center justify-center gap-3 border-2 border-slate-200 rounded-xl p-4 text-slate-500 font-bold transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 hover:border-slate-300 text-sm">
                            <i class="fa-solid fa-arrow-trend-up text-xl"></i>
                            Khoản thu
                        </div>
                    </label>
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="type" id="edit-type-expense" value="Expense" class="sr-only peer">
                        <div class="flex items-center justify-center gap-3 border-2 border-slate-200 rounded-xl p-4 text-slate-500 font-bold transition-all peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 hover:border-slate-300 text-sm">
                            <i class="fa-solid fa-arrow-trend-down text-xl"></i>
                            Khoản chi
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Số tiền (VNĐ) <span class="text-rose-500">*</span></label>
                    <div class="relative group">
                        <input type="number" name="amount" id="edit-amount" min="1000" step="1000" style="padding-left: 1.5rem; padding-right: 3rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3.5 text-lg font-black text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm" required>
                        <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-base">đ</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Mã giao dịch</label>
                    <div class="relative group">
                        <i class="fa-solid fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="number" name="reference_id" id="edit-ref" style="padding-left: 2.5rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl pr-5 py-3.5 text-sm font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm">
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Thời gian ghi nhận</label>
                <div class="relative group">
                    <i class="fa-solid fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="datetime-local" name="created_at" id="edit-date" style="padding-left: 2.5rem;" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl pr-5 py-3.5 text-sm font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-sm">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Mô tả chi tiết <span class="text-rose-500">*</span></label>
                <textarea name="description" id="edit-description" rows="2" maxlength="500" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-6 py-4 text-sm font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all resize-none shadow-sm" required></textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="button" onclick="closeModal('modal-edit')" class="flex-1 bg-slate-100 text-slate-600 py-4 rounded-xl font-black text-sm hover:bg-slate-200 transition-all active:scale-95">Hủy bỏ</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-xl font-black text-sm transition-all shadow-xl shadow-indigo-100 active:scale-95">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL XÓA GIAO DỊCH (ĐƠN) --}}
<div id="modal-delete" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 modal-backdrop">
    <div class="absolute inset-0 backdrop-blur-sm transition-opacity duration-300" style="background-color: rgba(15, 23, 42, 0.6);"></div>
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
<div id="modal-bulk-delete" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 modal-backdrop">
    <div class="absolute inset-0 backdrop-blur-sm transition-opacity duration-300" style="background-color: rgba(15, 23, 42, 0.6);"></div>
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
        <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-black text-xs" id="selected-count">0</span>
        <span class="font-bold text-slate-800 text-sm">giao dịch đã chọn</span>
    </div>
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
    // QUẢN LÝ MODAL (CUSTOM)
    // ══════════════════════════════════════════════════════════
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('modal-active');
        // Cho một chút delay để trigger animation CSS
        setTimeout(() => {
            modal.classList.add('opacity-100');
        }, 10);
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('opacity-100');
        // Đợi animation kết thúc rồi mới ẩn hoàn toàn
        setTimeout(() => {
            modal.classList.remove('modal-active');
        }, 300);
    }

    // Đóng modal khi click ra ngoài
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-backdrop')) {
            closeModal(event.target.id);
        }
    }

    // ══════════════════════════════════════════════════════════
    // BIỂU ĐỒ (CHART)
    // ══════════════════════════════════════════════════════════
    const ctx = document.getElementById('cashflowChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),
            datasets: [
                {
                    label: 'Thu',
                    data: @json($chartData['income']),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2,
                },
                {
                    label: 'Chi',
                    data: @json($chartData['expense']),
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244, 63, 94, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#f43f5e',
                    pointBorderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    usePointStyle: true,
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${new Intl.NumberFormat('vi-VN').format(ctx.parsed.y)}đ`
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } }
            }
        }
    });

    // ══════════════════════════════════════════════════════════
    // CHỨC NĂNG CHỌN NHIỀU (BULK SELECTION)
    // ══════════════════════════════════════════════════════════
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const bulkBar = document.getElementById('bulk-action-bar');
    const selectedCount = document.getElementById('selected-count');

    function updateBulkBar() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        if (checkedCount > 0) {
            bulkBar.style.display = 'flex';
            selectedCount.innerText = checkedCount;
        } else {
            bulkBar.style.display = 'none';
        }
    }

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkBar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            selectAll.checked = [...checkboxes].every(c => c.checked);
            updateBulkBar();
        });
    });

    function cancelSelection() {
        checkboxes.forEach(cb => cb.checked = false);
        selectAll.checked = false;
        updateBulkBar();
    }

    function bulkDelete() {
        openModal('modal-bulk-delete');
    }

    function confirmBulkDelete() {
        document.getElementById('bulk-delete-form').submit();
    }

    // ══════════════════════════════════════════════════════════
    // CHỨC NĂNG XÓA ĐƠN
    // ══════════════════════════════════════════════════════════
    let deleteIdToSubmit = null;
    function deleteItem(id) {
        deleteIdToSubmit = id;
        openModal('modal-delete');
    }

    function confirmDelete() {
        if(deleteIdToSubmit) {
            const form = document.getElementById(`delete-form-${deleteIdToSubmit}`);
            if (form) {
                form.submit();
            } else {
                alert('Không tìm thấy dữ liệu để xóa. Vui lòng tải lại trang.');
            }
        }
    }

    // ══════════════════════════════════════════════════════════
    // MODAL EDIT
    // ══════════════════════════════════════════════════════════
    function openEditModal(btn) {
        const id = btn.getAttribute('data-id');
        document.getElementById('form-edit').action = `{{ route('admin.cashbooks.index') }}/${id}`;
        document.getElementById('edit-id-label').innerText = id;
        document.getElementById('edit-amount').value = btn.getAttribute('data-amount');
        document.getElementById('edit-description').value = btn.getAttribute('data-desc');
        document.getElementById('edit-ref').value = btn.getAttribute('data-ref') || '';
        document.getElementById('edit-date').value = btn.getAttribute('data-date');
        
        if(btn.getAttribute('data-type') === 'Income') {
            document.getElementById('edit-type-income').checked = true;
        } else {
            document.getElementById('edit-type-expense').checked = true;
        }

        openModal('modal-edit');
    }

    @if($errors->any()) openModal('modal-add'); @endif

    function validateFilter(form) {
        const search = form.search.value.trim();
        const type = form.type.value;
        if (!search && !type) {
            showErrorToast('Vui lòng nhập nội dung hoặc chọn loại giao dịch cần tìm.');
            return false;
        }
        return true;
    }

    function showErrorToast(message) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = 'bg-white border-l-4 border-rose-500 shadow-[0_10px_40px_rgba(0,0,0,0.1)] rounded-xl p-4 flex items-center gap-4 animate-slide-left toast-item transition-all duration-300 w-80';
        toast.innerHTML = `
            <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-800 tracking-tight">Nhắc nhở</h4>
                <p class="text-[11px] font-bold text-slate-500 mt-0.5 leading-tight">${message}</p>
            </div>
        `;
        
        container.appendChild(toast);
        
        // Auto remove
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }

    // Tự động ẩn Toast Notifications (Server-side) sau 3 giây
    setTimeout(() => {
        document.querySelectorAll('.toast-item').forEach(toast => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400); // Đợi animation CSS kết thúc
        });
    }, 3000);

</script>
@endpush
