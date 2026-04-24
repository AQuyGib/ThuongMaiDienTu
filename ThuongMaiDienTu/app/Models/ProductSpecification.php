<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductSpecification extends Model {
    protected $primaryKey = 'spec_id';
    public $timestamps = false;
    protected $guarded = [];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
}