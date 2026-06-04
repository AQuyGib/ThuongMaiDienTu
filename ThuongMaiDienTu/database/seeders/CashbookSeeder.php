<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cashbook;
use App\Models\Order;
use App\Models\ServiceInvoice;
use App\Models\PurchaseOrder;
use App\Models\Installment;
use App\Models\InstallmentPayment;

class CashbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate existing cashbook records to ensure clean sync during seeding
        Cashbook::truncate();

        // 1. Ghi nhận doanh thu từ Đơn hàng thông thường (không phải trả góp và trạng thái thành công)
        $orders = Order::whereIn('status', ['Delivered', 'Completed'])
            ->where('payment_method', '!=', 'Installment')
            ->get();

        foreach ($orders as $order) {
            Cashbook::create([
                'type' => 'Income',
                'amount' => $order->final_amount,
                'description' => 'Khách hàng thanh toán đơn hàng #' . $order->order_code,
                'reference_id' => $order->order_id,
                'reference_type' => 'order',
                'created_at' => $order->created_at,
            ]);
        }

        // 2. Ghi nhận chi phí từ các đơn nhập hàng (Purchase Orders)
        $purchaseOrders = PurchaseOrder::with('supplier')->get();
        foreach ($purchaseOrders as $po) {
            Cashbook::create([
                'type' => 'Expense',
                'amount' => $po->total_cost,
                'description' => 'Thanh toán đơn nhập hàng #' . $po->po_id . ' từ nhà cung cấp: ' . ($po->supplier ? $po->supplier->name : 'N/A'),
                'reference_id' => $po->po_id,
                'reference_type' => 'purchase_order',
                'created_at' => $po->created_at,
            ]);
        }

        // 3. Ghi nhận doanh thu từ Hóa đơn dịch vụ (Service Invoices) đã thanh toán (status = paid)
        $serviceInvoices = ServiceInvoice::where('status', 'paid')->get();
        foreach ($serviceInvoices as $invoice) {
            Cashbook::create([
                'type' => 'Income',
                'amount' => $invoice->total_amount,
                'description' => 'Thanh toán hóa đơn dịch vụ #' . $invoice->invoice_no . ' - Khách hàng: ' . $invoice->customer_name,
                'reference_id' => $invoice->id,
                'reference_type' => 'service_invoice',
                'created_at' => $invoice->created_at,
            ]);
        }

        // 4. Ghi nhận doanh thu từ Trả góp (Tiền cọc + Các kỳ thanh toán hàng tháng)
        $installments = Installment::all();
        foreach ($installments as $inst) {
            // A. Nếu có khoản trả trước (prepay_amount > 0)
            if ($inst->prepay_amount > 0 && in_array($inst->status, ['Approved', 'Paying', 'Completed'])) {
                Cashbook::create([
                    'type' => 'Income',
                    'amount' => $inst->prepay_amount,
                    'description' => 'Thu tiền trả trước hợp đồng trả góp #' . $inst->installment_code . ' - Khách hàng: ' . $inst->customer_name,
                    'reference_id' => $inst->id,
                    'reference_type' => 'installment',
                    'created_at' => $inst->created_at,
                ]);
            }

            // B. Nếu có các kỳ thanh toán đã đóng (Paid)
            $paidPayments = InstallmentPayment::where('installment_id', $inst->id)
                ->where('status', 'Paid')
                ->get();

            foreach ($paidPayments as $payment) {
                Cashbook::create([
                    'type' => 'Income',
                    'amount' => $payment->amount,
                    'description' => 'Thu tiền trả góp định kỳ thứ ' . $payment->term_number . '/' . $inst->period . ' - Hợp đồng: #' . $inst->installment_code . ' - Khách hàng: ' . $inst->customer_name,
                    'reference_id' => $inst->id,
                    'reference_type' => 'installment',
                    'created_at' => $payment->payment_date ? \Illuminate\Support\Carbon::parse($payment->payment_date) : $payment->created_at,
                ]);
            }
        }
    }
}
