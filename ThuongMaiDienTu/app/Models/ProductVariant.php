<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model {
    protected $primaryKey = 'variant_id';
    public $timestamps = false;
    protected $guarded = [];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function inventoryItems() {
        return $this->hasMany(InventoryItem::class, 'variant_id');
    }
}