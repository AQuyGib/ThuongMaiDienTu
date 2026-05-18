@extends('admin.layouts.master')

@section('title', 'Chi tiết hóa đơn')
@section('page-title', 'Chi tiết hóa đơn')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Chi tiết hóa đơn</h1>
            <p class="text-sm text-gray-500">Thông tin hóa đơn dịch vụ và trạng thái thanh toán.</p>
        </div>
        <div class="inline-flex flex-wrap gap-2">
            <x-ui.button
                variant="secondary"
                :href="route('admin.service-invoices.index')"
                title="Quay lại danh sách"
            >
                <i class="fa-solid fa-chevron-left"></i> Quay lại
            </x-ui.button>
            <x-ui.button
                variant="secondary"
                :href="route('admin.service-invoices.print', $serviceInvoice)"
                target="_blank"
                title="Mở bản in để in nhanh"
            >
                <i class="fa-solid fa-print"></i> In
            </x-ui.button>
            <x-ui.button
                variant="info"
                :href="route('admin.service-invoices.pdf.open', $serviceInvoice)"
                target="_blank"
                title="Xem file PDF trực tiếp"
            >
                <i class="fa-solid fa-folder-open"></i> Mở PDF
            </x-ui.button>
            <x-ui.button
                variant="success"
                :href="route('admin.service-invoices.pdf.download', $serviceInvoice)"
                title="Tải file PDF về máy"
            >
                <i class="fa-solid fa-download"></i> Tải PDF
            </x-ui.button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Thông tin hóa đơn</h2>
                    <p class="text-sm text-gray-500">Mã, ngày xuất và tổng tiền.</p>
                </div>
                <x-ui.status-badge :status="$serviceInvoice->status" />
            </div>

            <dl class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-sm text-slate-500">Mã hóa đơn</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-900">{{ $serviceInvoice->invoice_no }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-sm text-slate-500">Ngày xuất</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-900">{{ optional($serviceInvoice->issued_date)->format('d/m/Y') ?? '-' }}</dd>
                </div>
                <div class="rounded-lg bg-indigo-50 p-4 md:col-span-2">
                    <dt class="text-sm text-indigo-700">Tổng tiền</dt>
                    <dd class="mt-1 text-2xl font-bold text-indigo-900">{{ number_format($serviceInvoice->total_amount, 0, ',', '.') }} đ</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Khách hàng & dịch vụ</h2>
            <div class="mt-4 space-y-4 text-sm">
                <div>
                    <p class="text-gray-500">Khách hàng</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $serviceInvoice->customer_name }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Số điện thoại</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $serviceInvoice->customer_phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Email</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $serviceInvoice->customer_email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Dịch vụ</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $serviceInvoice->service_name }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Chi tiết tiền</h2>
                <p class="text-sm text-gray-500">Tạm tính, thuế và giảm giá.</p>
            </div>
        </div>
        <div class="grid gap-3 md:grid-cols-4">
            <div class="rounded-lg bg-slate-50 p-4">
                <p class="text-sm text-slate-500">Tạm tính</p>
                <p class="mt-1 text-base font-semibold text-slate-900">{{ number_format($serviceInvoice->subtotal, 0, ',', '.') }} đ</p>
            </div>
            <div class="rounded-lg bg-slate-50 p-4">
                <p class="text-sm text-slate-500">Thuế</p>
                <p class="mt-1 text-base font-semibold text-slate-900">{{ number_format($serviceInvoice->tax_amount, 0, ',', '.') }} đ</p>
            </div>
            <div class="rounded-lg bg-slate-50 p-4">
                <p class="text-sm text-slate-500">Giảm giá</p>
                <p class="mt-1 text-base font-semibold text-slate-900">{{ number_format($serviceInvoice->discount_amount, 0, ',', '.') }} đ</p>
            </div>
            <div class="rounded-lg bg-indigo-50 p-4">
                <p class="text-sm text-indigo-700">Tổng cộng</p>
                <p class="mt-1 text-xl font-bold text-indigo-900">{{ number_format($serviceInvoice->total_amount, 0, ',', '.') }} đ</p>
            </div>
        </div>
    </div>
</div>
@endsection
