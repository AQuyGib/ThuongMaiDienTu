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
        <x-ui.button
            variant="primary"
            :href="route('admin.service-invoices.create')"
            title="Tạo hóa đơn dịch vụ mới"
        >
            <i class="fa-solid fa-plus"></i> Tạo hóa đơn mới
        </x-ui.button>
    </div>

    <form method="GET" class="grid gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Trạng thái</label>
            <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="">Tất cả</option>
                <option value="draft" @selected(request('status') === 'draft')>Nháp</option>
                <option value="issued" @selected(request('status') === 'issued')>Đã phát hành</option>
                <option value="paid" @selected(request('status') === 'paid')>Đã thanh toán</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Đã hủy</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Từ ngày</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Đến ngày</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full rounded-lg border-gray-300 text-sm">
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
                            <div class="inline-flex flex-wrap justify-end gap-2">
                                <x-ui.button variant="secondary" :href="route('admin.service-invoices.show', $invoice)" title="Xem chi tiết hóa đơn">
                                    <i class="fa-solid fa-eye"></i> Xem
                                </x-ui.button>
                                <x-ui.button variant="secondary" :href="route('admin.service-invoices.print', $invoice)" target="_blank" title="Mở bản in để in nhanh">
                                    <i class="fa-solid fa-print"></i> In
                                </x-ui.button>
                                <x-ui.button variant="primary" :href="route('admin.service-invoices.pdf', $invoice)" title="Tải PDF tạo ngay">
                                    <i class="fa-solid fa-file-pdf"></i> Tải PDF
                                </x-ui.button>
                                <x-ui.button variant="success" :href="route('admin.service-invoices.pdf.save', $invoice)" title="Lưu PDF vào thư mục storage">
                                    <i class="fa-solid fa-floppy-disk"></i> Lưu file PDF
                                </x-ui.button>
                                <x-ui.button variant="info" :href="route('admin.service-invoices.pdf.open', $invoice)" target="_blank" title="Mở file PDF đã lưu">
                                    <i class="fa-solid fa-folder-open"></i> Mở PDF đã lưu
                                </x-ui.button>
                                <x-ui.button variant="warning" :href="route('admin.service-invoices.pdf.download', $invoice)" title="Tải lại file PDF đã lưu">
                                    <i class="fa-solid fa-download"></i> Tải PDF đã lưu
                                </x-ui.button>
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
</div>
@endsection
