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

    protected static function booted()
    {
        static::saving(function ($category) {
            if (empty($category->slug)) {
                $category->slug = \Illuminate\Support\Str::slug($category->name);
            }
        });
    }

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