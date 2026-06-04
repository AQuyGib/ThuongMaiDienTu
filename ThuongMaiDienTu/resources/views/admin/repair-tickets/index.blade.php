@extends('admin.layouts.master')

@section('title', 'Danh sách phiếu sửa chữa')
@section('page-title', 'Danh sách phiếu sửa chữa')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Danh sách phiếu sửa chữa</h1>
            <p class="text-sm text-gray-500">Quản lý phiếu sửa chữa và xuất hóa đơn dịch vụ tương ứng.</p>
        </div>
        <x-ui.button
            variant="primary"
            :href="route('admin.repair-tickets.create')"
            title="Tạo phiếu sửa chữa"
        >
            <i class="fa-solid fa-plus mr-1"></i> Tạo phiếu sửa chữa
        </x-ui.button>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Mã phiếu</th>
                    <th class="px-4 py-3">Khách hàng</th>
                    <th class="px-4 py-3">IMEI / Serial</th>
                    <th class="px-4 py-3">Dịch vụ</th>
                    <th class="px-4 py-3">Phí sửa chữa</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($repairTickets as $repairTicket)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 font-semibold text-slate-800">
                            <div>#RT-{{ $repairTicket->ticket_id }}</div>
                            @if ($repairTicket->ai_diagnosed)
                                <span class="inline-flex items-center gap-1 rounded bg-purple-50 px-1.5 py-0.5 text-[10px] font-bold text-purple-700 border border-purple-200 mt-1 shadow-2xs">
                                    <i class="fa-solid fa-robot text-[8px]"></i> AI Chẩn Đoán
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-gray-900">{{ $repairTicket->customer_name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $repairTicket->customer_phone ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600 font-mono font-semibold">{{ $repairTicket->imei_serial ?? '-' }}</td>
                        <td class="px-4 py-4 text-gray-700 font-medium">{{ $repairTicket->service_name ?? '-' }}</td>
                        <td class="px-4 py-4 font-semibold text-indigo-600">
                            {{ number_format($repairTicket->service_fee ?? 0, 0, ',', '.') }} đ
                        </td>
                        <td class="px-4 py-4">
                            @if ($repairTicket->invoice_no)
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Đã xuất hóa đơn
                                    </span>
                                    <div class="text-[11px] font-mono text-slate-500">{{ $repairTicket->invoice_no }}</div>
                                </div>
                            @elseif ($repairTicket->status === 'Done')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 border border-blue-200 shadow-sm">
                                    <i class="fa-solid fa-circle-check text-blue-500"></i> Hoàn thành
                                </span>
                            @elseif ($repairTicket->status === 'Checking')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 border border-sky-200 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> Kiểm tra & Báo giá
                                </span>
                            @elseif ($repairTicket->status === 'Under_Repair')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 border border-indigo-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Đang sửa chữa
                                </span>
                            @elseif ($repairTicket->status === 'Waiting_Parts')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Chờ linh kiện
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-600 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Đã tiếp nhận
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="inline-flex flex-wrap justify-end gap-1.5">
                                <x-ui.button 
                                    variant="warning" 
                                    class="!px-2.5 !py-1 !text-xs font-bold" 
                                    :href="route('admin.repair-tickets.edit', $repairTicket)" 
                                    title="Sửa phiếu sửa chữa"
                                >
                                    <i class="fa-solid fa-pen text-[10px]"></i> Sửa
                                </x-ui.button>
                                
                                <button 
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition"
                                    title="Xóa phiếu sửa chữa"
                                    onclick="openDeleteModal('{{ route('admin.repair-tickets.destroy', $repairTicket) }}', '#RT-{{ $repairTicket->ticket_id }}')"
                                >
                                    <i class="fa-solid fa-trash text-[10px]"></i> Xóa
                                </button>

                                @if ($repairTicket->invoice_no && $repairTicket->serviceInvoice)
                                    <x-ui.button 
                                        variant="secondary" 
                                        class="!px-2.5 !py-1 !text-xs" 
                                        :href="route('admin.service-invoices.show', $repairTicket->serviceInvoice)" 
                                        title="Xem chi tiết hóa đơn"
                                    >
                                        <i class="fa-solid fa-eye text-[10px]"></i> Xem HD
                                    </x-ui.button>
                                @elseif ($repairTicket->status === 'Done')
                                    <x-ui.button 
                                        variant="primary" 
                                        class="!px-2.5 !py-1 !text-xs font-bold" 
                                        :href="route('admin.repair-tickets.invoice.create', $repairTicket)"
                                        title="Xuất hóa đơn"
                                    >
                                        <i class="fa-solid fa-file-invoice-dollar text-[10px]"></i> Xuất HD
                                    </x-ui.button>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-2 py-1 text-[11px] font-medium text-amber-700">
                                        <i class="fa-solid fa-clock text-[9px]"></i> Chờ hoàn thành
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-500">Chưa có phiếu sửa chữa nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $repairTickets->links() }}
    </div>

    {{-- Delete confirmation modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
        <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex flex-col items-center text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-2xl text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Xác nhận xóa</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Bạn có chắc chắn muốn xóa phiếu <span id="deleteTicketCode" class="font-semibold text-gray-700"></span>?
                    <br>Hành động này không thể hoàn tác.
                </p>
            </div>
            <form id="deleteForm" method="POST" class="mt-6 flex gap-3">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()" class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Hủy bỏ
                </button>
                <button type="submit" class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition">
                    <i class="fa-solid fa-trash mr-1"></i> Xóa phiếu
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal(actionUrl, ticketCode) {
    document.getElementById('deleteForm').action = actionUrl;
    document.getElementById('deleteTicketCode').textContent = ticketCode;
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
