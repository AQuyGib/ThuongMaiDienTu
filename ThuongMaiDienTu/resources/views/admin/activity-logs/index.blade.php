@extends('admin.layouts.master')
@section('title', 'Nhật ký hoạt động')
@section('page-title', 'Nhật ký hoạt động (Security Audit Logs)')

@section('content')
<div class="space-y-6">
    {{-- ═══ 1. TIÊU ĐỀ & THANH THAO TÁC NHANH ═══ --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Nhật ký hoạt động hệ thống</h1>
            <p class="text-slate-500 text-xs mt-0.5">Giám sát các thao tác nghiệp vụ, bảo mật liên kết chuỗi mật mã chống giả mạo.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            {{-- Toggle Live Feed --}}
            <button id="btn-toggle-live" class="w-full sm:w-auto px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 border border-slate-200 shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span id="live-indicator-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span id="live-indicator-dot" class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span id="live-text">Hoạt động liên tục: Bật</span>
            </button>

            <button id="btn-verify-integrity" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 shadow-sm">
                <i class="fa-solid fa-shield-halved"></i>
                Kiểm tra toàn vẹn chuỗi log
            </button>
        </div>
    </div>

    {{-- ═══ 2. BỘ LỌC TÌM KIẾM NÂNG CAO ═══ --}}
    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Sự kiện</label>
                <select name="event" class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Tất cả sự kiện</option>
                    <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Tạo mới (Created)</option>
                    <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Cập nhật (Updated)</option>
                    <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Xóa (Deleted)</option>
                    <option value="export" {{ request('event') === 'export' ? 'selected' : '' }}>Xuất file (Export)</option>
                    <option value="login" {{ request('event') === 'login' ? 'selected' : '' }}>Đăng nhập (Login)</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Người thực hiện</label>
                <input type="text" name="causer_name" value="{{ request('causer_name') }}" placeholder="Tên tài khoản..." class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Địa chỉ IP</label>
                <input type="text" name="ip_address" value="{{ request('ip_address') }}" placeholder="192.168.1.1..." class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Loại thực thể</label>
                <input type="text" name="subject_type" value="{{ request('subject_type') }}" placeholder="Product, Order..." class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Từ ngày</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Đến ngày</label>
                <div class="flex gap-2">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <button type="submit" class="px-3 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-colors">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if(request()->anyFilled(['event','causer_name','ip_address','subject_type','start_date','end_date']))
                        <a href="{{ route('admin.activity-logs.index') }}" class="px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-bold transition-colors flex items-center justify-center">
                            <i class="fa-solid fa-rotate"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ═══ 3. DANH SÁCH BẢNG LOG ═══ --}}
    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3">STT</th>
                        <th class="px-6 py-3">Sự kiện</th>
                        <th class="px-6 py-3">Người thao tác</th>
                        <th class="px-6 py-3">Hành động / Thực thể</th>
                        <th class="px-6 py-3 hidden md:table-cell">Địa chỉ IP & Client</th>
                        <th class="px-6 py-3">Thời gian</th>
                        <th class="px-6 py-3 text-center hidden lg:table-cell">Chữ ký Hash</th>
                        <th class="px-6 py-3 text-right">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-600">
                    @forelse($logs as $index => $log)
                    @php
                        $stCls = [
                            'created' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            'updated' => 'bg-blue-50 text-blue-700 border-blue-100',
                            'deleted' => 'bg-red-50 text-red-700 border-red-100',
                            'export' => 'bg-purple-50 text-purple-700 border-purple-100',
                            'login' => 'bg-indigo-50 text-indigo-700 border-indigo-100'
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors" data-log-id="{{ $log->log_id }}">
                        <td class="px-6 py-4 font-bold text-slate-400">
                            {{ ($logs->currentPage() - 1) * $logs->perPage() + $index + 1 }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase {{ $stCls[strtolower($log->event)] ?? 'bg-slate-50 text-slate-600 border-slate-100' }}">
                                {{ $log->event }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">{{ $log->causer_name ?? 'System Engine' }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ class_basename($log->causer_type) }} #{{ $log->causer_id }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">{{ $log->action }}</div>
                            @if($log->subject_type)
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <div class="font-semibold text-slate-700">{{ $log->ip_address ?? 'localhost' }}</div>
                            <div class="text-[10px] text-slate-400 truncate max-w-xs" title="{{ $log->user_agent }}">{{ $log->user_agent }}</div>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-500">
                            {{ $log->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 text-center hidden lg:table-cell">
                            <span class="font-mono text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded select-all cursor-help" title="{{ $log->hash_chain }}">
                                {{ substr($log->hash_chain, 0, 8) }}...{{ substr($log->hash_chain, -8) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="btn-view-diff px-2.5 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 font-bold rounded-lg transition-all"
                                    data-event="{{ $log->event }}"
                                    data-causer="{{ $log->causer_name }}"
                                    data-action="{{ $log->action }}"
                                    data-subject="{{ $log->subject_type ? class_basename($log->subject_type) . ' #' . $log->subject_id : 'N/A' }}"
                                    data-old="{{ json_encode($log->old_values ?? []) }}"
                                    data-new="{{ json_encode($log->new_values ?? []) }}">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-200"></i>
                            <p class="text-xs">Không tìm thấy bản ghi nhật ký hoạt động nào phù hợp.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang --}}
        @if($logs->hasPages())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ═══ MODAL DIFF VIEWER (GITHUB STYLE) ═══ --}}
<div id="modal-diff-viewer" class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm hidden items-center justify-center z-[9999] p-4 animate-in fade-in duration-200">
    <div class="bg-white rounded-2xl w-full max-w-3xl shadow-2xl border border-slate-100 flex flex-col max-h-[85vh] scale-in duration-300">
        {{-- Header Modal --}}
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-slate-900 text-sm">Chi tiết thay đổi dữ liệu</h3>
                <p class="text-[10px] text-slate-400 mt-0.5" id="diff-meta-subtitle"></p>
            </div>
            <button id="btn-close-modal" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Body Content --}}
        <div class="p-6 overflow-y-auto flex-1 space-y-4">
            <div id="diff-empty-state" class="text-center text-slate-400 text-xs py-8 hidden">
                Không phát hiện thay đổi thuộc tính nào (Dữ liệu không đổi).
            </div>
            <div class="border border-slate-200 rounded-xl overflow-x-auto shadow-sm" id="diff-table-container">
                <table class="w-full min-w-[600px] text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-2 w-1/3">Thuộc tính</th>
                            <th class="px-4 py-2 w-1/3 text-rose-700 bg-rose-50/50">Trạng thái Cũ</th>
                            <th class="px-4 py-2 w-1/3 text-emerald-700 bg-emerald-50/50">Trạng thái Mới</th>
                        </tr>
                    </thead>
                    <tbody id="diff-tbody" class="divide-y divide-slate-100 font-mono text-[11px]">
                        {{-- Dữ liệu do JS vẽ --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function initActivityLogs() {
    const modal = document.getElementById('modal-diff-viewer');
    const closeBtn = document.getElementById('btn-close-modal');
    const subtitle = document.getElementById('diff-meta-subtitle');
    const diffTbody = document.getElementById('diff-tbody');
    const diffEmpty = document.getElementById('diff-empty-state');
    const diffTable = document.getElementById('diff-table-container');

    // Từ điển bản dịch thuộc tính tiếng Việt chuyên sâu cho các Model nghiệp vụ chính
    const keyMap = {
        // Tài khoản & Nhân viên (User & Employee)
        'name': 'Tên / Tiêu đề',
        'full_name': 'Họ và tên',
        'email': 'Địa chỉ Email',
        'phone': 'Số điện thoại',
        'address': 'Địa chỉ',
        'role_id': 'Vai trò (Mã)',
        'status': 'Trạng thái',
        'comment_banned_until': 'Hạn chế bình luận đến',
        'member_tier': 'Hạng thành viên',
        'points': 'Điểm tích lũy',
        'password_hash': 'Mật khẩu băm',
        'email_verified_at': 'Thời điểm xác thực email',
        'remember_token': 'Token ghi nhớ phiên',

        // Sản phẩm & Biến thể (Product & Variant)
        'base_price': 'Giá gốc',
        'sale_price': 'Giá khuyến mãi',
        'safe_stock': 'Mức tồn kho an toàn',
        'seo_description': 'Mô tả SEO',
        'category_id': 'Mã danh mục',
        'slug': 'Đường dẫn thân thiện (Slug)',
        'description': 'Mô tả chi tiết',
        'image_path': 'Đường dẫn ảnh đại diện',
        'thumbnail_path': 'Đường dẫn ảnh thu nhỏ',
        'color': 'Màu sắc',
        'rom_capacity': 'Dung lượng bộ nhớ (ROM)',
        'extra_price': 'Chi phí phụ trội',
        'stock': 'Số lượng tồn kho',
        'is_active': 'Kích hoạt hoạt động',

        // Đánh giá & Bình luận (Review & VideoComment)
        'rating': 'Đánh giá (Số sao)',
        'content': 'Nội dung bình luận',
        'is_approved': 'Trạng thái phê duyệt',
        'report_count': 'Số lượng báo cáo vi phạm',
        'parent_id': 'Mã bình luận cha (ID)',

        // Phiếu sửa chữa (RepairTicket)
        'customer_name': 'Tên khách hàng',
        'customer_phone': 'Số điện thoại khách',
        'customer_email': 'Địa chỉ Email khách',
        'customer_address': 'Địa chỉ liên hệ khách',
        'imei_serial': 'Mã IMEI / Số Serial',
        'issue_desc': 'Mô tả lỗi thiết bị',
        'schedule_date': 'Ngày hẹn trả máy',
        'tech_id': 'Mã số kỹ thuật viên',
        'real_fee': 'Chi phí sửa chữa thực tế',
        'est_cost': 'Chi phí ước tính ban đầu',
        'ticket_id': 'Mã phiếu sửa chữa',

        // Hóa đơn dịch vụ (ServiceInvoice)
        'invoice_no': 'Số ký hiệu hóa đơn',
        'subtotal': 'Tổng tiền trước thuế (Tạm tính)',
        'vat_rate': 'Thuế suất VAT (%)',
        'tax_amount': 'Tổng tiền thuế VAT',
        'total': 'Tổng thanh toán cuối cùng',
        'invoiced_at': 'Thời điểm xuất hóa đơn',

        // Phiếu nhập kho (PurchaseOrder)
        'supplier_id': 'Mã nhà cung cấp',
        'total_cost': 'Tổng giá trị nhập kho',
        'po_id': 'Mã phiếu nhập kho',

        // Hợp đồng trả góp (Installment)
        'installment_code': 'Mã hợp đồng trả góp',
        'method': 'Phương thức trả góp',
        'partner': 'Đối tác tài chính / Ngân hàng',
        'period': 'Kỳ hạn thanh toán (Tháng)',
        'product_price': 'Giá trị sản phẩm trả góp',
        'prepay_amount': 'Số tiền trả trước',
        'loan_amount': 'Số tiền vay trả góp',
        'monthly_payment': 'Số tiền đóng hàng tháng',
        'interest_rate': 'Lãi suất áp dụng',
        'service_fee': 'Phí dịch vụ hồ sơ',
        'total_payment': 'Tổng số tiền phải trả',
        'difference_amount': 'Tổng chênh lệch trả góp',
        'customer_id_card': 'Số CCCD / CMND khách',
        'trade_in': 'Chương trình Thu cũ Đổi mới',

        // Cài đặt cấu hình (Settings)
        'setting_key': 'Từ khóa cấu hình',
        'setting_value': 'Giá trị cấu hình',

        // Chứng nhận bảo hành (Warranty)
        'warranty_id': 'Mã chứng nhận bảo hành',
        'item_id': 'Mã hiện vật kho (Item ID)',
        'warranty_status': 'Trạng thái bảo hành',
        'warranty_type': 'Loại bảo hành',
        'note': 'Ghi chú / Lưu ý',

        // Phần thưởng (RewardCatalog)
        'reward_id': 'Mã phần thưởng',
        'code': 'Mã code ưu đãi/phần thưởng',
        'reward_type': 'Loại phần thưởng',
        'reward_category': 'Danh mục phần thưởng',
        'points_cost': 'Chi phí điểm đổi thưởng',
        'discount_amount': 'Số tiền giảm giá',
        'shipping_discount_amount': 'Số tiền giảm phí vận chuyển',
        'starts_at': 'Thời điểm bắt đầu hiệu lực',
        'ends_at': 'Thời điểm kết thúc hiệu lực',
        'metadata': 'Dữ liệu cấu hình mở rộng',

        // Thông tin hệ thống & Khác
        'title': 'Tiêu đề hiển thị',
        'created_at': 'Thời điểm tạo mới',
        'updated_at': 'Thời điểm cập nhật',
    };

    // 1. Logic mở Modal và so sánh dữ liệu (Sử dụng Event Delegation)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-view-diff');
        if (btn) {
            const event = btn.getAttribute('data-event');
            const causer = btn.getAttribute('data-causer');
            const action = btn.getAttribute('data-action');
            const subject = btn.getAttribute('data-subject');
            let oldVal = JSON.parse(btn.getAttribute('data-old') || '{}');
            let newVal = JSON.parse(btn.getAttribute('data-new') || '{}');
            if (!oldVal || typeof oldVal !== 'object') oldVal = {};
            if (!newVal || typeof newVal !== 'object') newVal = {};

            subtitle.innerHTML = `
<div class="flex flex-wrap items-center gap-2 mt-1 text-[10px] font-semibold">
    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded"><strong>Người thực hiện:</strong> ${causer}</span>
    <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded"><strong>Hành động:</strong> ${action}</span>
    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded"><strong>Thực thể:</strong> ${subject}</span>
</div>`;
            
            // Vẽ bảng Diff
            diffTbody.innerHTML = '';
            const allKeys = new Set([...Object.keys(oldVal), ...Object.keys(newVal)]);
            let hasChanges = false;

            allKeys.forEach(key => {
                const oldText = oldVal[key] !== undefined ? JSON.stringify(oldVal[key]) : undefined;
                const newText = newVal[key] !== undefined ? JSON.stringify(newVal[key]) : undefined;

                if (oldText !== newText) {
                    hasChanges = true;
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50/50 transition-colors';

                    // Cột thuộc tính (Key) - hiển thị Tiếng Việt và nhỏ ở dưới là tiếng Anh
                    const tdKey = document.createElement('td');
                    tdKey.className = 'px-4 py-3 border-r border-slate-100';
                    const keyVietnamese = keyMap[key] || key;
                    if (keyMap[key]) {
                        tdKey.innerHTML = `<span class="font-bold text-slate-800 font-sans block text-[12px]">${keyVietnamese}</span><span class="text-[9px] text-slate-400 font-mono block mt-0.5">${key}</span>`;
                    } else {
                        tdKey.innerHTML = `<span class="font-bold text-slate-800 font-mono block text-[11px]">${key}</span>`;
                    }
                    tr.appendChild(tdKey);

                    // Cột Old (Red)
                    const tdOld = document.createElement('td');
                    tdOld.className = 'px-4 py-3 text-rose-600 bg-rose-50/20 border-r border-slate-100';
                    if (oldText !== undefined) {
                        tdOld.innerHTML = `<span class="line-through block whitespace-pre-wrap select-all bg-rose-100/50 px-1 py-0.5 rounded text-[11px]">- ${oldText}</span>`;
                    } else {
                        tdOld.innerHTML = '<span class="text-slate-300 italic text-[11px]">null</span>';
                    }
                    tr.appendChild(tdOld);

                    // Cột New (Green)
                    const tdNew = document.createElement('td');
                    tdNew.className = 'px-4 py-3 text-emerald-600 bg-emerald-50/20';
                    if (newText !== undefined) {
                        tdNew.innerHTML = `<span class="block whitespace-pre-wrap select-all bg-emerald-100/50 px-1 py-0.5 rounded text-[11px]">+ ${newText}</span>`;
                    } else {
                        tdNew.innerHTML = '<span class="text-slate-300 italic text-[11px]">null</span>';
                    }
                    tr.appendChild(tdNew);

                    diffTbody.appendChild(tr);
                }
            });

            if (hasChanges) {
                diffEmpty.classList.add('hidden');
                diffTable.classList.remove('hidden');
            } else {
                diffEmpty.classList.remove('hidden');
                diffTable.classList.add('hidden');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    });

    // Đóng Modal
    closeBtn.addEventListener('click', function() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });

    // 2. Logic kiểm tra toàn vẹn bằng SweetAlert2
    const verifyBtn = document.getElementById('btn-verify-integrity');
    if (verifyBtn) {
        verifyBtn.addEventListener('click', function() {
            if (typeof Swal === 'undefined') {
                alert('Vui lòng đợi hệ thống khởi tạo thông báo.');
                return;
            }

            Swal.fire({
                title: 'Đang xác minh chuỗi log...',
                text: 'Hệ thống đang đối chiếu và tính toán lại mã hash chain toàn vẹn.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('{{ route("admin.activity-logs.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Xác minh thành công!',
                        text: data.message,
                        confirmButtonText: 'Đồng ý',
                        confirmButtonColor: '#4f46e5'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Phát hiện giả mạo!',
                        text: data.message,
                        footer: `<span class="text-xs text-rose-500 font-bold font-mono">${data.details}</span>`,
                        confirmButtonText: 'Đóng',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi kiểm tra',
                    text: 'Không thể kết nối với máy chủ để thực hiện kiểm tra bảo mật.',
                    confirmButtonText: 'Đóng',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    }

    // 3. Logic Hoạt động liên tục (Live Feed Polling)
    const toggleLiveBtn = document.getElementById('btn-toggle-live');
    const liveIndicatorPing = document.getElementById('live-indicator-ping');
    const liveIndicatorDot = document.getElementById('live-indicator-dot');
    const liveText = document.getElementById('live-text');
    const tbody = document.querySelector('tbody');
    
    let isLive = true; // Mặc định bật
    let liveIntervalId = null;

    function updateLiveButtonUI() {
        if (isLive) {
            toggleLiveBtn.classList.remove('bg-slate-100', 'text-slate-500', 'border-slate-200');
            toggleLiveBtn.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
            liveIndicatorPing.classList.remove('hidden');
            liveIndicatorDot.classList.remove('bg-slate-400');
            liveIndicatorDot.classList.add('bg-emerald-500');
            liveText.textContent = 'Hoạt động liên tục: Bật';
        } else {
            toggleLiveBtn.classList.add('bg-slate-100', 'text-slate-500', 'border-slate-200');
            toggleLiveBtn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
            liveIndicatorPing.classList.add('hidden');
            liveIndicatorDot.classList.remove('bg-emerald-500');
            liveIndicatorDot.classList.add('bg-slate-400');
            liveText.textContent = 'Hoạt động liên tục: Tắt';
        }
    }

    async function pollNewLogs() {
        if (!isLive) return;
        
        // Chỉ chạy tự động cập nhật ở trang 1 để tránh lỗi hiển thị phân trang
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');
        if (page && page !== '1') return;

        try {
            const response = await fetch(window.location.href);
            if (!response.ok) return;

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newRows = doc.querySelectorAll('tbody tr[data-log-id]');
            
            if (!newRows || newRows.length === 0) return;

            // Lấy ID cao nhất hiện tại trên giao diện
            let currentMaxId = 0;
            const currentRows = tbody.querySelectorAll('tr[data-log-id]');
            currentRows.forEach(row => {
                const id = parseInt(row.getAttribute('data-log-id'));
                if (id > currentMaxId) currentMaxId = id;
            });

            // Lọc ra các dòng mới hơn ID cao nhất hiện tại
            const rowsToAdd = [];
            newRows.forEach(row => {
                const id = parseInt(row.getAttribute('data-log-id'));
                if (id > currentMaxId) {
                    rowsToAdd.push(row);
                }
            });

            if (rowsToAdd.length > 0) {
                console.log(`[Live Feed] Phát hiện ${rowsToAdd.length} hoạt động mới. Đang thêm...`);
                
                // Do chúng ta xếp giảm dần, nên ta thêm các dòng mới nhất vào đầu bảng.
                // Để dòng mới nhất lên trên cùng, ta sắp xếp rowsToAdd theo thứ tự tăng dần của log_id rồi chèn
                rowsToAdd.sort((a, b) => parseInt(a.getAttribute('data-log-id')) - parseInt(b.getAttribute('data-log-id')));

                rowsToAdd.forEach(row => {
                    // Thêm class highlight emerald
                    row.classList.add('bg-emerald-50/80', 'transition-all', 'duration-1000');
                    
                    // Chèn vào đầu tbody
                    if (tbody.firstChild) {
                        tbody.insertBefore(row, tbody.firstChild);
                    } else {
                        tbody.appendChild(row);
                    }

                    // Tự động phai màu highlight sau 3 giây
                    setTimeout(() => {
                        row.classList.remove('bg-emerald-50/80');
                    }, 3000);
                });

                // Cập nhật lại cột số thứ tự (STT) của tất cả dòng trong bảng hiện tại
                const updatedRows = tbody.querySelectorAll('tr[data-log-id]');
                updatedRows.forEach((row, index) => {
                    const sttCell = row.querySelector('td:first-child');
                    if (sttCell) sttCell.textContent = index + 1;

                    // Nếu vượt quá 20 dòng (số bản ghi 1 trang), ta xóa dòng ở dưới cùng
                    if (index >= 20) {
                        row.remove();
                    }
                });
            }
        } catch (err) {
            console.error('[Live Feed] Lỗi tự động cập nhật:', err);
        }
    }

    function startLivePolling() {
        if (liveIntervalId) clearInterval(liveIntervalId);
        liveIntervalId = setInterval(pollNewLogs, 5000); // Polling mỗi 5 giây
    }

    function stopLivePolling() {
        if (liveIntervalId) {
            clearInterval(liveIntervalId);
            liveIntervalId = null;
        }
    }

    if (toggleLiveBtn) {
        toggleLiveBtn.addEventListener('click', function() {
            isLive = !isLive;
            updateLiveButtonUI();
            if (isLive) {
                startLivePolling();
            } else {
                stopLivePolling();
            }
        });
    }

    // Khởi chạy mặc định
    updateLiveButtonUI();
    startLivePolling();
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initActivityLogs);
} else {
    initActivityLogs();
}
</script>
@endsection
