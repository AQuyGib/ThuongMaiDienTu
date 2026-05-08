<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model {
    protected $primaryKey = 'variant_id';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Accessor: Tính tổng giá (base_price + extra_price)
     */
    public function getTotalPriceAttribute() {
        return ($this->product->base_price ?? 0) + $this->extra_price;
    }

    /**
     * Tạo label hiển thị cho biến thể (VD: "Đen / 8GB / 256GB")
     */
    public function getLabelAttribute() {
        $parts = array_filter([
            $this->color,
            $this->ram,
            $this->rom_capacity,
        ]);
        return implode(' / ', $parts) ?: '—';
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function inventoryItems() {
        return $this->hasMany(InventoryItem::class, 'variant_id');
    }
}