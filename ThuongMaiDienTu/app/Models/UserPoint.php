<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model UserPoint - Quản lý ví điểm tích lũy và xếp hạng thành viên của khách hàng.
 *
 * Bảng: `user_points`
 * Khóa chính: `user_points_id`
 *
 * Chức năng:
 * 1. Lưu trữ số dư điểm tiêu dùng khả dụng hiện có trong ví (`wallet_points`).
 * 2. Lưu trữ tổng số điểm hạng tích lũy (`rank_points`) dùng để xếp hạng thành viên.
 * 3. Quản lý phân hạng thành viên VIP hiện tại của người dùng (`current_rank`).
 * 4. Theo dõi thống kê trọn đời: tổng điểm tích lũy được và tổng điểm đã tiêu dùng.
 */
class UserPoint extends Model
{
    // Tên bảng liên kết trong database
    protected $table = 'user_points';

    // Khóa chính của bảng
    protected $primaryKey = 'user_points_id';

    // Chặn chống lỗ hổng Mass Assignment
    protected $guarded = [];

    /**
     * Mối quan hệ thuộc về (Belongs To) với tài khoản người dùng sở hữu ví điểm.
     * Kết nối với bảng `users` thông qua khóa ngoại `user_id`.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
