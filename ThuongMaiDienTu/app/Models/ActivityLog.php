<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'log_id';
    
    // Vô hiệu hóa cột updated_at vì nhật ký chỉ được ghi nhận một lần (Insert Only)
    const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Mối quan hệ đa hình với đối tượng thực hiện hành động (User, API, System)
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Mối quan hệ tương thích ngược với người dùng thực hiện (khi causer là User)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * Mối quan hệ đa hình với đối tượng chịu tác động (Product, Order, etc.)
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Tạo nhãn mô tả hành động (action) tương thích ngược cho giao diện cũ
     */
    public function getActionAttribute(): string
    {
        $event = strtolower($this->event ?? '');
        $subjectName = $this->subject_type ? class_basename($this->subject_type) : '';
        
        $translate = [
            'User' => 'tài khoản/khách hàng',
            'Product' => 'sản phẩm',
            'Order' => 'đơn hàng',
            'Article' => 'bài viết',
            'Category' => 'danh mục',
            'Supplier' => 'nhà cung cấp',
            'Attribute' => 'thuộc tính',
            'Page' => 'trang tĩnh',
            'FlashSale' => 'flash sale',
            'CouponFlashSale' => 'mã giảm giá',
            'WarehouseTransfer' => 'phiếu chuyển kho',
            'InventoryAudit' => 'phiếu kiểm kê',
            'Cashbook' => 'sổ quỹ',
            'HomeSection' => 'khung trang chủ',
            'ServiceInvoice' => 'hóa đơn dịch vụ',
            'RepairTicket' => 'phiếu sửa chữa',
            'Video' => 'video',
            'Role' => 'vai trò',
            'Review' => 'đánh giá',
            'VideoComment' => 'bình luận video',
            'PurchaseOrder' => 'phiếu nhập kho',
            'Setting' => 'cấu hình hệ thống',
            'Warranty' => 'chứng nhận bảo hành',
            'Installment' => 'hợp đồng trả góp',
            'RewardCatalog' => 'phần thưởng',
            'InstallmentPayment' => 'kỳ trả góp',
            'RewardRedemption' => 'lượt đổi thưởng',
        ];
        
        $displayName = $translate[$subjectName] ?? strtolower($subjectName);

        switch ($event) {
            case 'created':
                return "Thêm mới " . ($subjectName === 'User' ? 'khách hàng' : $displayName) . " (ID: #{$this->subject_id})";
            case 'updated':
                return "Cập nhật " . ($subjectName === 'User' ? 'khách hàng' : $displayName) . " (ID: #{$this->subject_id})";
            case 'deleted':
                return "Xóa " . ($subjectName === 'User' ? 'khách hàng' : $displayName) . " (ID: #{$this->subject_id})";
            case 'restored':
                return "Khôi phục " . ($subjectName === 'User' ? 'khách hàng' : $displayName) . " (ID: #{$this->subject_id})";
            case 'export':
                return "Xuất file báo cáo " . ($subjectName === 'User' ? 'nhân sự/khách hàng' : $displayName);
            case 'login':
                return "Đăng nhập hệ thống thành công";
            default:
                return "Thao tác " . ($this->event ?? 'unknown') . " trên " . ($subjectName ? $displayName : 'hệ thống') . ($this->subject_id ? " #{$this->subject_id}" : "");
        }
    }
}