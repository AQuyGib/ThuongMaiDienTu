<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class RewardHistorySeeder
 * Seeder tạo dữ liệu lịch sử đổi thưởng và lịch sử quay số vòng quay may mắn giả lập cho người dùng để test giao diện.
 */
class RewardHistorySeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh sách 3 tài khoản người dùng đầu tiên để gán lịch sử
        $userIds = DB::table('users')->limit(3)->pluck('user_id');
        
        // Lấy phần thưởng dạng voucher đầu tiên từ catalog
        $reward = DB::table('reward_catalog')->where('reward_type', 'voucher')->first();
        
        // Lấy quà vòng quay đầu tiên từ catalog
        $wheel = DB::table('reward_catalog')->where('reward_type', 'wheel_prize')->first();

        // Nếu thiếu dữ liệu đầu vào thì dừng seeder để tránh lỗi
        if ($userIds->isEmpty() || ! $reward || ! $wheel) {
            return;
        }

        // Lặp qua từng người dùng để tạo lịch sử đổi quà và lượt quay trúng thưởng giả lập
        foreach ($userIds as $index => $userId) {
            // Sinh mã code đổi thưởng ngẫu nhiên
            $redemptionCode = 'RWDDEMO' . Str::upper(Str::random(5)) . $index;
            
            // Ghi nhận lịch sử đổi thưởng
            DB::table('reward_redemptions')->insert([
                'user_id' => $userId,
                'reward_id' => $reward->reward_id,
                'redemption_code' => $redemptionCode,
                'status' => 'issued', // Trạng thái đã phát hành
                'points_spent' => $reward->points_cost,
                'discount_amount' => $reward->discount_amount,
                'shipping_discount_amount' => $reward->shipping_discount_amount,
                'reward_snapshot' => json_encode([
                    'reward_id' => $reward->reward_id,
                    'reward_name' => $reward->name,
                    'reward_code' => $reward->code,
                ], JSON_UNESCAPED_UNICODE),
                'issued_at' => now()->subDays($index + 1), // Lùi ngày đổi để dữ liệu đa dạng
                'expires_at' => now()->addDays(30), // Hạn dùng 30 ngày
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Ghi nhận lịch sử lượt quay trúng thưởng giả lập
            DB::table('lucky_wheel_spins')->insert([
                'user_id' => $userId,
                'reward_id' => $wheel->reward_id,
                'spin_code' => 'SPNDEMO' . Str::upper(Str::random(5)) . $index,
                'status' => 'won', // Trạng thái đã trúng thưởng
                'points_spent' => 10,
                'result_snapshot' => json_encode([
                    'reward_id' => $wheel->reward_id,
                    'reward_name' => $wheel->name,
                    'reward_code' => $wheel->code,
                ], JSON_UNESCAPED_UNICODE),
                'spun_at' => now()->subDays($index + 2), // Lùi ngày quay
                'expires_at' => now()->addDays(7), // Quà vòng quay có hạn dùng 7 ngày
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
