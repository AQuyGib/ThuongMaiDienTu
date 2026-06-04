<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model RewardRedemption - Quản lý lịch sử và trạng thái các lượt đổi điểm lấy Voucher/Quà tặng của người dùng.
 *
 * Bảng: `reward_redemptions`
 * Khóa chính: `redemption_id`
 *
 * Chức năng:
 * 1. Ghi nhận thời gian, mã code đổi thưởng (`redemption_code`).
 * 2. Lưu trữ cấu hình snapshoot (`reward_snapshot`) để làm bằng chứng đối soát khi quà tặng thay đổi giá hoặc bị xoá.
 * 3. Quản lý hạn sử dụng của voucher đã đổi.
 */
class RewardRedemption extends Model
{
    // Tên bảng liên kết trong database
    protected $table = 'reward_redemptions';

    // Khóa chính của bảng
    protected $primaryKey = 'redemption_id';

    // Chặn chống lỗ hổng Mass Assignment
    protected $guarded = [];

    /**
     * Định cấu hình ép kiểu dữ liệu (Data Casting) cho các cột đặc thù.
     */
    protected $casts = [
        'reward_snapshot' => 'array', // Giải mã cấu hình snapshot JSON tại thời điểm đổi quà thành mảng PHP
        'issued_at' => 'datetime',    // Thời gian phát hành voucher
        'expires_at' => 'datetime',   // Thời gian hết hạn sử dụng voucher
    ];

    /**
     * Mối quan hệ thuộc về (Belongs To) với tài khoản khách hàng thực hiện đổi điểm.
     * Kết nối với bảng `users` thông qua khóa ngoại `user_id`.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Mối quan hệ thuộc về (Belongs To) với phần thưởng gốc trong catalog.
     * Kết nối với bảng `reward_catalog` thông qua khóa ngoại `reward_id`.
     */
    public function reward()
    {
        return $this->belongsTo(RewardCatalog::class, 'reward_id', 'reward_id');
    }
}
