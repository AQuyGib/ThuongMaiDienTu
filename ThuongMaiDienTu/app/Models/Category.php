<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Category extends Model {
    protected $primaryKey = 'category_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'filter_config' => 'array',
    ];

    public function parent() {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function children() {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function products() {
        return $this->hasMany(Product::class, 'category_id');
    }
}