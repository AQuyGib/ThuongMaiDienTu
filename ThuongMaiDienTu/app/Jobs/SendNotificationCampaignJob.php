<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * Job xử lý việc gửi chiến dịch thông báo hàng loạt chạy ngầm (Queue Job).
 * Giúp giải phóng request của Admin ngay lập tức, ngăn ngừa lỗi timeout PHP khi gửi cho hàng ngàn người dùng.
 */
class SendNotificationCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Khởi tạo Job mới với danh sách user_id nhận tin và cấu hình payload thông báo.
     */
    public function __construct(
        protected array $userIds,
        protected array $payload
    ) {}

    /**
     * Thực thi tác vụ chạy ngầm.
     * Laravel sẽ tự động tiêm NotificationService vào phương thức handle khi job được kích hoạt.
     */
    public function handle(NotificationService $notificationService): void
    {
        // Lấy danh sách thực tế của người dùng từ cơ sở dữ liệu dựa trên danh sách ID nhận được
        $users = User::query()->whereIn('user_id', $this->userIds)->get();

        // Gửi hàng loạt thông báo qua NotificationService
        $notificationService->createForUsers($users, $this->payload);

        // Xóa cache thống kê trang chủ admin sau khi chiến dịch được phân phối thành công
        Cache::forget('admin_notifications_index_stats_and_charts');
    }
}

