<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditLog;

class PurchaseOrder extends Model {
    use HasAuditLog;

    protected $primaryKey = 'po_id';
    const UPDATED_AT = null;
    protected $guarded = [];

    public function supplier() {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function inventoryItems() {
        return $this->hasMany(InventoryItem::class, 'po_id');
    }
}