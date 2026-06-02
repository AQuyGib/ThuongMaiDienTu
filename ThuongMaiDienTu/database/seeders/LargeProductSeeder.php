<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LargeProductSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo Supplier và Purchase Order để làm khóa ngoại cho Inventory Items nếu chưa có
        $supplierId = DB::table('suppliers')->first()?->supplier_id;
        if (!$supplierId) {
            $supplierId = DB::table('suppliers')->insertGetId([
                'name' => 'Tổng kho Điện Máy Pro',
                'contact_info' => '1900 1008'
            ]);
        }

        $poId = DB::table('purchase_orders')->first()?->po_id;
        if (!$poId) {
            $poId = DB::table('purchase_orders')->insertGetId([
                'supplier_id' => $supplierId,
                'total_cost' => 0,
                'created_at' => now(),
            ]);
        }

        // Dữ liệu mẫu theo danh mục để sinh ngẫu nhiên
        $brandsData = [
            'Apple' => ['iPhone', 'iPad', 'MacBook', 'Tai nghe', 'Đồng hồ thông minh'],
            'Samsung' => ['Điện thoại', 'Tablet', 'Tivi, Màn hình', 'Gia dụng, Smarthome', 'Đồng hồ thông minh'],
            'Xiaomi' => ['Điện thoại', 'Tablet', 'Gia dụng, Smarthome', 'Đồng hồ thông minh', 'Phụ kiện'],
            'OPPO' => ['Điện thoại'],
            'Asus' => ['Laptop', 'Tivi, Màn hình'],
            'Dell' => ['Laptop', 'Tivi, Màn hình'],
            'HP' => ['Laptop'],
            'Lenovo' => ['Laptop', 'Tablet'],
            'Acer' => ['Laptop'],
            'MSI' => ['Laptop'],
            'Sony' => ['Âm thanh', 'Tivi, Màn hình'],
            'JBL' => ['Âm thanh'],
            'Marshall' => ['Âm thanh'],
            'Anker' => ['Phụ kiện'],
            'Baseus' => ['Phụ kiện'],
            'Ugreen' => ['Phụ kiện'],
            'LG' => ['Tivi, Màn hình'],
            'Panasonic' => ['Gia dụng, Smarthome'],
            'Philips' => ['Gia dụng, Smarthome'],
        ];

        // Lấy tất cả danh mục
        $categories = Category::all();

        foreach ($categories as $category) {
            $catId = $category->category_id;
            $catName = $category->name;
            $catSlug = $category->slug;

            // Đếm số sản phẩm hiện tại của danh mục này
            $currentCount = Product::where('category_id', $catId)->count();
            $needed = 20 - $currentCount;

            if ($needed <= 0) {
                continue;
            }

            // Tìm các thương hiệu phù hợp với danh mục
            $possibleBrands = [];
            foreach ($brandsData as $brandName => $cats) {
                foreach ($cats as $cat) {
                    if (Str::contains(Str::lower($catName), Str::lower($cat)) || Str::contains(Str::lower($cat), Str::lower($catName))) {
                        $possibleBrands[] = $brandName;
                    }
                }
            }
            if (empty($possibleBrands)) {
                $possibleBrands = ['OEM', 'Điện Máy Pro'];
            }

            for ($i = 0; $i < $needed; $i++) {
                $brand = $possibleBrands[array_rand($possibleBrands)];
                $name = $this->generateProductName($catName, $brand, $i);
                $basePrice = $this->generateBasePrice($catName);
                
                // 30% có giá cũ (để test bộ lọc khuyến mãi)
                $oldPrice = null;
                $discountPercent = 0;
                if (rand(1, 10) <= 3) {
                    $discountPercent = rand(5, 30);
                    $oldPrice = (int)($basePrice / (1 - $discountPercent / 100));
                    // Làm tròn số đẹp
                    $oldPrice = ceil($oldPrice / 10000) * 10000;
                }

                // Cấu hình thông số kỹ thuật (specifications) ngẫu nhiên hợp lý
                $specs = $this->generateSpecifications($catName, $brand);

                $product = Product::create([
                    'category_id' => $catId,
                    'name' => $name,
                    'brand' => $brand,
                    'slug' => Str::slug($name) . '-' . Str::random(4),
                    'thumbnail' => $this->getRandomThumbnail($catName),
                    'seo_description' => "Mua ngay $name chính hãng tại Điện Máy Pro. Giá tốt, bảo hành lâu dài, trả góp 0%.",
                    'base_price' => $basePrice,
                    'old_price' => $oldPrice,
                    'discount_percent' => $discountPercent,
                    'rating' => number_format(rand(350, 500) / 100, 2), // 3.50 đến 5.00
                    'review_count' => rand(5, 120),
                    'view_count' => rand(100, 2000),
                    'sold_count' => rand(10, 500),
                    'status' => 1,
                    'hot_flag' => rand(0, 1),
                    'specifications' => json_encode($specs, JSON_UNESCAPED_UNICODE),
                ]);

                // Tạo 1 - 3 variants
                $colors = ['Đen', 'Trắng', 'Xám', 'Bạc', 'Xanh dương', 'Vàng'];
                $variantColors = (array)array_rand(array_flip($colors), rand(1, 3));
                
                foreach ($variantColors as $color) {
                    $rom = isset($specs['Bộ nhớ trong']) ? $specs['Bộ nhớ trong'] : (isset($specs['Dung lượng']) ? $specs['Dung lượng'] : null);
                    $ram = isset($specs['RAM']) ? $specs['RAM'] : null;
                    
                    $extraPrice = 0;
                    if ($rom && Str::contains($rom, 'GB')) {
                        // Variant có dung lượng lớn hơn thì đắt hơn chút
                        if (Str::contains($color, 'Vàng') || Str::contains($color, 'Xám')) {
                            $extraPrice = rand(0, 2) * 500000;
                        }
                    }

                    $variant = ProductVariant::create([
                        'product_id' => $product->product_id,
                        'color' => $color,
                        'rom_capacity' => $rom,
                        'ram' => $ram,
                        'cpu_chip' => isset($specs['Chip']) ? $specs['Chip'] : null,
                        'gpu_chip' => isset($specs['Đồ họa']) ? $specs['Đồ họa'] : null,
                        'extra_price' => $extraPrice,
                        'safe_stock' => rand(5, 50),
                    ]);

                    // Tạo các inventory items (hàng tồn kho)
                    // 15% khả năng hết hàng (để test lọc "Sẵn hàng")
                    $inStockCount = (rand(1, 100) <= 15) ? 0 : rand(1, 8);
                    
                    for ($k = 0; $k < $inStockCount; $k++) {
                        InventoryItem::create([
                            'variant_id' => $variant->variant_id,
                            'po_id' => $poId,
                            'imei_serial' => 'IMEI-' . Str::upper(Str::random(12)),
                            'status' => 'In_Stock',
                        ]);
                    }
                    
                    // Tạo một số sản phẩm đã bán
                    $soldCount = rand(0, 5);
                    for ($k = 0; $k < $soldCount; $k++) {
                        InventoryItem::create([
                            'variant_id' => $variant->variant_id,
                            'po_id' => $poId,
                            'imei_serial' => 'IMEI-' . Str::upper(Str::random(12)),
                            'status' => 'Sold',
                        ]);
                    }
                }
            }
        }
    }

    private function generateProductName(string $catName, string $brand, int $index): string
    {
        $catLower = Str::lower($catName);
        $randomSuffix = rand(10, 99) . ($index + 1);

        if (Str::contains($catLower, 'phone') || Str::contains($catLower, 'thoại')) {
            $models = ['Galaxy S', 'Galaxy A', 'Redmi Note', 'Mi Pro', 'Find X', 'Reno', 'iPhone 15', 'iPhone 14', 'iPhone SE'];
            $model = $models[array_rand($models)];
            if ($brand === 'Apple') {
                return "iPhone " . (rand(0, 1) ? "15 Pro Max" : "14 Pro") . " " . (rand(0, 1) ? "256GB" : "128GB") . " ($randomSuffix)";
            }
            return "$brand $model $randomSuffix";
        }

        if (Str::contains($catLower, 'laptop') || Str::contains($catLower, 'macbook')) {
            $models = ['Pro 14', 'Air 13', 'ROG Strix', 'TUF Gaming', 'Inspiron', 'Vostro', 'Pavilion', 'ThinkPad', 'Nitro 5', 'Katana'];
            $model = $models[array_rand($models)];
            if ($brand === 'Apple') {
                return "MacBook Pro M3 " . (rand(0, 1) ? "16GB/512GB" : "8GB/256GB") . " ($randomSuffix)";
            }
            return "Laptop $brand $model $randomSuffix";
        }

        if (Str::contains($catLower, 'tablet') || Str::contains($catLower, 'ipad') || Str::contains($catLower, 'tab')) {
            $models = ['Galaxy Tab S9', 'Galaxy Tab A9', 'Pad 6', 'Redmi Pad', 'M10 Plus'];
            $model = $models[array_rand($models)];
            if ($brand === 'Apple') {
                return "iPad Pro 11 inch M2 ($randomSuffix)";
            }
            return "Máy tính bảng $brand $model $randomSuffix";
        }

        if (Str::contains($catLower, 'tai nghe') || Str::contains($catLower, 'âm thanh') || Str::contains($catLower, 'loa')) {
            $types = ['Tai nghe không dây', 'Loa Bluetooth', 'Tai nghe Gaming', 'Loa Soundbar'];
            $type = $types[array_rand($types)];
            return "$type $brand " . Str::upper(Str::random(3)) . "-$randomSuffix";
        }

        if (Str::contains($catLower, 'đồng hồ') || Str::contains($catLower, 'watch')) {
            return "Đồng hồ thông minh $brand Watch S$randomSuffix";
        }

        if (Str::contains($catLower, 'sạc') || Str::contains($catLower, 'phụ kiện') || Str::contains($catLower, 'cáp') || Str::contains($catLower, 'ốp')) {
            $items = ['Sạc nhanh GaN 65W', 'Cáp sạc USB-C', 'Sạc dự phòng 20000mAh', 'Ốp lưng Silicon chống sốc'];
            $item = $items[array_rand($items)];
            return "$item $brand $randomSuffix";
        }

        if (Str::contains($catLower, 'tivi') || Str::contains($catLower, 'màn hình')) {
            $types = ['Smart Tivi 4K', 'Màn hình Gaming OLED', 'Tivi QLED', 'Màn hình đồ họa IPS'];
            $type = $types[array_rand($types)];
            $size = [24, 27, 32, 43, 55, 65][array_rand([24, 27, 32, 43, 55, 65])];
            return "$type $brand $size inch $randomSuffix";
        }

        if (Str::contains($catLower, 'gia dụng') || Str::contains($catLower, 'smarthome')) {
            $items = ['Robot hút bụi lau nhà', 'Máy lọc không khí thông minh', 'Nồi cơm điện cao tần', 'Lò vi sóng có nướng'];
            $item = $items[array_rand($items)];
            return "$item $brand $randomSuffix";
        }

        return "Sản phẩm công nghệ $brand $randomSuffix";
    }

    private function generateBasePrice(string $catName): int
    {
        $catLower = Str::lower($catName);

        if (Str::contains($catLower, 'phone') || Str::contains($catLower, 'thoại')) {
            return rand(3, 35) * 1000000;
        }
        if (Str::contains($catLower, 'laptop') || Str::contains($catLower, 'macbook')) {
            return rand(10, 60) * 1000000;
        }
        if (Str::contains($catLower, 'tablet') || Str::contains($catLower, 'ipad') || Str::contains($catLower, 'tab')) {
            return rand(4, 25) * 1000000;
        }
        if (Str::contains($catLower, 'tivi') || Str::contains($catLower, 'màn hình')) {
            return rand(3, 40) * 1000000;
        }
        if (Str::contains($catLower, 'tai nghe') || Str::contains($catLower, 'loa') || Str::contains($catLower, 'âm thanh')) {
            return rand(5, 120) * 100000;
        }
        if (Str::contains($catLower, 'đồng hồ')) {
            return rand(15, 150) * 100000;
        }
        if (Str::contains($catLower, 'gia dụng') || Str::contains($catLower, 'smarthome')) {
            return rand(15, 150) * 100000;
        }

        return rand(1, 20) * 100000; // Phụ kiện, linh tinh khác
    }

    private function generateSpecifications(string $catName, string $brand): array
    {
        $catLower = Str::lower($catName);
        $specs = [
            'Thương hiệu' => $brand,
            'Tình trạng' => 'Hàng mới 100%',
            'Bảo hành' => '12 tháng',
            'eco_friendly' => (rand(1, 100) <= 25) ? 'Yes' : 'No', // 25% thân thiện môi trường
        ];

        if (Str::contains($catLower, 'phone') || Str::contains($catLower, 'thoại') || Str::contains($catLower, 'tablet') || Str::contains($catLower, 'ipad')) {
            $rams = ['4GB', '6GB', '8GB', '12GB', '16GB'];
            $roms = ['64GB', '128GB', '256GB', '512GB'];
            $chips = ['Apple A16 Bionic', 'Apple A17 Pro', 'Snapdragon 8 Gen 2', 'Snapdragon 8 Gen 3', 'MediaTek Dimensity 9200', 'Helio G99'];
            
            $specs['RAM'] = $rams[array_rand($rams)];
            $specs['Bộ nhớ trong'] = $roms[array_rand($roms)];
            $specs['Chip'] = $chips[array_rand($chips)];
            $specs['Pin'] = rand(4000, 6000) . ' mAh';
            $specs['Màn hình'] = (rand(1, 10) > 5 ? 'AMOLED' : 'IPS LCD') . ', ' . (rand(60, 129) / 10) . ' inch';
            
            // Dành cho bộ lọc gợi ý nhanh (Needs)
            $ramInt = (int)filter_var($specs['RAM'], FILTER_SANITIZE_NUMBER_INT);
            if ($ramInt >= 12 || Str::contains($specs['Chip'], 'A17') || Str::contains($specs['Chip'], 'Gen 3')) {
                $specs['Phù hợp'] = 'Chơi mượt Genshin';
            } else {
                $specs['Phù hợp'] = 'Học Web Dev';
            }
            return $specs;
        }

        if (Str::contains($catLower, 'laptop') || Str::contains($catLower, 'macbook')) {
            $rams = ['8GB', '16GB', '32GB', '64GB'];
            $roms = ['256GB', '512GB', '1TB', '2TB'];
            $chips = ['Intel Core i5-1340P', 'Intel Core i7-13700H', 'AMD Ryzen 7 7735HS', 'Apple M2', 'Apple M3 Pro', 'Intel Core i9-13900HX'];
            
            $specs['RAM'] = $rams[array_rand($rams)];
            $specs['Bộ nhớ trong'] = $roms[array_rand($roms)];
            $specs['Chip'] = $chips[array_rand($chips)];
            $specs['Đồ họa'] = (rand(1, 10) > 5 ? 'NVIDIA GeForce RTX 4060' : 'Intel Iris Xe Graphics');
            $specs['Màn hình'] = [13.3, 14.0, 15.6, 16.0][array_rand([13.3, 14.0, 15.6, 16.0])] . ' inch, 144Hz';
            
            $ramInt = (int)filter_var($specs['RAM'], FILTER_SANITIZE_NUMBER_INT);
            if ($ramInt >= 16 && (Str::contains($specs['Đồ họa'], 'RTX') || Str::contains($specs['Chip'], 'i9') || Str::contains($specs['Chip'], 'M3'))) {
                $specs['Phù hợp'] = 'Chơi mượt Genshin';
            } else {
                $specs['Phù hợp'] = 'Học Web Dev';
            }
            return $specs;
        }

        if (Str::contains($catLower, 'tivi') || Str::contains($catLower, 'màn hình')) {
            $specs['Độ phân giải'] = '4K Ultra HD (3840 x 2160)';
            $specs['Tần số quét'] = rand(0, 1) ? '60Hz' : '120Hz';
            $specs['Kết nối'] = 'HDMI, USB, Wi-Fi, Bluetooth';
            return $specs;
        }

        if (Str::contains($catLower, 'tai nghe') || Str::contains($catLower, 'loa') || Str::contains($catLower, 'âm thanh')) {
            $specs['Kết nối'] = 'Bluetooth 5.3';
            $specs['Thời lượng pin'] = rand(5, 30) . ' giờ';
            $specs['Chống nước'] = 'IPX' . rand(4, 7);
            return $specs;
        }

        $specs['Thông tin'] = 'Sản phẩm công nghệ cao cấp chính hãng.';
        return $specs;
    }

    private function getRandomThumbnail(string $catName): string
    {
        $catLower = Str::lower($catName);

        if (Str::contains($catLower, 'phone') || Str::contains($catLower, 'thoại')) {
            return 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&q=80';
        }
        if (Str::contains($catLower, 'laptop') || Str::contains($catLower, 'macbook')) {
            return 'https://images.unsplash.com/photo-1496181130204-755241524eab?w=400&q=80';
        }
        if (Str::contains($catLower, 'tablet') || Str::contains($catLower, 'ipad') || Str::contains($catLower, 'tab')) {
            return 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&q=80';
        }
        if (Str::contains($catLower, 'tai nghe') || Str::contains($catLower, 'loa') || Str::contains($catLower, 'âm thanh')) {
            return 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80';
        }
        if (Str::contains($catLower, 'tivi') || Str::contains($catLower, 'màn hình')) {
            return 'https://images.unsplash.com/photo-1593305841991-05c297ba4575?w=400&q=80';
        }
        if (Str::contains($catLower, 'đồng hồ')) {
            return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80';
        }
        if (Str::contains($catLower, 'gia dụng') || Str::contains($catLower, 'smarthome')) {
            return 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=400&q=80';
        }

        return 'https://images.unsplash.com/photo-1468495244122-4a69b0fa8480?w=400&q=80';
    }
}
