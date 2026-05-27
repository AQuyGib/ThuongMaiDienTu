<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class NotificationService
{
    public function listForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Notification::query()
            ->where('user_id', $user->user_id)
            ->orderByDesc('notification_id')
            ->paginate($perPage);
    }

    public function unreadCountForUser(User $user): int
    {
        return Notification::query()
            ->where('user_id', $user->user_id)
            ->unread()
            ->count();
    }

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

    public function createForUsers(iterable $users, array $payload): int
    {
        $count = 0;
        $now = Carbon::now();
        
        $usersCollection = collect($users);
        
        $usersCollection->chunk(1000)->each(function ($chunk) use ($payload, $now, &$count) {
            $insertData = [];
            foreach ($chunk as $user) {
                if ($user instanceof User) {
                    $insertData[] = [
                        'user_id' => $user->user_id,
                        'type' => $payload['type'],
                        'title' => $payload['title'],
                        'content' => $payload['content'],
                        'data' => isset($payload['data']) ? json_encode($payload['data']) : null,
                        'action_url' => $payload['action_url'] ?? null,
                        'read_at' => $payload['read_at'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $count++;
                }
            }
            if (!empty($insertData)) {
                Notification::insert($insertData);
            }
        });

        return $count;
    }

    public function notifyAdmins(array $payload): int
    {
        $admins = User::query()->whereIn('role_id', [1, 2, 4])->get();
        return $this->createForUsers($admins, $payload);
    }

    public function notifyCustomers(array $payload): int
    {
        $customers = User::query()->whereNotIn('role_id', [1, 2, 4])->get();
        return $this->createForUsers($customers, $payload);
    }

    public function notifyAll(array $payload): int
    {
        return $this->createForUsers(User::query()->get(), $payload);
    }

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

    public function markAsRead(Notification $notification): Notification
    {
        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => Carbon::now()]);
        }

        return $notification->refresh();
    }

    public function markAllAsRead(User $user): int
    {
        return Notification::query()
            ->where('user_id', $user->user_id)
            ->unread()
            ->update(['read_at' => Carbon::now()]);
    }

    public function getLowStockThreshold(): int
    {
        return (int) config('notifications.low_stock_threshold', 10);
    }
}
