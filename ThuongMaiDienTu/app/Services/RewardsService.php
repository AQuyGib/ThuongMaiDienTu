<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class RewardsService
{
    public function getWalletBalance(User $user): int
    {
        $row = DB::table('user_points')->where('user_id', $user->user_id)->first();

        return (int) ($row->wallet_points ?? 0);
    }

    public function getCatalog(array $filters = [])
    {
        return DB::table('reward_catalog')
            ->when(!empty($filters['active_only']), fn ($q) => $q->where('is_active', true))
            ->when(!empty($filters['reward_type']), fn ($q) => $q->where('reward_type', $filters['reward_type']))
            ->when(!empty($filters['reward_category']), fn ($q) => $q->where('reward_category', $filters['reward_category']))
            ->orderBy('points_cost')
            ->orderBy('reward_id')
            ->get();
    }

    public function getRankPoints(User $user): int
    {
        return (int) (DB::table('user_points')->where('user_id', $user->user_id)->value('rank_points') ?? 0);
    }

    public function redeemVoucher(User $user, int $rewardId): array
    {
        return DB::transaction(function () use ($user, $rewardId) {
            $reward = DB::table('reward_catalog')->where('reward_id', $rewardId)->lockForUpdate()->first();
            if (! $reward || ! $reward->is_active) {
                throw new RuntimeException('Phần thưởng không tồn tại hoặc đã bị khóa.');
            }

            $this->assertRewardAvailable($reward);
            $this->assertRewardRankRule($reward, $user);
            $this->assertUserBalance($user, (int) $reward->points_cost);

            $wallet = DB::table('user_points')->where('user_id', $user->user_id)->lockForUpdate()->first();
            if (! $wallet) {
                throw new RuntimeException('Người dùng chưa có ví điểm.');
            }

            $newBalance = (int) $wallet->wallet_points - (int) $reward->points_cost;
            DB::table('user_points')->where('user_id', $user->user_id)->update([
                'wallet_points' => $newBalance,
                'wallet_total_used' => ((int) $wallet->wallet_total_used) + (int) $reward->points_cost,
                'updated_at' => now(),
            ]);

            $redemptionCode = 'RWD' . now()->format('YmdHis') . Str::upper(Str::random(6));
            $snapshot = [
                'reward_id' => (int) $reward->reward_id,
                'reward_code' => $reward->code,
                'reward_name' => $reward->name,
                'reward_type' => $reward->reward_type,
                'reward_category' => $reward->reward_category,
                'points_cost' => (int) $reward->points_cost,
                'discount_amount' => (int) $reward->discount_amount,
                'shipping_discount_amount' => (int) $reward->shipping_discount_amount,
            ];

            $redemptionId = DB::table('reward_redemptions')->insertGetId([
                'user_id' => $user->user_id,
                'reward_id' => $reward->reward_id,
                'redemption_code' => $redemptionCode,
                'status' => 'issued',
                'points_spent' => $reward->points_cost,
                'discount_amount' => $reward->discount_amount,
                'shipping_discount_amount' => $reward->shipping_discount_amount,
                'reward_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'issued_at' => now(),
                'expires_at' => now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! is_null($reward->stock)) {
                DB::table('reward_catalog')->where('reward_id', $reward->reward_id)->update([
                    'stock' => max(0, (int) $reward->stock - 1),
                    'updated_at' => now(),
                ]);
            }

            DB::table('point_transactions')->insert([
                'user_id' => $user->user_id,
                'point_type' => 'wallet',
                'action' => 'use',
                'points' => (int) $reward->points_cost,
                'reference_type' => 'reward_redemptions',
                'reference_id' => $redemptionId,
                'description' => 'Đổi thưởng: ' . $reward->name,
                'metadata' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'redemption_code' => $redemptionCode,
                'reward_name' => $reward->name,
                'points_spent' => (int) $reward->points_cost,
                'remaining_points' => $newBalance,
            ];
        });
    }

    public function spinWheel(User $user, string $wheelType = 'standard'): array
    {
        return DB::transaction(function () use ($user, $wheelType) {
            $luckyWheelsSetting = DB::table('settings')->where('setting_key', 'lucky_wheels_config')->value('setting_value');
            $wheels = json_decode($luckyWheelsSetting ?? '[]', true);
            if (empty($wheels)) {
                $wheels = [
                    ['key' => 'standard', 'name' => 'Vòng Thường', 'name_en' => 'Standard Wheel', 'points_cost' => 10],
                    ['key' => 'silver', 'name' => 'Vòng Bạc', 'name_en' => 'Silver Wheel', 'points_cost' => 20],
                    ['key' => 'gold', 'name' => 'Vòng Vàng', 'name_en' => 'Gold Wheel', 'points_cost' => 50]
                ];
            }

            $spinCost = 10;
            $minRank = 'none';
            foreach ($wheels as $w) {
                if ($w['key'] === $wheelType) {
                    $spinCost = (int)$w['points_cost'];
                    $minRank = $w['min_rank'] ?? 'none';
                    break;
                }
            }

            // Kiểm tra hạng thành viên tối thiểu để quay vòng quay này
            $rankOrder = [
                'none' => 0,
                'Dong' => 1,
                'Bronze' => 1,
                'Bac' => 2,
                'Silver' => 2,
                'Vang' => 3,
                'Gold' => 3,
                'KimCuong' => 4,
                'Diamond' => 4,
            ];

            $userRank = $user->member_tier ?: 'Dong';
            $userRankVal = $rankOrder[$userRank] ?? 1;
            $minRankVal = $rankOrder[$minRank] ?? 0;

            if ($userRankVal < $minRankVal) {
                $rankNames = [
                    'Dong' => 'Đồng',
                    'Bac' => 'Bạc',
                    'Vang' => 'Vàng',
                    'KimCuong' => 'Kim Cương',
                ];
                $requiredRankName = $rankNames[$minRank] ?? $minRank;
                throw new \RuntimeException("Bạn cần đạt hạng từ {$requiredRankName} trở lên mới có thể quay vòng quay này.");
            }

            $wallet = DB::table('user_points')->where('user_id', $user->user_id)->lockForUpdate()->first();
            if (! $wallet) {
                throw new RuntimeException('Người dùng chưa có ví điểm.');
            }
            if ((int) $wallet->wallet_points < $spinCost) {
                throw new RuntimeException('Không đủ điểm để quay vòng may mắn này.');
            }

            $allRewards = DB::table('reward_catalog')
                ->where('reward_type', 'wheel_prize')
                ->where('is_active', true)
                ->get();

            $rewards = $allRewards->filter(function ($item) use ($wheelType) {
                $meta = json_decode($item->metadata ?? '{}', true);
                $itemWheelType = $meta['wheel_type'] ?? 'standard';
                return $itemWheelType === $wheelType;
            });

            if ($rewards->isEmpty()) {
                throw new RuntimeException('Chưa có phần thưởng vòng quay khả dụng cho loại vòng quay này.');
            }

            // Tính toán quà trúng thưởng theo tỷ lệ trọng số (winning_rate trong metadata)
            $weightedRewards = [];
            $totalWeight = 0;

            foreach ($rewards as $item) {
                $meta = json_decode($item->metadata ?? '{}', true);
                $weight = (int) ($meta['winning_rate'] ?? 10); // Mặc định là 10 nếu không cấu hình
                if ($weight < 1) {
                    $weight = 1;
                }
                $weightedRewards[] = [
                    'item' => $item,
                    'weight' => $weight
                ];
                $totalWeight += $weight;
            }

            $rand = rand(1, $totalWeight);
            $currentSum = 0;
            $reward = null;

            foreach ($weightedRewards as $wr) {
                $currentSum += $wr['weight'];
                if ($rand <= $currentSum) {
                    $reward = $wr['item'];
                    break;
                }
            }

            if (! $reward) {
                $reward = $rewards->first();
            }

            $newBalance = (int) $wallet->wallet_points - $spinCost;
            DB::table('user_points')->where('user_id', $user->user_id)->update([
                'wallet_points' => $newBalance,
                'wallet_total_used' => ((int) $wallet->wallet_total_used) + $spinCost,
                'updated_at' => now(),
            ]);

            $spinCode = 'SPN' . now()->format('YmdHis') . Str::upper(Str::random(6));
            $snapshot = [
                'reward_id' => (int) $reward->reward_id,
                'reward_code' => $reward->code,
                'reward_name' => $reward->name,
                'reward_type' => $reward->reward_type,
                'points_cost' => $spinCost,
                'wheel_type' => $wheelType,
            ];

            $spinId = DB::table('lucky_wheel_spins')->insertGetId([
                'user_id' => $user->user_id,
                'reward_id' => $reward->reward_id,
                'spin_code' => $spinCode,
                'status' => 'won',
                'points_spent' => $spinCost,
                'result_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'spun_at' => now(),
                'expires_at' => now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('point_transactions')->insert([
                'user_id' => $user->user_id,
                'point_type' => 'wallet',
                'action' => 'use',
                'points' => $spinCost,
                'reference_type' => 'lucky_wheel_spins',
                'reference_id' => $spinId,
                'description' => 'Quay ' . ($wheelType === 'silver' ? 'vòng Bạc' : ($wheelType === 'gold' ? 'vòng Vàng' : 'vòng Thường')) . ': ' . $reward->name,
                'metadata' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'spin_code' => $spinCode,
                'reward_name' => $reward->name,
                'points_spent' => $spinCost,
                'remaining_points' => $newBalance,
                'result' => $snapshot,
            ];
        });
    }

    protected function assertRewardAvailable(object $reward): void
    {
        if (! is_null($reward->stock) && (int) $reward->stock <= 0) {
            throw new RuntimeException('Phần thưởng đã hết lượt.');
        }
    }

    protected function assertUserBalance(User $user, int $pointsCost): void
    {
        $balance = $this->getWalletBalance($user);
        if ($balance < $pointsCost) {
            throw new RuntimeException('Không đủ điểm tiêu dùng.');
        }
    }

    protected function assertRewardRankRule(object $reward, User $user): void
    {
        if (! (bool) ($reward->requires_rank_check ?? false)) {
            return;
        }

        $meta = json_decode($reward->metadata ?? '{}', true);
        $minRank = $meta['min_rank'] ?? 'none';

        $rankOrder = [
            'none' => 0,
            'Dong' => 1,
            'Bronze' => 1,
            'Bac' => 2,
            'Silver' => 2,
            'Vang' => 3,
            'Gold' => 3,
            'KimCuong' => 4,
            'Diamond' => 4,
        ];

        $userRank = $user->member_tier ?: 'Dong';
        $userRankVal = $rankOrder[$userRank] ?? 1;
        $minRankVal = $rankOrder[$minRank] ?? 0;

        if ($userRankVal < $minRankVal) {
            $rankNames = [
                'none' => 'Thành viên',
                'Dong' => 'Đồng',
                'Bac' => 'Bạc',
                'Vang' => 'Vàng',
                'KimCuong' => 'Kim Cương',
            ];
            $requiredRankName = $rankNames[$minRank] ?? $minRank;
            throw new RuntimeException("Bạn cần đạt hạng từ {$requiredRankName} trở lên mới có thể đổi phần thưởng này.");
        }
    }
}
