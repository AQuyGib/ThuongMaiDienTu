<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterRule extends Model
{
    protected $table = 'filter_rules';

    protected $guarded = [];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function getGroupKeyAttribute(): ?string
    {
        return $this->attributes['group_key'] ?? $this->attributes['group'] ?? null;
    }

    public function getRuleKeyAttribute(): ?string
    {
        return $this->attributes['rule_key'] ?? $this->attributes['key'] ?? null;
    }
}

