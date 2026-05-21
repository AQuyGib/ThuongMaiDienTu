<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model {
    protected $primaryKey = 'item_id';
    public $timestamps = false;
    protected $guarded = [];

    public function variant() {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
    public function warranties() {
        return $this->hasMany(Warranty::class, 'item_id');
    }
}