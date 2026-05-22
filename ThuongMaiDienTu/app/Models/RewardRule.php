<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardRule extends Model
{
    protected $table = 'reward_rules';
    protected $primaryKey = 'rule_id';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];
}
