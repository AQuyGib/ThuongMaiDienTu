<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardCatalog extends Model
{
    protected $table = 'reward_catalog';
    protected $primaryKey = 'reward_id';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected $appends = ['display_image'];

    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class, 'reward_id', 'reward_id');
    }

    public function wheelSpins()
    {
        return $this->hasMany(LuckyWheelSpin::class, 'reward_id', 'reward_id');
    }

    public function getDisplayImageAttribute(): ?string
    {
        return $this->thumbnail_path ?: $this->image_path ?: null;
    }
}
