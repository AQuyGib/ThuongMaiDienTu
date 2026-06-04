@extends('admin.layouts.master')

@section('title', 'Tạo yêu cầu bảo hành & đổi trả')
@section('page-title', 'Tạo yêu cầu bảo hành & đổi trả')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tạo yêu cầu bảo hành & đổi trả</h1>
            <p class="text-sm text-gray-500">Tạo yêu cầu dịch vụ trực tiếp tại quầy cho khách hàng.</p>
        </div>
        <x-ui.button
            variant="secondary"
            :href="route('admin.warranty-claims.index')"
            title="Quay lại danh sách"
        >
            <i class="fa-solid fa-chevron-left"></i> Quay lại
        </x-ui.button>
    </div>

    <form method="POST" action="{{ route('admin.warranty-claims.store') }}" class="space-y-6">
        @csrf

        <!-- Card 1: Thông tin Khách hàng -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-users text-indigo-500"></i> Thông tin khách hàng
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Nhập thông tin liên hệ của khách hàng yêu cầu bảo hành/đổi trả.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tên khách hàng <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="w-full rounded-lg border {{ $errors->has('customer_name') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Nhập tên khách hàng">
                    @error('customer_name') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Số điện thoại <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="w-full rounded-lg border {{ $errors->has('customer_phone') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Nhập số điện thoại">
                    @error('customer_phone') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="w-full rounded-lg border {{ $errors->has('customer_email') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Email (tùy chọn)">
                    @error('customer_email') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Card 2: Thông tin thiết bị & yêu cầu -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-mobile-screen text-indigo-500"></i> Thông tin thiết bị & Yêu cầu
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Nhập mã định danh thiết bị và chi tiết yêu cầu bảo hành/đổi trả.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Mã IMEI / Serial <span class="text-red-500">*</span></label>
                    <input type="text" name="imei_serial" value="{{ old('imei_serial') }}" class="w-full rounded-lg border {{ $errors->has('imei_serial') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Nhập mã IMEI hoặc Serial Number">
                    @error('imei_serial') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Loại yêu cầu <span class="text-red-500">*</span></label>
                    <select name="claim_type" class="w-full rounded-lg border {{ $errors->has('claim_type') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">-- Chọn loại yêu cầu --</option>
                        <option value="warranty" {{ old('claim_type') == 'warranty' ? 'selected' : '' }}>Bảo hành (Warranty)</option>
                        <option value="return" {{ old('claim_type') == 'return' ? 'selected' : '' }}>Đổi trả hoàn tiền (Return)</option>
                        <option value="exchange" {{ old('claim_type') == 'exchange' ? 'selected' : '' }}>Đổi máy khác/mới (Exchange)</option>
                    </select>
                    @error('claim_type') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Lý do yêu cầu / Tình trạng máy <span class="text-red-500">*</span></label>
                <textarea name="reason" rows="3" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Nhập mô tả lỗi hoặc lý do đổi trả...">{{ old('reason') }}</textarea>
                @error('reason') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Card 3: Thông tin nhận tiền hoàn (chỉ cho Đổi trả hoàn tiền) -->
        <!-- Card này ẩn/hiện động dựa trên 'claim_type' == 'return' (sẽ hiển thị) hoặc khác (sẽ ẩn) -->
        <div id="bankDetailsCard" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4 {{ old('claim_type') == 'return' ? '' : 'hidden' }}">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-building-columns text-amber-500"></i> Thông tin nhận tiền hoàn
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Thông tin tài khoản ngân hàng để thực hiện hoàn tiền cho khách.</p>
            </div>
            
            {{-- Chọn phương thức hoàn tiền và điền số tiền trước --}}
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Phương thức hoàn tiền</label>
                    <select name="refund_method" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="cash" {{ old('refund_method') == 'cash' ? 'selected' : '' }}>Tiền mặt (Cash)</option>
                        <option value="bank_transfer" {{ old('refund_method', 'bank_transfer') == 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản ngân hàng (Bank Transfer)</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Số tiền hoàn trả (VNĐ)</label>
                    <input type="number" name="refund_amount" value="{{ old('refund_amount') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="VD: 5000000">
                </div>
            </div>

            {{-- Phần điền tài khoản ngân hàng bên dưới: chỉ hiện nếu phương thức hoàn là chuyển khoản (bank_transfer) --}}
            <div id="bankAccountDetails" class="grid gap-4 md:grid-cols-3 pt-4 border-t border-gray-100 {{ old('refund_method', 'bank_transfer') == 'bank_transfer' ? '' : 'hidden' }}">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tên ngân hàng</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="VD: Vietcombank">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Số tài khoản</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="VD: 123456789">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tên chủ tài khoản</label>
                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 uppercase" placeholder="VD: NGUYEN VAN A">
                </div>
            </div>
        </div>

        <!-- Card 4: Trạng thái & Xử lý -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-indigo-500"></i> Trạng thái & Ghi chú xử lý
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Đặt trạng thái giải quyết và phản hồi của quản trị viên.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Trạng thái yêu cầu <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full rounded-lg border {{ $errors->has('status') ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300' }} bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt (Pending)</option>
                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Đã duyệt (Approved)</option>
                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Từ chối (Rejected)</option>
                    </select>
                    @error('status') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Ghi chú phản hồi của Admin</label>
                    <textarea name="admin_note" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Nhập phản hồi hoặc hướng xử lý...">{{ old('admin_note') }}</textarea>
                    @error('admin_note') <p class="mt-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.button
                variant="primary"
                type="submit"
                title="Lưu yêu cầu"
            >
                <i class="fa-solid fa-floppy-disk"></i> Lưu yêu cầu
            </x-ui.button>
        </div>
    </form>
</div>

<!-- =========================================================================
     PHẦN SCRIPT JAVASCRIPT HỖ TRỢ GIAO DIỆN
     ========================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Tự động điền thông tin khách hàng dựa vào số điện thoại (AJAX Search)
    const phoneInput = document.querySelector('input[name="customer_phone"]');
    const nameInput = document.querySelector('input[name="customer_name"]');
    const emailInput = document.querySelector('input[name="customer_email"]');

    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            const phone = this.value.trim();
            // Chỉ bắt đầu tìm kiếm khi số điện thoại nhập từ 9 ký tự trở lên
            if (phone.length >= 9) {
                fetch(`{{ route('admin.api.customers.search-by-phone') }}?phone=${encodeURIComponent(phone)}`)
                    .then(response => response.json())
                    .then(data => {
                        // Nếu tìm thấy khách hàng đã từng mua hàng, tự động điền Tên & Email
                        if (data) {
                            if (nameInput && data.customer_name) nameInput.value = data.customer_name;
                            if (emailInput && data.customer_email) emailInput.value = data.customer_email;
                        }
                    })
                    .catch(err => console.error('Lỗi khi truy vấn thông tin khách hàng:', err));
            }
        });
    }

    // 2. Ẩn/hiện card nhập tài khoản ngân hàng dựa theo Loại yêu cầu
    const claimTypeSelect = document.querySelector('select[name="claim_type"]');
    const bankDetailsCard = document.getElementById('bankDetailsCard');
    
    if (claimTypeSelect && bankDetailsCard) {
        claimTypeSelect.addEventListener('change', function() {
            // Chỉ hiển thị card nhập thông tin ngân hàng khi loại yêu cầu là 'return' (Đổi trả hoàn tiền)
            if (this.value === 'return') {
                bankDetailsCard.classList.remove('hidden');
            } else {
                bankDetailsCard.classList.add('hidden');
            }
        });
    }

    // 3. Ẩn/hiện chi tiết tài khoản ngân hàng dựa theo Phương thức hoàn tiền
    const refundMethodSelect = document.querySelector('select[name="refund_method"]');
    const bankAccountDetails = document.getElementById('bankAccountDetails');

    if (refundMethodSelect && bankAccountDetails) {
        refundMethodSelect.addEventListener('change', function() {
            if (this.value === 'bank_transfer') {
                bankAccountDetails.classList.remove('hidden');
            } else {
                bankAccountDetails.classList.add('hidden');
            }
        });
    }
});
</script>
@endsection
