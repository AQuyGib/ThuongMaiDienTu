<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyClaim extends Model
{
    protected $guarded = [];

    /**
     * Relationship to the User who submitted the request (optional)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship to the associated inventory item via IMEI/Serial
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'imei_serial', 'imei_serial');
    }
}
