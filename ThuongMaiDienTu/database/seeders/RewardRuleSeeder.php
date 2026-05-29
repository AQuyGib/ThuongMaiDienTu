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
                'rule_name' => 'Chi phí lượt quay',
                'rule_value' => '10',
                'value_type' => 'integer',
                'description' => 'Số điểm cho 1 lượt quay may mắn',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rule_key' => 'redeem_max_per_user_day',
                'rule_name' => 'Giới hạn đổi thưởng',
                'rule_value' => '5',
                'value_type' => 'integer',
                'description' => 'Giới hạn đổi thưởng mỗi user trong ngày',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
