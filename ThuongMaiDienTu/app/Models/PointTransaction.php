<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model PointTransaction - Quản lý lịch sử biến động điểm chi tiết (Lịch sử giao dịch điểm) của người dùng.
 *
 * Bảng: `point_transactions`
 * Khóa chính: `transaction_id`
 *
 * Chức năng:
 * 1. Ghi nhận loại điểm biến động (`wallet` - điểm tiêu dùng, `rank` - điểm hạng).
 * 2. Ghi nhận hành động biến động (`earn` - cộng điểm, `use` - trừ điểm, `refund` - hoàn điểm).
 * 3. Hỗ trợ liên kết đa hình (`morphTo`) với các nguồn phát sinh biến động điểm (như Đơn hàng `orders`, Đổi quà `reward_redemptions`, Quay thưởng `lucky_wheel_spins`).
 */
class PointTransaction extends Model
{
    // Tên bảng liên kết trong database
    protected $table = 'point_transactions';

    // Khóa chính của bảng
    protected $primaryKey = 'transaction_id';

    // Chặn chống lỗ hổng Mass Assignment
    protected $guarded = [];

    /**
     * Định cấu hình ép kiểu dữ liệu (Data Casting) cho các cột đặc thù.
     */
    protected $casts = [
        'metadata' => 'array', // Tự động decode chuỗi metadata JSON thành mảng PHP tiện dụng
    ];

    /**
     * Mối quan hệ thuộc về (Belongs To) với tài khoản người dùng thực hiện giao dịch điểm.
     * Kết nối với bảng `users` thông qua khóa ngoại `user_id`.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Quan hệ Đa hình ngược (Polymorphic Relation Inverse).
     * Cho phép liên kết giao dịch điểm với nhiều Model khác nhau (như Order, RewardRedemption, LuckyWheelSpin)
     * thông qua hai cột `reference_type` và `reference_id`.
     *
     * Cách gọi trong code: `$transaction->reference`
     */
    public function reference()
    {
        return $this->morphTo(null, 'reference_type', 'reference_id');
    }
}
