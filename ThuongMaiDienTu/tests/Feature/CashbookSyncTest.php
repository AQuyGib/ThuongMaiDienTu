<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Order;
use App\Models\ServiceInvoice;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashbookSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdminUser(): User
    {
        $role = Role::create([
            'role_id' => 1,
            'name' => 'Admin',
            'description' => 'Admin role',
        ]);

        return User::create([
            'role_id' => $role->role_id,
            'full_name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('admin123'),
            'member_tier' => 'Vang',
            'status' => 'Active',
        ]);
    }

    protected function makeProductVariant(int $stock = 5): ProductVariant
    {
        $category = Category::create([
            'parent_id' => null,
            'name' => 'Test Category',
            'slug' => 'test-category-' . uniqid(),
        ]);

        $product = Product::create([
            'category_id' => $category->category_id,
            'name' => 'Test Product',
            'base_price' => 100000,
            'sold_count' => 0,
            'stock' => $stock,
        ]);

        return ProductVariant::create([
            'product_id' => $product->product_id,
            'color' => 'Black',
            'rom_capacity' => '128GB',
            'extra_price' => 0,
            'stock' => $stock,
        ]);
    }

    /**
     * Test that updating an order's status to Delivered or Completed triggers
     * the creation of a Cashbook Income record.
     */
    public function test_order_delivered_status_triggers_cashbook_income(): void
    {
        $admin = $this->makeAdminUser();

        $order = Order::create([
            'user_id' => $admin->user_id,
            'order_code' => 'ORD12345',
            'customer_name' => 'Test Customer',
            'customer_phone' => '0987654321',
            'order_type' => 'Online',
            'total_amount' => 500000,
            'shipping_fee' => 0,
            'final_amount' => 500000,
            'payment_method' => 'COD',
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.orders.updateStatus', $order->order_id), [
                'status' => 'Delivered',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cashbooks', [
            'type' => 'Income',
            'amount' => 500000,
            'reference_id' => $order->order_id,
            'reference_type' => 'order',
        ]);
    }

    /**
     * Test that creating a Service Invoice with 'paid' status triggers
     * a Cashbook Income record.
     */
    public function test_service_invoice_creation_triggers_cashbook_income(): void
    {
        $admin = $this->makeAdminUser();

        $response = $this->actingAs($admin)
            ->post(route('admin.service-invoices.store'), [
                'invoice_no' => 'HD0001',
                'customer_name' => 'Test Customer',
                'customer_phone' => '0987654321',
                'customer_email' => 'customer@example.com',
                'imei_serial' => 'IMEI1234567890',
                'service_name' => 'Sửa chữa điện thoại',
                'subtotal' => 1500000,
                'vat_rate' => 0,
                'discount_amount' => 0,
                'status' => 'paid',
            ]);

        $response->assertSessionHasNoErrors();

        $invoice = ServiceInvoice::where('invoice_no', 'HD0001')->first();
        $this->assertNotNull($invoice);

        $this->assertDatabaseHas('cashbooks', [
            'type' => 'Income',
            'amount' => 1500000,
            'reference_id' => $invoice->id,
            'reference_type' => 'service_invoice',
        ]);
    }

    /**
     * Test that updating a Service Invoice status from draft to paid triggers
     * a Cashbook Income record.
     */
    public function test_service_invoice_update_to_paid_triggers_cashbook_income(): void
    {
        $admin = $this->makeAdminUser();

        $invoice = ServiceInvoice::create([
            'invoice_no' => 'HD0002',
            'customer_name' => 'Test Customer',
            'customer_phone' => '0987654321',
            'imei_serial' => 'IMEI9876543210',
            'service_name' => 'Thay thế linh kiện',
            'subtotal' => 2000000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 2000000,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.service-invoices.update', $invoice->id), [
                'invoice_no' => 'HD0002',
                'customer_name' => 'Test Customer',
                'customer_phone' => '0987654321',
                'imei_serial' => 'IMEI9876543210',
                'service_name' => 'Thay thế linh kiện',
                'subtotal' => 2000000,
                'vat_rate' => 0,
                'discount_amount' => 0,
                'status' => 'paid',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cashbooks', [
            'type' => 'Income',
            'amount' => 2000000,
            'reference_id' => $invoice->id,
            'reference_type' => 'service_invoice',
        ]);
    }

    /**
     * Test that creating a Purchase Order triggers a Cashbook Expense record.
     */
    public function test_purchase_order_triggers_cashbook_expense(): void
    {
        $admin = $this->makeAdminUser();
        $variant = $this->makeProductVariant();

        $supplier = Supplier::create([
            'name' => 'Nha Cung Cap Test',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.purchase-orders.store'), [
                'supplier_id' => $supplier->supplier_id,
                'items' => [
                    [
                        'variant_id' => $variant->variant_id,
                        'imei_serial' => 'IMEI_TEST_001',
                        'cost_price' => 12000000,
                        'warehouse_loc' => 'Ke A',
                    ]
                ]
            ]);

        $response->assertSessionHasNoErrors();

        $po = PurchaseOrder::where('supplier_id', $supplier->supplier_id)->first();
        $this->assertNotNull($po);

        $this->assertDatabaseHas('cashbooks', [
            'type' => 'Expense',
            'amount' => 12000000,
            'reference_id' => $po->po_id,
            'reference_type' => 'purchase_order',
        ]);
    }

    /**
     * Test that manually creating a Cashbook entry with a reference_type
     * works and validates correctly.
     */
    public function test_manual_cashbook_creation_with_reference_type(): void
    {
        $admin = $this->makeAdminUser();

        $order = Order::create([
            'user_id' => $admin->user_id,
            'order_code' => 'ORD99999',
            'customer_name' => 'Test Customer',
            'customer_phone' => '0987654321',
            'order_type' => 'Online',
            'total_amount' => 500000,
            'shipping_fee' => 0,
            'final_amount' => 500000,
            'payment_method' => 'COD',
            'status' => 'Pending',
        ]);

        $installment = \App\Models\Installment::create([
            'order_id' => $order->order_id,
            'installment_code' => 'TGO-2606030001',
            'method' => 'financial_company',
            'partner' => 'Home Credit',
            'period' => 6,
            'product_price' => 500000,
            'prepay_amount' => 100000,
            'loan_amount' => 400000,
            'monthly_payment' => 80000,
            'total_payment' => 580000,
            'difference_amount' => 80000,
            'customer_name' => 'Test Customer',
            'customer_phone' => '0987654321',
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.cashbooks.store'), [
                'type' => 'Income',
                'amount' => 3000000,
                'description' => 'Thu tiền thủ công liên kết',
                'reference_id' => $installment->id,
                'reference_type' => 'installment',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cashbooks', [
            'type' => 'Income',
            'amount' => 3000000,
            'description' => 'Thu tiền thủ công liên kết',
            'reference_id' => $installment->id,
            'reference_type' => 'installment',
        ]);
    }

    /**
     * Test that manual creation is rejected if input parameters are incomplete
     * or refer to non-existent resources.
     */
    public function test_manual_cashbook_creation_rejection_rules(): void
    {
        $admin = $this->makeAdminUser();

        // 1. Missing reference_type when reference_id is provided
        $response1 = $this->actingAs($admin)
            ->post(route('admin.cashbooks.store'), [
                'type' => 'Income',
                'amount' => 100000,
                'description' => 'Test missing type',
                'reference_id' => 1,
            ]);
        $response1->assertSessionHasErrors(['reference_type']);

        // 2. Missing reference_id when reference_type is provided
        $response2 = $this->actingAs($admin)
            ->post(route('admin.cashbooks.store'), [
                'type' => 'Income',
                'amount' => 100000,
                'description' => 'Test missing ID',
                'reference_type' => 'order',
            ]);
        $response2->assertSessionHasErrors(['reference_id']);

        // 3. Non-existent reference_id in database
        $response3 = $this->actingAs($admin)
            ->post(route('admin.cashbooks.store'), [
                'type' => 'Income',
                'amount' => 100000,
                'description' => 'Test non-existent order',
                'reference_id' => 999999,
                'reference_type' => 'order',
            ]);
        $response3->assertSessionHasErrors(['reference_id']);
    }

    /**
     * Test bulk delete matching specific query filters across multiple pages.
     */
    public function test_bulk_destroy_matching_filters(): void
    {
        $admin = $this->makeAdminUser();

        // Create 2 Income cashbook transactions
        Cashbook::create([
            'type' => 'Income',
            'amount' => 5000,
            'description' => 'Thu tiền A',
        ]);
        Cashbook::create([
            'type' => 'Income',
            'amount' => 10000,
            'description' => 'Thu tiền B',
        ]);

        // Create 1 Expense cashbook transaction
        Cashbook::create([
            'type' => 'Expense',
            'amount' => 8000,
            'description' => 'Chi tiền C',
        ]);

        $this->assertEquals(3, Cashbook::count());

        // Perform bulk delete selecting matching 'Expense' filter
        $response = $this->actingAs($admin)
            ->post(route('admin.cashbooks.bulkDestroy'), [
                'select_all_matching' => '1',
                'type' => 'Expense',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.cashbooks.index'));

        // Verify only 2 Income transactions remain, Expense transaction is deleted
        $this->assertEquals(2, Cashbook::count());
        $this->assertDatabaseMissing('cashbooks', [
            'description' => 'Chi tiền C',
        ]);
        $this->assertDatabaseHas('cashbooks', [
            'description' => 'Thu tiền A',
        ]);
        $this->assertDatabaseHas('cashbooks', [
            'description' => 'Thu tiền B',
        ]);
    }
}
