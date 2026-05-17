<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RepairTicket;
use App\Models\ServiceInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RepairTicketInvoiceController extends Controller
{
    public function index(): View
    {
        $repairTickets = RepairTicket::with('serviceInvoice')->latest('ticket_id')->paginate(10);
        return view('admin.repair-tickets.index', compact('repairTickets'));
    }

    public function create(RepairTicket $repairTicket): View
    {
        return view('admin.service-invoices.create', [
            'repairTicket' => $repairTicket,
            'prefill' => [
                'customer_name' => $repairTicket->customer_name,
                'customer_phone' => $repairTicket->customer_phone,
                'service_name' => $repairTicket->service_name,
                'subtotal' => (float) $repairTicket->service_fee,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'service_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,issued,paid,cancelled'],
        ]);

        $subtotal = (float) $data['subtotal'];
        $taxAmount = (float) ($data['tax_amount'] ?? 0);
        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);

        $invoice = ServiceInvoice::create([
            'invoice_no' => 'INV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'service_name' => $data['service_name'],
            'description' => $data['description'] ?? null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'status' => $data['status'],
            'issued_date' => $data['status'] === 'draft' ? null : now()->toDateString(),
            'created_by' => $request->user()?->id,
        ]);

        if ($request->filled('repair_ticket_id')) {
            $repairTicket = RepairTicket::find($request->integer('repair_ticket_id'));
            if ($repairTicket && !$repairTicket->invoice_no) {
                $repairTicket->update([
                    'invoice_no' => $invoice->invoice_no,
                    'invoiced_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.service-invoices.show', $invoice)->with('success', 'Đã tạo hóa đơn dịch vụ.');
    }
}
