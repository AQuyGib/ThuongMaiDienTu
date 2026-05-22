<?php

namespace App\Models;

use App\Services\NotificationService;
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

        static::saved(function (Order $order) {
            if (! $order->user_id) {
                return;
            }

            $user = $order->user;
            if (! $user) {
                return;
            }

            $totalSpent = $user->orders()->where('status', 'Delivered')->sum('final_amount');
            $newTier = 'Dong';
            if ($totalSpent >= 20000000) {
                $newTier = 'Vang';
            } elseif ($totalSpent >= 5000000) {
                $newTier = 'Bac';
            }

            if ($user->member_tier !== $newTier) {
                $user->member_tier = $newTier;
                $user->save();
            }
        });
    }
}
