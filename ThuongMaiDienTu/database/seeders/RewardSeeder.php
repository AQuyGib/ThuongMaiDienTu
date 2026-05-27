<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RewardSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('reward_catalog')->insert([
            [
                'code' => 'FREE_SHIP_10K',
                'name' => 'Voucher Free Ship 10K',
                'reward_type' => 'voucher',
                'reward_category' => 'free_ship',
                'points_cost' => 50,
                'discount_amount' => 0,
                'shipping_discount_amount' => 10000,
                'stock' => 100,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6),
                'description' => 'Giảm 10.000đ phí vận chuyển cho đơn hàng đủ điều kiện.',
                'metadata' => json_encode(['label' => 'Free ship'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'DISCOUNT_100K',
                'name' => 'Voucher Giảm 100K',
                'reward_type' => 'voucher',
                'reward_category' => 'discount',
                'points_cost' => 500,
                'discount_amount' => 100000,
                'shipping_discount_amount' => 0,
                'stock' => 50,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6),
                'description' => 'Giảm ngay 100.000đ trên đơn hàng tiếp theo.',
                'metadata' => json_encode(['label' => 'Discount 100K'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'GIFT_MUG',
                'name' => 'Quà tặng Bình giữ nhiệt',
                'reward_type' => 'product',
                'reward_category' => 'gift',
                'points_cost' => 300,
                'discount_amount' => 0,
                'shipping_discount_amount' => 0,
                'stock' => 30,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6),
                'description' => 'Đổi lấy bình giữ nhiệt cao cấp của DIENMAYPRO.',
                'metadata' => json_encode(['label' => 'Gift'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WHEEL_20K',
                'name' => 'Vòng quay - Giảm 20K',
                'reward_type' => 'wheel_prize',
                'reward_category' => 'wheel',
                'points_cost' => 0,
                'discount_amount' => 20000,
                'shipping_discount_amount' => 0,
                'stock' => null,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6),
                'description' => 'Phần thưởng vòng quay may mắn: giảm 20.000đ cho đơn hàng kế tiếp.',
                'metadata' => json_encode(['label' => 'Wheel Prize'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WHEEL_FREE_SHIP',
                'name' => 'Vòng quay - Free Ship',
                'reward_type' => 'wheel_prize',
                'reward_category' => 'wheel',
                'points_cost' => 0,
                'discount_amount' => 0,
                'shipping_discount_amount' => 15000,
                'stock' => null,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6),
                'description' => 'Phần thưởng vòng quay may mắn: miễn phí vận chuyển 15.000đ.',
                'metadata' => json_encode(['label' => 'Wheel Prize'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
