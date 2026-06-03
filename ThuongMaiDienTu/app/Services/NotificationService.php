<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Service xử lý các thao tác nghiệp vụ cốt lõi liên quan đến thông báo trong hệ thống.
 * Hỗ trợ tạo thông báo đơn lẻ, tạo hàng loạt cho nhiều người dùng (sử dụng chunking để tối ưu hiệu năng SQL),
 * đánh dấu đã đọc thông báo, gửi thông báo cho quản trị viên, khách hàng hoặc các nhóm đối tượng đặc thù.
 */
class NotificationService
{
    /**
     * Lấy danh sách thông báo phân trang của một người dùng cụ thể.
     * Sắp xếp theo thứ tự mới nhất đứng trước.
     */
    public function listForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Notification::query()
            ->where('user_id', $user->user_id)
            ->orderByDesc('notification_id')
            ->paginate($perPage);
    }

    /**
     * Đếm số lượng thông báo chưa đọc của người dùng.
     */
    public function unreadCountForUser(User $user): int
    {
        return Notification::query()
            ->where('user_id', $user->user_id)
            ->unread()
            ->count();
    }

    /**
     * Tạo và lưu một thông báo đơn lẻ dành cho một người dùng.
     */
    public function createForUser(User $user, array $payload): Notification
    {
        return Notification::create([
            'user_id' => $user->user_id,
            'type' => $payload['type'],
            'title' => $payload['title'],
            'content' => $payload['content'],
            'data' => $payload['data'] ?? null,
            'action_url' => $payload['action_url'] ?? null,
            'read_at' => $payload['read_at'] ?? null,
        ]);
    }

    /**
     * Tạo thông báo hàng loạt cho nhiều người dùng cùng một lúc.
     * Tối ưu hóa hiệu năng bằng cách chia nhỏ danh sách (chunking 1000 bản ghi mỗi lượt)
     * và thực hiện một lệnh insert duy nhất cho mỗi chunk thay vì insert từng dòng.
     */
    public function createForUsers(iterable $users, array $payload): int
    {
        $count = 0;
        $now = Carbon::now();
        
        $usersCollection = collect($users);
        
        // Chia nhỏ bộ dữ liệu thành từng cụm 1000 người dùng
        $usersCollection->chunk(1000)->each(function ($chunk) use ($payload, $now, &$count) {
            $insertData = [];
            foreach ($chunk as $user) {
                if ($user instanceof User) {
                    $insertData[] = [
                        'user_id' => $user->user_id,
                        'type' => $payload['type'],
                        'title' => $payload['title'],
                        'content' => $payload['content'],
                        // Ép kiểu array sang chuỗi JSON khi insert thô trực tiếp
                        'data' => isset($payload['data']) ? json_encode($payload['data']) : null,
                        'action_url' => $payload['action_url'] ?? null,
                        'read_at' => $payload['read_at'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $count++;
                }
            }
            // Thực hiện chèn thô hàng loạt tối ưu hóa
            if (!empty($insertData)) {
                Notification::insert($insertData);
            }
        });

        return $count;
    }

    /**
     * Gửi thông báo đến tất cả các quản trị viên trong hệ thống (role_id là 1, 2, hoặc 4).
     */
    public function notifyAdmins(array $payload): int
    {
        $admins = User::query()->whereIn('role_id', [1, 2, 4])->get();
        return $this->createForUsers($admins, $payload);
    }

    /**
     * Gửi thông báo đến tất cả các khách hàng thông thường (loại trừ các role admin 1, 2, 4).
     */
    public function notifyCustomers(array $payload): int
    {
        $customers = User::query()->whereNotIn('role_id', [1, 2, 4])->get();
        return $this->createForUsers($customers, $payload);
    }

    /**
     * Gửi thông báo đến toàn bộ người dùng trong hệ thống mà không lọc phân quyền.
     */
    public function notifyAll(array $payload): int
    {
        return $this->createForUsers(User::query()->get(), $payload);
    }

    /**
     * Gửi thông báo khuyến mãi đến các người dùng đăng ký nhận thông báo qua Email.
     * Kiểm tra trường email_notifications = 1 hoặc null (mặc định cho phép nhận).
     */
    public function notifyPromoUsers(array $payload): int
    {
        $users = User::query()
            ->whereNotNull('email')
            ->where(function ($query) {
                $query->where('email_notifications', 1)
                      ->orWhereNull('email_notifications');
            })
            ->get();

        return $this->createForUsers($users, $payload);
    }

    /**
     * Đánh dấu một thông báo cụ thể là đã đọc.
     */
    public function markAsRead(Notification $notification): Notification
    {
        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => Carbon::now()]);
        }

        return $notification->refresh();
    }

    /**
     * Đánh dấu tất cả các thông báo chưa đọc của người dùng cụ thể là đã đọc.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::query()
            ->where('user_id', $user->user_id)
            ->unread()
            ->update(['read_at' => Carbon::now()]);
    }

    /**
     * Lấy giá trị ngưỡng cảnh báo tồn kho thấp từ cấu hình hệ thống (mặc định là 10).
     */
    public function getLowStockThreshold(): int
    {
        return (int) config('notifications.low_stock_threshold', 10);
    }
}

