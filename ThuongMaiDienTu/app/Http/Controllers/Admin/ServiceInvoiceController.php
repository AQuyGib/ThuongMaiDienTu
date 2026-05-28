<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceInvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = ServiceInvoice::query();

        if ($request->filled('invoice_no')) {
            $query->where('invoice_no', 'like', '%' . $request->string('invoice_no') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('issued_date', '>=', $request->date('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('issued_date', '<=', $request->date('to_date'));
        }

        $invoices = $query->latest()->paginate(10)->withQueryString();

        return view('admin.service-invoices.index', compact('invoices'));
    }

    public function create(): View
    {
        return view('admin.service-invoices.create', [
            'prefill' => [
                'invoice_no' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
            ]
        ]);
    }
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'invoice_no' => ['required', 'string', 'max:50', 'unique:service_invoices,invoice_no'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'imei_serial' => ['required', 'string', 'max:255'],
            'service_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'vat_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,issued,paid,cancelled'],
        ], [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'service_name.required' => 'Vui lòng nhập tên dịch vụ.',
            'subtotal.required' => 'Vui lòng nhập số tiền tạm tính.',
            'subtotal.integer' => 'Số tiền tạm tính phải là số nguyên.',
            'imei_serial.required' => 'Vui lòng nhập mã IMEI / Serial.',
            'customer_phone.regex' => 'Số điện thoại phải đúng 10 chữ số.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $status = $request->input('status');
            $subtotal = (float) $request->input('subtotal', 0);

            if (in_array($status, ['issued', 'paid']) && $subtotal <= 0) {
                $validator->errors()->add('subtotal', 'Số tiền tạm tính phải lớn hơn 0 khi phát hành hoặc thanh toán hóa đơn.');
            }
        });

        $data = $validator->validate();

        $subtotal = (int) $data['subtotal'];
        $vatRate = (int) ($data['vat_rate'] ?? 0);
        $taxAmount = (int) round(($subtotal * $vatRate) / 100);
        $discountAmount = (int) ($data['discount_amount'] ?? 0);
        $totalAmount = (int) max(0, $subtotal + $taxAmount - $discountAmount);

        ServiceInvoice::create([
            'invoice_no' => $data['invoice_no'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'imei_serial' => $data['imei_serial'] ?? null,
            'service_name' => $data['service_name'],
            'description' => $data['description'] ?? null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'status' => $data['status'],
            'issued_date' => $data['status'] === 'draft' ? null : now()->toDateString(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.service-invoices.index')->with('success', 'Tạo hóa đơn dịch vụ thành công.');
    }

    public function show(ServiceInvoice $serviceInvoice): View
    {
        return view('admin.service-invoices.show', compact('serviceInvoice'));
    }

    public function print(ServiceInvoice $serviceInvoice): View
    {
        return view('admin.service-invoices.print', compact('serviceInvoice'));
    }

    public function pdf(ServiceInvoice $serviceInvoice)
    {
        $pdf = Pdf::loadView('admin.service-invoices.print', compact('serviceInvoice'));

        return $pdf->download($serviceInvoice->invoice_no . '.pdf');
    }

    public function savePdf(ServiceInvoice $serviceInvoice)
    {
        $pdf = Pdf::loadView('admin.service-invoices.print', compact('serviceInvoice'));
        $filename = $serviceInvoice->invoice_no . '.pdf';
        $path = 'invoices/' . $filename;

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $pdf->output());
        }

        return response()->download(storage_path('app/public/' . $path), $filename);
    }

    public function openPdf(ServiceInvoice $serviceInvoice)
    {
        $path = 'invoices/' . $serviceInvoice->invoice_no . '.pdf';

        if (! Storage::disk('public')->exists($path)) {
            $pdf = Pdf::loadView('admin.service-invoices.print', compact('serviceInvoice'));
            Storage::disk('public')->put($path, $pdf->output());
        }

        return response()->file(storage_path('app/public/' . $path));
    }

    public function downloadSavedPdf(ServiceInvoice $serviceInvoice)
    {
        $path = 'invoices/' . $serviceInvoice->invoice_no . '.pdf';

        if (! Storage::disk('public')->exists($path)) {
            $pdf = Pdf::loadView('admin.service-invoices.print', compact('serviceInvoice'));
            Storage::disk('public')->put($path, $pdf->output());
        }

        return response()->download(storage_path('app/public/' . $path), $serviceInvoice->invoice_no . '.pdf');
    }

    public function edit(ServiceInvoice $serviceInvoice): View
    {
        return view('admin.service-invoices.edit', compact('serviceInvoice'));
    }

    public function update(Request $request, ServiceInvoice $serviceInvoice): \Illuminate\Http\RedirectResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'imei_serial' => ['required', 'string', 'max:255'],
            'service_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'vat_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,issued,paid,cancelled'],
        ], [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'service_name.required' => 'Vui lòng nhập tên dịch vụ.',
            'subtotal.required' => 'Vui lòng nhập số tiền tạm tính.',
            'subtotal.integer' => 'Số tiền tạm tính phải là số nguyên.',
            'imei_serial.required' => 'Vui lòng nhập mã IMEI / Serial.',
            'customer_phone.regex' => 'Số điện thoại phải đúng 10 chữ số.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $status = $request->input('status');
            $subtotal = (float) $request->input('subtotal', 0);

            if (in_array($status, ['issued', 'paid']) && $subtotal <= 0) {
                $validator->errors()->add('subtotal', 'Số tiền tạm tính phải lớn hơn 0 khi phát hành hoặc thanh toán hóa đơn.');
            }
        });

        $data = $validator->validate();

        $subtotal = (int) $data['subtotal'];
        $vatRate = (int) ($data['vat_rate'] ?? 0);
        $taxAmount = (int) round(($subtotal * $vatRate) / 100);
        $discountAmount = (int) ($data['discount_amount'] ?? 0);
        $totalAmount = (int) max(0, $subtotal + $taxAmount - $discountAmount);

        $updateData = [
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'imei_serial' => $data['imei_serial'] ?? null,
            'service_name' => $data['service_name'],
            'description' => $data['description'] ?? null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'status' => $data['status'],
        ];

        if ($data['status'] !== 'draft' && !$serviceInvoice->issued_date) {
            $updateData['issued_date'] = now()->toDateString();
        }

        $serviceInvoice->update($updateData);

        return redirect()->route('admin.service-invoices.index')->with('success', 'Cập nhật hóa đơn dịch vụ thành công.');
    }

    public function destroy(ServiceInvoice $serviceInvoice)
    {
        // Giải phóng liên kết ở các phiếu sửa chữa liên quan (nếu có)
        \App\Models\RepairTicket::where('invoice_no', $serviceInvoice->invoice_no)
            ->update([
                'invoice_no' => null,
                'invoiced_at' => null,
            ]);

        $serviceInvoice->delete();

        return redirect()->route('admin.service-invoices.index')->with('success', 'Xóa hóa đơn dịch vụ thành công.');
    }
}
