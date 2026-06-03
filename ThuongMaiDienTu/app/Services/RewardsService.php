<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Class RewardsService
 * Dịch vụ cốt lõi xử lý nghiệp vụ ví điểm tích lũy, đổi phần thưởng, và quay số trúng thưởng theo tỉ lệ trọng số.
 */
class RewardsService
{
    /**
     * Lấy số dư điểm khả dụng trong ví của khách hàng.
     *
     * @param  \App\Models\User  $user
     * @return int
     */
    public function getWalletBalance(User $user): int
    {
        $row = DB::table('user_points')->where('user_id', $user->user_id)->first();

        return (int) ($row->wallet_points ?? 0);
    }

    /**
     * Lấy danh mục phần thưởng theo các điều kiện lọc đầu vào.
     *
     * @param  array  $filters
     * @return \Illuminate\Support\Collection
     */
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

    /**
     * Lấy tổng điểm tích lũy hạng thành viên (dùng để xác định level Đồng, Bạc, Vàng, Kim Cương).
     *
     * @param  \App\Models\User  $user
     * @return int
     */
    public function getRankPoints(User $user): int
    {
        return (int) (DB::table('user_points')->where('user_id', $user->user_id)->value('rank_points') ?? 0);
    }

    /**
     * Thực hiện logic đổi điểm lấy phần thưởng (Voucher/Quà tặng).
     * Sử dụng Transaction để đảm bảo tính nhất quán dữ liệu khi trừ điểm và giảm tồn kho.
     *
     * @param  \App\Models\User  $user
     * @param  int  $rewardId
     * @return array
     * @throws \RuntimeException
     */
    public function redeemVoucher(User $user, int $rewardId): array
    {
        return DB::transaction(function () use ($user, $rewardId) {
            // Lấy thông tin quà và khóa dòng (Lock For Update) để tránh tranh chấp số lượng tồn kho (Race Condition)
            $reward = DB::table('reward_catalog')->where('reward_id', $rewardId)->lockForUpdate()->first();
            if (! $reward || ! $reward->is_active) {
                throw new RuntimeException('Phần thưởng không tồn tại hoặc đã bị khóa.');
            }

            // 1. Kiểm tra tồn kho của phần thưởng
            $this->assertRewardAvailable($reward);
            // 2. Kiểm tra điều kiện hạng thành viên tối thiểu
            $this->assertRewardRankRule($reward, $user);
            // 3. Kiểm tra số dư điểm khả dụng trong ví của khách hàng
            $this->assertUserBalance($user, (int) $reward->points_cost);

            // Khóa dòng ví điểm của người dùng để cập nhật số điểm mới
            $wallet = DB::table('user_points')->where('user_id', $user->user_id)->lockForUpdate()->first();
            if (! $wallet) {
                throw new RuntimeException('Người dùng chưa có ví điểm.');
            }

            // Tính số điểm dư và cập nhật ví điểm người dùng
            $newBalance = (int) $wallet->wallet_points - (int) $reward->points_cost;
            DB::table('user_points')->where('user_id', $user->user_id)->update([
                'wallet_points' => $newBalance,
                'wallet_total_used' => ((int) $wallet->wallet_total_used) + (int) $reward->points_cost,
                'updated_at' => now(),
            ]);

            // Sinh mã code đổi thưởng ngẫu nhiên không trùng lặp
            $redemptionCode = 'RWD' . now()->format('YmdHis') . Str::upper(Str::random(6));
            
            // Lưu trữ snapshot của phần thưởng tại thời điểm đổi (đề phòng quản trị viên đổi tên hoặc số tiền giảm sau này)
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

            // Lưu lịch sử đổi thưởng
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
                'expires_at' => now()->addDays(30), // Voucher đổi điểm có hạn sử dụng là 30 ngày
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Giảm số lượng tồn kho của quà tặng (nếu quà tặng đó giới hạn số lượng - stock không phải null)
            if (! is_null($reward->stock)) {
                DB::table('reward_catalog')->where('reward_id', $reward->reward_id)->update([
                    'stock' => max(0, (int) $reward->stock - 1),
                    'updated_at' => now(),
                ]);
            }

            // Ghi nhận lịch sử giao dịch điểm tích lũy
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

    /**
     * Xử lý quay Vòng quay may mắn tiêu tốn điểm.
     * Tính toán quà trúng ngẫu nhiên dựa theo trọng số tỉ lệ trúng giải (`winning_rate`).
     *
     * @param  \App\Models\User  $user
     * @param  string  $wheelType (standard, silver, gold)
     * @return array
     * @throws \RuntimeException
     */
    public function spinWheel(User $user, string $wheelType = 'standard'): array
    {
        return DB::transaction(function () use ($user, $wheelType) {
            // Lấy thông tin cấu hình giá điểm/xếp hạng của 3 tầng vòng quay
            $luckyWheelsSetting = DB::table('settings')->where('setting_key', 'lucky_wheels_config')->value('setting_value');
            $wheels = json_decode($luckyWheelsSetting ?? '[]', true);
            if (empty($wheels)) {
                $wheels = [
                    ['key' => 'standard', 'name' => 'Vòng Thường', 'name_en' => 'Standard Wheel', 'points_cost' => 10],
                    ['key' => 'silver', 'name' => 'Vòng Bạc', 'name_en' => 'Silver Wheel', 'points_cost' => 20],
                    ['key' => 'gold', 'name' => 'Vòng Vàng', 'name_en' => 'Gold Wheel', 'points_cost' => 50]
                ];
            }

            // Gán giá trị điểm mặc định cho mỗi lượt quay
            $spinCost = 10;
            $minRank = 'none';
            foreach ($wheels as $w) {
                if ($w['key'] === $wheelType) {
                    $spinCost = (int)$w['points_cost'];
                    $minRank = $w['min_rank'] ?? 'none';
                    break;
                }
            }

            // Bản đồ đối chiếu thứ tự cấp bậc thành viên để validate
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

            // Kiểm tra xem thành viên có đủ thứ hạng để quay vòng quay cấp cao (bạc, vàng) hay không
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

            // Khóa ví điểm của user để trừ điểm và chống trùng lặp request
            $wallet = DB::table('user_points')->where('user_id', $user->user_id)->lockForUpdate()->first();
            if (! $wallet) {
                throw new RuntimeException('Người dùng chưa có ví điểm.');
            }
            if ((int) $wallet->wallet_points < $spinCost) {
                throw new RuntimeException('Không đủ điểm để quay vòng may mắn này.');
            }

            // Lấy danh sách các ô quà đang hoạt động của Vòng quay
            $allRewards = DB::table('reward_catalog')
                ->where('reward_type', 'wheel_prize')
                ->where('is_active', true)
                ->get();

            // Lọc ra các quà thuộc tầng vòng quay tương ứng (standard, silver, gold)
            $rewards = $allRewards->filter(function ($item) use ($wheelType) {
                $meta = json_decode($item->metadata ?? '{}', true);
                $itemWheelType = $meta['wheel_type'] ?? 'standard';
                return $itemWheelType === $wheelType;
            });

            if ($rewards->isEmpty()) {
                throw new RuntimeException('Chưa có phần thưởng vòng quay khả dụng cho loại vòng quay này.');
            }

            // ==========================================
            // THUẬT TOÁN QUAY THƯỞNG THEO TRỌNG SỐ (WEIGHTED RANDOM)
            // ==========================================
            $weightedRewards = [];
            $totalWeight = 0;

            foreach ($rewards as $item) {
                $meta = json_decode($item->metadata ?? '{}', true);
                $weight = (int) ($meta['winning_rate'] ?? 10); // Lấy tỉ lệ trúng giải (mặc định là 10 nếu không cấu hình)
                if ($weight < 1) {
                    $weight = 1;
                }
                $weightedRewards[] = [
                    'item' => $item,
                    'weight' => $weight
                ];
                $totalWeight += $weight;
            }

            // Random ra một số ngẫu nhiên trong khoảng tổng trọng số
            $rand = rand(1, $totalWeight);
            $currentSum = 0;
            $reward = null;

            // Tìm ô quà tương ứng dựa vào dải số ngẫu nhiên
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

            // Trừ điểm lượt quay trên ví người dùng
            $newBalance = (int) $wallet->wallet_points - $spinCost;
            DB::table('user_points')->where('user_id', $user->user_id)->update([
                'wallet_points' => $newBalance,
                'wallet_total_used' => ((int) $wallet->wallet_total_used) + $spinCost,
                'updated_at' => now(),
            ]);

            // Sinh mã code trúng thưởng
            $spinCode = 'SPN' . now()->format('YmdHis') . Str::upper(Str::random(6));
            $snapshot = [
                'reward_id' => (int) $reward->reward_id,
                'reward_code' => $reward->code,
                'reward_name' => $reward->name,
                'reward_type' => $reward->reward_type,
                'points_spent' => $spinCost,
                'wheel_type' => $wheelType,
            ];

            // Lưu lịch sử lượt quay trúng thưởng
            $spinId = DB::table('lucky_wheel_spins')->insertGetId([
                'user_id' => $user->user_id,
                'reward_id' => $reward->reward_id,
                'spin_code' => $spinCode,
                'status' => 'won', // Mặc định ghi nhận là won (trúng thưởng)
                'points_spent' => $spinCost,
                'result_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'spun_at' => now(),
                'expires_at' => now()->addDays(7), // Quà vòng quay có hạn sử dụng đổi quà là 7 ngày
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Ghi nhận lịch sử giao dịch điểm trong ví
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

    /**
     * Xác minh xem phần thưởng còn trong kho hay không.
     *
     * @param  object  $reward
     * @return void
     * @throws \RuntimeException
     */
    protected function assertRewardAvailable(object $reward): void
    {
        if (! is_null($reward->stock) && (int) $reward->stock <= 0) {
            throw new RuntimeException('Phần thưởng đã hết lượt.');
        }
    }

    /**
     * Xác minh số dư ví điểm người dùng có lớn hơn hoặc bằng giá trị đổi quà không.
     *
     * @param  \App\Models\User  $user
     * @param  int  $pointsCost
     * @return void
     * @throws \RuntimeException
     */
    protected function assertUserBalance(User $user, int $pointsCost): void
    {
        $balance = $this->getWalletBalance($user);
        if ($balance < $pointsCost) {
            throw new RuntimeException('Không đủ điểm tiêu dùng.');
        }
    }

    /**
     * Kiểm tra điều kiện hạn mức thẻ thành viên (Bronze, Silver, Gold, Diamond) của user có đủ điều kiện đổi quà.
     *
     * @param  object  $reward
     * @param  \App\Models\User  $user
     * @return void
     * @throws \RuntimeException
     */
    protected function assertRewardRankRule(object $reward, User $user): void
    {
        // Nếu phần thưởng không yêu cầu bắt buộc kiểm tra cấp bậc thì cho qua
        if (! (bool) ($reward->requires_rank_check ?? false)) {
            return;
        }

        $meta = json_decode($reward->metadata ?? '{}', true);
        $minRank = $meta['min_rank'] ?? 'none';

        // Bản đồ thứ tự xếp hạng
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

        // Nếu cấp bậc của người dùng nhỏ hơn cấp bậc tối thiểu được quy định
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
