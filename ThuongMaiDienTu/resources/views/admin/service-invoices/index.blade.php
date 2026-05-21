@extends('admin.layouts.master')

@section('title', 'Hóa đơn dịch vụ')
@section('page-title', 'Hóa đơn dịch vụ')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Hóa đơn dịch vụ</h1>
            <p class="text-sm text-gray-500">Quản lý, lọc và xuất hóa đơn dịch vụ.</p>
        </div>
    </div>

    <form method="GET" class="grid gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-5">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Mã hóa đơn</label>
            <input type="text" name="invoice_no" value="{{ request('invoice_no') }}" placeholder="Ví dụ: INV-..." class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Trạng thái</label>
            <select name="status" class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả</option>
                <option value="draft" @selected(request('status') === 'draft')>Nháp</option>
                <option value="issued" @selected(request('status') === 'issued')>Đã phát hành</option>
                <option value="paid" @selected(request('status') === 'paid')>Đã thanh toán</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Đã hủy</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Từ ngày</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Đến ngày</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="flex items-end gap-2">
            <x-ui.button variant="secondary" type="submit" title="Áp dụng bộ lọc">
                <i class="fa-solid fa-filter"></i> Lọc
            </x-ui.button>
            <x-ui.button variant="secondary" :href="route('admin.service-invoices.index')" title="Xóa toàn bộ điều kiện lọc">
                <i class="fa-solid fa-filter-circle-xmark"></i> Xóa lọc
            </x-ui.button>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Mã hóa đơn</th>
                    <th class="px-4 py-3">Khách hàng</th>
                    <th class="px-4 py-3">Dịch vụ</th>
                    <th class="px-4 py-3">Tổng tiền</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3">Ngày xuất</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 font-medium text-gray-900">{{ $invoice->invoice_no }}</td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-gray-900">{{ $invoice->customer_name }}</div>
                            <div class="text-sm text-gray-500">{{ $invoice->customer_phone ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4 text-gray-700">{{ $invoice->service_name }}</td>
                        <td class="px-4 py-4 font-semibold text-gray-900">{{ number_format($invoice->total_amount, 0, ',', '.') }} đ</td>
                        <td class="px-4 py-4"><x-ui.status-badge :status="$invoice->status" /></td>
                        <td class="px-4 py-4 text-gray-600">{{ optional($invoice->issued_date)->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <x-ui.button 
                                    variant="primary" 
                                    class="!px-2.5 !py-1.5 !text-xs" 
                                    :href="route('admin.service-invoices.show', $invoice)" 
                                    title="Xem chi tiết"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </x-ui.button>
                                
                                <x-ui.button 
                                    variant="warning" 
                                    class="!px-2.5 !py-1.5 !text-xs" 
                                    :href="route('admin.service-invoices.edit', $invoice)" 
                                    title="Chỉnh sửa"
                                >
                                    <i class="fa-solid fa-pen"></i>
                                </x-ui.button>
                                
                                <x-ui.button 
                                    variant="secondary" 
                                    class="!px-2.5 !py-1.5 !text-xs" 
                                    :href="route('admin.service-invoices.print', $invoice)" 
                                    target="_blank" 
                                    title="In hóa đơn"
                                >
                                    <i class="fa-solid fa-print"></i>
                                </x-ui.button>
                                
                                <x-ui.button 
                                    variant="info" 
                                    class="!px-2.5 !py-1.5 !text-xs" 
                                    :href="route('admin.service-invoices.pdf.open', $invoice)" 
                                    target="_blank" 
                                    title="Xem PDF"
                                >
                                    <i class="fa-solid fa-file-pdf"></i>
                                </x-ui.button>
                                
                                <x-ui.button 
                                    variant="success" 
                                    class="!px-2.5 !py-1.5 !text-xs" 
                                    :href="route('admin.service-invoices.pdf.download', $invoice)" 
                                    title="Tải PDF"
                                >
                                    <i class="fa-solid fa-download"></i>
                                </x-ui.button>
                                
                                <button 
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition"
                                    title="Xóa hóa đơn"
                                    onclick="openDeleteModal('{{ route('admin.service-invoices.destroy', $invoice) }}', '{{ $invoice->invoice_no }}')"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-500">Chưa có hóa đơn nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $invoices->links() }}
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
                    Bạn có chắc chắn muốn xóa hóa đơn <span id="deleteInvoiceCode" class="font-semibold text-gray-700"></span>?
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
                    <i class="fa-solid fa-trash mr-1"></i> Xóa hóa đơn
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal(actionUrl, invoiceCode) {
    document.getElementById('deleteForm').action = actionUrl;
    document.getElementById('deleteInvoiceCode').textContent = invoiceCode;
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
