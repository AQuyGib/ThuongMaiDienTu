<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model {
    protected $primaryKey = 'detail_id';
    public $timestamps = false;
    protected $guarded = [];

    public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function inventoryItem() {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}