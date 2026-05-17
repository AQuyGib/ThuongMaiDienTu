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
            :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M10 4v12M4 10h12\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\"/></svg>'"
        >
            Tạo hóa đơn mới
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
            <x-ui.button variant="secondary" type="submit" title="Áp dụng bộ lọc" :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M4 5h12M6 10h8M8 15h4\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\"/></svg>'">Lọc</x-ui.button>
            <x-ui.button variant="secondary" :href="route('admin.service-invoices.index')" title="Xóa toàn bộ điều kiện lọc" :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M5 5l10 10M15 5L5 15\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\"/></svg>'">Xóa lọc</x-ui.button>
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
                                <x-ui.button variant="secondary" :href="route('admin.service-invoices.show', $invoice)" title="Xem chi tiết hóa đơn" :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M1.5 10s3.25-5.5 8.5-5.5S18.5 10 18.5 10s-3.25 5.5-8.5 5.5S1.5 10 1.5 10Z\" stroke=\"currentColor\" stroke-width=\"1.6\"/><circle cx=\"10\" cy=\"10\" r=\"2.25\" stroke=\"currentColor\" stroke-width=\"1.6\"/></svg>'">Xem</x-ui.button>
                                <x-ui.button variant="secondary" :href="route('admin.service-invoices.print', $invoice)" target="_blank" title="Mở bản in để in nhanh" :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M6 6V3.8A1.8 1.8 0 0 1 7.8 2h4.4A1.8 1.8 0 0 1 14 3.8V6\" stroke=\"currentColor\" stroke-width=\"1.6\" stroke-linecap=\"round\"/><path d=\"M5 14H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1\" stroke=\"currentColor\" stroke-width=\"1.6\" stroke-linecap=\"round\"/><path d=\"M6 12h8v6H6z\" stroke=\"currentColor\" stroke-width=\"1.6\"/></svg>'">In</x-ui.button>
                                <x-ui.button variant="primary" :href="route('admin.service-invoices.pdf', $invoice)" title="Tải PDF tạo ngay" :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M10 2v9m0 0 3-3m-3 3-3-3\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/><path d=\"M4 14.5V16a1.5 1.5 0 0 0 1.5 1.5h9A1.5 1.5 0 0 0 16 16v-1.5\" stroke=\"currentColor\" stroke-width=\"1.6\" stroke-linecap=\"round\"/></svg>'">Tải PDF</x-ui.button>
                                <x-ui.button variant="success" :href="route('admin.service-invoices.pdf.save', $invoice)" title="Lưu PDF vào thư mục storage" :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M4.5 3.5h8l3 3V16a1 1 0 0 1-1 1h-10a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z\" stroke=\"currentColor\" stroke-width=\"1.6\"/><path d=\"M6 3.5v4h6v-4\" stroke=\"currentColor\" stroke-width=\"1.6\"/><path d=\"M7 10.5h6M7 13h6\" stroke=\"currentColor\" stroke-width=\"1.6\" stroke-linecap=\"round\"/></svg>'">Lưu file PDF</x-ui.button>
                                <x-ui.button variant="info" :href="route('admin.service-invoices.pdf.open', $invoice)" target="_blank" title="Mở file PDF đã lưu" :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M10 4h6v6\" stroke=\"currentColor\" stroke-width=\"1.6\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/><path d=\"M9 11l7-7\" stroke=\"currentColor\" stroke-width=\"1.6\" stroke-linecap=\"round\"/><path d=\"M5 6h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z\" stroke=\"currentColor\" stroke-width=\"1.6\"/></svg>'">Mở PDF đã lưu</x-ui.button>
                                <x-ui.button variant="warning" :href="route('admin.service-invoices.pdf.download', $invoice)" title="Tải lại file PDF đã lưu" :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M10 2v9m0 0 3-3m-3 3-3-3\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/><path d=\"M4 14h12\" stroke=\"currentColor\" stroke-width=\"1.6\" stroke-linecap=\"round\"/></svg>'">Tải PDF đã lưu</x-ui.button>
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
