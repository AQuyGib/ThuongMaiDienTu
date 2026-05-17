@extends('admin.layouts.master')

@section('title', 'Danh sách phiếu sửa chữa')
@section('page-title', 'Danh sách phiếu sửa chữa')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Danh sách phiếu sửa chữa</h1>
            <p class="text-sm text-gray-500">Quản lý phiếu sửa chữa dịch vụ kỹ thuật và xuất hóa đơn tương ứng.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Mã phiếu</th>
                    <th class="px-4 py-3">Khách hàng</th>
                    <th class="px-4 py-3">Dịch vụ</th>
                    <th class="px-4 py-3">Phí sửa chữa</th>
                    <th class="px-4 py-3">Trạng thái hóa đơn</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($repairTickets as $repairTicket)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 font-semibold text-slate-800">#RT-{{ $repairTicket->ticket_id }}</td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-gray-900">{{ $repairTicket->customer_name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $repairTicket->customer_phone ?? '-' }}</div>
                        </td>
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
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-600 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Chưa xuất hóa đơn
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            @if ($repairTicket->invoice_no && $repairTicket->serviceInvoice)
                                <div class="inline-flex flex-wrap justify-end gap-1.5">
                                    <x-ui.button 
                                        variant="secondary" 
                                        class="!px-2.5 !py-1 !text-xs" 
                                        :href="route('admin.service-invoices.show', $repairTicket->serviceInvoice)" 
                                        title="Xem chi tiết hóa đơn"
                                    >
                                        <i class="fa-solid fa-eye text-[10px]"></i> Xem
                                    </x-ui.button>
                                    
                                    <x-ui.button 
                                        variant="secondary" 
                                        class="!px-2.5 !py-1 !text-xs" 
                                        :href="route('admin.service-invoices.print', $repairTicket->serviceInvoice)" 
                                        target="_blank" 
                                        title="Mở bản in để in nhanh"
                                    >
                                        <i class="fa-solid fa-print text-[10px]"></i> In
                                    </x-ui.button>
                                    
                                    <x-ui.button 
                                        variant="primary" 
                                        class="!px-2.5 !py-1 !text-xs" 
                                        :href="route('admin.service-invoices.pdf', $repairTicket->serviceInvoice)" 
                                        title="Tải PDF tạo ngay"
                                    >
                                        <i class="fa-solid fa-file-pdf text-[10px]"></i> Tải PDF
                                    </x-ui.button>
                                    
                                    <x-ui.button 
                                        variant="success" 
                                        class="!px-2.5 !py-1 !text-xs" 
                                        :href="route('admin.service-invoices.pdf.save', $repairTicket->serviceInvoice)" 
                                        title="Lưu PDF vào thư mục storage"
                                    >
                                        <i class="fa-solid fa-floppy-disk text-[10px]"></i> Lưu file PDF
                                    </x-ui.button>
                                    
                                    <x-ui.button 
                                        variant="info" 
                                        class="!px-2.5 !py-1 !text-xs" 
                                        :href="route('admin.service-invoices.pdf.open', $repairTicket->serviceInvoice)" 
                                        target="_blank" 
                                        title="Mở file PDF đã lưu"
                                    >
                                        <i class="fa-solid fa-folder-open text-[10px]"></i> Mở PDF đã lưu
                                    </x-ui.button>
                                    
                                    <x-ui.button 
                                        variant="warning" 
                                        class="!px-2.5 !py-1 !text-xs" 
                                        :href="route('admin.service-invoices.pdf.download', $repairTicket->serviceInvoice)" 
                                        title="Tải lại file PDF đã lưu"
                                    >
                                        <i class="fa-solid fa-download text-[10px]"></i> Tải PDF đã lưu
                                    </x-ui.button>
                                </div>
                            @else
                                <x-ui.button 
                                    variant="primary" 
                                    class="!px-3 !py-1.5 !text-xs font-bold" 
                                    :href="route('admin.repair-tickets.invoice.create', $repairTicket)"
                                >
                                    <i class="fa-solid fa-file-invoice-dollar mr-1"></i> Xuất hóa đơn
                                </x-ui.button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-500">Chưa có phiếu sửa chữa nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $repairTickets->links() }}
    </div>
</div>
@endsection
