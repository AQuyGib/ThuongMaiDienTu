<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    protected $table = 'reward_redemptions';
    protected $primaryKey = 'redemption_id';
    protected $guarded = [];

    protected $casts = [
        'reward_snapshot' => 'array',
        'issued_at' => 'datetime',
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
