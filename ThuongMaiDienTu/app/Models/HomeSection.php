<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = [
        'title', 'type', 'category_id', 'limit', 'sidebar_banner', 'sidebar_link', 'order', 'status'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'home_section_products', 'home_section_id', 'product_id')
                    ->withPivot('order')
                    ->withTimestamps()
                    ->orderBy('pivot_order', 'asc');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
}
