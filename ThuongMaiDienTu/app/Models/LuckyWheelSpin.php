<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model LuckyWheelSpin - Quản lý lịch sử quay thưởng Vòng quay may mắn tiêu dùng điểm.
 *
 * Bảng: `lucky_wheel_spins`
 * Khóa chính: `spin_id`
 *
 * Chức năng:
 * 1. Ghi nhận lượt quay, thời gian quay, số điểm đã tiêu tốn.
 * 2. Lưu trữ snapshot kết quả trúng thưởng (`result_snapshot`).
 * 3. Quản lý thời hạn nhận thưởng (expires_at) của các phần quà quay trúng.
 */
class LuckyWheelSpin extends Model
{
    // Tên bảng liên kết trong database
    protected $table = 'lucky_wheel_spins';

    // Khóa chính của bảng
    protected $primaryKey = 'spin_id';

    // Chặn chống lỗ hổng Mass Assignment
    protected $guarded = [];

    /**
     * Định cấu hình ép kiểu dữ liệu (Data Casting) cho các cột đặc thù.
     */
    protected $casts = [
        'result_snapshot' => 'array', // Giải mã cấu hình JSON của kết quả trúng thưởng thành mảng PHP
        'spun_at' => 'datetime',      // Thời điểm thực hiện hành động quay vòng quay
        'expires_at' => 'datetime',   // Hạn cuối cùng để khách hàng nhận/đổi quà trúng giải
    ];

    /**
     * Mối quan hệ thuộc về (Belongs To) với tài khoản khách hàng thực hiện quay thưởng.
     * Kết nối với bảng `users` thông qua khóa ngoại `user_id`.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Mối quan hệ thuộc về (Belongs To) với phần thưởng cụ thể đã quay trúng.
     * Kết nối với bảng `reward_catalog` thông qua khóa ngoại `reward_id`.
     */
    public function reward()
    {
        return $this->belongsTo(RewardCatalog::class, 'reward_id', 'reward_id');
    }
}
