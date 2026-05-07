<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model {
    protected $primaryKey = 'supplier_id';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Scope tìm kiếm theo tên hoặc thông tin liên hệ
     */
    public function scopeSearch($query, $term)
    {
        if ($term) {
            return $query->where('name', 'like', '%' . $term . '%')
                         ->orWhere('phone', 'like', '%' . $term . '%')
                         ->orWhere('email', 'like', '%' . $term . '%')
                         ->orWhere('address', 'like', '%' . $term . '%');
        }
        return $query;
    }

    public function purchaseOrders() {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }
}