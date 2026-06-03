<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    protected $primaryKey = 'warranty_id';
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    /**
     * Check if warranty is currently active (not expired by date)
     */
    public function isActive(): bool
    {
        return $this->warranty_status === 'active' && $this->end_date->isFuture();
    }
}
