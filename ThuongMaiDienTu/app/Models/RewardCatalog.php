<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model RewardCatalog - Quản lý Danh mục phần thưởng và Quà tặng vòng quay may mắn.
 *
 * Bảng: `reward_catalog`
 * Khóa chính: `reward_id`
 *
 * Chức năng chính:
 * 1. Định nghĩa cấu trúc quà tặng (Voucher giảm giá hóa đơn, giảm giá vận chuyển, hiện vật).
 * 2. Lưu trữ cấu hình nâng cao trong cột JSON `metadata` (ví dụ: tỉ lệ trúng vòng quay, hạng VIP tối thiểu).
 * 3. Cung cấp các thuộc tính ảo (accessors) để phục vụ hiển thị trực quan ở cả Frontend và Backend.
 */
class RewardCatalog extends Model
{
    // Tên bảng liên kết trong database
    protected $table = 'reward_catalog';

    // Tên cột khóa chính của bảng
    protected $primaryKey = 'reward_id';

    // Chặn chống lỗ hổng Mass Assignment (rỗng có nghĩa là cho phép gán hàng loạt tất cả các cột)
    protected $guarded = [];

    /**
     * Định cấu hình ép kiểu dữ liệu (Data Casting) cho các cột đặc thù.
     */
    protected $casts = [
        'is_active' => 'boolean',  // Chuyển trạng thái hoạt động về kiểu boolean thực tế (true/false)
        'metadata' => 'array',     // Tự động giải mã chuỗi JSON trong database thành mảng PHP tiện dụng
        'starts_at' => 'datetime', // Tự động parse chuỗi ngày giờ bắt đầu thành đối tượng Carbon
        'ends_at' => 'datetime',   // Tự động parse chuỗi ngày giờ kết thúc thành đối tượng Carbon
    ];

    /**
     * Đăng ký danh sách các thuộc tính ảo (Append Accessors) để tự động đính kèm vào chuỗi JSON trả về.
     */
    protected $appends = ['display_image', 'status_label', 'progress_percent'];

    /**
     * Mối quan hệ một-nhiều (One-to-Many) với lịch sử đổi điểm lấy voucher của người dùng.
     * Kết nối với bảng `reward_redemptions`.
     */
    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class, 'reward_id', 'reward_id');
    }

    /**
     * Mối quan hệ một-nhiều (One-to-Many) với lịch sử các lượt quay vòng quay trúng quà này.
     * Kết nối với bảng `lucky_wheel_spins`.
     */
    public function wheelSpins()
    {
        return $this->hasMany(LuckyWheelSpin::class, 'reward_id', 'reward_id');
    }

    /**
     * Accessor 'display_image': Trả về đường dẫn ảnh hiển thị ưu tiên cho phần thưởng.
     * Ưu tiên lấy ảnh thu nhỏ (thumbnail_path), nếu không có thì lấy ảnh gốc (image_path).
     *
     * Cách gọi trong code: `$reward->display_image`
     */
    public function getDisplayImageAttribute(): ?string
    {
        return $this->thumbnail_path ?: $this->image_path ?: null;
    }

    /**
     * Accessor 'status_label': Xác định nhãn trạng thái hoạt động thực tế dựa theo thời gian hiện tại.
     * Trả về các chuỗi trạng thái: 'Tắt', 'Hết hạn', 'Sắp mở', 'Đang bật'.
     *
     * Cách gọi trong code: `$reward->status_label`
     */
    public function getStatusLabelAttribute(): string
    {
        // 1. Nếu admin chủ động tắt trạng thái hoạt động
        if (! $this->is_active) {
            return 'Tắt';
        }

        // 2. Nếu đã quá thời hạn kết thúc chương trình
        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'Hết hạn';
        }

        // 3. Nếu chưa tới thời điểm bắt đầu chương trình
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'Sắp mở';
        }

        return 'Đang bật';
    }

    /**
     * Accessor 'progress_percent': Tính toán phần trăm quà tặng đã được đổi để hiển thị thanh tiến độ (Progress Bar).
     * Phục vụ hiển thị trực quan mức độ khan hiếm hoặc số lượng quà còn lại trong kho.
     *
     * Cách gọi trong code: `$reward->progress_percent`
     */
    public function getProgressPercentAttribute(): int
    {
        // Nếu quà không giới hạn số lượng tồn kho (stock là null), coi như lúc nào cũng đạt 100% khả dụng
        if (is_null($this->stock)) {
            return 100;
        }

        // Tính toán tỉ lệ phần trăm dựa trên lượng tồn kho giả định
        $used = max(0, 100 - min(100, (int) ($this->stock > 0 ? 100 - $this->stock : 100)));
        return max(0, min(100, $used));
    }
}
