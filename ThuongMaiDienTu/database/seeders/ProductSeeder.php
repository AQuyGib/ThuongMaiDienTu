<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo Danh mục mẫu trước
        $catDienThoai = Category::create(['name' => 'Điện thoại']);
        $catLaptop = Category::create(['name' => 'Laptop']);
        $catTablet = Category::create(['name' => 'Tablet']);
        $catPhuKien = Category::create(['name' => 'Phụ kiện']);

        // 2. Tạo Sản phẩm mẫu
        $products = [
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'iPhone 15 Pro Max 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=300',
                'base_price' => 34990000,
                'old_price' => 35990000,
            ],
            [
                'category_id' => $catDienThoai->category_id,
                'name' => 'Samsung Galaxy S24 Ultra 5G',
                'thumbnail' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=300',
                'base_price' => 33990000,
                'old_price' => null,
            ],
            [
                'category_id' => $catLaptop->category_id,
                'name' => 'MacBook Air 15 inch M2 2023',
                'thumbnail' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=300',
                'base_price' => 29490000,
                'old_price' => 32990000,
            ],
            [
                'category_id' => $catPhuKien->category_id,
                'name' => 'Apple Watch Series 9 GPS 41mm',
                'thumbnail' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=300',
                'base_price' => 9290000,
                'old_price' => 10490000,
            ],
            [
                'category_id' => $catPhuKien->category_id,
                'name' => 'Tai nghe Bluetooth AirPods Pro 2',
                'thumbnail' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300',
                'base_price' => 5590000,
                'old_price' => 6990000,
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
