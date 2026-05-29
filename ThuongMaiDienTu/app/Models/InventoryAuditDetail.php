<?php
// app/Models/InventoryAuditDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAuditDetail extends Model
{
    protected $table = 'inventory_audit_details';
    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'audit_id',
        'variant_id',
        'imei_serial',
        'system_qty',
        'actual_qty',
        'discrepancy_qty',
        'notes',
    ];

    /**
     * Quan hệ với Phiếu kiểm kê kho
     */
    public function audit(): BelongsTo
    {
        return $this->belongsTo(InventoryAudit::class, 'audit_id', 'audit_id');
    }

    /**
     * Quan hệ với Biến thể sản phẩm
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id', 'variant_id');
    }
}
