@extends('admin.layouts.master')

@section('title', 'Sửa phiếu sửa chữa')
@section('page-title', 'Sửa phiếu sửa chữa')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sửa phiếu sửa chữa</h1>
            <p class="text-sm text-gray-500">Chỉnh sửa thông tin phiếu sửa chữa #RT-{{ $repairTicket->ticket_id }}.</p>
        </div>
        <x-ui.button
            variant="secondary"
            :href="route('admin.repair-tickets.index')"
            title="Quay lại danh sách"
        >
            <i class="fa-solid fa-chevron-left"></i> Quay lại
        </x-ui.button>
    </div>



    <form method="POST" action="{{ route('admin.repair-tickets.update', $repairTicket) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Thông tin Khách hàng & Kỹ thuật viên -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-users text-indigo-500"></i> Thông tin khách hàng & Nhân viên phụ trách
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Nhập thông tin khách hàng liên hệ và chỉ định kỹ thuật viên phụ trách.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tên khách hàng <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', $repairTicket->customer_name) }}" class="w-full rounded-lg border {{ $errors->has('customer_name') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Nhập tên khách hàng">
                    @error('customer_name') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Số điện thoại <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone', $repairTicket->customer_phone) }}" class="w-full rounded-lg border {{ $errors->has('customer_phone') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Nhập số điện thoại">
                    @error('customer_phone') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Kỹ thuật viên phụ trách</label>
                    <select name="technician_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Chọn kỹ thuật viên --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->user_id }}" {{ old('technician_id', $repairTicket->technician_id) == $tech->user_id ? 'selected' : '' }}>
                                {{ $tech->full_name }} ({{ optional($tech->role)->name ?? 'Nhân viên' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Địa chỉ</label>
                    <input type="text" name="customer_address" value="{{ old('customer_address', $repairTicket->customer_address) }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Địa chỉ (tùy chọn)">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email', $repairTicket->customer_email) }}" class="w-full rounded-lg border {{ $errors->has('customer_email') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Email (tùy chọn)">
                    @error('customer_email') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Nguồn khách hàng</label>
                    <select name="customer_source" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Không chọn</option>
                        <option value="Facebook" {{ old('customer_source', $repairTicket->customer_source) == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="Google" {{ old('customer_source', $repairTicket->customer_source) == 'Google' ? 'selected' : '' }}>Google</option>
                        <option value="Người quen giới thiệu" {{ old('customer_source', $repairTicket->customer_source) == 'Người quen giới thiệu' ? 'selected' : '' }}>Người quen giới thiệu</option>
                        <option value="Website" {{ old('customer_source', $repairTicket->customer_source) == 'Website' ? 'selected' : '' }}>Website</option>
                        <option value="Khác" {{ old('customer_source', $repairTicket->customer_source) == 'Khác' ? 'selected' : '' }}>Khác</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 2: Thông tin thiết bị & Dịch vụ -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-mobile-screen text-indigo-500"></i> Thông tin thiết bị & Dịch vụ
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Nhập chi tiết thiết bị, mã số định danh, ngày hẹn và mô tả yêu cầu.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Mã IMEI / Serial <span class="text-red-500">*</span></label>
                    <input type="text" name="imei_serial" value="{{ old('imei_serial', $repairTicket->imei_serial) }}" class="w-full rounded-lg border {{ $errors->has('imei_serial') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Nhập mã IMEI hoặc Serial Number">
                    @error('imei_serial') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Ngày hẹn trả</label>
                    <input type="date" name="schedule_date" value="{{ old('schedule_date', $repairTicket->schedule_date ? \Carbon\Carbon::parse($repairTicket->schedule_date)->format('Y-m-d') : '') }}" min="{{ date('Y-m-d') }}" class="w-full rounded-lg border {{ $errors->has('schedule_date') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('schedule_date') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Mô tả lỗi / Yêu cầu sửa chữa <span class="text-red-500">*</span></label>
                <textarea name="issue_desc" rows="3" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Nhập chi tiết lỗi máy...">{{ old('issue_desc', $repairTicket->issue_desc) }}</textarea>
            </div>
        </div>

        <!-- Card 3: Chi phí & Tiến độ -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-coins text-indigo-500"></i> Trạng thái & Chi phí sửa chữa
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Thiết lập ước tính chi phí và cập nhật tiến độ xử lý thiết bị.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Chi phí dự kiến (đ)</label>
                    <input type="number" name="estimated_cost" value="{{ old('estimated_cost', $repairTicket->estimated_cost) }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required min="0">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tên dịch vụ thực hiện</label>
                    <input type="text" name="service_name" value="{{ old('service_name', $repairTicket->service_name) }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ví dụ: Thay màn hình OLED">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Phí dịch vụ thực tế (đ)</label>
                    <input type="number" step="0.01" name="service_fee" value="{{ old('service_fee', (float) $repairTicket->service_fee) }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" min="0">
                </div>
            </div>

            <div class="max-w-xs">
                <label class="mb-1 block text-sm font-medium text-gray-700">Trạng thái sửa chữa <span class="text-red-500">*</span></label>
                <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="Received" {{ old('status', $repairTicket->status) == 'Received' ? 'selected' : '' }}>Đã tiếp nhận (Received)</option>
                    <option value="Waiting_Parts" {{ old('status', $repairTicket->status) == 'Waiting_Parts' ? 'selected' : '' }}>Đang chờ linh kiện (Waiting_Parts)</option>
                    <option value="Done" {{ old('status', $repairTicket->status) == 'Done' ? 'selected' : '' }}>Hoàn thành (Done)</option>
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.button
                variant="primary"
                type="submit"
                title="Cập nhật phiếu sửa chữa"
            >
                <i class="fa-solid fa-floppy-disk"></i> Cập nhật phiếu
            </x-ui.button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.querySelector('input[name="customer_phone"]');
    const nameInput = document.querySelector('input[name="customer_name"]');
    const addressInput = document.querySelector('input[name="customer_address"]');
    const emailInput = document.querySelector('input[name="customer_email"]');
    const sourceSelect = document.querySelector('select[name="customer_source"]');

    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            const phone = this.value.trim();
            if (phone.length >= 9) {
                fetch(`{{ route('admin.api.customers.search-by-phone') }}?phone=${encodeURIComponent(phone)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            if (nameInput && data.customer_name) nameInput.value = data.customer_name;
                            if (addressInput && data.customer_address) addressInput.value = data.customer_address;
                            if (emailInput && data.customer_email) emailInput.value = data.customer_email;
                            if (sourceSelect && data.customer_source) sourceSelect.value = data.customer_source;
                        }
                    })
                    .catch(err => console.error('Error fetching customer data:', err));
            }
        });
    }
});
</script>
@endsection
