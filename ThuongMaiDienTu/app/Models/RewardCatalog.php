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

    protected $appends = ['display_image', 'status_label', 'progress_percent'];

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

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return 'Tắt';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'Hết hạn';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'Sắp mở';
        }

        return 'Đang bật';
    }

    public function getProgressPercentAttribute(): int
    {
        if (is_null($this->stock)) {
            return 100;
        }

        $used = max(0, 100 - min(100, (int) ($this->stock > 0 ? 100 - $this->stock : 100)));
        return max(0, min(100, $used));
    }
}
