<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class FlashSale
 * 
 * Đại diện cho bảng `flash_sales` lưu trữ thông tin về các chiến dịch Flash Sale (sự kiện giảm giá chớp nhoáng).
 * Mỗi chiến dịch có một khoảng thời gian giới hạn (bắt đầu và kết thúc) và chứa nhiều sản phẩm Flash Sale được cấu hình.
 * 
 * @property int $flash_sale_id Khóa chính tự tăng của chiến dịch
 * @property string $name Tên chiến dịch Flash Sale
 * @property \Carbon\Carbon $start_at Thời điểm bắt đầu chiến dịch
 * @property \Carbon\Carbon $end_at Thời điểm kết thúc chiến dịch
 * @property bool $is_active Trạng thái kích hoạt (hoạt động/không hoạt động) của chiến dịch
 */
class FlashSale extends Model
{
    // Tên khóa chính của bảng (Laravel mặc định là id, ở đây dùng flash_sale_id)
    protected $primaryKey = 'flash_sale_id';
    
    // Thuộc tính bảo vệ chống Mass Assignment. Rỗng có nghĩa cho phép cập nhật hàng loạt tất cả các cột.
    protected $guarded = [];

    // Tự động chuyển đổi kiểu dữ liệu (casting) khi truy vấn từ DB lên hoặc lưu xuống DB
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Mối quan hệ 1-Nhiều (1-to-Many).
     * Một chiến dịch Flash Sale có thể chứa nhiều sản phẩm đăng ký tham gia.
     * 
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(FlashSaleProduct::class, 'flash_sale_id', 'flash_sale_id');
    }
}
