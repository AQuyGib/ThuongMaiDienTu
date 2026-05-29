<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventorySyncTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(): User
    {
        $role = Role::create([
            'name' => 'Staff',
            'description' => 'Staff role',
        ]);

        return User::create([
            'role_id' => $role->role_id,
            'full_name' => 'Test User',
            'email' => 'test-user@example.com',
            'password_hash' => bcrypt('password'),
            'member_tier' => 'Dong',
            'status' => 'Active',
        ]);
    }

    protected function makeVariant(int $stock = 1): ProductVariant
    {
        $slug = 'test-category-' . uniqid();
        $category = Category::create([
            'parent_id' => null,
            'name' => 'Test Category',
            'slug' => $slug,
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

    protected function makePurchaseOrder(): PurchaseOrder
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
        ]);

        return PurchaseOrder::create([
            'supplier_id' => $supplier->supplier_id,
            'total_cost' => 100000,
        ]);
    }

    public function test_it_marks_inventory_item_as_sold_and_decreases_variant_stock(): void
    {
        $variant = $this->makeVariant(1);
        $po = $this->makePurchaseOrder();
        $item = InventoryItem::create([
            'variant_id' => $variant->variant_id,
            'po_id' => $po->po_id,
            'imei_serial' => 'IMEI001',
            'warehouse_loc' => 'A1',
            'status' => 'In_Stock',
        ]);

        app(InventoryService::class)->markInventoryItemSold($item, ['note' => 'Test sale']);

        $this->assertDatabaseHas('inventory_items', [
            'item_id' => $item->item_id,
            'status' => 'Sold',
        ]);

        $this->assertDatabaseHas('product_variants', [
            'variant_id' => $variant->variant_id,
            'stock' => 0,
        ]);
    }

    public function test_it_restores_inventory_item_and_increases_variant_stock(): void
    {
        $variant = $this->makeVariant(0);
        $po = $this->makePurchaseOrder();
        $item = InventoryItem::create([
            'variant_id' => $variant->variant_id,
            'po_id' => $po->po_id,
            'imei_serial' => 'IMEI002',
            'warehouse_loc' => 'A1',
            'status' => 'Sold',
        ]);

        app(InventoryService::class)->restoreInventoryItem($item, ['note' => 'Test restore']);

        $this->assertDatabaseHas('inventory_items', [
            'item_id' => $item->item_id,
            'status' => 'In_Stock',
        ]);

        $this->assertDatabaseHas('product_variants', [
            'variant_id' => $variant->variant_id,
            'stock' => 1,
        ]);
    }

    public function test_it_updates_order_status_and_triggers_observer(): void
    {
        $user = $this->makeUser();
        $variant = $this->makeVariant(1);
        $po = $this->makePurchaseOrder();
        $item = InventoryItem::create([
            'variant_id' => $variant->variant_id,
            'po_id' => $po->po_id,
            'imei_serial' => 'IMEI003',
            'warehouse_loc' => 'A1',
            'status' => 'In_Stock',
        ]);

        $order = Order::create([
            'user_id' => $user->user_id,
            'order_type' => 'Online',
            'total_amount' => 100000,
            'shipping_fee' => 0,
            'final_amount' => 100000,
            'payment_method' => 'COD',
            'status' => 'Pending',
        ]);

        OrderDetail::create([
            'order_id' => $order->order_id,
            'item_id' => $item->item_id,
            'price' => 100000,
        ]);

        // Manually trigger the initial sync as done in CartController
        $order->load('details.inventoryItem.variant');
        app(InventoryService::class)->syncOrderByStatus($order, null, $order->status);

        // Verify the item is Sold and variant stock decreased to 0
        $this->assertDatabaseHas('inventory_items', [
            'item_id' => $item->item_id,
            'status' => 'Sold',
        ]);
        $this->assertEquals(0, $variant->fresh()->stock);

        // Cancel order - this triggers OrderObserver which should restore the stock
        $order->update(['status' => 'Cancelled']);

        $this->assertDatabaseHas('orders', [
            'order_id' => $order->order_id,
            'status' => 'Cancelled',
        ]);

        // Verify the item is In_Stock again and variant stock increased back to 1
        $this->assertDatabaseHas('inventory_items', [
            'item_id' => $item->item_id,
            'status' => 'In_Stock',
        ]);
        $this->assertEquals(1, $variant->fresh()->stock);
    }
}
