@php
    // KHỞI TẠO CÁC BIẾN CẤU HÌNH BỘ LỌC THÔNG BÁO (NHẬN TỪ CONTROLLER HOẶC VIEW CHA)
    // $filters: mảng lưu trữ giá trị lọc hiện tại (type, read, recipient, from, to)
    $filters = $filters ?? [];
    // $typeOptions: danh sách các option loại thông báo (VD: hệ thống, đơn hàng, khuyến mãi,...)
    $typeOptions = $typeOptions ?? [];
    // $showRecipient: cấu hình ẩn/hiện bộ lọc theo thông tin người nhận (dành cho Admin)
    $showRecipient = $showRecipient ?? false;
    // $showDateRange: cấu hình ẩn/hiện bộ lọc khoảng thời gian từ ngày - đến ngày
    $showDateRange = $showDateRange ?? false;
@endphp

<!-- Container chính của bộ lọc thông báo với kiểu thiết kế bo tròn nhẹ và viền nhạt -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
    <!-- Form thực hiện truy vấn lọc theo phương thức GET (gửi trực tiếp tham số lên URL) -->
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <!-- 1. Bộ lọc phân loại thông báo (Type) -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Lọc theo loại</label>
            <select name="type" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả</option>
                @foreach($typeOptions as $value => $label)
                    <!-- Giữ lại trạng thái đã chọn (selected) ứng với giá trị lọc hiện tại -->
                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <!-- 2. Bộ lọc trạng thái đọc (Chưa đọc / Đã đọc) -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Trạng thái đọc</label>
            <select name="read" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả</option>
                <option value="unread" @selected(($filters['read'] ?? '') === 'unread')>Chưa đọc</option>
                <option value="read" @selected(($filters['read'] ?? '') === 'read')>Đã đọc</option>
            </select>
        </div>

        <!-- 3. Bộ lọc theo thông tin người nhận (Chỉ hiển thị khi biến $showRecipient = true) -->
        @if($showRecipient)
            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Người nhận</label>
                <input type="text" name="recipient" value="{{ $filters['recipient'] ?? '' }}" placeholder="Tên / email người nhận" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        @endif

        <!-- 4. Bộ lọc khoảng thời gian (Chỉ hiển thị khi biến $showDateRange = true) -->
        @if($showDateRange)
            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Từ ngày - đến ngày</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        @endif

        <!-- 5. Khối chứa các nút hành động (Nút Áp dụng Lọc và Nút Xóa Lọc để reset) -->
        <div class="flex gap-3 md:col-span-4">
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition">Lọc</button>
            <!-- Nút reset lọc: chuyển hướng người dùng về trang hiện tại không kèm theo query string -->
            <a href="{{ $resetUrl ?? url()->current() }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Xóa lọc</a>
        </div>
    </form>
</div>
