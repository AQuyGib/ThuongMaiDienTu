<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Services\NotificationService;
use App\Services\PointsService;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primaryKey = 'order_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    protected static function booted()
    {
        static::created(function (Order $order) {
            if ($order->user) {
                app(NotificationService::class)->createForUser($order->user, [
                    'type' => 'order.created',
                    'title' => 'Đơn hàng đã được tạo',
                    'content' => 'Đơn hàng #' . $order->order_id . ' của bạn đã được ghi nhận và đang chờ xử lý.',
                    'action_url' => url('/orders'),
                    'data' => [
                        'order_id' => $order->order_id,
                        'status' => $order->status,
                    ],
                ]);
            }

            $admins = User::query()->whereIn('role_id', [1, 2, 4])->get();
            foreach ($admins as $admin) {
                app(NotificationService::class)->createForUser($admin, [
                    'type' => 'admin.order.created',
                    'title' => 'Đơn hàng mới',
                    'content' => 'Có đơn hàng mới #' . $order->order_id . ' từ ' . ($order->user->full_name ?? 'khách hàng') . '.',
                    'action_url' => url('/orders'),
                    'data' => [
                        'order_id' => $order->order_id,
                        'user_id' => $order->user_id,
                        'status' => $order->status,
                    ],
                ]);
            }
        });

        static::updated(function (Order $order) {
            if (! $order->wasChanged('status') || ! $order->user) {
                return;
            }

            $statusLabel = match ($order->status) {
                'Pending' => 'đang chờ xử lý',
                'Processing' => 'đang được xử lý',
                'Shipped' => 'đang giao',
                'Delivered' => 'đã giao thành công',
                'Cancelled' => 'đã bị hủy',
                default => $order->status,
            };

            app(NotificationService::class)->createForUser($order->user, [
                'type' => 'order.status_updated',
                'title' => 'Cập nhật trạng thái đơn hàng',
                'content' => 'Đơn hàng #' . $order->order_id . ' hiện ' . $statusLabel . '.',
                'action_url' => url('/orders'),
                'data' => [
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                ],
            ]);
        });

        static::saved(function ($order) {
            if ($order->wasChanged('status')) {
                if (self::isCompletedStatus($order->status)) {
                    app(PointsService::class)->applyOrderCompletedPoints($order);
                    self::syncMemberTierByPoints($order);
                } elseif (strtolower((string) $order->status) === 'cancelled') {
                    app(PointsService::class)->cancelOrderPoints($order);
                    self::syncMemberTierByPoints($order);
                }
            }
        });
    }

    protected static function syncMemberTierByPoints(self $order): void
    {
        if (! $order->user_id) {
            return;
        }

        $pointsService = app(PointsService::class);
        $balance = $pointsService->getBalance($order->user);
        $rankPoints = (int) ($balance['rank_points'] ?? 0);
        $newTier = $pointsService->resolveRankLevel($rankPoints);

        $user = $order->user;
        if ($user && $user->member_tier !== $newTier) {
            $user->member_tier = match ($newTier) {
                'Diamond' => 'KimCuong',
                'Gold' => 'Vang',
                'Silver' => 'Bac',
                'Bronze' => 'Dong',
                default => 'Dong',
            };
            $user->save();
        }
    }

    protected static function isCompletedStatus(?string $status): bool
    {
        return in_array(strtolower((string) $status), [
            OrderStatus::DELIVERED->value,
            'delivered',
            'completed',
        ], true);
    }
}
