<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransferItem extends Model
{
    public $timestamps = false;
    
    protected $guarded = [];

    public function transfer()
    {
        return $this->belongsTo(WarehouseTransfer::class, 'transfer_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
