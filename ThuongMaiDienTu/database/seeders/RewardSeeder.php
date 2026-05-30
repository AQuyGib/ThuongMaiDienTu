<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RewardSeeder extends Seeder
{
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại để truncate bảng có khóa ngoại liên kết
        Schema::disableForeignKeyConstraints();
        DB::table('reward_catalog')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('reward_catalog')->insert([
            // ==========================================
            // PHẦN THƯỞNG ĐỔI ĐIỂM (EXCHANGE REWARDS)
            // ==========================================
            [
                'code' => 'FREE_SHIP_10K',
                'name' => 'Voucher giảm 10K phí ship',
                'reward_type' => 'shipping',
                'reward_category' => 'free_ship',
                'points_cost' => 50,
                'discount_amount' => 0,
                'shipping_discount_amount' => 10000,
                'stock' => 100,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Giảm 10.000đ phí vận chuyển cho mọi đơn hàng.',
                'metadata' => json_encode([
                    'min_rank' => 'bronze'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'FREE_SHIP_30K',
                'name' => 'Voucher giảm 30K phí ship',
                'reward_type' => 'shipping',
                'reward_category' => 'free_ship',
                'points_cost' => 120,
                'discount_amount' => 0,
                'shipping_discount_amount' => 30000,
                'stock' => 80,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Giảm 30.000đ phí vận chuyển. Yêu cầu hạng Bạc trở lên.',
                'metadata' => json_encode([
                    'min_rank' => 'silver'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'FREE_SHIP_FREE',
                'name' => 'Voucher miễn phí vận chuyển 50K',
                'reward_type' => 'shipping',
                'reward_category' => 'free_ship',
                'points_cost' => 200,
                'discount_amount' => 0,
                'shipping_discount_amount' => 50000,
                'stock' => 50,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Hỗ trợ tối đa 50.000đ phí vận chuyển. Dành cho hạng Vàng trở lên.',
                'metadata' => json_encode([
                    'min_rank' => 'gold'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'DISCOUNT_50K',
                'name' => 'Voucher giảm giá 50.000đ',
                'reward_type' => 'voucher',
                'reward_category' => 'discount',
                'points_cost' => 250,
                'discount_amount' => 50000,
                'shipping_discount_amount' => 0,
                'stock' => 100,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Giảm ngay 50.000đ trực tiếp trên hóa đơn mua sắm tiếp theo.',
                'metadata' => json_encode([
                    'min_rank' => 'bronze'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'DISCOUNT_100K',
                'name' => 'Voucher giảm giá 100.000đ',
                'reward_type' => 'voucher',
                'reward_category' => 'discount',
                'points_cost' => 500,
                'discount_amount' => 100000,
                'shipping_discount_amount' => 0,
                'stock' => 50,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Giảm ngay 100.000đ trực tiếp vào đơn hàng. Yêu cầu hạng Bạc.',
                'metadata' => json_encode([
                    'min_rank' => 'silver'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'DISCOUNT_200K',
                'name' => 'Voucher giảm giá 200.000đ',
                'reward_type' => 'voucher',
                'reward_category' => 'discount',
                'points_cost' => 900,
                'discount_amount' => 200000,
                'shipping_discount_amount' => 0,
                'stock' => 30,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Giảm giá cực khủng 200.000đ trực tiếp. Dành cho hạng Vàng.',
                'metadata' => json_encode([
                    'min_rank' => 'gold'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'DISCOUNT_500K',
                'name' => 'Voucher giảm giá siêu cấp 500.000đ',
                'reward_type' => 'voucher',
                'reward_category' => 'discount',
                'points_cost' => 2000,
                'discount_amount' => 500000,
                'shipping_discount_amount' => 0,
                'stock' => 10,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Đặc quyền siêu cấp giảm 500.000đ hóa đơn. Chỉ dành cho thành viên Kim Cương.',
                'metadata' => json_encode([
                    'min_rank' => 'diamond'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'GIFT_MUG',
                'name' => 'Quà tặng Bình giữ nhiệt cao cấp',
                'reward_type' => 'product',
                'reward_category' => 'gift',
                'points_cost' => 300,
                'discount_amount' => 0,
                'shipping_discount_amount' => 0,
                'stock' => 40,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Bình giữ nhiệt chất liệu inox 316 an toàn, giữ nhiệt tới 12 giờ.',
                'metadata' => json_encode([
                    'min_rank' => 'bronze'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'GIFT_BACKPACK',
                'name' => 'Quà tặng Balo thời trang chống nước',
                'reward_type' => 'product',
                'reward_category' => 'gift',
                'points_cost' => 800,
                'discount_amount' => 0,
                'shipping_discount_amount' => 0,
                'stock' => 20,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Balo thiết kế trẻ trung, nhiều ngăn tiện lợi, chất liệu chống thấm nước.',
                'metadata' => json_encode([
                    'min_rank' => 'silver'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'GIFT_AIRPODS',
                'name' => 'Quà tặng Tai nghe không dây cao cấp',
                'reward_type' => 'product',
                'reward_category' => 'gift',
                'points_cost' => 3000,
                'discount_amount' => 0,
                'shipping_discount_amount' => 0,
                'stock' => 5,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Tai nghe âm thanh cực đỉnh, chống ồn chủ động, thời lượng pin ấn tượng.',
                'metadata' => json_encode([
                    'min_rank' => 'gold'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ==========================================
            // PHẦN QUÀ VÒNG QUAY MAY MẮN (WHEEL PRIZES)
            // ==========================================
            [
                'code' => 'WHEEL_VOUCHER_10K',
                'name' => 'Quà vòng quay - Voucher giảm 10.000đ',
                'reward_type' => 'wheel_prize',
                'reward_category' => 'wheel',
                'points_cost' => 0,
                'discount_amount' => 10000,
                'shipping_discount_amount' => 0,
                'stock' => null,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Mã giảm giá 10.000đ trúng được từ Vòng quay may mắn.',
                'metadata' => json_encode([
                    'wheel_prize_type' => 'voucher',
                    'wheel_type' => 'standard',
                    'winning_rate' => 20
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WHEEL_VOUCHER_50K',
                'name' => 'Quà vòng quay - Voucher giảm 50.000đ',
                'reward_type' => 'wheel_prize',
                'reward_category' => 'wheel',
                'points_cost' => 0,
                'discount_amount' => 50000,
                'shipping_discount_amount' => 0,
                'stock' => null,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Mã giảm giá 50.000đ trúng được từ Vòng quay may mắn.',
                'metadata' => json_encode([
                    'wheel_prize_type' => 'voucher',
                    'wheel_type' => 'standard',
                    'winning_rate' => 10
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WHEEL_SHIP_15K',
                'name' => 'Quà vòng quay - Giảm ship 15.000đ',
                'reward_type' => 'wheel_prize',
                'reward_category' => 'wheel',
                'points_cost' => 0,
                'discount_amount' => 0,
                'shipping_discount_amount' => 15000,
                'stock' => null,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Mã giảm 15.000đ phí giao hàng trúng từ Vòng quay.',
                'metadata' => json_encode([
                    'wheel_prize_type' => 'shipping',
                    'wheel_type' => 'standard',
                    'winning_rate' => 20
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WHEEL_SHIP_30K',
                'name' => 'Quà vòng quay - Giảm ship 30.000đ',
                'reward_type' => 'wheel_prize',
                'reward_category' => 'wheel',
                'points_cost' => 0,
                'discount_amount' => 0,
                'shipping_discount_amount' => 30000,
                'stock' => null,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Mã miễn phí vận chuyển 30.000đ trúng từ Vòng quay.',
                'metadata' => json_encode([
                    'wheel_prize_type' => 'shipping',
                    'wheel_type' => 'standard',
                    'winning_rate' => 15
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WHEEL_PRODUCT_KEYCHAIN',
                'name' => 'Quà vòng quay - Móc khóa lưu niệm',
                'reward_type' => 'wheel_prize',
                'reward_category' => 'wheel',
                'points_cost' => 0,
                'discount_amount' => 0,
                'shipping_discount_amount' => 0,
                'stock' => 100,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Móc khóa lưu niệm in logo DIENMAYPRO.',
                'metadata' => json_encode([
                    'wheel_prize_type' => 'product',
                    'wheel_type' => 'standard',
                    'winning_rate' => 15
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WHEEL_PRODUCT_MUG',
                'name' => 'Quà vòng quay - Ly sứ lưu niệm',
                'reward_type' => 'wheel_prize',
                'reward_category' => 'wheel',
                'points_cost' => 0,
                'discount_amount' => 0,
                'shipping_discount_amount' => 0,
                'stock' => 50,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Ly sứ lưu niệm phiên bản giới hạn của DIENMAYPRO.',
                'metadata' => json_encode([
                    'wheel_prize_type' => 'product',
                    'wheel_type' => 'standard',
                    'winning_rate' => 10
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WHEEL_LUCK_NEXT_TIME',
                'name' => 'Chúc bạn may mắn lần sau',
                'reward_type' => 'wheel_prize',
                'reward_category' => 'wheel',
                'points_cost' => 0,
                'discount_amount' => 0,
                'shipping_discount_amount' => 0,
                'stock' => null,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Không trúng thưởng. Chúc bạn may mắn hơn vào lượt quay sau!',
                'metadata' => json_encode([
                    'wheel_prize_type' => 'product',
                    'wheel_type' => 'standard',
                    'winning_rate' => 10
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
