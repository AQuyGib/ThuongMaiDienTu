<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RewardRuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('reward_rules')->insert([
            [
                'rule_key' => 'wheel_spin_cost',
                'rule_value' => '10',
                'rule_type' => 'number',
                'description' => 'Số điểm cho 1 lượt quay may mắn',
                'is_active' => true,
                'config' => json_encode(['unit' => 'points'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rule_key' => 'redeem_max_per_user_day',
                'rule_value' => '5',
                'rule_type' => 'number',
                'description' => 'Giới hạn đổi thưởng mỗi user trong ngày',
                'is_active' => true,
                'config' => json_encode(['scope' => 'user_day'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
