@extends('admin.layouts.master')

@section('title', isset($repairTicket) ? 'Xuất hóa đơn dịch vụ' : 'Tạo hóa đơn dịch vụ')
@section('page-title', isset($repairTicket) ? 'Xuất hóa đơn dịch vụ' : 'Tạo hóa đơn dịch vụ')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ isset($repairTicket) ? 'Xuất hóa đơn dịch vụ' : 'Tạo hóa đơn dịch vụ' }}</h1>
            <p class="text-sm text-gray-500">{{ isset($repairTicket) ? 'Nhập thông tin để xuất hóa đơn nhanh chóng và chính xác.' : 'Nhập thông tin để tạo hóa đơn nhanh chóng và chính xác.' }}</p>
        </div>
        <x-ui.button
            variant="secondary"
            :href="isset($repairTicket) ? route('admin.repair-tickets.index') : route('admin.service-invoices.index')"
            title="Quay lại danh sách"
        >
            <i class="fa-solid fa-chevron-left"></i> Quay lại
        </x-ui.button>
    </div>

    <form method="POST" action="{{ isset($repairTicket) ? route('admin.repair-tickets.invoice.store') : route('admin.service-invoices.store') }}" class="space-y-6">
        @csrf

        @if(isset($repairTicket))
            <input type="hidden" name="repair_ticket_id" value="{{ $repairTicket->ticket_id }}">
        @endif

        <!-- Card 1: Thông tin Khách hàng & Dịch vụ -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-gear text-indigo-500"></i> Thông tin khách hàng & Dịch vụ
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Nhập các thông tin liên hệ và tên dịch vụ thực hiện.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Mã hóa đơn</label>
                    <input type="text" name="invoice_no" value="{{ old('invoice_no', $prefill['invoice_no'] ?? '') }}" readonly class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 shadow-sm cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500">Mã hóa đơn được tạo tự động.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Khách hàng <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', $prefill['customer_name'] ?? '') }}" class="w-full rounded-lg border {{ $errors->has('customer_name') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('customer_name') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Số điện thoại</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone', $prefill['customer_phone'] ?? '') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email', $prefill['customer_email'] ?? '') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">IMEI / Serial</label>
                    <input type="text" name="imei_serial" value="{{ old('imei_serial', $prefill['imei_serial'] ?? '') }}" placeholder="Nhập IMEI hoặc Serial thiết bị..." class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tên dịch vụ <span class="text-red-500">*</span></label>
                    <input type="text" name="service_name" value="{{ old('service_name', $prefill['service_name'] ?? '') }}" class="w-full rounded-lg border {{ $errors->has('service_name') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('service_name') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Card 2: Chi tiết Chi phí -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-indigo-500"></i> Chi tiết chi phí (đ)
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Cấu hình tạm tính, VAT và giảm giá.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tạm tính (đ) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="subtotal" id="subtotal" value="{{ old('subtotal', $prefill['subtotal'] ?? 0) }}" class="w-full rounded-lg border {{ $errors->has('subtotal') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required min="0">
                    @error('subtotal') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">VAT (%)</label>
                    <input type="number" step="0.01" name="vat_rate" id="vat_rate" value="{{ old('vat_rate', 0) }}" min="0" max="100" placeholder="Ví dụ: 10" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Giảm giá (đ)</label>
                    <input type="number" step="0.01" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', 0) }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="rounded-lg bg-indigo-50 px-4 py-3 flex items-center justify-between">
                <span class="text-sm text-indigo-700 font-medium">Tổng cộng dự tính:</span>
                <span id="total_preview" class="text-lg font-bold text-indigo-900">0 đ</span>
            </div>
        </div>

        <!-- Card 3: Trạng thái & Mô tả -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-indigo-500"></i> Trạng thái & Ghi chú
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Chọn trạng thái hóa đơn và nhập mô tả chi tiết của dịch vụ.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Trạng thái</label>
                    <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="draft">Nháp</option>
                        <option value="issued">Đã phát hành</option>
                        <option value="paid">Đã thanh toán</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Mô tả chi tiết</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.button
                variant="primary"
                title="{{ isset($repairTicket) ? 'Xuất hóa đơn' : 'Lưu hóa đơn' }}"
            >
                <i class="fa-solid fa-file-invoice-dollar"></i> {{ isset($repairTicket) ? 'Xuất hóa đơn' : 'Lưu hóa đơn' }}
            </x-ui.button>
        </div>
    </form>
</div>

<script>
(function () {
    const subtotalEl = document.getElementById('subtotal');
    const vatEl = document.getElementById('vat_rate');
    const discountEl = document.getElementById('discount_amount');
    const previewEl = document.getElementById('total_preview');

    function updatePreview() {
        if (!previewEl) return;
        const subtotal = parseFloat(subtotalEl?.value) || 0;
        const vat = parseFloat(vatEl?.value) || 0;
        const discount = parseFloat(discountEl?.value) || 0;
        const vatAmount = (subtotal * vat) / 100;
        const total = Math.max(0, subtotal + vatAmount - discount);
        previewEl.textContent = total.toLocaleString('vi-VN') + ' đ';
    }

    [subtotalEl, vatEl, discountEl].forEach(el => el?.addEventListener('input', updatePreview));
    updatePreview();
})();
</script>
@endsection
