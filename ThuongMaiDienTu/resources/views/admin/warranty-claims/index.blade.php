@extends('admin.layouts.master') {{-- Sử dụng khung giao diện chung (Layout Master) của hệ thống Admin --}}
@section('title', 'Quản lý yêu cầu bảo hành & đổi trả') {{-- Thiết lập tiêu đề cho tab trình duyệt --}}
@section('page-title', 'Yêu cầu bảo hành & đổi trả') {{-- Thiết lập tên trang hiển thị --}}

@section('content')
<div class="space-y-6">
    {{-- =========================================================================
         PHẦN TIÊU ĐỀ TRANG VÀ NÚT TẠO MỚI YÊU CẦU TẠI QUẦY
         ========================================================================= --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Yêu cầu bảo hành & đổi trả</h1>
            <p class="text-slate-500 text-sm mt-0.5">Duyệt và xử lý các yêu cầu bảo hành, đổi trả, hoặc đổi mới sản phẩm từ khách hàng</p>
        </div>
        {{-- Nút bấm dẫn tới trang điền thông tin để tạo trực tiếp một yêu cầu khi khách đem máy đến quầy --}}
        <x-ui.button
            variant="primary"
            :href="route('admin.warranty-claims.create')"
            title="Tạo yêu cầu tại quầy"
        >
            <i class="fa-solid fa-plus mr-1"></i> Tạo yêu cầu tại quầy
        </x-ui.button>
    </div>

    {{-- =========================================================================
         BỘ LỌC TÌM KIẾM DỮ LIỆU (FILTERS & SEARCH)
         Cho phép tìm kiếm theo từ khóa hoặc lọc theo loại yêu cầu/trạng thái xử lý
         ========================================================================= --}}
    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
        <form action="{{ route('admin.warranty-claims.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Ô nhập từ khóa tìm kiếm (IMEI, Họ tên khách, Số điện thoại) --}}
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Tìm kiếm</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="IMEI, Tên, Số điện thoại..." class="pl-9 pr-3 py-2 w-full bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:border-indigo-600 focus:bg-white transition-all">
                </div>
            </div>

            {{-- Bộ lọc theo Loại yêu cầu (Bảo hành, Đổi trả hoàn tiền, Đổi máy mới) --}}
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Loại yêu cầu</label>
                <select name="claim_type" class="px-3 py-2 w-full bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:border-indigo-600 focus:bg-white transition-all">
                    <option value="">Tất cả loại yêu cầu</option>
                    <option value="warranty" {{ request('claim_type') === 'warranty' ? 'selected' : '' }}>Bảo hành</option>
                    <option value="return" {{ request('claim_type') === 'return' ? 'selected' : '' }}>Đổi trả hoàn tiền</option>
                    <option value="exchange" {{ request('claim_type') === 'exchange' ? 'selected' : '' }}>Đổi máy mới/khác</option>
                </select>
            </div>

            {{-- Bộ lọc theo Trạng thái duyệt (Chờ duyệt, Đã duyệt, Đã từ chối) --}}
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Trạng thái</label>
                <select name="status" class="px-3 py-2 w-full bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:border-indigo-600 focus:bg-white transition-all">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                </select>
            </div>

            {{-- Các nút kích hoạt bộ lọc hoặc thiết lập lại (xóa lọc) --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all flex-1">
                    <i class="fa-solid fa-filter mr-1.5"></i> Lọc dữ liệu
                </button>
                @if(request()->anyFilled(['search', 'claim_type', 'status']))
                    <a href="{{ route('admin.warranty-claims.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-all text-center">
                        Xóa lọc
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- =========================================================================
         BẢNG DANH SÁCH CÁC YÊU CẦU DỊCH VỤ (CLAIMS TABLE)
         ========================================================================= --}}
    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                {{-- Tiêu đề cột của bảng --}}
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Khách hàng</th>
                        <th class="px-6 py-4">Thông tin IMEI</th>
                        <th class="px-6 py-4">Loại yêu cầu</th>
                        <th class="px-6 py-4">Lý do gửi</th>
                        <th class="px-6 py-4">Ngày yêu cầu</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    {{-- Duyệt qua từng dòng yêu cầu lấy từ cơ sở dữ liệu --}}
                    @forelse($claims as $claim)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        {{-- ID Yêu cầu --}}
                        <td class="px-6 py-4 font-bold text-slate-900 text-xs">#{{ $claim->id }}</td>
                        
                        {{-- Cột thông tin người gửi + Chi tiết ngân hàng hoàn trả (nếu có) --}}
                        <td class="px-6 py-4">
                            <div class="text-xs font-semibold text-slate-800">{{ $claim->customer_name }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $claim->customer_phone }}</div>
                            @if($claim->customer_email)
                                <div class="text-[10px] text-slate-400">{{ $claim->customer_email }}</div>
                            @endif
                            
                            {{-- Nếu là yêu cầu đổi trả hoàn tiền và đã khai báo thông tin ngân hàng thì hiển thị khối tài khoản --}}
                            @if($claim->claim_type === 'return' && $claim->bank_name)
                                <div class="mt-2 bg-amber-50/70 border border-amber-200/50 rounded-lg p-2 text-[10px] text-amber-800 font-medium">
                                    <div class="font-bold flex items-center gap-1 text-amber-700">
                                        <i class="fa-solid fa-building-columns"></i> Nhận hoàn tiền:
                                    </div>
                                    <div class="mt-1">N.Hàng: <span class="select-all font-semibold text-slate-800">{{ $claim->bank_name }}</span></div>
                                    <div>STK: <span class="select-all font-mono font-semibold text-slate-800">{{ $claim->bank_account_number }}</span></div>
                                    <div>Tên: <span class="select-all uppercase font-semibold text-slate-800">{{ $claim->bank_account_name }}</span></div>
                                </div>
                            @endif
                        </td>

                        {{-- Mã định danh thiết bị IMEI / Serial Number --}}
                        <td class="px-6 py-4 font-mono text-xs text-slate-600">
                            {{ $claim->imei_serial }}
                        </td>

                        {{-- Nhãn phân loại yêu cầu dịch vụ (Bảo hành / Đổi trả hoàn tiền / Đổi máy mới) --}}
                        <td class="px-6 py-4">
                            @if($claim->claim_type === 'warranty')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    <i class="fa-solid fa-shield mr-1"></i> Bảo hành
                                </span>
                            @elseif($claim->claim_type === 'return')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                    <i class="fa-solid fa-rotate-left mr-1"></i> Đổi trả hoàn tiền
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                    <i class="fa-solid fa-rotate mr-1"></i> Đổi máy khác
                                </span>
                            @endif
                        </td>

                        {{-- Lý do khách hàng gửi yêu cầu kèm tệp tin đính kèm (Ảnh/Video) --}}
                        <td class="px-6 py-4 max-w-xs">
                            <p class="text-xs text-slate-600 leading-relaxed" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $claim->reason }}">
                                {{ $claim->reason }}
                            </p>
                            @if($claim->media_path)
                                <div class="mt-2">
                                    @php
                                        // Kiểm tra đuôi file để phân loại hiển thị nhãn Xem Video hay Xem Ảnh
                                        $extension = pathinfo($claim->media_path, PATHINFO_EXTENSION);
                                        $isVideo = in_array(strtolower($extension), ['mp4', 'mov', 'avi', 'mkv', 'webm', '3gp', 'm4v']);
                                    @endphp
                                    @if($isVideo)
                                        <a href="{{ asset($claim->media_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 transition-colors">
                                            <i class="fa-solid fa-video"></i> Xem video minh họa
                                        </a>
                                    @else
                                        <a href="{{ asset($claim->media_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 transition-colors">
                                            <i class="fa-solid fa-image"></i> Xem ảnh minh họa
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </td>
                        
                        {{-- Ngày giờ gửi yêu cầu ban đầu --}}
                        <td class="px-6 py-4 text-xs text-slate-500">
                            {{ $claim->created_at ? $claim->created_at->format('d/m/Y H:i') : '—' }}
                        </td>

                        {{-- Cột hiển thị nhãn Trạng thái xử lý (Chờ duyệt / Đã duyệt / Từ chối) --}}
                        <td class="px-6 py-4 text-center">
                            @if($claim->status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-yellow-50 text-yellow-700 border border-yellow-100">
                                    Chờ duyệt
                                </span>
                            @elseif($claim->status === 'approved')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-100">
                                    Đã duyệt
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-100">
                                    Đã từ chối
                                </span>
                            @endif
                        </td>

                        {{-- Cột các nút hành động (Duyệt nhanh, Từ chối nhanh, Sửa chi tiết, Xóa) --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col gap-1.5 items-center justify-center">
                                @if($claim->status === 'pending')
                                    <div class="flex items-center gap-1">
                                        {{-- Nút duyệt nhanh hiển thị hộp thoại điền số tiền và ghi chú --}}
                                        <button type="button" 
                                            onclick="handleClaimAction({{ $claim->id }}, 'approve', '{{ $claim->claim_type }}', this)" 
                                            data-bank-name="{{ $claim->bank_name }}" 
                                            data-bank-number="{{ $claim->bank_account_number }}" 
                                            data-bank-user="{{ $claim->bank_account_name }}"
                                            data-refund-method="{{ $claim->refund_method }}"
                                            class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-[10px] font-bold transition-all flex items-center gap-1" 
                                            title="Phê duyệt nhanh"
                                        >
                                            <i class="fa-solid fa-check"></i> Duyệt
                                        </button>
                                        {{-- Nút từ chối nhanh yêu cầu dịch vụ --}}
                                        <button type="button" onclick="handleClaimAction({{ $claim->id }}, 'reject', '{{ $claim->claim_type }}')" class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-[10px] font-bold transition-all flex items-center gap-1" title="Từ chối nhanh">
                                            <i class="fa-solid fa-ban"></i> Từ chối
                                        </button>
                                    </div>
                                @else
                                    {{-- Nếu đã duyệt hoặc từ chối thì chỉ hiển thị ghi chú tóm tắt --}}
                                    <div class="text-[10px] text-slate-400 text-center max-w-[150px] italic mb-1" title="{{ $claim->admin_note }}">
                                        @if($claim->admin_note)
                                            Note: {{ Str::limit($claim->admin_note, 30) }}
                                        @else
                                            Không có ghi chú
                                        @endif
                                    </div>
                                @endif

                                <div class="flex items-center gap-1">
                                    {{-- Nút điều hướng sang trang Sửa thông tin chi tiết --}}
                                    <x-ui.button 
                                        variant="warning" 
                                        class="!px-2 !py-1 !text-[10px] font-bold" 
                                        :href="route('admin.warranty-claims.edit', $claim->id)" 
                                        title="Sửa yêu cầu"
                                    >
                                        <i class="fa-solid fa-pen text-[9px]"></i> Sửa
                                    </x-ui.button>
                                    {{-- Nút hiển thị popup xác nhận xóa bản ghi khỏi hệ thống --}}
                                    <button type="button" 
                                        onclick="openDeleteModal('{{ route('admin.warranty-claims.destroy', $claim->id) }}', '#{{ $claim->id }}')" 
                                        class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded text-[10px] font-bold border border-rose-200 transition-all flex items-center gap-1"
                                        title="Xóa yêu cầu"
                                    >
                                        <i class="fa-solid fa-trash text-[9px]"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    {{-- Trường hợp danh sách trống, không có yêu cầu nào --}}
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-inbox text-2xl mb-2 text-slate-300 block"></i>
                            Không tìm thấy yêu cầu dịch vụ nào phù hợp.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang danh sách dữ liệu --}}
        @if($claims->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $claims->links() }}
        </div>
        @endif
    </div>
</div>

{{-- =========================================================================
     POPUP HỘP THOẠI XÁC NHẬN XÓA YÊU CẦU (DELETE CONFIRMATION MODAL)
     ========================================================================= --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    {{-- Lớp nền mờ tối click vào sẽ tự đóng popup --}}
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex flex-col items-center text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 mb-4">
                <i class="fa-solid fa-triangle-exclamation text-2xl text-red-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Xác nhận xóa</h3>
            <p class="mt-2 text-sm text-gray-500">
                Bạn có chắc chắn muốn xóa yêu cầu <span id="deleteClaimCode" class="font-semibold text-gray-700"></span>?
                <br>Hành động này không thể hoàn tác.
            </p>
        </div>
        {{-- Form thực hiện request DELETE ẩn --}}
        <form id="deleteForm" method="POST" class="mt-6 flex gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                Hủy bỏ
            </button>
            <button type="submit" class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition">
                <i class="fa-solid fa-trash mr-1"></i> Xóa yêu cầu
            </button>
        </form>
    </div>
</div>

{{-- =========================================================================
     CÁC FORM GỬI REQUEST DUYỆT HOẶC TỪ CHỐI ẨN (ACTION FORMS)
     Giúp gửi dữ liệu an toàn tránh bị chỉnh sửa client-side bypass F12
     ========================================================================= --}}
<form id="actionFormApprove" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="admin_note" id="approveAdminNote">
    <input type="hidden" name="refund_amount" id="approveRefundAmount">
    <input type="hidden" name="refund_method" id="approveRefundMethod">
</form>
<form id="actionFormReject" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="admin_note" id="rejectAdminNote">
</form>

@push('scripts')
<script>
// Mở hộp thoại xóa và gán URL của Laravel route vào action của Form
function openDeleteModal(actionUrl, claimCode) {
    document.getElementById('deleteForm').action = actionUrl;
    document.getElementById('deleteClaimCode').textContent = claimCode;
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Đóng hộp thoại xóa
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

/**
 * Hàm xử lý phê duyệt hoặc từ chối các yêu cầu bảo hành/đổi trả
 * 
 * @param {number} id - ID của yêu cầu (warranty_claims.id)
 * @param {string} action - Hành động xử lý: 'approve' (duyệt) hoặc 'reject' (từ chối)
 * @param {string} claimType - Loại yêu cầu: 'warranty' (bảo hành), 'return' (đổi trả hoàn tiền), 'exchange' (đổi máy mới)
 * @param {HTMLElement} el - Element nút bấm kích hoạt sự kiện để lấy thông tin tài khoản khách hàng
 */
function handleClaimAction(id, action, claimType, el) {
    // Biến cờ xác định đây có phải hành động duyệt hay không
    const isApprove = action === 'approve';

    // =========================================================================
    // LUỒNG 1: Duyệt yêu cầu đổi trả hoàn tiền (claimType === 'return')
    // =========================================================================
    if (isApprove && claimType === 'return') {
        // Lấy thông tin tài khoản ngân hàng của khách được truyền từ thuộc tính của nút bấm
        const bankName = el ? el.getAttribute('data-bank-name') : '';
        const bankNumber = el ? el.getAttribute('data-bank-number') : '';
        const bankUser = el ? el.getAttribute('data-bank-user') : '';
        
        // Lấy phương thức hoàn tiền mà khách đã chọn lúc gửi yêu cầu (mặc định là bank_transfer)
        let refundMethod = el ? el.getAttribute('data-refund-method') : 'bank_transfer';
        // Đảm bảo giá trị luôn hợp lệ (fallback nếu rỗng hoặc null)
        if (refundMethod !== 'cash' && refundMethod !== 'bank_transfer') {
            refundMethod = 'bank_transfer';
        }

        // Xây dựng đoạn mã HTML hiển thị thông tin ngân hàng của khách (chỉ hiện khi chuyển khoản và có dữ liệu)
        let bankInfoHtml = '';
        if (refundMethod === 'bank_transfer' && bankName) {
            bankInfoHtml = `
                <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:10px; margin-bottom:12px; font-size:11px; color:#92400e; line-height:1.5;">
                    <div style="font-weight:700; margin-bottom:4px; color:#b45309;"><i class="fa-solid fa-building-columns"></i> Tài khoản nhận hoàn tiền của khách:</div>
                    <div>Ngân hàng: <strong class="select-all">${bankName}</strong></div>
                    <div>Số tài khoản: <strong class="select-all">${bankNumber}</strong></div>
                    <div>Tên tài khoản: <strong class="select-all" style="text-transform: uppercase;">${bankUser}</strong></div>
                </div>
            `;
        }

        // Định dạng nhãn và màu sắc hiển thị phương thức hoàn tiền dựa trên lựa chọn của khách
        const refundMethodLabel = refundMethod === 'cash' ? 'Tiền mặt (Hoàn tại quầy)' : 'Chuyển khoản ngân hàng';
        const refundMethodColor = refundMethod === 'cash' ? '#16a34a' : '#2563eb';

        // Mở hộp thoại thông minh SweetAlert2 để nhập số tiền hoàn và ghi chú
        Swal.fire({
            title: 'Duyệt & Hoàn tiền',
            html: `
                <div style="text-align:left;">
                    ${bankInfoHtml}
                    <!-- Hiển thị phương thức hoàn tiền của khách dưới dạng nhãn tĩnh (không cho phép sửa đổi) -->
                    <div style="margin-bottom:12px; padding:8px 12px; background:#f8fafc; border-left:4px solid ${refundMethodColor}; border-radius:0 6px 6px 0;">
                        <span style="font-size:12px; color:#64748b; font-weight:600; display:block;">Yêu cầu hoàn tiền bằng:</span>
                        <strong style="font-size:14px; color:#1e293b;">${refundMethodLabel}</strong>
                        <input type="hidden" id="swalRM" value="${refundMethod}">
                    </div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:6px;">Số tiền hoàn trả (VNĐ)</label>
                    <input type="number" id="swalRA" class="swal2-input" placeholder="VD: 15000000" min="0" style="margin:0 0 12px; width:100%; box-sizing:border-box;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:6px;">Ghi chú</label>
                    <textarea id="swalAN" class="swal2-textarea" placeholder="Ghi chú cho khách..." style="margin:0; width:100%; box-sizing:border-box;"></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Xác nhận hoàn tiền',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#94a3b8',
            preConfirm: () => ({
                amount: parseInt(document.getElementById('swalRA').value) || 0,
                method: document.getElementById('swalRM').value,
                note:   document.getElementById('swalAN').value,
            })
        }).then((result) => {
            // Khi Admin xác nhận hoàn tiền thành công
            if (result.isConfirmed) {
                const form = document.getElementById('actionFormApprove');
                form.action = `/admin/warranty-claims/${id}/approve`;
                document.getElementById('approveAdminNote').value   = result.value.note;
                document.getElementById('approveRefundAmount').value = result.value.amount;
                document.getElementById('approveRefundMethod').value = result.value.method;
                form.submit(); // Gửi form phê duyệt lên backend
            }
        });
        return;
    }

    // =========================================================================
    // LUỒNG 2: Duyệt yêu cầu đổi máy mới (claimType === 'exchange')
    // =========================================================================
    if (isApprove && claimType === 'exchange') {
        Swal.fire({
            title: 'Duyệt Đổi Máy Mới',
            html: `<p style="font-size:13px;color:#64748b;margin-bottom:12px;">Thu hồi máy cũ và chuẩn bị máy thay thế. Không hoàn tiền.</p><textarea id="swalANEx" class="swal2-textarea" placeholder="Ghi chú: máy thay thế, thời gian xử lý..." style="width:100%;"></textarea>`,
            showCancelButton: true,
            confirmButtonText: 'Xác nhận đổi máy',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: '#94a3b8',
            preConfirm: () => ({ note: document.getElementById('swalANEx').value })
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('actionFormApprove');
                form.action = `/admin/warranty-claims/${id}/approve`;
                document.getElementById('approveAdminNote').value   = result.value.note;
                document.getElementById('approveRefundAmount').value = '';
                document.getElementById('approveRefundMethod').value = '';
                form.submit(); // Gửi form lên backend
            }
        });
        return;
    }

    // =========================================================================
    // LUỒNG 3: Duyệt yêu cầu bảo hành thông thường (warranty) HOẶC Từ chối (reject)
    // =========================================================================
    const title = isApprove ? 'Duyệt yêu cầu bảo hành' : 'Từ chối yêu cầu';
    const text  = isApprove ? 'Xác nhận tiếp nhận bảo hành sửa chữa.' : 'Từ chối yêu cầu dịch vụ này.';
    const confirmButtonColor = isApprove ? '#16a34a' : '#dc2626';
    const confirmButtonText  = isApprove ? 'Đồng ý duyệt' : 'Xác nhận từ chối';

    Swal.fire({
        title, text, input: 'textarea',
        inputPlaceholder: isApprove ? 'Ghi chú cho khách (không bắt buộc)...' : 'Nhập lý do từ chối...',
        showCancelButton: true, confirmButtonText, cancelButtonText: 'Hủy', confirmButtonColor, cancelButtonColor: '#94a3b8',
        inputValidator: (value) => { 
            // Nếu từ chối yêu cầu thì bắt buộc admin phải nhập lý do giải thích
            if (!isApprove && !value) return 'Vui lòng nhập lý do từ chối!'; 
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Xác định form ẩn nào sẽ gửi đi
            const formId      = isApprove ? 'actionFormApprove' : 'actionFormReject';
            const noteFieldId = isApprove ? 'approveAdminNote'  : 'rejectAdminNote';
            
            const form = document.getElementById(formId);
            document.getElementById(noteFieldId).value = result.value || '';
            
            // Thiết lập link gửi request phù hợp
            form.action = isApprove
                ? `/admin/warranty-claims/${id}/approve`
                : `/admin/warranty-claims/${id}/reject`;
            form.submit(); // Gửi form xử lý lên hệ thống
        }
    });
}

</script>
@endpush
@endsection
