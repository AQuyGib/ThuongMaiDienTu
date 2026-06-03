@extends('admin.layouts.master')
@section('title', 'Quản lý yêu cầu bảo hành & đổi trả')
@section('page-title', 'Yêu cầu bảo hành & đổi trả')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Yêu cầu bảo hành & đổi trả</h1>
            <p class="text-slate-500 text-sm mt-0.5">Duyệt và xử lý các yêu cầu bảo hành, đổi trả, hoặc đổi mới sản phẩm từ khách hàng</p>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
        <form action="{{ route('admin.warranty-claims.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Search input --}}
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Tìm kiếm</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="IMEI, Tên, Số điện thoại..." class="pl-9 pr-3 py-2 w-full bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:border-indigo-600 focus:bg-white transition-all">
                </div>
            </div>

            {{-- Claim Type Filter --}}
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Loại yêu cầu</label>
                <select name="claim_type" class="px-3 py-2 w-full bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:border-indigo-600 focus:bg-white transition-all">
                    <option value="">Tất cả loại yêu cầu</option>
                    <option value="warranty" {{ request('claim_type') === 'warranty' ? 'selected' : '' }}>Bảo hành</option>
                    <option value="return" {{ request('claim_type') === 'return' ? 'selected' : '' }}>Đổi trả hoàn tiền</option>
                    <option value="exchange" {{ request('claim_type') === 'exchange' ? 'selected' : '' }}>Đổi máy mới/khác</option>
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Trạng thái</label>
                <select name="status" class="px-3 py-2 w-full bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:border-indigo-600 focus:bg-white transition-all">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                </select>
            </div>

            {{-- Filter Actions --}}
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

    {{-- Claims Table --}}
    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
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
                    @forelse($claims as $claim)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-900 text-xs">#{{ $claim->id }}</td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-semibold text-slate-800">{{ $claim->customer_name }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $claim->customer_phone }}</div>
                            @if($claim->customer_email)
                                <div class="text-[10px] text-slate-400">{{ $claim->customer_email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-600">
                            {{ $claim->imei_serial }}
                        </td>
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
                        <td class="px-6 py-4 max-w-xs">
                            <p class="text-xs text-slate-600 leading-relaxed" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $claim->reason }}">
                                {{ $claim->reason }}
                            </p>
                            @if($claim->media_path)
                                <div class="mt-2">
                                    @php
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
                        <td class="px-6 py-4 text-xs text-slate-500">
                            {{ $claim->created_at ? $claim->created_at->format('d/m/Y H:i') : '—' }}
                        </td>
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
                        <td class="px-6 py-4 text-center">
                            @if($claim->status === 'pending')
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="handleClaimAction({{ $claim->id }}, 'approve')" class="px-2.5 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-[10px] font-bold transition-all flex items-center gap-1">
                                        <i class="fa-solid fa-check"></i> Duyệt
                                    </button>
                                    <button type="button" onclick="handleClaimAction({{ $claim->id }}, 'reject')" class="px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-[10px] font-bold transition-all flex items-center gap-1">
                                        <i class="fa-solid fa-ban"></i> Từ chối
                                    </button>
                                </div>
                            @else
                                <div class="text-[10px] text-slate-400 text-center max-w-[150px] mx-auto italic" title="{{ $claim->admin_note }}">
                                    @if($claim->admin_note)
                                        Note: {{ Str::limit($claim->admin_note, 30) }}
                                    @else
                                        Không có ghi chú
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
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

        {{-- Pagination --}}
        @if($claims->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $claims->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Action Forms --}}
<form id="actionFormApprove" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="admin_note" id="approveAdminNote">
</form>
<form id="actionFormReject" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="admin_note" id="rejectAdminNote">
</form>

@push('scripts')
<script>
function handleClaimAction(id, action) {
    const isApprove = action === 'approve';
    const title = isApprove ? 'Duyệt yêu cầu?' : 'Từ chối yêu cầu?';
    const text = isApprove 
        ? 'Duyệt yêu cầu dịch vụ này của khách hàng.' 
        : 'Từ chối tiếp nhận yêu cầu dịch vụ này.';
    const confirmButtonText = isApprove ? 'Đồng ý duyệt' : 'Xác nhận từ chối';
    const confirmButtonColor = isApprove ? '#16a34a' : '#dc2626';

    Swal.fire({
        title: title,
        text: text,
        input: 'textarea',
        inputPlaceholder: 'Nhập ghi chú phản hồi cho khách hàng (không bắt buộc)...',
        showCancelButton: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Hủy',
        confirmButtonColor: confirmButtonColor,
        cancelButtonColor: '#94a3b8',
        inputValidator: (value) => {
            if (!isApprove && !value) {
                return 'Vui lòng nhập lý do từ chối yêu cầu!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const noteInput = result.value || '';
            const formId = isApprove ? 'actionFormApprove' : 'actionFormReject';
            const noteFieldId = isApprove ? 'approveAdminNote' : 'rejectAdminNote';
            
            const form = document.getElementById(formId);
            const noteField = document.getElementById(noteFieldId);
            
            // Set action URL
            if (isApprove) {
                form.action = `/admin/warranty-claims/${id}/approve`;
            } else {
                form.action = `/admin/warranty-claims/${id}/reject`;
            }
            
            noteField.value = noteInput;
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
