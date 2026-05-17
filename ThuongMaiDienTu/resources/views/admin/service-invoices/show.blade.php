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
                :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M12.5 15.5 7 10l5.5-5.5\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/></svg>'"
            >
                Quay lại
            </x-ui.button>
            <x-ui.button
                variant="info"
                :href="route('admin.service-invoices.print', $serviceInvoice)"
                target="_blank"
                title="Mở bản in"
                :icon="'<svg class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"none\" aria-hidden=\"true\"><path d=\"M6 6V3.8A1.8 1.8 0 0 1 7.8 2h4.4A1.8 1.8 0 0 1 14 3.8V6\" stroke=\"currentColor\" stroke-width=\"1.6\" stroke-linecap=\"round\"/><path d=\"M5 14H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1\" stroke=\"currentColor\" stroke-width=\"1.6\" stroke-linecap=\"round\"/><path d=\"M6 12h8v6H6z\" stroke=\"currentColor\" stroke-width=\"1.6\"/></svg>'"
            >
                In hóa đơn
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
