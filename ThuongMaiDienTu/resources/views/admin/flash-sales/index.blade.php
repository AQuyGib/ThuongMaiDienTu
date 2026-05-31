@extends('admin.layouts.master')

@section('title', 'Quản Lý Flash Sale')

@push('styles')
    {{-- Giữ Select2 để bổ trợ cho dropdown sản phẩm --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        /* Tinh chỉnh Select2 để khớp với phong cách Tailwind */
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            min-height: 42px;
            display: flex;
            align-items: center;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 1rem;
            color: #1e293b;
        }
    </style>
@endpush

@section('content')
<div class="max-w-[1600px] mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
                <i class="fa-solid fa-bolt-lightning text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Quản Lý Flash Sale</h1>
                <p class="text-slate-500 text-sm">Thiết kế chương trình khuyến mãi chớp nhoáng và tối ưu doanh số.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.flash-sales.index') }}" 
               class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold text-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-rotate"></i> Làm mới
            </a>
        </div>
    </div>

    {{-- Thông báo --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 rounded-r-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300">
            <i class="fa-solid fa-circle-exclamation text-lg"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        {{-- Cột Trái: Form Tạo/Sửa --}}
        <div class="xl:col-span-4 space-y-8">
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden sticky top-8">
                <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white flex items-center justify-between">
                    <h3 class="font-bold flex items-center gap-2 italic">
                        <i class="fa-solid fa-pen-to-square"></i>
                        {{ $editingFlashSale ? 'Cập nhật Flash Sale' : 'Tạo Chiến Dịch Mới' }}
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{ $editingFlashSale ? route('admin.flash-sales.update', $editingFlashSale->flash_sale_id) : route('admin.flash-sales.store') }}" method="POST" class="space-y-5">
                        @csrf
                        @if($editingFlashSale)
                            @method('PUT')
                        @endif
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tên chương trình <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none text-slate-700 font-medium" 
                                   placeholder="Ví dụ: Flash Sale Hè Rực Rỡ" required maxlength="150" value="{{ $editingFlashSale->name ?? '' }}">
                        </div>

                        <div class="space-y-6">
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest">Thời gian bắt đầu</label>
                                    <button type="button" onclick="setCurrentTime('start_at')" class="text-[9px] px-3 py-1 bg-white text-indigo-600 rounded-lg border border-indigo-100 hover:bg-indigo-600 hover:text-white transition-all font-black uppercase shadow-sm">Lấy giờ hiện tại</button>
                                </div>
                                <input type="datetime-local" name="start_at" id="start_at" step="1" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none text-base font-bold text-slate-700 bg-white" 
                                       required value="{{ isset($editingFlashSale) ? \Carbon\Carbon::parse($editingFlashSale->start_at)->format('Y-m-d\\TH:i:s') : '' }}">
                            </div>
                            
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest">Thời gian kết thúc</label>
                                    <button type="button" onclick="setCurrentTime('end_at')" class="text-[9px] px-3 py-1 bg-white text-indigo-600 rounded-lg border border-indigo-100 hover:bg-indigo-600 hover:text-white transition-all font-black uppercase shadow-sm">Lấy giờ hiện tại</button>
                                </div>
                                <input type="datetime-local" name="end_at" id="end_at" step="1" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none text-base font-bold text-slate-700 bg-white" 
                                       required value="{{ isset($editingFlashSale) ? \Carbon\Carbon::parse($editingFlashSale->end_at)->format('Y-m-d\\TH:i:s') : '' }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả chi tiết</label>
                            <textarea name="description" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none text-slate-700" 
                                      rows="3" placeholder="Nhập mô tả ngắn cho chương trình...">{{ $editingFlashSale->description ?? '' }}</textarea>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ !isset($editingFlashSale) || $editingFlashSale->is_active ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                <span class="ml-3 text-sm font-bold text-slate-700">Kích hoạt chiến dịch</span>
                            </label>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="flex-1 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> {{ $editingFlashSale ? 'Lưu Thay Đổi' : 'Xác Nhận Tạo' }}
                            </button>
                            @if($editingFlashSale)
                                <a href="{{ route('admin.flash-sales.index') }}" 
                                   class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all text-center">Hủy</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Cột Phải: Danh sách & Gán sản phẩm --}}
        <div class="xl:col-span-8 space-y-8">
            {{-- Danh sách chương trình --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-list-ul text-indigo-500"></i> Danh sách các đợt Flash Sale
                    </h3>
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-black px-2.5 py-1 rounded-full uppercase tracking-tighter">
                        {{ $flashSales->total() }} chiến dịch
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100">
                                <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-widest">Chiến dịch</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-widest">Thời gian diễn ra</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Trạng thái</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Sản phẩm</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($flashSales as $flashSale)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">{{ $flashSale->name }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 italic">ID: #FS-{{ $flashSale->flash_sale_id }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 text-xs text-slate-600">
                                            <i class="fa-regular fa-calendar-check text-emerald-500"></i> {{ \Carbon\Carbon::parse($flashSale->start_at)->format('H:i:s d/m/Y') }}
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-slate-400 mt-1">
                                            <i class="fa-regular fa-calendar-xmark text-rose-400"></i> {{ \Carbon\Carbon::parse($flashSale->end_at)->format('H:i:s d/m/Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            @if($flashSale->is_active)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                    ĐANG BẬT
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                    TẠM TẮT
                                                </span>
                                            @endif

                                            @php
                                                $now = now();
                                                $start = \Carbon\Carbon::parse($flashSale->start_at);
                                                $end = \Carbon\Carbon::parse($flashSale->end_at);
                                            @endphp

                                            @if($now->lt($start))
                                                <span class="text-[9px] font-black text-amber-600 uppercase tracking-tighter italic">Sắp diễn ra</span>
                                            @elseif($now->gt($end))
                                                <span class="text-[9px] font-black text-rose-500 uppercase tracking-tighter italic">Đã kết thúc</span>
                                            @else
                                                <span class="text-[9px] font-black text-indigo-500 uppercase tracking-tighter animate-pulse italic">Đang diễn ra</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-black text-slate-700 bg-slate-100 w-8 h-8 inline-flex items-center justify-center rounded-lg">{{ $flashSale->products_count ?? 0 }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.flash-sales.index', ['edit' => $flashSale->flash_sale_id]) }}" 
                                               class="px-3 py-1.5 bg-white border border-slate-200 text-indigo-600 rounded-lg flex items-center gap-2 hover:bg-indigo-50 hover:border-indigo-200 transition-all text-xs font-bold" title="Quản lý sản phẩm & Chỉnh sửa">
                                                <i class="fa-solid fa-box-open"></i> Quản lý SP
                                            </a>
                                            <form action="{{ route('admin.flash-sales.destroy', $flashSale->flash_sale_id) }}" method="POST" onsubmit="return confirm('Xóa Flash Sale này?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-8 h-8 bg-white border border-slate-200 text-rose-500 rounded-lg flex items-center justify-center hover:bg-rose-50 hover:border-rose-200 transition-all" title="Xóa">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 italic font-medium">Chưa có chiến dịch Flash Sale nào được tạo.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($flashSales->hasPages())
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                        {{ $flashSales->links() }}
                    </div>
                @endif
            </div>

            {{-- Gán sản phẩm vào Flash Sale --}}
            @php($currentFlashSale = $editingFlashSale)
            @if($currentFlashSale)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-box-archive text-indigo-500"></i> 
                        Sản phẩm trong chiến dịch: <span class="text-indigo-600 underline underline-offset-4 decoration-indigo-200 italic ml-1">"{{ $currentFlashSale->name ?? '...' }}"</span>
                    </h3>
                </div>
                <div class="p-6">
                    <form id="add-product-form" action="{{ route('admin.flash-sales.products.store', $currentFlashSale->flash_sale_id) }}" method="POST" class="bg-slate-50/80 p-6 rounded-3xl border border-slate-200 mb-8 space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 gap-4">
                                <div class="w-full">
                                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">1. Chọn sản phẩm cần chạy Flash Sale</label>
                                    <select name="product_id" id="product_select" class="select2-bootstrap-5" required>
                                        <option value="">-- Tìm tên sản phẩm hoặc mã sản phẩm --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->product_id }}" 
                                                    data-price="{{ $product->base_price }}"
                                                    data-image="{{ $product->thumbnail }}">
                                                {{ $product->name }} (Giá gốc: {{ number_format($product->base_price, 0, ',', '.') }}đ)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 items-end">
                                <div>
                                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">2. Giảm (%)</label>
                                    <input type="number" id="discount_percent" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-base font-bold text-indigo-600 bg-white" min="0" max="100" placeholder="0">
                                </div>
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">3. Giá Sale (VNĐ)</label>
                                    <input type="number" name="sale_price" id="sale_price" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-base font-black text-rose-600 bg-white" required min="0" placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">4. Kho Sale</label>
                                    <input type="number" name="stock_limit" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-base font-bold text-slate-700 bg-white" required min="1" value="10">
                                </div>
                                <div>
                                    <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-plus"></i> Thêm vào Sale
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="overflow-x-auto rounded-2xl border border-slate-100">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100">
                                        <th class="px-5 py-3 text-[10px] font-black text-slate-400 uppercase">Sản phẩm</th>
                                        <th class="px-5 py-3 text-[10px] font-black text-slate-400 uppercase text-center">Giá Sale</th>
                                        <th class="px-5 py-3 text-[10px] font-black text-slate-400 uppercase text-center">Kho / Đã bán</th>
                                        <th class="px-5 py-3 text-[10px] font-black text-slate-400 uppercase text-center">Trạng thái</th>
                                        <th class="px-5 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody id="flash-sale-products-list" class="divide-y divide-slate-50">
                                    @forelse($currentFlashSale->products as $item)
                                        <tr class="hover:bg-slate-50/30 transition-colors">
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-3">
                                                    <img src="{{ $item->product->thumbnail }}" class="w-10 h-10 rounded-lg object-cover shadow-sm border border-slate-200">
                                                    <div>
                                                        <div class="font-bold text-slate-700 text-sm line-clamp-1">{{ $item->product->name ?? 'N/A' }}</div>
                                                        <div class="text-[10px] text-slate-400 italic">PID: #{{ $item->product->product_id ?? '?' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 text-center">
                                                <span class="text-rose-600 font-black text-sm">{{ number_format($item->sale_price, 0, ',', '.') }}đ</span>
                                            </td>
                                            <td class="px-5 py-4 text-center">
                                                <div class="text-xs font-bold text-slate-700">
                                                    <span class="text-indigo-600">{{ $item->sold_quantity }}</span> / {{ $item->stock_limit }}
                                                </div>
                                                <div class="w-full bg-slate-100 h-1.5 rounded-full mt-1.5 overflow-hidden">
                                                    @php($percent = ($item->stock_limit > 0) ? ($item->sold_quantity / $item->stock_limit * 100) : 0)
                                                    <div class="bg-indigo-500 h-full rounded-full" style="width: {{ $percent }}%"></div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 text-center">
                                                @if($item->is_active)
                                                    <span class="w-2 h-2 bg-emerald-500 rounded-full inline-block shadow-sm shadow-emerald-200"></span>
                                                    <span class="text-[10px] font-bold text-emerald-600 ml-1">ĐANG BÁN</span>
                                                @else
                                                    <span class="w-2 h-2 bg-slate-300 rounded-full inline-block"></span>
                                                    <span class="text-[10px] font-bold text-slate-400 ml-1">DỪNG</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button" 
                                                            class="btn-edit-product text-slate-400 hover:text-indigo-600 transition-colors p-2"
                                                            data-id="{{ $item->product->product_id }}"
                                                            data-price="{{ (int)$item->sale_price }}"
                                                            data-stock="{{ $item->stock_limit }}"
                                                            data-sort="{{ $item->sort_order }}"
                                                            title="Sửa thông tin">
                                                        <i class="fa-solid fa-pen-to-square text-lg"></i>
                                                    </button>
                                                    <form action="{{ route('admin.flash-sales.products.destroy', [$currentFlashSale->flash_sale_id, $item->flash_sale_product_id]) }}" method="POST" onsubmit="return confirm('Gỡ sản phẩm này?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-slate-300 hover:text-rose-500 transition-colors p-2" title="Gỡ sản phẩm">
                                                            <i class="fa-solid fa-circle-xmark text-lg"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400 text-xs italic">Chương trình này chưa có sản phẩm nào.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Hàm gán thời gian hiện tại
    function setCurrentTime(inputId) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        const nowString = now.toISOString().slice(0, 19);
        document.getElementById(inputId).value = nowString;
    }

    $(document).ready(function() {
        // Tìm vị trí chữ "Hôm nay" trong Topbar để chèn giờ vào một cách tự nhiên
        function injectClock() {
            const todaySpan = $('span:contains("Hôm nay")').filter(function() {
                return $(this).text().trim() === "Hôm nay";
            });

            if (todaySpan.length > 0 && $('#live-clock-hhmmss').length === 0) {
                // Chèn vào cùng hàng với chữ Hôm nay
                todaySpan.after('<span id="live-clock-hhmmss" style="margin-left: 8px; color: #4f46e5; font-weight: 900;">00:00:00</span>');
            }
        }

        function updateLiveTime() {
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                               now.getMinutes().toString().padStart(2, '0') + ':' + 
                               now.getSeconds().toString().padStart(2, '0');
            
            if ($('#live-clock-hhmmss').length > 0) {
                $('#live-clock-hhmmss').text(timeString);
            } else {
                injectClock(); // Thử chèn lại nếu React re-render làm mất
            }
        }

        // Chạy liên tục để đối phó với việc React có thể render lại làm mất element
        setInterval(updateLiveTime, 1000);
        injectClock();
        updateLiveTime();

        // Trì hoãn một chút để đảm bảo Select2 không bị script khác ghi đè
        setTimeout(function() {
            if ($('#product_select').data('select2')) {
                $('#product_select').select2('destroy');
            }

            $('#product_select').select2({
                theme: 'bootstrap-5',
                templateResult: function(opt) {
                    if (!opt.id) return opt.text;
                    var imageUrl = $(opt.element).attr('data-image');
                    if (!imageUrl) return opt.text;
                    
                    return $('<div style="display: flex; align-items: center; gap: 10px; padding: 2px 0;">' +
                             '<img src="' + imageUrl + '" style="width: 32px; height: 32px; border-radius: 6px; object-fit: cover; border: 1px solid #eee;">' +
                             '<span style="font-weight: 600; color: #334155;">' + opt.text + '</span>' +
                             '</div>');
                },
                templateSelection: function(opt) {
                    if (!opt.id) return opt.text;
                    var imageUrl = $(opt.element).attr('data-image');
                    if (!imageUrl) return opt.text;
                    
                    return $('<div style="display: flex; align-items: center; gap: 8px;">' +
                             '<img src="' + imageUrl + '" style="width: 20px; height: 20px; border-radius: 4px; object-fit: cover;">' +
                             '<span>' + opt.text + '</span>' +
                             '</div>');
                },
                escapeMarkup: function(m) { return m; }
            });
        }, 500);

        // Xử lý AJAX thêm sản phẩm
        $('#add-product-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnHtml = submitBtn.html();

            // Hiệu ứng loading
            submitBtn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch animate-spin"></i> Đang thêm...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        // Thêm hàng mới vào bảng
                        const product = response.product;
                        const newRow = `
                            <tr class="hover:bg-slate-50/30 transition-colors animate-in fade-in slide-in-from-left duration-500">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="${product.thumbnail}" class="w-10 h-10 rounded-lg object-cover shadow-sm border border-slate-200">
                                        <div>
                                            <div class="font-bold text-slate-700 text-sm line-clamp-1">${product.name}</div>
                                            <div class="text-[10px] text-slate-400 italic">PID: #${product.id}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-rose-600 font-black text-sm">${product.sale_price}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="text-xs font-bold text-slate-700">
                                        <span class="text-indigo-600">${product.sold_quantity}</span> / ${product.stock_limit}
                                    </div>
                                    <div class="w-24 h-1.5 bg-slate-100 rounded-full mx-auto mt-2 overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full" style="width: 0%"></div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase">
                                        Đang bán
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <form action="${product.delete_url}" method="POST" class="inline delete-product-form">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="w-8 h-8 bg-white border border-slate-200 text-slate-400 rounded-lg flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 hover:border-rose-200 transition-all">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        `;
                        
                        // Xóa dòng "Chưa có sản phẩm" nếu có
                        $('#flash-sale-products-list').find('td[colspan]').parent().remove();
                        
                        $('#flash-sale-products-list').prepend(newRow);
                        
                        // Reset form
                        form[0].reset();
                        $('#product_select').val('').trigger('change');
                        
                        // Thông báo
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON ? xhr.responseJSON.message : 'Có lỗi xảy ra, vui lòng thử lại.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: message
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                }
            });
        });

        const productSelect = $('#product_select');
        const discountInput = $('#discount_percent');
        const salePriceInput = $('#sale_price');

        productSelect.select2({
            theme: 'bootstrap-5',
            placeholder: "Gõ tên sản phẩm cần tìm...",
            width: '100%',
            allowClear: true,
            language: {
                noResults: function () {
                    return "Không tìm thấy sản phẩm nào";
                }
            }
        });

        function calculateSalePrice() {
            const selected = productSelect.find(':selected');
            const basePrice = parseFloat(selected.data('price')) || 0;
            const percent = parseFloat(discountInput.val()) || 0;

            if (basePrice > 0 && percent > 0) {
                const calculatedPrice = Math.round(basePrice * (1 - percent / 100));
                salePriceInput.val(calculatedPrice);
            }
        }

        function calculateDiscountPercent() {
            const selected = productSelect.find(':selected');
            const basePrice = parseFloat(selected.data('price')) || 0;
            const salePrice = parseFloat(salePriceInput.val()) || 0;

            if (basePrice > 0 && salePrice > 0) {
                const percent = Math.round((1 - (salePrice / basePrice)) * 100);
                discountInput.val(percent);
            }
        }

        discountInput.on('input', calculateSalePrice);
        productSelect.on('change', calculateSalePrice);
        salePriceInput.on('input', calculateDiscountPercent);

        // Xử lý nút Sửa sản phẩm trong bảng
        $('.btn-edit-product').on('click', function() {
            const btn = $(this);
            const productId = btn.data('id');
            const salePrice = btn.data('price');
            const stockLimit = btn.data('stock');
            const sortOrder = btn.data('sort');

            // Điền dữ liệu vào form
            productSelect.val(productId).trigger('change');
            salePriceInput.val(salePrice);
            $('input[name="stock_limit"]').val(stockLimit);
            $('input[name="sort_order"]').val(sortOrder);

            // Tính lại % giảm giá
            calculateDiscountPercent();

            // Hiệu ứng cuộn lên form
            window.scrollTo({
                top: $('#product_select').offset().top - 150,
                behavior: 'smooth'
            });

            // Hiệu ứng highlight form
            const form = productSelect.closest('form');
            form.addClass('ring-2 ring-indigo-500 ring-offset-2');
            setTimeout(() => {
                form.removeClass('ring-2 ring-indigo-500 ring-offset-2');
            }, 2000);
        });
    });
</script>
@endpush
