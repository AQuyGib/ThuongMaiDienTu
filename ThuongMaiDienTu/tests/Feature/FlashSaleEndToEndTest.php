<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FlashSaleEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_flash_sale_checkout_flow_locks_and_confirms_quantity(): void
    {
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

        $this->get(route('cart.pay'))->assertOk();
        $fsp->refresh();
        $this->assertSame(2, $fsp->sold_quantity);
        $this->assertTrue(session('cart_locked'));

        $this->post(route('cart.confirm'))->assertRedirect(route('home'));
        $this->assertFalse(session()->has('cart'));
        $this->assertFalse(session()->has('cart_locked'));
        $fsp->refresh();
        $this->assertSame(2, $fsp->sold_quantity);
    }

    public function test_timeout_after_pay_releases_reserved_quantity(): void
    {
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

        $this->get(route('cart.pay'))->assertOk();
        $this->post(route('cart.timeout'))->assertRedirect(route('cart.index'));
        $fsp->refresh();
        $this->assertSame(0, $fsp->sold_quantity);
        $this->assertFalse(session()->has('cart_locked'));
    }
}
