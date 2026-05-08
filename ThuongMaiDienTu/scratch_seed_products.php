<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

$productsData = [
    [
        'category_name' => 'iPhone',
        'name' => 'iPhone 15 Pro Max 256GB',
        'thumbnail' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400',
        'base_price' => 34990000,
        'old_price' => 35990000,
        'ram' => '8GB',
        'rom' => '256GB',
        'cpu' => 'Apple A17 Pro',
        'screen' => '6.7 inch OLED',
        'os' => 'iOS 17',
    ],
    [
        'category_name' => 'Samsung',
        'name' => 'Samsung Galaxy S24 Ultra 5G',
        'thumbnail' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=400',
        'base_price' => 33990000,
        'old_price' => 36990000,
        'ram' => '12GB',
        'rom' => '256GB',
        'cpu' => 'Snapdragon 8 Gen 3',
        'screen' => '6.8 inch AMOLED',
        'os' => 'Android 14',
    ],
    [
        'category_name' => 'MacBook',
        'name' => 'MacBook Air 13 inch M3 2024',
        'thumbnail' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400',
        'base_price' => 27990000,
        'old_price' => 29990000,
        'ram' => '8GB',
        'rom' => '256GB',
        'cpu' => 'Apple M3',
        'screen' => '13.6 inch Liquid Retina',
        'os' => 'macOS',
    ],
    [
        'category_name' => 'Laptop Gaming',
        'name' => 'ASUS ROG Strix G16 2024',
        'thumbnail' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400',
        'base_price' => 32990000,
        'old_price' => 36990000,
        'ram' => '16GB',
        'rom' => '512GB',
        'cpu' => 'Core i7-14700HX',
        'screen' => '16 inch 165Hz',
        'os' => 'Windows 11',
    ],
    [
        'category_name' => 'iPad',
        'name' => 'iPad Pro M4 11 inch 2024',
        'thumbnail' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400',
        'base_price' => 28990000,
        'old_price' => 30990000,
        'ram' => '8GB',
        'rom' => '256GB',
        'cpu' => 'Apple M4',
        'screen' => '11 inch OLED',
        'os' => 'iPadOS 17',
    ],
    [
        'category_name' => 'Tai nghe',
        'name' => 'Sony WH-1000XM5 Noise Cancelling',
        'thumbnail' => 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=400',
        'base_price' => 6990000,
        'old_price' => 8490000,
        'ram' => 'N/A',
        'rom' => 'N/A',
        'cpu' => 'V1 Processor',
        'screen' => 'N/A',
        'os' => 'N/A',
    ],
    [
        'category_name' => 'Đồng hồ thông minh',
        'name' => 'Apple Watch Series 9 41mm',
        'thumbnail' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=400',
        'base_price' => 9490000,
        'old_price' => 10490000,
        'ram' => 'N/A',
        'rom' => '64GB',
        'cpu' => 'S9 SiP',
        'screen' => 'LTPO OLED',
        'os' => 'watchOS 10',
    ],
    [
        'category_name' => 'Xiaomi',
        'name' => 'Xiaomi 14 Ultra 5G Leica',
        'thumbnail' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400',
        'base_price' => 23990000,
        'old_price' => 25990000,
        'ram' => '16GB',
        'rom' => '512GB',
        'cpu' => 'Snapdragon 8 Gen 3',
        'screen' => '6.73 inch AMOLED',
        'os' => 'HyperOS',
    ],
    [
        'category_name' => 'OPPO',
        'name' => 'OPPO Find X7 Ultra 5G',
        'thumbnail' => 'https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=400',
        'base_price' => 21990000,
        'old_price' => 23990000,
        'ram' => '12GB',
        'rom' => '256GB',
        'cpu' => 'Snapdragon 8 Gen 3',
        'screen' => '6.82 inch AMOLED',
        'os' => 'ColorOS 14',
    ],
    [
        'category_name' => 'Laptop Văn phòng',
        'name' => 'Dell XPS 15 9530 Core i7',
        'thumbnail' => 'https://images.unsplash.com/photo-1531297572550-8cc3df7a0f69?w=400',
        'base_price' => 38490000,
        'old_price' => 41990000,
        'ram' => '16GB',
        'rom' => '512GB',
        'cpu' => 'Core i7-13700H',
        'screen' => '15.6 inch 4K OLED',
        'os' => 'Windows 11',
    ],
    [
        'category_name' => 'Loa Bluetooth',
        'name' => 'Marshall Emberton II Black and Brass',
        'thumbnail' => 'https://images.unsplash.com/photo-1589492477829-5e65395b66cc?w=400',
        'base_price' => 3990000,
        'old_price' => 4490000,
        'ram' => 'N/A',
        'rom' => 'N/A',
        'cpu' => 'N/A',
        'screen' => 'N/A',
        'os' => 'N/A',
    ],
    [
        'category_name' => 'Samsung',
        'name' => 'Samsung Galaxy Z Fold 5 512GB',
        'thumbnail' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400',
        'base_price' => 40990000,
        'old_price' => 44990000,
        'ram' => '12GB',
        'rom' => '512GB',
        'cpu' => 'Snapdragon 8 Gen 2',
        'screen' => '7.6 inch Foldable',
        'os' => 'Android 13',
    ],
    [
        'category_name' => 'Tivi, Màn hình',
        'name' => 'LG C3 OLED 4K TV 55 inch',
        'thumbnail' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=400',
        'base_price' => 31900000,
        'old_price' => 35900000,
        'ram' => 'N/A',
        'rom' => 'N/A',
        'cpu' => 'a9 Gen6 AI',
        'screen' => '55 inch OLED 4K',
        'os' => 'webOS 23',
    ],
    [
        'category_name' => 'Tai nghe',
        'name' => 'Apple AirPods Pro 2 MagSafe',
        'thumbnail' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400',
        'base_price' => 5590000,
        'old_price' => 6190000,
        'ram' => 'N/A',
        'rom' => 'N/A',
        'cpu' => 'H2 Chip',
        'screen' => 'N/A',
        'os' => 'N/A',
    ],
    [
        'category_name' => 'Đồng hồ thông minh',
        'name' => 'Garmin Fenix 7 Solar Sapphire',
        'thumbnail' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400',
        'base_price' => 18990000,
        'old_price' => 21490000,
        'ram' => 'N/A',
        'rom' => '32GB',
        'cpu' => 'N/A',
        'screen' => '1.3 inch MIP',
        'os' => 'Garmin OS',
    ],
    [
        'category_name' => 'Gia dụng, Smarthome',
        'name' => 'Dyson V15 Detect Absolute',
        'thumbnail' => 'https://images.unsplash.com/photo-1558317374-067fb5f30001?w=400',
        'base_price' => 19990000,
        'old_price' => 22990000,
        'ram' => 'N/A',
        'rom' => 'N/A',
        'cpu' => 'Hyperdymium',
        'screen' => 'LCD Display',
        'os' => 'N/A',
    ],
    [
        'category_name' => 'Phụ kiện',
        'name' => 'Anker 737 Power Bank 140W',
        'thumbnail' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=400',
        'base_price' => 2990000,
        'old_price' => 3490000,
        'ram' => 'N/A',
        'rom' => 'N/A',
        'cpu' => 'GaNPrime',
        'screen' => 'Smart Display',
        'os' => 'N/A',
    ],
    [
        'category_name' => 'Phụ kiện',
        'name' => 'Keychron K2 V2 Mechanical Keyboard',
        'thumbnail' => 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=400',
        'base_price' => 1990000,
        'old_price' => 2290000,
        'ram' => 'N/A',
        'rom' => 'N/A',
        'cpu' => 'Bluetooth 5.1',
        'screen' => 'N/A',
        'os' => 'macOS/Windows',
    ],
    [
        'category_name' => 'Phụ kiện',
        'name' => 'Logitech MX Master 3S Mouse',
        'thumbnail' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400',
        'base_price' => 2490000,
        'old_price' => 2990000,
        'ram' => 'N/A',
        'rom' => 'N/A',
        'cpu' => '8K DPI Sensor',
        'screen' => 'N/A',
        'os' => 'Logi Options+',
    ],
    [
        'category_name' => 'Tivi, Màn hình',
        'name' => 'Nintendo Switch OLED Model',
        'thumbnail' => 'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=400',
        'base_price' => 7990000,
        'old_price' => 8990000,
        'ram' => '4GB',
        'rom' => '64GB',
        'cpu' => 'NVIDIA Tegra',
        'screen' => '7 inch OLED',
        'os' => 'Nintendo OS',
    ],
];

foreach ($productsData as $data) {
    $catName = $data['category_name'];
    unset($data['category_name']);
    
    $category = Category::where('name', $catName)->first();
    if (!$category) {
        // Fallback to a default category if not found
        $category = Category::first();
    }
    
    $data['category_id'] = $category->category_id;
    $data['slug'] = Str::slug($data['name']);
    $data['description'] = 'Mô tả chi tiết cho ' . $data['name'] . '. Sản phẩm chất lượng cao, bảo hành chính hãng.';
    $data['specifications'] = json_encode([
        'Bảo hành' => '12 tháng',
        'Tình trạng' => 'Mới 100%',
        'Xuất xứ' => 'Chính hãng',
    ]);
    $data['status'] = 1;
    $data['hot_flag'] = rand(0, 1);
    $data['rating'] = rand(40, 50) / 10;
    $data['review_count'] = rand(50, 2000);
    $data['view_count'] = rand(1000, 10000);
    $data['sold_count'] = rand(10, 500);
    $data['discount_percent'] = round((($data['old_price'] - $data['base_price']) / $data['old_price']) * 100);

    Product::create($data);
}

echo "Seeded 20 products successfully!";
