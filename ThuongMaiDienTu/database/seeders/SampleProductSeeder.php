<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SampleProductSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('inventory_items')->truncate();
        DB::table('purchase_orders')->truncate();
        DB::table('suppliers')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('product_specifications')->truncate();
        DB::table('products')->truncate();
        Schema::enableForeignKeyConstraints();

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Tổng kho Điện Máy Pro',
            'phone' => '1900 1008'
        ]);

        $poId = DB::table('purchase_orders')->insertGetId([
            'supplier_id' => $supplierId,
            'total_cost' => 0,
            'created_at' => now(),
        ]);

        // Cấu hình dữ liệu theo từ khóa trong tên danh mục
        $config = [
            'iPhone' => [
                'names' => ['iPhone 15 Pro Max', 'iPhone 15 Pro', 'iPhone 14', 'iPhone 13'],
                'img' => 'https://images.unsplash.com/photo-1696446701796-da61225697cc?w=600&q=80',
                'cpu' => 'Apple A17 Pro', 'rams' => ['8GB'], 'roms' => ['256GB', '512GB']
            ],
            'Samsung' => [
                'names' => ['Samsung Galaxy S24 Ultra', 'Galaxy Z Fold 5', 'Galaxy S23 Plus'],
                'img' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&q=80',
                'cpu' => 'Snapdragon 8 Gen 3', 'rams' => ['12GB'], 'roms' => ['256GB', '512GB']
            ],
            'MacBook' => [
                'names' => ['MacBook Pro 14 M3', 'MacBook Air 13 M2', 'MacBook Pro 16 M2 Max'],
                'img' => 'https://images.unsplash.com/photo-1517336714460-45788a1f27e1?w=600&q=80',
                'cpu' => 'Apple M3', 'rams' => ['16GB', '24GB'], 'roms' => ['512GB', '1TB']
            ],
            'Gaming' => [
                'names' => ['ASUS ROG Strix G16', 'Acer Nitro 5', 'MSI Katana 15'],
                'img' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&q=80',
                'cpu' => 'Intel Core i7-13650HX', 'rams' => ['16GB'], 'roms' => ['512GB']
            ],
            'iPad' => [
                'names' => ['iPad Pro M2', 'iPad Air 5', 'iPad mini 6'],
                'img' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&q=80',
                'cpu' => 'Apple M2', 'rams' => ['8GB'], 'roms' => ['128GB', '256GB']
            ],
            'Tai nghe' => [
                'names' => ['AirPods Pro 2', 'Sony WH-1000XM5', 'Marshall Major IV'],
                'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&q=80',
                'cpu' => 'H2 Chip', 'rams' => ['N/A'], 'roms' => ['N/A']
            ],
            'Loa' => [
                'names' => ['Marshall Emberton II', 'JBL Charge 5', 'Sony SRS-XE200'],
                'img' => 'https://images.unsplash.com/photo-1589003020619-47211019625a?w=600&q=80',
                'cpu' => 'Bluetooth 5.3', 'rams' => ['N/A'], 'roms' => ['N/A']
            ],
            'Sạc dự phòng' => [
                'names' => ['Anker 737 PowerCore', 'Baseus Adaman 22.5W', 'Samsung 20,000mAh'],
                'img' => 'https://images.unsplash.com/photo-1609091839311-d536801027d3?w=600&q=80',
                'cpu' => 'GaN Technology', 'rams' => ['N/A'], 'roms' => ['20000mAh']
            ],
            'Default' => [
                'names' => ['Sản phẩm công nghệ mới', 'Phụ kiện cao cấp'],
                'img' => 'https://images.unsplash.com/photo-1468495244122-4a69b0fa8480?w=600&q=80',
                'cpu' => 'N/A', 'rams' => ['N/A'], 'roms' => ['N/A']
            ]
        ];

        $categories = Category::whereNotNull('parent_id')->get();

        for ($i = 1; $i <= 50; $i++) {
            $category = $categories->random();
            $catName = $category->name;
            
            // Tìm cấu hình phù hợp nhất dựa trên tên danh mục
            $matchedKey = 'Default';
            foreach (array_keys($config) as $key) {
                if (Str::contains($catName, $key)) {
                    $matchedKey = $key;
                    break;
                }
            }
            $t = $config[$matchedKey];

            $basePrice = rand(2, 45) * 1000000;
            $oldPrice = rand(0, 1) ? $basePrice + rand(1, 8) * 500000 : null;

            $product = Product::create([
                'category_id' => $category->category_id,
                'name' => $t['names'][array_rand($t['names'])] . ' (' . Str::random(5) . ')',
                'thumbnail' => $t['img'],
                'seo_description' => 'Sản phẩm thuộc danh mục ' . $catName . ' chất lượng cao tại Điện Máy Pro.',
                'base_price' => $basePrice,
                'old_price' => $oldPrice,
            ]);

            ProductSpecification::create([
                'product_id' => $product->product_id,
                'cpu_chip' => $t['cpu'],
                'ram_capacity' => $t['rams'][array_rand($t['rams'])],
                'screen_size' => rand(6, 16) . ' inch',
                'battery' => rand(3000, 5000) . ' mAh',
            ]);

            $variant = ProductVariant::create([
                'product_id' => $product->product_id,
                'color' => ['Đen', 'Trắng', 'Xanh', 'Vàng'][rand(0, 3)],
                'rom_capacity' => $t['roms'][array_rand($t['roms'])],
                'extra_price' => 0,
            ]);

            InventoryItem::create([
                'variant_id' => $variant->variant_id,
                'po_id' => $poId,
                'imei_serial' => 'SER-' . Str::upper(Str::random(12)),
                'status' => 'In_Stock',
            ]);
        }
    }
}
