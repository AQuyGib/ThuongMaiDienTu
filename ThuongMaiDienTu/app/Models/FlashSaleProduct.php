<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class FlashSaleProduct
 * 
 * Đại diện cho bảng `flash_sale_products` lưu trữ thông tin sản phẩm cụ thể tham gia vào một chiến dịch Flash Sale.
 * Lưu trữ giá khuyến mãi, giới hạn tồn kho được phép bán trong đợt sale, và số lượng đã bán thực tế.
 * 
 * @property int $flash_sale_product_id Khóa chính của bảng
 * @property int $flash_sale_id Khóa ngoại tham chiếu đến chiến dịch Flash Sale
 * @property int $product_id Khóa ngoại tham chiếu đến sản phẩm gốc
 * @property int $sale_price Giá bán khuyến mãi áp dụng trong chiến dịch Flash Sale
 * @property int $stock_limit Số lượng tồn kho tối đa được phân bổ cho đợt Flash Sale này
 * @property int $sold_quantity Số lượng sản phẩm đã bán được trong chiến dịch
 * @property int $sort_order Thứ tự sắp xếp hiển thị sản phẩm trên giao diện
 * @property bool $is_active Trạng thái kích hoạt của sản phẩm trong đợt sale
 */
class FlashSaleProduct extends Model
{
    // Tên khóa chính của bảng
    protected $primaryKey = 'flash_sale_product_id';
    
    // Cho phép Mass Assignment cập nhật dữ liệu hàng loạt cho tất cả các cột
    protected $guarded = [];

    // Tự động cast kiểu dữ liệu khi lấy dữ liệu ra khỏi cơ sở dữ liệu
    protected $casts = [
        'sale_price' => 'integer',
        'stock_limit' => 'integer',
        'sold_quantity' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Mối quan hệ Nhiều-1 (Many-to-1).
     * Sản phẩm Flash Sale thuộc về một chiến dịch Flash Sale cụ thể.
     * 
     * @return BelongsTo
     */
    public function flashSale(): BelongsTo
    {
        return $this->belongsTo(FlashSale::class, 'flash_sale_id', 'flash_sale_id');
    }

    /**
     * Mối quan hệ Nhiều-1 (Many-to-1).
     * Sản phẩm Flash Sale liên kết tới sản phẩm gốc để lấy thông tin chi tiết (tên, hình ảnh, giá gốc...).
     * 
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
