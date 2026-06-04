<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FlashSaleEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_full_flash_sale_checkout_flow_locks_and_confirms_quantity(): void
    {
        // Tạo User
        $user = User::create([
            'role_id' => 3, // Khách hàng
            'full_name' => 'Nguyen Van A',
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password'),
        ]);

        $category = Category::create(['name' => 'Laptop']);
        $product = Product::create([
            'category_id' => $category->category_id,
            'name' => 'E2E Product',
            'base_price' => 1000000,
        ]);

        $flashSale = FlashSale::create([
            'name' => 'Sale',
            'start_at' => Carbon::now()->subHour(),
            'end_at' => Carbon::now()->addHour(),
            'is_active' => true,
        ]);

        $fsp = FlashSaleProduct::create([
            'flash_sale_id' => $flashSale->flash_sale_id,
            'product_id' => $product->product_id,
            'sale_price' => 700000,
            'stock_limit' => 3,
            'sold_quantity' => 0,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        session(['cart' => [
            $product->product_id => [
                'name' => $product->name,
                'quantity' => 2,
                'price' => 700000,
                'flash_sale_price' => 700000,
            ],
        ]]);

        $this->actingAs($user)->get(route('cart.pay'))->assertOk();
        $fsp->refresh();
        $this->assertSame(2, $fsp->sold_quantity);
        $this->assertTrue(session('cart_locked'));

        // Tạo Supplier và PurchaseOrder mặc định để tránh lỗi khóa ngoại trong InventoryItem
        $supplier = \DB::table('suppliers')->insertGetId([
            'name' => 'Supplier Test',
        ]);
        \DB::table('purchase_orders')->insertGetId([
            'po_id' => 1,
            'supplier_id' => $supplier,
            'total_cost' => 10000000,
        ]);

        $this->actingAs($user)->post(route('cart.confirm'), [
            'name' => 'Nguyen Van A',
            'phone' => '0901234567',
            'address' => '123 Duong ABC',
            'province' => 'hcm',
            'district' => 'Quan 1',
            'ward' => 'Ben Nghe',
            'note' => 'Ghi chu test',
        ])->assertJson(['status' => 'success']);
        $this->assertFalse(session()->has('cart'));
        $this->assertFalse(session()->has('cart_locked'));
        $fsp->refresh();
        $this->assertSame(2, $fsp->sold_quantity);
    }

    public function test_timeout_after_pay_releases_reserved_quantity(): void
    {
        // Tạo User
        $user = User::create([
            'role_id' => 3, // Khách hàng
            'full_name' => 'Nguyen Van A',
            'email' => 'test2@example.com',
            'password_hash' => bcrypt('password'),
        ]);

        $category = Category::create(['name' => 'Laptop']);
        $product = Product::create([
            'category_id' => $category->category_id,
            'name' => 'E2E Product',
            'base_price' => 1000000,
        ]);

        $flashSale = FlashSale::create([
            'name' => 'Sale',
            'start_at' => Carbon::now()->subHour(),
            'end_at' => Carbon::now()->addHour(),
            'is_active' => true,
        ]);

        $fsp = FlashSaleProduct::create([
            'flash_sale_id' => $flashSale->flash_sale_id,
            'product_id' => $product->product_id,
            'sale_price' => 700000,
            'stock_limit' => 3,
            'sold_quantity' => 0,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        session(['cart' => [
            $product->product_id => [
                'name' => $product->name,
                'quantity' => 1,
                'price' => 700000,
                'flash_sale_price' => 700000,
            ],
        ]]);

        $this->actingAs($user)->get(route('cart.pay'))->assertOk();
        $this->actingAs($user)->post(route('cart.timeout'))->assertRedirect(route('cart.index'));
        $fsp->refresh();
        $this->assertSame(0, $fsp->sold_quantity);
        $this->assertFalse(session()->has('cart_locked'));
    }
}
