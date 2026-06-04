<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model đại diện cho bảng 'notifications' trong cơ sở dữ liệu.
 * Lưu trữ thông tin chi tiết về từng thông báo được phân phối cho người dùng.
 */
class Notification extends Model
{
    // Xác định khóa chính của bảng là 'notification_id'
    protected $primaryKey = 'notification_id';

    // Cho phép gán hàng loạt (Mass Assignment) đối với tất cả các cột
    protected $guarded = [];

    // Tự động ép kiểu dữ liệu khi truy vấn
    protected $casts = [
        'user_id' => 'integer',
        'data' => 'array', // Tự động serialize/deserialize cột JSON metadata sang array PHP
        'read_at' => 'datetime',
    ];

    /**
     * Mối quan hệ nhiều-một (Many-to-One): Một thông báo thuộc về một tài khoản User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Local Scope để lọc nhanh các thông báo chưa đọc (read_at là null).
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}

