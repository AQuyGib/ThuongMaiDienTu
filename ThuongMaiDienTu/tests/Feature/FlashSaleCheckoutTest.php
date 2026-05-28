<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FlashSaleCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_order_keeps_reserved_flash_sale_quantity(): void
    {
        $category = Category::create(['name' => 'Laptop']);
        $product = Product::create([
            'category_id' => $category->category_id,
            'name' => 'Reserved Product',
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
            'stock_limit' => 5,
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

        // Tạo Supplier và PurchaseOrder mặc định để tránh lỗi khóa ngoại trong InventoryItem
        $supplier = \DB::table('suppliers')->insertGetId([
            'name' => 'Supplier Test',
        ]);
        \DB::table('purchase_orders')->insertGetId([
            'po_id' => 1,
            'supplier_id' => $supplier,
            'total_cost' => 10000000,
        ]);

        $this->post(route('cart.confirm'), [
            'name' => 'Nguyen Van A',
            'phone' => '0901234567',
            'address' => '123 Duong ABC, Quan 1, TP HCM',
            'note' => 'Ghi chu test',
        ])->assertJson(['status' => 'success. success']);
        $fsp->refresh();
        $this->assertSame(0, $fsp->sold_quantity);
        $this->assertFalse(session()->has('cart'));
    }

    public function test_cancel_order_releases_reserved_flash_sale_quantity(): void
    {
        $category = Category::create(['name' => 'Laptop']);
        $product = Product::create([
            'category_id' => $category->category_id,
            'name' => 'Reserved Product',
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
            'stock_limit' => 5,
            'sold_quantity' => 2,
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

        $this->post(route('cart.cancel'))->assertRedirect(route('cart.index'));
        $fsp->refresh();
        $this->assertSame(0, $fsp->sold_quantity);
    }

    public function test_timeout_order_releases_reserved_flash_sale_quantity(): void
    {
        $category = Category::create(['name' => 'Laptop']);
        $product = Product::create([
            'category_id' => $category->category_id,
            'name' => 'Reserved Product',
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
            'stock_limit' => 5,
            'sold_quantity' => 1,
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

        $this->post(route('cart.timeout'))->assertRedirect(route('cart.index'));
        $fsp->refresh();
        $this->assertSame(0, $fsp->sold_quantity);
    }
}
