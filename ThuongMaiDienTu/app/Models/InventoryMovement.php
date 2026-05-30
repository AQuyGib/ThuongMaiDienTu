<?php
// app/Models/InventoryMovement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movements';
    protected $primaryKey = 'movement_id';

    protected $fillable = [
        'product_id',
        'variant_id',
        'order_id',
        'reference_type',
        'reference_id',
        'type',
        'quantity_change',
        'before_stock',
        'after_stock',
        'note',
        'created_by',
    ];

    /**
     * Quan hệ với Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Quan hệ với ProductVariant
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id', 'variant_id');
    }

    /**
     * Quan hệ với Order (nếu biến động do mua hàng)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    /**
     * Người thực hiện biến động kho
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
