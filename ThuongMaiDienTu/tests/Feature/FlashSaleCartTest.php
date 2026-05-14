<?php

namespace Tests\Feature;

use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FlashSaleCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_to_cart_uses_flash_sale_price_when_active(): void
    {
        $category = Category::create([
            'name' => 'Laptop',
        ]);

        $product = Product::create([
            'category_id' => $category->category_id,
            'name' => 'Flash Product',
            'base_price' => 1000000,
        ]);

        $flashSale = FlashSale::create([
            'name' => 'Sale',
            'start_at' => Carbon::now()->subHour(),
            'end_at' => Carbon::now()->addHour(),
            'is_active' => true,
        ]);

        FlashSaleProduct::create([
            'flash_sale_id' => $flashSale->flash_sale_id,
            'product_id' => $product->product_id,
            'sale_price' => 750000,
            'stock_limit' => 5,
            'sold_quantity' => 0,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('cart');

        $cart = session('cart');
        $this->assertSame(750000, $cart[$product->product_id]['price']);
        $this->assertSame(750000, $cart[$product->product_id]['flash_sale_price']);
    }

    public function test_add_to_cart_rejects_when_flash_sale_stock_is_exceeded(): void
    {
        $category = Category::create([
            'name' => 'Laptop',
        ]);

        $product = Product::create([
            'category_id' => $category->category_id,
            'name' => 'Flash Product',
            'base_price' => 1000000,
        ]);

        $flashSale = FlashSale::create([
            'name' => 'Sale',
            'start_at' => Carbon::now()->subHour(),
            'end_at' => Carbon::now()->addHour(),
            'is_active' => true,
        ]);

        FlashSaleProduct::create([
            'flash_sale_id' => $flashSale->flash_sale_id,
            'product_id' => $product->product_id,
            'sale_price' => 750000,
            'stock_limit' => 1,
            'sold_quantity' => 1,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
        ]);

        $response->assertSessionHas('error');
    }
}
