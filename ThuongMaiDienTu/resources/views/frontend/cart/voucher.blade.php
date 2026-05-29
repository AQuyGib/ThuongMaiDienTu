@extends('admin.layouts.master')

@section('title', 'Quản Lý Voucher')

@section('content')
<div class="max-w-[1400px] mx-auto space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
                <i class="fa-solid fa-ticket"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Quản Lý Voucher</h1>
                <p class="text-slate-500 text-sm">Tạo, cập nhật và theo dõi mã giảm giá cho khách hàng.</p>
            </div>
        </div>
        <a href="{{ route('admin.vouchers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold text-sm transition-all flex items-center gap-2">
            <i class="fa-solid fa-rotate"></i> Làm mới
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <div class="xl:col-span-4">
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden sticky top-8">
                <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white">
                    <h3 class="font-bold flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square"></i>
                        {{ $editingVoucher ? 'Cập nhật voucher' : 'Tạo voucher mới' }}
                    </h3>
                </div>
                <div class="p-6">
                    @php
                        $editingDiscountType = $editingVoucher->discount_type ?? 'fixed';
                        $editingDiscountValue = $editingVoucher->discount_val ?? '';
                    @endphp
                    <form action="{{ $editingVoucher ? route('admin.vouchers.update', $editingVoucher->promo_id) : route('admin.vouchers.store') }}" method="POST" class="space-y-5">
                        @csrf
                        @if($editingVoucher)
                            @method('PUT')
                        @endif

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mã voucher <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" required maxlength="50" value="{{ old('code', $editingVoucher->code ?? '') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none text-slate-700 font-semibold uppercase"
                                placeholder="VD: GIAM50K">
                            @error('code')
                                <p class="mt-2 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Kiểu giảm <span class="text-rose-500">*</span></label>
                                <select name="discount_type" id="discount_type"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none text-slate-700 font-semibold">
                                    <option value="fixed" {{ old('discount_type', $editingDiscountType) === 'fixed' ? 'selected' : '' }}>Theo tiền (VNĐ)</option>
                                    <option value="percent" {{ old('discount_type', $editingDiscountType) === 'percent' ? 'selected' : '' }}>Theo phần trăm (%)</option>
                                </select>
                                @error('discount_type')
                                    <p class="mt-2 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giá giảm (VNĐ)</label>
                                <input type="number" id="discount_fixed" name="discount_fixed" min="1" value="{{ old('discount_fixed', $editingDiscountType === 'fixed' ? $editingDiscountValue : '') }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none text-slate-700 font-semibold"
                                    placeholder="50000">
                                @error('discount_fixed')
                                    <p class="mt-2 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giảm theo phần trăm (%)</label>
                                <input type="number" id="discount_percent" name="discount_percent" min="1" max="100" value="{{ old('discount_percent', $editingDiscountType === 'percent' ? $editingDiscountValue : '') }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none text-slate-700 font-semibold"
                                    placeholder="10">
                                <p class="mt-1 text-xs text-slate-500">Giá trị hợp lệ: 1-100.</p>
                                @error('discount_percent')
                                    <p class="mt-2 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div></div>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Bắt đầu</label>
                                <input type="datetime-local" name="start_time"
                                    value="{{ old('start_time', isset($editingVoucher->start_time) ? \Carbon\Carbon::parse($editingVoucher->start_time)->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none text-slate-700">
                                @error('start_time')
                                    <p class="mt-2 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Kết thúc</label>
                                <input type="datetime-local" name="end_time"
                                    value="{{ old('end_time', isset($editingVoucher->end_time) ? \Carbon\Carbon::parse($editingVoucher->end_time)->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none text-slate-700">
                                @error('end_time')
                                    <p class="mt-2 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="flex-1 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-indigo-200">
                                {{ $editingVoucher ? 'Lưu thay đổi' : 'Tạo voucher' }}
                            </button>
                            @if($editingVoucher)
                                <a href="{{ route('admin.vouchers.index') }}" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all text-center">Hủy</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="xl:col-span-8">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-list text-indigo-500"></i> Danh sách voucher
                    </h3>
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-black px-2.5 py-1 rounded-full uppercase tracking-tighter">
                        {{ $vouchers->total() }} mã
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100">
                                <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-widest">Mã voucher</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-widest">Giảm giá</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-widest">Thời gian</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Trạng thái</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($vouchers as $voucher)
                                @php
                                    $now = now();
                                    $start = $voucher->start_time ? \Carbon\Carbon::parse($voucher->start_time) : null;
                                    $end = $voucher->end_time ? \Carbon\Carbon::parse($voucher->end_time) : null;

                                    if ($start && $now->lt($start)) {
                                        $status = 'Sắp diễn ra';
                                        $statusClass = 'bg-amber-100 text-amber-700 border-amber-200';
                                    } elseif ($end && $now->gt($end)) {
                                        $status = 'Hết hạn';
                                        $statusClass = 'bg-rose-100 text-rose-700 border-rose-200';
                                    } else {
                                        $status = 'Đang hiệu lực';
                                        $statusClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                                    }
                                @endphp

                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800 uppercase">{{ $voucher->code }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 italic">ID: #VC-{{ $voucher->promo_id }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if(($voucher->discount_type ?? 'fixed') === 'percent')
                                            <span class="text-indigo-600 font-black">{{ rtrim(rtrim(number_format((float) $voucher->discount_val, 2, '.', ''), '0'), '.') }}%</span>
                                        @else
                                            <span class="text-indigo-600 font-black">{{ number_format($voucher->discount_val, 0, ',', '.') }}đ</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-slate-600">
                                            <span class="font-semibold">Từ:</span>
                                            {{ $start ? $start->format('H:i d/m/Y') : 'Không giới hạn' }}
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            <span class="font-semibold">Đến:</span>
                                            {{ $end ? $end->format('H:i d/m/Y') : 'Không giới hạn' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $statusClass }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.vouchers.index', ['edit' => $voucher->promo_id]) }}"
                                                class="px-3 py-1.5 bg-white border border-slate-200 text-indigo-600 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 transition-all text-xs font-bold">
                                                Sửa
                                            </a>
                                            <form action="{{ route('admin.vouchers.destroy', $voucher->promo_id) }}" method="POST" onsubmit="return confirm('Xóa voucher này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 bg-white border border-slate-200 text-rose-600 rounded-lg hover:bg-rose-50 hover:border-rose-200 transition-all text-xs font-bold">
                                                    Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic font-medium">Chưa có voucher nào được tạo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($vouchers->hasPages())
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                        {{ $vouchers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const discountType = document.getElementById('discount_type');
        const fixedInput = document.getElementById('discount_fixed');
        const percentInput = document.getElementById('discount_percent');

        function syncDiscountInputs() {
            const isPercent = discountType.value === 'percent';
            fixedInput.disabled = isPercent;
            percentInput.disabled = !isPercent;

            fixedInput.required = !isPercent;
            percentInput.required = isPercent;

            if (isPercent) {
                fixedInput.value = '';
            } else {
                percentInput.value = '';
            }
        }

        if (discountType && fixedInput && percentInput) {
            discountType.addEventListener('change', syncDiscountInputs);
            syncDiscountInputs();
        }
    })();
</script>
@endpush
