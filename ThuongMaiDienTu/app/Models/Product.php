<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'product_id';
    public $timestamps = false;
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function productSpecifications()
    {
        return $this->hasMany(ProductSpecification::class, 'product_id');
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    // Scopes cho Lọc Nâng Cao
    public function scopeFilterCategory($query, $categoryId, $categorySlug = null)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        } elseif ($categorySlug) {
            return $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }
        return $query;
    }

    public function scopeFinalPriceBetween($query, $min, $max)
    {
        // Sử dụng base_price làm giá bán thực tế (final_price) để lọc
        if ($min)
            $query->where('base_price', '>=', (float) $min);
        if ($max)
            $query->where('base_price', '<=', (float) $max);
        return $query;
    }

    public function scopeSearchKeyword($query, $keyword)
    {
        if ($keyword) {
            return $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('seo_description', 'LIKE', "%{$keyword}%");
            });
        }
        return $query;
    }

    public function scopeFilterBySpecs($query, $specs)
    {
        if (!empty($specs) && is_array($specs)) {
            foreach ($specs as $key => $values) {
                $query->where(function ($q) use ($key, $values) {
                    foreach ((array) $values as $value) {
                        $q->orWhereJsonContains('specifications->' . $key, $value);
                    }
                });
            }
        }
        return $query;
    }

    public function scopeSortBy($query, $sort)
    {
        switch ($sort) {
            case 'price_asc':
                return $query->orderBy('base_price', 'asc');
            case 'price_desc':
                return $query->orderBy('base_price', 'desc');
            case 'name_asc':
                return $query->orderBy('name', 'asc');
            case 'name_desc':
                return $query->orderBy('name', 'desc');
            case 'promo':
                return $query->orderBy('discount_percent', 'desc');
            case 'newest':
            default:
                return $query->orderBy('product_id', 'desc');
        }
    }
}