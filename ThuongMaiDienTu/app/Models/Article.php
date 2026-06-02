<?php

namespace App\Models;

use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Article Model - Lớp đại diện cho bảng "articles" (Bài viết tin tức / Lifestyle).
 *
 * Quản lý thông tin bài viết do ban quản trị tự soạn thảo hoặc do người dùng gửi lên đóng góp (UGC),
 * hỗ trợ phân định dạng (Standard, Lookbook, Storytelling), liên kết với phiếu sửa chữa thiết bị,
 * và tự động gửi thông báo (Push Notification) đến toàn hệ thống khi có bài viết mới.
 */
class Article extends Model
{
    use HasFactory, SoftDeletes; // Sử dụng Factory để seed dữ liệu và SoftDeletes để hỗ trợ xóa tạm thời (thùng rác)

    // Khai báo khóa chính của bảng là "article_id" thay vì cột "id" mặc định
    protected $primaryKey = 'article_id';

    // Các cột dữ liệu được phép gán giá trị hàng loạt (Mass Assignment)
    protected $fillable = [
        'title', 'slug', 'summary', 'content', 'thumbnail', 'format_type', 
        'related_ticket_id', 'author_id', 'author_type', 'status', 
        'reward_points_awarded', 'embedded_product_ids', 'published_at',
        'tags', 'seo_title', 'seo_description', 'seo_keywords', 'seo_score',
        'ai_quality_score', 'ai_moderation_verdict', 'ai_analysis', 'ai_checked'
    ];

    // Chuyển đổi định dạng dữ liệu (Casting) khi đọc/ghi vào DB
    protected $casts = [
        'embedded_product_ids' => 'array', // Tự động chuyển JSON thành Array và ngược lại
        'published_at' => 'datetime',      // Tự động cast về đối tượng Carbon Datetime
        'tags' => 'array',
        'seo_keywords' => 'array',
        'ai_analysis' => 'array',
    ];

    /**
     * Phương thức boot tự động chạy khi Model được khởi động.
     * Dùng để lắng nghe sự kiện ghi dữ liệu nhằm tự động phát đi thông báo đến người dùng.
     */
    protected static function booted(): void
    {
        // Khi một bản ghi bài viết mới được ghi xuống cơ sở dữ liệu thành công
        static::created(function (Article $article) {
            // Nếu bài đăng không có slug (chưa sẵn sàng xuất bản), bỏ qua không gửi thông báo
            if (! $article->slug) {
                return;
            }

            // Gọi dịch vụ NotificationService để gửi thông báo đẩy đến tất cả khách hàng
            app(NotificationService::class)->notifyCustomers([
                'type' => 'article.published',
                'title' => 'Có bài viết mới: ' . $article->title,
                'content' => $article->summary ?: 'Khám phá ngay bài viết mới trên trang tin công nghệ.',
                'action_url' => url('/lifestyle/' . $article->slug), // Đường dẫn đến bài đăng thực tế
                'data' => [
                    'article_id' => $article->article_id,
                    'slug' => $article->slug,
                ],
            ]);
        });
    }

    /**
     * Mối quan hệ liên kết đến tác giả viết bài (Bảng Users).
     * Một bài viết sẽ thuộc sở hữu của một tài khoản User nhất định.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    /**
     * Mối quan hệ liên kết chéo hệ sinh thái (Ecosystem) tới Phiếu Sửa Chữa (Repair Ticket).
     * Phục vụ cho series nội dung "Right to Repair" thực tế.
     */
    public function repairTicket()
    {
        return $this->belongsTo(RepairTicket::class, 'related_ticket_id', 'ticket_id');
    }

    /**
     * Phương thức xử lý duyệt bài viết UGC (do người dùng tự viết) và cộng điểm thưởng thành viên tương ứng.
     * 
     * @param int $points Số điểm thưởng muốn cộng cho người viết
     * @return bool Trả về true nếu duyệt bài thành công, ngược lại là false
     */
    public function approveAndReward($points)
    {
        // Chỉ duyệt các bài viết do khách hàng viết ('customer') đang ở trạng thái chờ duyệt ('pending')
        if ($this->author_type === 'customer' && $this->status === 'pending') {
            // Cập nhật trạng thái bài viết thành đã duyệt ('approved') và lưu số điểm được thưởng
            $this->update([
                'status' => 'approved',
                'reward_points_awarded' => $points,
                'published_at' => now(),
            ]);

            // Ghi nhận giao dịch cộng điểm thưởng tích lũy (earned) vào lịch sử ví điểm của khách hàng
            RewardPoint::create([
                'user_id' => $this->author_id,
                'points' => $points,
                'reason' => 'Thưởng điểm đóng góp bài viết: ' . $this->title,
                'type' => 'earned' 
            ]);
            
            return true;
        }
        return false;
    }
}
