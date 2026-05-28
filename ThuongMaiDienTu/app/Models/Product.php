<?php
namespace App\Models;

use App\Services\NotificationService;
use App\Traits\BaseTranslationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, BaseTranslationTrait;

    protected $primaryKey = 'product_id';
    public $timestamps = false;
    protected $guarded = [];

    protected array $translatable = [
        'name',
        'description',
        'seo_description',
    ];

    protected $casts = [
    ];

    protected static function booted()
    {
        static::created(function (Product $product) {
            if ((float) ($product->discount_percent ?? 0) > 0) {
                app(NotificationService::class)->notifyCustomers([
                    'type' => 'promotion.product_discount',
                    'title' => 'Sản phẩm mới có ưu đãi',
                    'content' => 'Sản phẩm ' . $product->name . ' vừa có giá ưu đãi. Xem ngay để không bỏ lỡ.',
                    'action_url' => url('/product/' . $product->product_id),
                    'data' => [
                        'product_id' => $product->product_id,
                        'discount_percent' => $product->discount_percent,
                        'base_price' => $product->base_price,
                    ],
                ]);
            }
        });

        static::updated(function (Product $product) {
            if (! $product->wasChanged(['base_price', 'discount_percent'])) {
                return;
            }

            if ((float) ($product->discount_percent ?? 0) <= 0) {
                return;
            }

            $headline = 'Sản phẩm giảm giá: ' . $product->name;
            $content = 'Sản phẩm ' . $product->name . ' đang được giảm ' . $product->discount_percent . '%. Xem ngay để không bỏ lỡ ưu đãi.';

            $service = app(NotificationService::class);
            $service->notifyCustomers([
                'type' => 'promotion.product_discount',
                'title' => $headline,
                'content' => $content,
                'action_url' => url('/product/' . $product->product_id),
                'data' => [
                    'product_id' => $product->product_id,
                    'discount_percent' => $product->discount_percent,
                    'base_price' => $product->base_price,
                ],
            ]);
        });
    }

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

    public function flashSaleProducts()
    {
        return $this->hasMany(FlashSaleProduct::class, 'product_id', 'product_id');
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
    /**
     * Lấy tổng số lượng tồn kho thực tế (In_Stock) của tất cả biến thể
     */
    public function getInStockCountAttribute() {
        return $this->variants->sum(fn($v) => $v->in_stock_count);
    }

    /**
     * Kiểm tra xem sản phẩm có bị rơi vào tình trạng sắp hết hàng hay không
     */
    public function getIsLowStockAttribute() {
        if ($this->variants->isEmpty()) {
            return $this->in_stock_count <= ($this->safe_stock ?? 5);
        }
        
        if ($this->in_stock_count <= ($this->safe_stock ?? 5)) {
            return true;
        }

        return $this->variants->contains(fn($v) => $v->is_low_stock);
    }

    public function crossSells()
    {
        return $this->belongsToMany(Product::class, 'product_cross_sells', 'product_id', 'cross_sell_id')
            ->withPivot('sort_order')
            ->orderBy('product_cross_sells.sort_order', 'asc');
    }

    public function comboProducts()
    {
        return $this->belongsToMany(Product::class, 'product_combos', 'product_id', 'combo_product_id')
            ->withPivot('sort_order', 'discount_type', 'discount_value')
            ->orderBy('product_combos.sort_order', 'asc');
    }

    public function wishlistRecentlyViewed()
    {
        return $this->hasMany(WishlistRecentlyViewed::class, 'product_id', 'product_id');
    }
}
