<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PointsService
{
    public const EARN_RATE = 10000; // 10,000 VND = 1 point
    public const POINT_VALUE = 1000; // 1 point = 1,000 VND discount

    public function calculateEarnedPoints(int|float $amount): int
    {
        return (int) floor(max(0, $amount) / self::EARN_RATE);
    }

    public function getBalance(User $user): array
    {
        $row = DB::table('user_points')->where('user_id', $user->user_id)->first();

        return [
            'wallet_points' => (int) ($row->wallet_points ?? 0),
            'rank_points' => (int) ($row->rank_points ?? 0),
            'current_rank' => $row->current_rank ?? 'Bronze',
        ];
    }

    public function applyOrderCompletedPoints(Order $order): array
    {
        if (! $order->user_id) {
            return ['processed' => false, 'reason' => 'missing_user'];
        }

        if (($order->points_status ?? 'pending') !== 'pending') {
            return ['processed' => false, 'reason' => 'already_processed'];
        }

        $amount = (int) ($order->final_amount ?? 0);
        $points = $this->calculateEarnedPoints($amount);

        if ($points <= 0) {
            DB::table('orders')
                ->where('order_id', $order->order_id)
                ->update([
                    'wallet_points_earned' => 0,
                    'rank_points_earned' => 0,
                    'points_status' => 'processed',
                    'points_processed_at' => now(),
                ]);

            return ['processed' => true, 'points' => 0];
        }

        return DB::transaction(function () use ($order, $points) {
            $userPoints = DB::table('user_points')
                ->where('user_id', $order->user_id)
                ->lockForUpdate()
                ->first();

            if (! $userPoints) {
                DB::table('user_points')->insert([
                    'user_id' => $order->user_id,
                    'wallet_points' => 0,
                    'rank_points' => 0,
                    'wallet_total_earned' => 0,
                    'wallet_total_used' => 0,
                    'rank_total_earned' => 0,
                    'current_rank' => 'Bronze',
                    'last_rank_updated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $userPoints = DB::table('user_points')
                    ->where('user_id', $order->user_id)
                    ->lockForUpdate()
                    ->first();
            }

            $newWallet = (int) $userPoints->wallet_points + $points;
            $newRank = (int) $userPoints->rank_points + $points;
            $newWalletTotalEarned = (int) $userPoints->wallet_total_earned + $points;
            $newRankTotalEarned = (int) $userPoints->rank_total_earned + $points;
            $newRankLevel = $this->resolveRankLevel($newRank);

            DB::table('user_points')
                ->where('user_id', $order->user_id)
                ->update([
                    'wallet_points' => $newWallet,
                    'rank_points' => $newRank,
                    'wallet_total_earned' => $newWalletTotalEarned,
                    'rank_total_earned' => $newRankTotalEarned,
                    'current_rank' => $newRankLevel,
                    'last_rank_updated_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->insertTransaction($order->user_id, 'wallet', 'earn', $points, $order, 'Tích điểm tiêu dùng từ đơn hàng hoàn tất');
            $this->insertTransaction($order->user_id, 'rank', 'earn', $points, $order, 'Tích điểm rank từ đơn hàng hoàn tất');

            DB::table('orders')
                ->where('order_id', $order->order_id)
                ->update([
                    'wallet_points_earned' => $points,
                    'rank_points_earned' => $points,
                    'points_status' => 'processed',
                    'points_processed_at' => now(),
                ]);

            return [
                'processed' => true,
                'points' => $points,
                'wallet_points' => $newWallet,
                'rank_points' => $newRank,
                'current_rank' => $newRankLevel,
            ];
        });
    }

    public function deductWalletPoints(User $user, int $points, ?Order $order = null, string $description = 'Dùng điểm tiêu dùng khi thanh toán'): array
    {
        $points = max(0, $points);
        if ($points === 0) {
            return ['deducted' => true, 'points' => 0, 'remaining' => $this->getBalance($user)['wallet_points']];
        }

        return DB::transaction(function () use ($user, $points, $order, $description) {
            $userPoints = DB::table('user_points')
                ->where('user_id', $user->user_id)
                ->lockForUpdate()
                ->first();

            if (! $userPoints) {
                throw new RuntimeException('Người dùng chưa có ví điểm.');
            }

            if ((int) $userPoints->wallet_points < $points) {
                throw new RuntimeException('Không đủ điểm tiêu dùng.');
            }

            $newWallet = (int) $userPoints->wallet_points - $points;
            $newWalletTotalUsed = (int) $userPoints->wallet_total_used + $points;

            DB::table('user_points')
                ->where('user_id', $user->user_id)
                ->update([
                    'wallet_points' => $newWallet,
                    'wallet_total_used' => $newWalletTotalUsed,
                    'updated_at' => now(),
                ]);

            if ($order) {
                $this->insertTransaction($user->user_id, 'wallet', 'use', $points, $order, $description);
            }

            return [
                'deducted' => true,
                'points' => $points,
                'remaining' => $newWallet,
            ];
        });
    }

    public function cancelOrderPoints(Order $order): array
    {
        if (! $order->user_id) {
            return ['processed' => false, 'reason' => 'missing_user'];
        }

        $pointsUsed = (int) ($order->wallet_points_used ?? 0);
        $pointsEarned = (int) ($order->wallet_points_earned ?? 0);

        if ($pointsUsed === 0 && $pointsEarned === 0) {
            return ['processed' => false, 'reason' => 'no_points_action_needed'];
        }

        return DB::transaction(function () use ($order, $pointsUsed, $pointsEarned) {
            $userPoints = DB::table('user_points')
                ->where('user_id', $order->user_id)
                ->lockForUpdate()
                ->first();

            if (! $userPoints) {
                DB::table('user_points')->insert([
                    'user_id' => $order->user_id,
                    'wallet_points' => 0,
                    'rank_points' => 0,
                    'wallet_total_earned' => 0,
                    'wallet_total_used' => 0,
                    'rank_total_earned' => 0,
                    'current_rank' => 'Bronze',
                    'last_rank_updated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $userPoints = DB::table('user_points')
                    ->where('user_id', $order->user_id)
                    ->lockForUpdate()
                    ->first();
            }

            $newWallet = (int) $userPoints->wallet_points;
            $newRank = (int) $userPoints->rank_points;
            $newWalletTotalUsed = (int) $userPoints->wallet_total_used;
            $newWalletTotalEarned = (int) $userPoints->wallet_total_earned;
            $newRankTotalEarned = (int) $userPoints->rank_total_earned;

            // 1. Hoàn lại số điểm tiêu dùng khách hàng đã sử dụng
            if ($pointsUsed > 0) {
                $newWallet += $pointsUsed;
                $newWalletTotalUsed = max(0, $newWalletTotalUsed - $pointsUsed);

                $this->insertTransaction(
                    $order->user_id,
                    'wallet',
                    'refund',
                    $pointsUsed,
                    $order,
                    'Hoàn trả điểm tiêu dùng từ đơn hàng đã hủy'
                );
            }

            // 2. Thu hồi điểm tích lũy đã cộng (chỉ khi đơn hàng ở trạng thái processed points)
            if ($pointsEarned > 0 && ($order->points_status ?? 'pending') === 'processed') {
                $newWallet = max(0, $newWallet - $pointsEarned);
                $newRank = max(0, $newRank - $pointsEarned);
                $newWalletTotalEarned = max(0, $newWalletTotalEarned - $pointsEarned);
                $newRankTotalEarned = max(0, $newRankTotalEarned - $pointsEarned);

                $this->insertTransaction(
                    $order->user_id,
                    'wallet',
                    'use',
                    $pointsEarned,
                    $order,
                    'Thu hồi điểm tiêu dùng từ đơn hàng đã hủy'
                );

                $this->insertTransaction(
                    $order->user_id,
                    'rank',
                    'use',
                    $pointsEarned,
                    $order,
                    'Thu hồi điểm rank từ đơn hàng đã hủy'
                );
            }

            $newRankLevel = $this->resolveRankLevel($newRank);

            DB::table('user_points')
                ->where('user_id', $order->user_id)
                ->update([
                    'wallet_points' => $newWallet,
                    'rank_points' => $newRank,
                    'wallet_total_used' => $newWalletTotalUsed,
                    'wallet_total_earned' => $newWalletTotalEarned,
                    'rank_total_earned' => $newRankTotalEarned,
                    'current_rank' => $newRankLevel,
                    'last_rank_updated_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('orders')
                ->where('order_id', $order->order_id)
                ->update([
                    'points_status' => 'cancelled',
                    'points_processed_at' => now(),
                ]);

            return [
                'processed' => true,
                'wallet_points' => $newWallet,
                'rank_points' => $newRank,
                'current_rank' => $newRankLevel,
            ];
        });
    }

    public function resolveRankLevel(int $rankPoints): string
    {
        if ($rankPoints >= 10000) {
            return 'Diamond';
        }

        if ($rankPoints >= 5000) {
            return 'Gold';
        }

        if ($rankPoints >= 1000) {
            return 'Silver';
        }

        return 'Bronze';
    }

    protected function insertTransaction(int $userId, string $pointType, string $action, int $points, Order $order, string $description): void
    {
        DB::table('point_transactions')->insert([
            'user_id' => $userId,
            'point_type' => $pointType,
            'action' => $action,
            'points' => $points,
            'reference_type' => Order::class,
            'reference_id' => $order->order_id,
            'description' => $description,
            'metadata' => json_encode([
                'order_code' => $order->order_code ?? null,
                'final_amount' => $order->final_amount ?? null,
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
