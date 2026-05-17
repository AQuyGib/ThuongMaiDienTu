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
            :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M12.5 15.5 7 10l5.5-5.5\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/></svg>'"
        >
            Quay lại
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
                :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M4.5 3.5h8l3 3V16a1 1 0 0 1-1 1h-10a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z\" stroke=\"currentColor\" stroke-width=\"1.6\"/><path d=\"M6 3.5v4h6v-4\" stroke=\"currentColor\" stroke-width=\"1.6\"/></svg>'"
            >
                Lưu hóa đơn
            </x-ui.button>
        </div>
    </form>
</div>
