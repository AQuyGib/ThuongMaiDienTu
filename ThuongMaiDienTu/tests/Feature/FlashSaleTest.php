<?php

namespace Tests\Feature;

use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_flash_sale_sync_command_deactivates_expired_sales(): void
    {
        $expired = FlashSale::create([
            'name' => 'Expired Sale',
            'start_at' => Carbon::now()->subDays(2),
            'end_at' => Carbon::now()->subDay(),
            'is_active' => true,
        ]);

        $active = FlashSale::create([
            'name' => 'Active Sale',
            'start_at' => Carbon::now()->subHour(),
            'end_at' => Carbon::now()->addHour(),
            'is_active' => true,
        ]);

        $this->artisan('flash-sales:sync')->assertExitCode(0);

        $this->assertDatabaseHas('flash_sales', [
            'flash_sale_id' => $expired->flash_sale_id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('flash_sales', [
            'flash_sale_id' => $active->flash_sale_id,
            'is_active' => true,
        ]);
    }

    public function test_flash_sale_product_is_created_with_valid_sale_price(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category_id' => 1,
            'base_price' => 1000000,
        ]);

        $flashSale = FlashSale::create([
            'name' => 'Sale',
            'start_at' => Carbon::now()->subHour(),
            'end_at' => Carbon::now()->addHour(),
            'is_active' => true,
        ]);

        $flashSaleProduct = FlashSaleProduct::create([
            'flash_sale_id' => $flashSale->flash_sale_id,
            'product_id' => $product->product_id,
            'sale_price' => 800000,
            'stock_limit' => 10,
            'sold_quantity' => 0,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('flash_sale_products', [
            'flash_sale_product_id' => $flashSaleProduct->flash_sale_product_id,
            'sale_price' => 800000,
        ]);
    }
}
