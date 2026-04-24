<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Tạo danh mục mẫu với cấu trúc cha-con.
     */
    public function run(): void
    {
        // ===== DANH MỤC CHA =====
        $dienThoai = Category::create(['name' => 'Điện thoại']);
        $laptop    = Category::create(['name' => 'Laptop']);
        $tablet    = Category::create(['name' => 'Tablet']);
        $amThanh   = Category::create(['name' => 'Âm thanh']);
        $dongHo    = Category::create(['name' => 'Đồng hồ thông minh']);
        $phuKien   = Category::create(['name' => 'Phụ kiện']);
        $tivi      = Category::create(['name' => 'Tivi, Màn hình']);
        $giaDung   = Category::create(['name' => 'Gia dụng, Smarthome']);

        // ===== DANH MỤC CON =====
        // Điện thoại
        Category::create(['name' => 'iPhone',         'parent_id' => $dienThoai->category_id]);
        Category::create(['name' => 'Samsung',        'parent_id' => $dienThoai->category_id]);
        Category::create(['name' => 'Xiaomi',         'parent_id' => $dienThoai->category_id]);
        Category::create(['name' => 'OPPO',           'parent_id' => $dienThoai->category_id]);

        // Laptop
        Category::create(['name' => 'MacBook',        'parent_id' => $laptop->category_id]);
        Category::create(['name' => 'Laptop Gaming',  'parent_id' => $laptop->category_id]);
        Category::create(['name' => 'Laptop Văn phòng', 'parent_id' => $laptop->category_id]);

        // Tablet
        Category::create(['name' => 'iPad',           'parent_id' => $tablet->category_id]);
        Category::create(['name' => 'Samsung Galaxy Tab', 'parent_id' => $tablet->category_id]);

        // Âm thanh
        Category::create(['name' => 'Tai nghe',       'parent_id' => $amThanh->category_id]);
        Category::create(['name' => 'Loa Bluetooth',  'parent_id' => $amThanh->category_id]);

        // Phụ kiện
        Category::create(['name' => 'Sạc dự phòng',   'parent_id' => $phuKien->category_id]);
        Category::create(['name' => 'Ốp lưng, bao da', 'parent_id' => $phuKien->category_id]);
        Category::create(['name' => 'Cáp sạc',        'parent_id' => $phuKien->category_id]);
    }
}
