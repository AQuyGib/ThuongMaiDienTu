<?php

namespace Database\Seeders;

use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FlashSaleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo chương trình Flash Sale đang diễn ra
        $flashSale = FlashSale::updateOrCreate(
            ['name' => 'Flash Sale Cuối Tuần - Giảm Sốc 50%'],
            [
                'description' => 'Chương trình khuyến mãi đặc biệt dành cho các sản phẩm công nghệ hot nhất hiện nay.',
                'start_at' => Carbon::now()->subHours(2),
                'end_at' => Carbon::now()->addHours(22),
                'is_active' => true,
            ]
        );

        // 2. Lấy danh sách sản phẩm mẫu để gán
        $products = Product::where('status', 'active')->inRandomOrder()->take(8)->get();

        foreach ($products as $index => $product) {
            // Giảm giá từ 10% đến 40%
            $discountPercent = rand(10, 40);
            $salePrice = round($product->base_price * (1 - $discountPercent / 100), -3); // Làm tròn đến nghìn

            FlashSaleProduct::updateOrCreate(
                [
                    'flash_sale_id' => $flashSale->flash_sale_id,
                    'product_id' => $product->product_id,
                ],
                [
                    'sale_price' => $salePrice,
                    'stock_limit' => rand(10, 50),
                    'sold_quantity' => rand(0, 8),
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }

        // 3. Tạo thêm một chương trình sắp diễn ra (để test nhãn)
        FlashSale::updateOrCreate(
            ['name' => 'Siêu Sale 6.6 Sắp Tới'],
            [
                'description' => 'Đón chờ cơn lốc giảm giá cực khủng vào tháng 6.',
                'start_at' => Carbon::now()->addDays(2),
                'end_at' => Carbon::now()->addDays(3),
                'is_active' => true,
            ]
        );
    }
}
