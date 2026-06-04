<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransfer extends Model
{
    use \App\Traits\HasAuditLog;

    protected $primaryKey = 'transfer_id';
    
    protected $guarded = [];

    /**
     * Quan hệ với người tạo phiếu
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Các mặt hàng IMEI trong phiếu chuyển kho này
     */
    public function items()
    {
        return $this->belongsToMany(
            InventoryItem::class,
            'warehouse_transfer_items',
            'transfer_id',
            'item_id'
        );
    }
}
