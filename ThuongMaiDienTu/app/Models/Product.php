<?php
namespace App\Models;

use App\Services\NotificationService;
use App\Traits\BaseTranslationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Product — Đại diện cho bảng `products` trong cơ sở dữ liệu.
 * 
 * Sản phẩm là thực thể trung tâm của hệ thống DIENMAYPRO. Mỗi sản phẩm thuộc một danh mục (Category),
 * có thể có nhiều biến thể (Variants: ROM/Màu), thông số kỹ thuật (Specifications),
 * tham gia chương trình Flash Sale, được cấu hình Combo mua kèm và gợi ý bán chéo (Cross-sell).
 * 
 * Sử dụng SoftDeletes để xóa mềm (không mất dữ liệu) và BaseTranslationTrait cho đa ngôn ngữ.
 * 
 * @property int    $product_id         Khóa chính
 * @property string $name               Tên sản phẩm
 * @property int    $base_price         Giá bán hiện tại (đ)
 * @property int    $old_price           Giá niêm yết gốc (gạch ngang)
 * @property string $thumbnail           URL ảnh đại diện
 * @property string $brand               Hãng sản xuất (Apple, Samsung...)
 * @property int    $category_id         ID danh mục cha
 * @property string $specifications      JSON chứa thông số kỹ thuật
 * @property float  $discount_percent    Phần trăm giảm giá
 * @property string $description         Mô tả chi tiết sản phẩm
 */
class Product extends Model
{
    use SoftDeletes, BaseTranslationTrait;

    /** @var string Tên cột khóa chính (không dùng 'id' mặc định của Laravel) */
    protected $primaryKey = 'product_id';
    /** @var bool Tắt tự động quản lý cột created_at/updated_at */
    public $timestamps = false;
    /** @var array Cho phép gán hàng loạt (mass assignment) tất cả các cột */
    protected $guarded = [];

    /** @var array Danh sách các cột hỗ trợ dịch đa ngôn ngữ (vi/en) thông qua bảng translations */
    protected array $translatable = [
        'name',
        'description',
        'seo_description',
    ];

    protected $casts = [
    ];

    /**
     * Hook lifecycle events của Eloquent Model.
     * 
     * - `created`: Khi sản phẩm mới được tạo có discount_percent > 0, tự động gửi thông báo khuyến mãi cho khách hàng.
     * - `updated`: Khi giá hoặc discount_percent thay đổi, tự động gửi thông báo cập nhật khuyến mãi.
     * 
     * Sử dụng NotificationService để push thông báo realtime đến tất cả khách hàng đã đăng ký.
     */
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

    /**
     * Quan hệ: Sản phẩm thuộc về một Danh mục (Category).
     * Dùng trong: Breadcrumb, hiển thị tên danh mục, bộ lọc, gợi ý sản phẩm cùng danh mục.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Quan hệ: Sản phẩm có nhiều bản ghi Thông số kỹ thuật (Product Specifications).
     * Dùng trong: Bảng "Cấu hình chi tiết" trên trang show.blade.php (CPU, RAM, Pin, Màn hình...).
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productSpecifications()
    {
        return $this->hasMany(ProductSpecification::class, 'product_id');
    }

    /**
     * Quan hệ: Sản phẩm có nhiều Biến thể (Product Variants).
     * Mỗi biến thể đại diện cho một tổ hợp ROM + Màu sắc với giá chênh lệch (extra_price) riêng.
     * Dùng trong: Nút chọn cấu hình trên trang chi tiết, tính giá bán thực tế.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    /**
     * Quan hệ: Sản phẩm có nhiều bản ghi tham gia Flash Sale (FlashSaleProduct).
     * Mỗi bản ghi chứa: sale_price (giá khuyến mãi), stock_limit (giới hạn tồn kho), sold_quantity (đã bán).
     * Dùng trong: FlashSaleService để kiểm tra sản phẩm có đang Flash Sale hay không.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function flashSaleProducts()
    {
        return $this->hasMany(FlashSaleProduct::class, 'product_id', 'product_id');
    }

    /**
     * Scope: Lọc sản phẩm theo danh mục (category_id hoặc slug).
     * Cách dùng: Product::filterCategory($id)->get() hoặc Product::filterCategory(null, 'dien-thoai')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null    $categoryId   ID danh mục (ưu tiên)
     * @param string|null $categorySlug Slug danh mục (dùng khi không có ID)
     * @return \Illuminate\Database\Eloquent\Builder
     */
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

    /**
     * Scope: Lọc sản phẩm theo khoảng giá bán (base_price).
     * Cách dùng: Product::finalPriceBetween(5000000, 15000000)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float|null $min Giá tối thiểu (đ)
     * @param float|null $max Giá tối đa (đ)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinalPriceBetween($query, $min, $max)
    {
        // Sử dụng base_price làm giá bán thực tế (final_price) để lọc
        if ($min)
            $query->where('base_price', '>=', (float) $min);
        if ($max)
            $query->where('base_price', '<=', (float) $max);
        return $query;
    }

    /**
     * Scope: Tìm kiếm sản phẩm theo từ khóa (Full-text search).
     * Tìm trong: tên SP, mô tả SEO, bản dịch đa ngôn ngữ, tên danh mục (cả gốc và bản dịch).
     * Cách dùng: Product::searchKeyword('iPhone 15')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $keyword Từ khóa tìm kiếm
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchKeyword($query, $keyword)
    {
        if ($keyword) {
            return $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('seo_description', 'LIKE', "%{$keyword}%")
                    ->orWhereHas('translations', function ($sub) use ($keyword) {
                        $sub->where('name', 'LIKE', "%{$keyword}%")
                            ->orWhere('description', 'LIKE', "%{$keyword}%")
                            ->orWhere('seo_description', 'LIKE', "%{$keyword}%");
                    })
                    ->orWhereHas('category', function ($sub) use ($keyword) {
                        $sub->where('name', 'LIKE', "%{$keyword}%")
                            ->orWhereHas('translations', function ($trans) use ($keyword) {
                                $trans->where('name', 'LIKE', "%{$keyword}%");
                            });
                    });
            });
        }
        return $query;
    }

    /**
     * Scope: Lọc sản phẩm theo thông số kỹ thuật trong cột JSON `specifications`.
     * Ví dụ: Lọc sản phẩm có RAM = '8GB' và ROM = '256GB'.
     * Cách dùng: Product::filterBySpecs(['ram' => ['8GB'], 'rom' => ['256GB']])->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $specs Mảng key-value các thông số cần lọc (VD: ['ram' => ['8GB', '12GB']])
     * @return \Illuminate\Database\Eloquent\Builder
     */
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

    /**
     * Scope: Sắp xếp danh sách sản phẩm theo tiêu chí được chọn.
     * Cách dùng: Product::sortBy('price_asc')->get()
     * 
     * Các giá trị hợp lệ:
     *   - 'price_asc'  : Giá tăng dần
     *   - 'price_desc' : Giá giảm dần
     *   - 'name_asc'   : Tên A-Z
     *   - 'name_desc'  : Tên Z-A
     *   - 'promo'      : Giảm giá nhiều nhất
     *   - 'newest'     : Mới nhất (mặc định)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $sort Tiêu chí sắp xếp
     * @return \Illuminate\Database\Eloquent\Builder
     */
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

    /**
     * Mối quan hệ nhiều - nhiều tự liên kết (Self-referencing belongsToMany)
     * Đại diện cho các sản phẩm gợi ý bán kèm (Cross-sell) được quản trị viên chỉ định thủ công trong database.
     * Liên kết thông qua bảng trung gian 'product_cross_sells' và sắp xếp theo thứ tự hiển thị.
     */
    public function crossSells()
    {
        return $this->belongsToMany(Product::class, 'product_cross_sells', 'product_id', 'cross_sell_id')
            ->withPivot('sort_order')
            ->orderBy('product_cross_sells.sort_order', 'asc');
    }

    /**
     * Mối quan hệ nhiều - nhiều tự liên kết (Self-referencing belongsToMany)
     * Đại diện cho các sản phẩm phụ kiện mua kèm dạng Combo ưu đãi tiết kiệm.
     * Liên kết qua bảng trung gian 'product_combos', có mang theo các thông tin bổ sung (pivot columns) 
     * gồm: loại giảm giá (discount_type), mức giảm giá (discount_value), và thứ tự sắp xếp (sort_order).
     */
    public function comboProducts()
    {
        return $this->belongsToMany(Product::class, 'product_combos', 'product_id', 'combo_product_id')
            ->withPivot('sort_order', 'discount_type', 'discount_value')
            ->orderBy('product_combos.sort_order', 'asc');
    }

    /**
     * Quan hệ: Sản phẩm có nhiều bản ghi Lịch sử xem / Yêu thích (Wishlist / Recently Viewed).
     * Dùng trong: CrossSellService (Tầng 1 - Personalization) để lấy phụ kiện user đã xem gần đây.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wishlistRecentlyViewed()
    {
        return $this->hasMany(WishlistRecentlyViewed::class, 'product_id', 'product_id');
    }
}
