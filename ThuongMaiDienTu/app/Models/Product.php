<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model {
    use SoftDeletes;
    protected $primaryKey = 'product_id';
    public $timestamps = false;
    protected $guarded = [];

    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function specifications() {
        return $this->hasMany(ProductSpecification::class, 'product_id');
    }
    public function variants() {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }
}