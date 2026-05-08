<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;


$productsData = [
    // ĐIỆN THOẠI
    ['cat' => 'Điện thoại', 'name' => 'iPhone 15 Pro Max 256GB', 'price' => 29990000, 'img' => 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=800', 'specs' => ['ram' => '8GB', 'rom' => '256GB']],
    ['cat' => 'Điện thoại', 'name' => 'Samsung Galaxy S24 Ultra', 'price' => 26990000, 'img' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800', 'specs' => ['ram' => '12GB', 'rom' => '256GB']],
    ['cat' => 'Điện thoại', 'name' => 'Xiaomi 14 Pro Premium', 'price' => 18990000, 'img' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800', 'specs' => ['ram' => '12GB', 'rom' => '512GB']],
    ['cat' => 'Điện thoại', 'name' => 'Oppo Reno 11 Pro 5G', 'price' => 14990000, 'img' => 'https://images.unsplash.com/photo-1585060544812-6b459033535d?w=800', 'specs' => ['ram' => '12GB', 'rom' => '256GB']],

    // LAPTOP
    ['cat' => 'Laptop', 'name' => 'MacBook Air M3 13 inch', 'price' => 27990000, 'img' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800', 'specs' => ['ram' => '16GB', 'cpu' => 'Apple M3', 'rom' => '256GB']],
    ['cat' => 'Laptop', 'name' => 'Dell XPS 13 Plus 9320', 'price' => 45000000, 'img' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800', 'specs' => ['ram' => '16GB', 'cpu' => 'Intel Core i7', 'rom' => '512GB']],
    ['cat' => 'Laptop', 'name' => 'ASUS ROG Strix G16 2024', 'price' => 32000000, 'img' => 'https://images.unsplash.com/photo-1544117518-2b04178ec3ea?w=800', 'specs' => ['ram' => '32GB', 'cpu' => 'Intel Core i9', 'rom' => '1TB']],
    ['cat' => 'Laptop', 'name' => 'HP Spectre x360 14', 'price' => 38000000, 'img' => 'https://images.unsplash.com/photo-1589561084283-930aa7b1ce50?w=800', 'specs' => ['ram' => '16GB', 'cpu' => 'Intel Core i7', 'rom' => '1TB']],

    // TABLET
    ['cat' => 'Tablet', 'name' => 'iPad Pro M2 11 inch Wi-Fi', 'price' => 21000000, 'img' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800', 'specs' => ['screen' => '11 inch', 'rom' => '128GB']],

    // ÂM THANH
    ['cat' => 'Âm thanh', 'name' => 'AirPods Pro Gen 2 MagSafe', 'price' => 5990000, 'img' => 'https://images.unsplash.com/photo-1504274066654-52ff9a59c0b8?w=800', 'specs' => ['type' => 'In-ear']],
    ['cat' => 'Âm thanh', 'name' => 'Sony WH-1000XM5 Headphone', 'price' => 8490000, 'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800', 'specs' => ['type' => 'Over-ear']],

    // WATCH
    ['cat' => 'Đồng hồ thông minh', 'name' => 'Apple Watch Series 9 GPS', 'price' => 10490000, 'img' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=800', 'specs' => ['size' => '45mm']],

    // TIVI
    ['cat' => 'Tivi, Màn hình', 'name' => 'LG OLED C3 65 inch 4K', 'price' => 39000000, 'img' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800', 'specs' => ['screen' => '65 inch', 'resolution' => '4K']],
];

Product::query()->forceDelete();

$count = 0;
// Lặp lại dữ liệu để đủ 100 cái nhưng tên sẽ có số thứ tự để phân biệt
for ($i = 1; $i <= 100; $i++) {
    $item = $productsData[($i - 1) % count($productsData)];
    $category = Category::where('name', $item['cat'])->first();
    if (!$category)
        continue;

    $finalName = $item['name'] . ' (Bản số ' . $i . ')';

    Product::create([
        'category_id' => $category->category_id,
        'name' => $finalName,
        'slug' => Str::slug($finalName) . '-' . Str::random(4),
        'old_price' => $item['price'] + 1000000,
        'base_price' => $item['price'],
        'discount_percent' => 10,
        'thumbnail' => $item['img'],
        'rating' => 5,
        'review_count' => 500 + $i,
        'specifications' => $item['specs'],
        'status' => 1,
        'created_at' => now(),
    ]);

    $count++;
}


