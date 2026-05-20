<?php
namespace App\Models;

use App\Enums\OrderStatus;
use App\Services\PointsService;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    protected $primaryKey = 'order_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function details() {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    protected static function booted()
    {
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
