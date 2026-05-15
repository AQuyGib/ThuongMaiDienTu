<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Tạo danh mục mẫu với cấu trúc cha-con.
     */
    public function run(): void
    {
        // ===== DANH MỤC CHA =====
        $dienThoai = Category::create(['name' => 'Điện thoại',          'slug' => Str::slug('Điện thoại')]);
        $laptop    = Category::create(['name' => 'Laptop',              'slug' => Str::slug('Laptop')]);
        $tablet    = Category::create(['name' => 'Tablet',              'slug' => Str::slug('Tablet')]);
        $amThanh   = Category::create(['name' => 'Âm thanh',            'slug' => Str::slug('Âm thanh')]);
        $dongHo    = Category::create(['name' => 'Đồng hồ thông minh', 'slug' => Str::slug('Đồng hồ thông minh')]);
        $phuKien   = Category::create(['name' => 'Phụ kiện',            'slug' => Str::slug('Phụ kiện')]);
        $tivi      = Category::create(['name' => 'Tivi, Màn hình',      'slug' => Str::slug('Tivi, Màn hình')]);
        $giaDung   = Category::create(['name' => 'Gia dụng, Smarthome', 'slug' => Str::slug('Gia dụng, Smarthome')]);

        // ===== DANH MỤC CON =====
        // Điện thoại
        Category::create(['name' => 'iPhone',         'parent_id' => $dienThoai->category_id, 'slug' => Str::slug('iPhone')]);
        Category::create(['name' => 'Samsung',        'parent_id' => $dienThoai->category_id, 'slug' => Str::slug('Samsung')]);
        Category::create(['name' => 'Xiaomi',         'parent_id' => $dienThoai->category_id, 'slug' => Str::slug('Xiaomi')]);
        Category::create(['name' => 'OPPO',           'parent_id' => $dienThoai->category_id, 'slug' => Str::slug('OPPO')]);

        // Laptop
        Category::create(['name' => 'MacBook',        'parent_id' => $laptop->category_id,    'slug' => Str::slug('MacBook')]);
        Category::create(['name' => 'Laptop Gaming',  'parent_id' => $laptop->category_id,    'slug' => Str::slug('Laptop Gaming')]);
        Category::create(['name' => 'Laptop Văn phòng', 'parent_id' => $laptop->category_id, 'slug' => Str::slug('Laptop Văn phòng')]);

        // Tablet
        Category::create(['name' => 'iPad',           'parent_id' => $tablet->category_id,    'slug' => Str::slug('iPad')]);
        Category::create(['name' => 'Samsung Galaxy Tab', 'parent_id' => $tablet->category_id, 'slug' => Str::slug('Samsung Galaxy Tab')]);

        // Âm thanh
        Category::create(['name' => 'Tai nghe',       'parent_id' => $amThanh->category_id,   'slug' => Str::slug('Tai nghe')]);
        Category::create(['name' => 'Loa Bluetooth',  'parent_id' => $amThanh->category_id,   'slug' => Str::slug('Loa Bluetooth')]);

        // Phụ kiện
        Category::create(['name' => 'Sạc dự phòng',   'parent_id' => $phuKien->category_id,   'slug' => Str::slug('Sạc dự phòng')]);
        Category::create(['name' => 'Ốp lưng, bao da', 'parent_id' => $phuKien->category_id, 'slug' => Str::slug('Ốp lưng, bao da')]);
        Category::create(['name' => 'Cáp sạc',        'parent_id' => $phuKien->category_id,   'slug' => Str::slug('Cáp sạc')]);
    }
}

