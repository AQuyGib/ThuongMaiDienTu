<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::take(20)->get();

        if ($products->isEmpty()) {
            $this->command->info("No products found to add variants to.");
            return;
        }

        $colors = ['Đen', 'Trắng', 'Xanh', 'Hồng', 'Vàng Titan', 'Titan Tự Nhiên', 'Bạc', 'Xám Space'];
        $rams = ['8GB', '12GB', '16GB', '24GB', '32GB'];
        $roms = ['128GB', '256GB', '512GB', '1TB', '2TB'];

        $count = 0;
        $targetCount = 50;
        
        while ($count < $targetCount) {
            $product = $products->random();
            
            $color = $colors[array_rand($colors)];
            $ram = $rams[array_rand($rams)];
            $rom = $roms[array_rand($roms)];
            $extraPrice = rand(0, 10) * 500000; // 0 to 5,000,000 in steps of 500k
            
            // Tránh trùng lặp tổ hợp cho cùng 1 sản phẩm
            $exists = DB::table('product_variants')
                ->where('product_id', $product->product_id)
                ->where('color', $color)
                ->where('rom_capacity', $rom)
                ->exists();
                
            if (!$exists) {
                DB::table('product_variants')->insert([
                    'product_id' => $product->product_id,
                    'color' => $color,
                    'ram' => $ram,
                    'rom_capacity' => $rom,
                    'extra_price' => $extraPrice,
                    'image_url' => $product->thumbnail,
                ]);
                $count++;
            }
        }

        $this->command->info("Successfully seeded 50 product variants.");
    }
}
