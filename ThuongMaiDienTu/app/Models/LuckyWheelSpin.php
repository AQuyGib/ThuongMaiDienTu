<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuckyWheelSpin extends Model
{
    protected $table = 'lucky_wheel_spins';
    protected $primaryKey = 'spin_id';
    protected $guarded = [];

    protected $casts = [
        'result_snapshot' => 'array',
        'spun_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function reward()
    {
        return $this->belongsTo(RewardCatalog::class, 'reward_id', 'reward_id');
    }
}
