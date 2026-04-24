<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh mục đã seed từ CategorySeeder
        $catDienThoai = Category::where('name', 'Điện thoại')->first();
        $catLaptop    = Category::where('name', 'Laptop')->first();
        $catTablet    = Category::where('name', 'Tablet')->first();
        $catAmThanh   = Category::where('name', 'Âm thanh')->first();
        $catDongHo    = Category::where('name', 'Đồng hồ thông minh')->first();
        $catPhuKien   = Category::where('name', 'Phụ kiện')->first();
        $catTivi      = Category::where('name', 'Tivi, Màn hình')->first();
        $catGiaDung   = Category::where('name', 'Gia dụng, Smarthome')->first();

        $products = [
            // ===== ĐIỆN THOẠI (10 sản phẩm) =====
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'iPhone 15 Pro Max 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=300',
                'base_price' => 34990000,
                'old_price' => 35990000,
            ],
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'iPhone 15 128GB Chính hãng VN/A',
                'thumbnail' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=300',
                'base_price' => 22990000,
                'old_price' => 24990000,
            ],
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'Samsung Galaxy S24 Ultra 5G 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=300',
                'base_price' => 33990000,
                'old_price' => 36990000,
            ],
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'Samsung Galaxy Z Fold5 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=300',
                'base_price' => 40990000,
                'old_price' => 41990000,
            ],
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'Xiaomi 14 Ultra 5G 512GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300',
                'base_price' => 23990000,
                'old_price' => 25990000,
            ],
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'OPPO Find X6 Pro 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=300',
                'base_price' => 19990000,
                'old_price' => 21990000,
            ],
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'Samsung Galaxy A55 5G 128GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?w=300',
                'base_price' => 9990000,
                'old_price' => 10990000,
            ],
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'OPPO Reno11 F 5G 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1585060544812-6b45742d762f?w=300',
                'base_price' => 8490000,
                'old_price' => 8990000,
            ],
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'Xiaomi Redmi Note 13 Pro 5G',
                'thumbnail' => 'https://images.unsplash.com/photo-1556656793-08538906a9f8?w=300',
                'base_price' => 7990000,
                'old_price' => 8990000,
            ],
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'iPhone 14 128GB Chính hãng',
                'thumbnail' => 'https://images.unsplash.com/photo-1591337676887-a217a6c7e2e4?w=300',
                'base_price' => 17990000,
                'old_price' => 19990000,
            ],

            // ===== LAPTOP (5 sản phẩm) =====
            [
                'category_id' => $catLaptop->category_id,
                'name' => 'MacBook Air 15 inch M3 2024 8GB/256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=300',
                'base_price' => 32990000,
                'old_price' => 35990000,
            ],
            [
                'category_id' => $catLaptop->category_id,
                'name' => 'MacBook Pro 14 inch M3 Pro 2024',
                'thumbnail' => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=300',
                'base_price' => 49990000,
                'old_price' => 52990000,
            ],
            [
                'category_id' => $catLaptop->category_id,
                'name' => 'ASUS ROG Strix G16 2024 i7-14700HX RTX4060',
                'thumbnail' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=300',
                'base_price' => 32990000,
                'old_price' => 36990000,
            ],
            [
                'category_id' => $catLaptop->category_id,
                'name' => 'Dell XPS 15 2024 Core i7/16GB/512GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1531297572550-8cc3df7a0f69?w=300',
                'base_price' => 38490000,
                'old_price' => 41990000,
            ],
            [
                'category_id' => $catLaptop->category_id,
                'name' => 'Lenovo ThinkPad X1 Carbon Gen 12',
                'thumbnail' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=300',
                'base_price' => 35990000,
                'old_price' => 39990000,
            ],

            // ===== TABLET (3 sản phẩm) =====
            [
                'category_id' => $catTablet->category_id,
                'name' => 'iPad Pro M4 11 inch 2024 WiFi 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=300',
                'base_price' => 28990000,
                'old_price' => 30990000,
            ],
            [
                'category_id' => $catTablet->category_id,
                'name' => 'iPad Air M2 13 inch 2024 WiFi 128GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1585790050230-5dd28404ccb9?w=300',
                'base_price' => 18990000,
                'old_price' => 19990000,
            ],
            [
                'category_id' => $catTablet->category_id,
                'name' => 'Samsung Galaxy Tab S9 FE 128GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1561154464-82e9aab32f4d?w=300',
                'base_price' => 9990000,
                'old_price' => 11990000,
            ],

            // ===== ÂM THANH (3 sản phẩm) =====
            [
                'category_id' => $catAmThanh->category_id,
                'name' => 'Apple AirPods Pro 2 USB-C 2024',
                'thumbnail' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300',
                'base_price' => 5590000,
                'old_price' => 6990000,
            ],
            [
                'category_id' => $catAmThanh->category_id,
                'name' => 'Sony WH-1000XM5 Chống ồn',
                'thumbnail' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=300',
                'base_price' => 7490000,
                'old_price' => 8490000,
            ],
            [
                'category_id' => $catAmThanh->category_id,
                'name' => 'JBL Charge 5 Loa Bluetooth chống nước',
                'thumbnail' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=300',
                'base_price' => 3190000,
                'old_price' => 3990000,
            ],

            // ===== ĐỒNG HỒ THÔNG MINH (3 sản phẩm) =====
            [
                'category_id' => $catDongHo->category_id,
                'name' => 'Apple Watch Series 9 GPS 45mm',
                'thumbnail' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=300',
                'base_price' => 10990000,
                'old_price' => 12490000,
            ],
            [
                'category_id' => $catDongHo->category_id,
                'name' => 'Samsung Galaxy Watch 6 Classic 47mm',
                'thumbnail' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=300',
                'base_price' => 8990000,
                'old_price' => 9990000,
            ],
            [
                'category_id' => $catDongHo->category_id,
                'name' => 'Apple Watch Ultra 2 GPS + Cellular 49mm',
                'thumbnail' => 'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=300',
                'base_price' => 21990000,
                'old_price' => 23990000,
            ],

            // ===== PHỤ KIỆN (3 sản phẩm) =====
            [
                'category_id' => $catPhuKien->category_id,
                'name' => 'Sạc dự phòng Anker 20000mAh 65W',
                'thumbnail' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=300',
                'base_price' => 1290000,
                'old_price' => 1590000,
            ],
            [
                'category_id' => $catPhuKien->category_id,
                'name' => 'Cáp sạc nhanh USB-C to Lightning 2m',
                'thumbnail' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=300',
                'base_price' => 390000,
                'old_price' => 490000,
            ],
            [
                'category_id' => $catPhuKien->category_id,
                'name' => 'Ốp lưng MagSafe iPhone 15 Pro Max',
                'thumbnail' => 'https://images.unsplash.com/photo-1601784551446-20c9e07cdbdb?w=300',
                'base_price' => 890000,
                'old_price' => 1190000,
            ],

            // ===== TIVI, MÀN HÌNH (2 sản phẩm) =====
            [
                'category_id' => $catTivi->category_id,
                'name' => 'Samsung Smart TV 4K 55 inch QA55Q80C',
                'thumbnail' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=300',
                'base_price' => 19990000,
                'old_price' => 24990000,
            ],
            [
                'category_id' => $catTivi->category_id,
                'name' => 'LG OLED 65 inch C3 4K Smart TV',
                'thumbnail' => 'https://images.unsplash.com/photo-1567690187548-f07b1d7bf5a9?w=300',
                'base_price' => 35990000,
                'old_price' => 42990000,
            ],

            // ===== GIA DỤNG, SMARTHOME (2 sản phẩm) =====
            [
                'category_id' => $catGiaDung->category_id,
                'name' => 'Robot hút bụi Xiaomi Vacuum X20 Pro',
                'thumbnail' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=300',
                'base_price' => 8990000,
                'old_price' => 11990000,
            ],
            [
                'category_id' => $catGiaDung->category_id,
                'name' => 'Máy lọc không khí Samsung AX60R5080WD',
                'thumbnail' => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=300',
                'base_price' => 6490000,
                'old_price' => 7990000,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
