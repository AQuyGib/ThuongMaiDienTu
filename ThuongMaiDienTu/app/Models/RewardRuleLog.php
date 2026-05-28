<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardRuleLog extends Model
{
    protected $table = 'reward_rule_logs';
    protected $primaryKey = 'log_id';
    protected $guarded = [];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];
}
