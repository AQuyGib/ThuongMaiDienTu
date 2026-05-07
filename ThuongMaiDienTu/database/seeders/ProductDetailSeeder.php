<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;

class ProductDetailSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::with('category')->get();

        foreach ($products as $product) {
            $catName = $product->category->name ?? '';

            // 1. Thêm Thông số kỹ thuật (Specifications)
            if ($catName === 'Điện thoại') {
                ProductSpecification::create([
                    'product_id' => $product->product_id,
                    'cpu_chip' => str_contains($product->name, 'iPhone') ? 'Apple A17 Pro' : 'Snapdragon 8 Gen 3',
                    'ram_capacity' => '12 GB',
                    'battery' => '5000 mAh',
                    'screen_size' => '6.7 inch',
                ]);

                // Thêm Biến thể (Variants)
                $colors = ['Đen', 'Trắng', 'Xanh', 'Vàng Titan'];
                $roms = ['128GB', '256GB', '512GB'];
                
                foreach ($colors as $color) {
                    foreach ($roms as $rom) {
                        ProductVariant::create([
                            'product_id' => $product->product_id,
                            'color' => $color,
                            'rom_capacity' => $rom,
                            'extra_price' => $rom === '512GB' ? 4000000 : ($rom === '256GB' ? 2000000 : 0),
                        ]);
                    }
                }
            } elseif ($catName === 'Laptop') {
                ProductSpecification::create([
                    'product_id' => $product->product_id,
                    'cpu_chip' => str_contains($product->name, 'MacBook') ? 'Apple M3' : 'Intel Core i7-14700HX',
                    'ram_capacity' => '16 GB',
                    'battery' => '70 Wh',
                    'screen_size' => '14 inch',
                ]);

                // Thêm Biến thể
                $colors = ['Xám không gian', 'Bạc'];
                $roms = ['256GB SSD', '512GB SSD', '1TB SSD'];

                foreach ($colors as $color) {
                    foreach ($roms as $rom) {
                        ProductVariant::create([
                            'product_id' => $product->product_id,
                            'color' => $color,
                            'rom_capacity' => $rom,
                            'extra_price' => $rom === '1TB SSD' ? 5000000 : ($rom === '512GB SSD' ? 2500000 : 0),
                        ]);
                    }
                }
            } else {
                // Các danh mục khác (Tablet, Watch, v.v.)
                ProductSpecification::create([
                    'product_id' => $product->product_id,
                    'cpu_chip' => 'Chip xử lý cao cấp',
                    'ram_capacity' => '8 GB',
                    'battery' => 'Tiêu chuẩn',
                    'screen_size' => 'Tiêu chuẩn',
                ]);

                ProductVariant::create([
                    'product_id' => $product->product_id,
                    'color' => 'Tiêu chuẩn',
                    'rom_capacity' => 'Mặc định',
                    'extra_price' => 0,
                ]);
            }
        }
    }
}
