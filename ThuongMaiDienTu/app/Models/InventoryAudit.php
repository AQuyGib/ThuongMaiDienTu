<?php
// app/Models/InventoryAudit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryAudit extends Model
{
    protected $table = 'inventory_audits';
    protected $primaryKey = 'audit_id';

    protected $fillable = [
        'audit_code',
        'warehouse_loc',
        'status',
        'notes',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Quan hệ với chi tiết phiếu kiểm kê
     */
    public function details(): HasMany
    {
        return $this->hasMany(InventoryAuditDetail::class, 'audit_id', 'audit_id');
    }

    /**
     * Người tạo phiếu
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
