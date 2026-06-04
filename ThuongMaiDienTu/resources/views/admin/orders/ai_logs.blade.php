@extends('admin.layouts.master')
@section('title', 'Lịch sử quét đơn hàng của AI')

@section('content')
<div class="space-y-6">
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.orders.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Lịch sử làm việc của AI</h1>
                <p class="text-sm text-slate-500">Xem vết phân tích đơn hàng và lịch sử ra quyết định tự động của AI</p>
            </div>
            <button type="button" onclick="openBatchScanModal()" class="px-3 py-1.5 bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-bold rounded-xl flex items-center gap-1.5 transition border border-violet-100 shadow-sm ml-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Quét AI hàng loạt
            </button>
        </div>
    </div>

    {{-- FILTER FORM --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <form action="{{ route('admin.orders.aiLogs') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Trạng thái đánh giá</label>
                <select name="ai_status" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="approved" {{ request('ai_status') === 'approved' ? 'selected' : '' }}>An toàn (Approved)</option>
                    <option value="flagged" {{ request('ai_status') === 'flagged' ? 'selected' : '' }}>Nghi ngờ (Flagged)</option>
                    <option value="cancelled" {{ request('ai_status') === 'cancelled' ? 'selected' : '' }}>Rủi ro cao (Cancelled)</option>
                    <option value="failed" {{ request('ai_status') === 'failed' ? 'selected' : '' }}>Thất bại (Failed)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Loại kích hoạt</label>
                <select name="trigger_type" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="">Tất cả loại kích hoạt</option>
                    <option value="auto" {{ request('trigger_type') === 'auto' ? 'selected' : '' }}>Tự động quét (Auto)</option>
                    <option value="manual" {{ request('trigger_type') === 'manual' ? 'selected' : '' }}>Admin quét lại (Manual)</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl text-sm transition">
                    <i class="fa-solid fa-filter mr-1.5"></i>Lọc kết quả
                </button>
                @if(request()->anyFilled(['ai_status', 'trigger_type']))
                    <a href="{{ route('admin.orders.aiLogs') }}" class="py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-xl text-sm transition">
                        Xóa lọc
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- LOGS TABLE --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Thời gian</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Đơn hàng</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Khách hàng</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Kích hoạt</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Đánh giá AI</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Lý do nhận định</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($logs as $log)
                @php
                    $badgeColors = [
                        'approved' => 'bg-green-50 text-green-700 border-green-200',
                        'flagged'  => 'bg-amber-50 text-amber-700 border-amber-200',
                        'cancelled'=> 'bg-red-50 text-red-700 border-red-200',
                        'failed'   => 'bg-rose-50 text-rose-800 border-rose-300 border-dashed',
                    ];
                    $statusLabel = [
                        'approved' => 'An toàn',
                        'flagged'  => 'Nghi ngờ',
                        'cancelled'=> 'Rủi ro cao',
                        'failed'   => 'Lỗi API',
                    ];
                @endphp
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-indigo-600 whitespace-nowrap">
                        @if($log->order)
                            #{{ $log->order->order_code ?? $log->order->order_id }}
                        @else
                            <span class="text-slate-400 italic">Đã xóa</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($log->order)
                            <div class="text-sm font-medium text-slate-700">{{ $log->order->customer_name ?? ($log->order->user->full_name ?? 'N/A') }}</div>
                            <div class="text-xs text-slate-400">{{ $log->order->customer_phone ?? ($log->order->user->phone_number ?? 'N/A') }}</div>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        @if($log->trigger_type === 'manual')
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">
                                <i class="fa-solid fa-user-gear mr-1"></i>Admin
                            </span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-50 text-slate-600 border border-slate-100">
                                <i class="fa-solid fa-robot mr-1"></i>Hệ thống
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center gap-1.5">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg border {{ $badgeColors[$log->ai_status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ $statusLabel[$log->ai_status] ?? ucfirst($log->ai_status) }}
                            </span>
                            @if($log->ai_status !== 'failed')
                                <div class="w-20 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $log->risk_score >= 80 ? 'bg-red-500' : ($log->risk_score >= 30 ? 'bg-amber-500' : 'bg-green-500') }}" style="width: {{ $log->risk_score }}%"></div>
                                </div>
                                <span class="text-[10px] font-semibold text-slate-500">{{ $log->risk_score }}% Rủi ro</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 max-w-xs break-words">
                        {{ $log->analysis }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                        <i class="fa-solid fa-terminal text-3xl mb-3 text-slate-300"></i>
                        <p class="text-sm">Chưa có bản ghi lịch sử làm việc nào của AI.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- PAGINATION --}}
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Open Batch Scan Modal
    window.openBatchScanModal = function() {
        const today = new Date().toISOString().split('T')[0];
        
        const fiveDaysAgo = new Date();
        fiveDaysAgo.setDate(fiveDaysAgo.getDate() - 5);
        const defaultStart = fiveDaysAgo.toISOString().split('T')[0];

        Swal.fire({
            title: 'Quét AI Hàng Loạt',
            html: `
                <div class="text-left space-y-4 font-sans">
                    <p class="text-xs text-slate-500 mb-4">Chọn khoảng thời gian tạo đơn hàng để tiến hành quét phân tích rủi ro AI hàng loạt.</p>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Từ ngày</label>
                            <input type="date" id="batch_start_date" value="${defaultStart}" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Đến ngày</label>
                            <input type="date" id="batch_end_date" value="${today}" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tiêu chí đơn hàng</label>
                        <select id="batch_scan_type" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white">
                            <option value="unscanned">Chỉ quét đơn hàng chưa được AI quét</option>
                            <option value="all">Tất cả đơn hàng (bao gồm quét lại)</option>
                        </select>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-play mr-1.5"></i> Bắt đầu quét',
            cancelButtonText: 'Hủy bỏ',
            customClass: {
                confirmButton: 'bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm shadow-sm transition outline-none',
                cancelButton: 'bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold px-4 py-2 rounded-xl text-sm transition outline-none ml-2'
            },
            buttonsStyling: false,
            preConfirm: () => {
                const startDate = document.getElementById('batch_start_date').value;
                const endDate = document.getElementById('batch_end_date').value;
                const scanType = document.getElementById('batch_scan_type').value;

                if (!startDate || !endDate) {
                    Swal.showValidationMessage('Vui lòng điền đầy đủ thông tin ngày.');
                    return false;
                }
                
                if (new Date(startDate) > new Date(endDate)) {
                    Swal.showValidationMessage('Ngày bắt đầu không được lớn hơn ngày kết thúc.');
                    return false;
                }

                return { startDate, endDate, scanType };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                runBatchScan(result.value);
            }
        });
    }

    function runBatchScan(params) {
        Swal.fire({
            title: 'Đang khởi tạo...',
            text: 'Đang lấy danh sách đơn hàng cần quét...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch("{{ route('admin.orders.batchGetIds') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                start_date: params.startDate,
                end_date: params.endDate,
                scan_type: params.scanType
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.orders || data.orders.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Không tìm thấy đơn hàng',
                    text: 'Không có đơn hàng nào phù hợp với khoảng thời gian và tiêu chí đã chọn.',
                    confirmButtonText: 'Đóng',
                    customClass: {
                        confirmButton: 'bg-indigo-600 text-white font-semibold px-4 py-2 rounded-xl text-sm'
                    },
                    buttonsStyling: false
                });
                return;
            }

            processOrdersSequentially(data.orders);
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể tải danh sách đơn hàng cần quét.',
                confirmButtonText: 'Đóng',
                customClass: {
                    confirmButton: 'bg-indigo-600 text-white font-semibold px-4 py-2 rounded-xl text-sm'
                },
                buttonsStyling: false
            });
        });
    }

    async function processOrdersSequentially(orders) {
        const total = orders.length;
        let successCount = 0;
        let failedCount = 0;

        Swal.fire({
            title: 'Đang phân tích đơn hàng',
            html: `
                <div class="text-left font-sans space-y-3">
                    <div class="flex justify-between items-center text-xs font-semibold text-slate-500">
                        <span id="batch-progress-text">Đang chuẩn bị...</span>
                        <span id="batch-progress-percent">0%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden shadow-inner border border-slate-50">
                        <div id="batch-progress-bar" class="bg-indigo-600 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div id="batch-console-log" class="bg-slate-900 text-slate-200 text-[10px] p-2.5 rounded-xl h-28 overflow-y-auto font-mono whitespace-pre-wrap leading-relaxed shadow-inner">
Khởi chạy tiến trình quét hàng loạt...
                    </div>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
        });

        const progressText = document.getElementById('batch-progress-text');
        const progressPercent = document.getElementById('batch-progress-percent');
        const progressBar = document.getElementById('batch-progress-bar');
        const consoleLog = document.getElementById('batch-console-log');

        const appendLog = (msg) => {
            consoleLog.textContent += '\n' + msg;
            consoleLog.scrollTop = consoleLog.scrollHeight;
        };

        for (let i = 0; i < total; i++) {
            const order = orders[i];
            const currentIndex = i + 1;
            const percent = Math.round((i / total) * 100);

            progressText.textContent = `Đang quét đơn hàng ${currentIndex}/${total} (#${order.order_code || order.order_id})...`;
            progressPercent.textContent = `${percent}%`;
            progressBar.style.width = `${percent}%`;

            appendLog(`[${currentIndex}/${total}] Đang quét đơn #${order.order_code || order.order_id}...`);

            try {
                const response = await fetch(`/admin/orders/${order.order_id}/reanalyze`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                const result = await response.json();

                if (result.success) {
                    successCount++;
                    appendLog(`=> Đơn #${order.order_code || order.order_id}: THÀNH CÔNG (Score: ${result.ai_risk_score}%, Status: ${result.ai_status})`);
                } else {
                    failedCount++;
                    appendLog(`=> Đơn #${order.order_code || order.order_id}: THẤT BẠI (${result.message || 'Lỗi không xác định'})`);
                }
            } catch (err) {
                failedCount++;
                appendLog(`=> Đơn #${order.order_code || order.order_id}: LỖI KẾT NỐI`);
                console.error(err);
            }
        }

        progressText.textContent = `Đã hoàn thành!`;
        progressPercent.textContent = `100%`;
        progressBar.style.width = `100%`;
        progressBar.classList.remove('bg-indigo-600');
        progressBar.classList.add('bg-green-500');
        appendLog(`\n================================\nTất cả tiến trình hoàn tất!\nThành công: ${successCount}\nThất bại: ${failedCount}`);

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Hoàn thành quét hàng loạt',
                text: `Đã hoàn tất phân tích ${total} đơn hàng. Thành công: ${successCount}, Thất bại: ${failedCount}.`,
                confirmButtonText: 'Đóng & Tải lại trang',
                customClass: {
                    confirmButton: 'bg-indigo-600 text-white font-semibold px-4 py-2 rounded-xl text-sm'
                },
                buttonsStyling: false
            }).then(() => {
                window.location.reload();
            });
        }, 1500);
    }
</script>
@endpush
