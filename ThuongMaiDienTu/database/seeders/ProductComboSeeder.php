<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Class: ProductComboSeeder
 * Công dụng: Khởi tạo dữ liệu mẫu cho tính năng Combo giảm giá mua kèm.
 *            - Truy vấn động sản phẩm chính bằng tên.
 *            - Gắn liên kết các sản phẩm phụ kiện bằng quan hệ comboProducts() vào bảng trung gian product_combos.
 *            - Thiết lập loại giảm giá (phần trăm % hoặc số tiền cố định đ) và mức giảm tương ứng cho từng combo.
 */
class ProductComboSeeder extends Seeder
{
    /**
     * Chạy seed dữ liệu combo.
     */
    public function run(): void
    {
        // 1. iPhone 15 Pro Max 256GB combo
        $iphone = Product::where('name', 'iPhone 15 Pro Max 256GB')->first();
        if ($iphone) {
            $airpods = Product::where('name', 'Apple AirPods Pro 2 USB-C 2024')->first();
            $oplung = Product::where('name', 'Ốp lưng MagSafe iPhone 15 Pro Max')->first();
            $capsac = Product::where('name', 'Cáp sạc nhanh USB-C to Lightning 2m')->first();

            if ($airpods) {
                $iphone->comboProducts()->syncWithoutDetaching([
                    $airpods->product_id => [
                        'discount_type' => 'percentage',
                        'discount_value' => 15.00,
                        'sort_order' => 1,
                    ]
                ]);
            }
            if ($oplung) {
                $iphone->comboProducts()->syncWithoutDetaching([
                    $oplung->product_id => [
                        'discount_type' => 'fixed',
                        'discount_value' => 200000.00,
                        'sort_order' => 2,
                    ]
                ]);
            }
            if ($capsac) {
                $iphone->comboProducts()->syncWithoutDetaching([
                    $capsac->product_id => [
                        'discount_type' => 'fixed',
                        'discount_value' => 100000.00,
                        'sort_order' => 3,
                    ]
                ]);
            }
        }

        // 2. Samsung Galaxy S24 Ultra 5G 256GB combo
        $s24 = Product::where('name', 'Samsung Galaxy S24 Ultra 5G 256GB')->first();
        if ($s24) {
            $watch = Product::where('name', 'Samsung Galaxy Watch 6 Classic 47mm')->first();
            $anker = Product::where('name', 'Sạc dự phòng Anker 20000mAh 65W')->first();

            if ($watch) {
                $s24->comboProducts()->syncWithoutDetaching([
                    $watch->product_id => [
                        'discount_type' => 'percentage',
                        'discount_value' => 10.00,
                        'sort_order' => 1,
                    ]
                ]);
            }
            if ($anker) {
                $s24->comboProducts()->syncWithoutDetaching([
                    $anker->product_id => [
                        'discount_type' => 'fixed',
                        'discount_value' => 150000.00,
                        'sort_order' => 2,
                    ]
                ]);
            }
        }

        // 3. MacBook Air 15 inch M3 2024 8GB/256GB
        $macbook = Product::where('name', 'MacBook Air 15 inch M3 2024 8GB/256GB')->first();
        if ($macbook) {
            $anker = Product::where('name', 'Sạc dự phòng Anker 20000mAh 65W')->first();
            if ($anker) {
                $macbook->comboProducts()->syncWithoutDetaching([
                    $anker->product_id => [
                        'discount_type' => 'percentage',
                        'discount_value' => 20.00,
                        'sort_order' => 1,
                    ]
                ]);
            }
        }
    }
}
