<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CouponFlashSale extends Model {
    protected $table = 'coupons_flash_sales';
    protected $primaryKey = 'promo_id';
    public $timestamps = false;
    protected $guarded = [];
}