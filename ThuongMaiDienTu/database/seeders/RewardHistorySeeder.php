<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RewardHistorySeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->limit(3)->pluck('user_id');
        $reward = DB::table('reward_catalog')->where('reward_type', 'voucher')->first();
        $wheel = DB::table('reward_catalog')->where('reward_type', 'wheel_prize')->first();

        if ($userIds->isEmpty() || ! $reward || ! $wheel) {
            return;
        }

        foreach ($userIds as $index => $userId) {
            $redemptionCode = 'RWDDEMO' . Str::upper(Str::random(5)) . $index;
            DB::table('reward_redemptions')->insert([
                'user_id' => $userId,
                'reward_id' => $reward->reward_id,
                'redemption_code' => $redemptionCode,
                'status' => 'issued',
                'points_spent' => $reward->points_cost,
                'discount_amount' => $reward->discount_amount,
                'shipping_discount_amount' => $reward->shipping_discount_amount,
                'reward_snapshot' => json_encode([
                    'reward_id' => $reward->reward_id,
                    'reward_name' => $reward->name,
                    'reward_code' => $reward->code,
                ], JSON_UNESCAPED_UNICODE),
                'issued_at' => now()->subDays($index + 1),
                'expires_at' => now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lucky_wheel_spins')->insert([
                'user_id' => $userId,
                'reward_id' => $wheel->reward_id,
                'spin_code' => 'SPNDEMO' . Str::upper(Str::random(5)) . $index,
                'status' => 'won',
                'points_spent' => 10,
                'result_snapshot' => json_encode([
                    'reward_id' => $wheel->reward_id,
                    'reward_name' => $wheel->name,
                    'reward_code' => $wheel->code,
                ], JSON_UNESCAPED_UNICODE),
                'spun_at' => now()->subDays($index + 2),
                'expires_at' => now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
