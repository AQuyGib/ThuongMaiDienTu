<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model {
    protected $primaryKey = 'supplier_id';
    public $timestamps = false;
    protected $guarded = [];

    public function purchaseOrders() {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }
}