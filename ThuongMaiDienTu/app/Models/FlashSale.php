<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlashSale extends Model
{
    protected $primaryKey = 'flash_sale_id';
    protected $guarded = [];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(FlashSaleProduct::class, 'flash_sale_id', 'flash_sale_id');
    }
}
