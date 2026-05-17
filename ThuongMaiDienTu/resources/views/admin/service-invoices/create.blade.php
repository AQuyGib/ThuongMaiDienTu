@extends('admin.layouts.master')

@section('title', 'Tạo hóa đơn dịch vụ')
@section('page-title', 'Tạo hóa đơn dịch vụ')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tạo hóa đơn dịch vụ</h1>
            <p class="text-sm text-gray-500">Nhập thông tin để tạo hóa đơn nhanh chóng và chính xác.</p>
        </div>
        <x-ui.button
            variant="secondary"
            :href="route('admin.service-invoices.index')"
            title="Quay lại danh sách"
        >
            <i class="fa-solid fa-chevron-left"></i> Quay lại
        </x-ui.button>
    </div>

    <form method="POST" action="{{ route('admin.service-invoices.store') }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        @csrf

        @if(isset($repairTicket))
            <input type="hidden" name="repair_ticket_id" value="{{ $repairTicket->id }}">
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Khách hàng</label>
                <input type="text" name="customer_name" value="{{ old('customer_name', $prefill['customer_name'] ?? '') }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Số điện thoại</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone', $prefill['customer_phone'] ?? '') }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tên dịch vụ</label>
                <input type="text" name="service_name" value="{{ old('service_name', $prefill['service_name'] ?? '') }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Mô tả</label>
            <textarea name="description" rows="4" class="w-full rounded-lg border-gray-300 text-sm">{{ old('description') }}</textarea>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tạm tính</label>
                <input type="number" step="0.01" name="subtotal" value="{{ old('subtotal', $prefill['subtotal'] ?? 0) }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Thuế</label>
                <input type="number" step="0.01" name="tax_amount" value="{{ old('tax_amount', 0) }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Giảm giá</label>
                <input type="number" step="0.01" name="discount_amount" value="{{ old('discount_amount', 0) }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
        </div>

        <div class="max-w-xs">
            <label class="mb-1 block text-sm font-medium text-gray-700">Trạng thái</label>
            <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="draft">Nháp</option>
                <option value="issued">Đã phát hành</option>
                <option value="paid">Đã thanh toán</option>
                <option value="cancelled">Đã hủy</option>
            </select>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.button
                variant="primary"
                title="Lưu hóa đơn"
            >
                <i class="fa-solid fa-floppy-disk"></i> Lưu hóa đơn
            </x-ui.button>
        </div>
    </form>
</div>
@endsection
