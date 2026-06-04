<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * PointsService - Dịch vụ Quản lý Hệ thống Tích lũy Điểm và Phân hạng Thành viên.
 *
 * Nhiệm vụ chính:
 * 1. Tính toán điểm tích lũy dựa trên số tiền khách hàng chi tiêu thực tế.
 * 2. Lấy số dư điểm ví, điểm tích lũy hạng, và hạng thành viên hiện tại.
 * 3. Tự động cộng điểm ví và điểm hạng (rank) khi đơn hàng hoàn tất thành công.
 * 4. Trừ điểm ví khi khách hàng đổi quà hoặc áp dụng mã giảm giá.
 * 5. Hoàn trả điểm tiêu dùng hoặc thu hồi điểm tích lũy khi đơn hàng bị hủy.
 * 6. Tự động nâng hạ hạng thành viên dựa trên tổng số điểm hạng tích lũy.
 * 7. Ghi nhận lịch sử biến động điểm chi tiết (Lịch sử giao dịch điểm) để đối soát.
 */
class PointsService
{
    // Tỷ lệ quy đổi tích điểm: Cứ mỗi 10.000 VND chi tiêu thực tế, khách hàng sẽ nhận được 1 điểm tiêu dùng.
    public const EARN_RATE = 10000; 
    
    // Giá trị quy đổi giảm giá: 1 điểm tiêu dùng có giá trị tương đương 1.000 VND khi thanh toán đơn hàng.
    public const POINT_VALUE = 1000; 

    /**
     * Tính toán số điểm khách hàng nhận được dựa trên số tiền chi trả thực tế của đơn hàng.
     * 
     * @param int|float $amount Số tiền đơn hàng thực tế
     * @return int Số điểm làm tròn xuống (ví dụ: 199.000đ / 10.000 = 19 điểm)
     */
    public function calculateEarnedPoints(int|float $amount): int
    {
        return (int) floor(max(0, $amount) / self::EARN_RATE);
    }

    /**
     * Truy vấn thông tin số dư điểm ví, điểm rank tích lũy và hạng thành viên hiện tại của người dùng.
     * 
     * @param User $user Người dùng cần kiểm tra
     * @return array Mảng chứa chi tiết: ví điểm tiêu dùng, điểm hạng, và hạng thành viên
     */
    public function getBalance(User $user): array
    {
        // Truy vấn bản ghi từ bảng `user_points` dựa theo khóa ngoại `user_id`
        $row = DB::table('user_points')->where('user_id', $user->user_id)->first();

        return [
            'wallet_points' => (int) ($row->wallet_points ?? 0),  // Điểm hiện tại trong ví (dùng để đổi quà, voucher)
            'rank_points' => (int) ($row->rank_points ?? 0),      // Điểm tích lũy hạng (chỉ tăng lên, dùng phân hạng VIP)
            'current_rank' => $row->current_rank ?? 'Bronze',     // Tên hạng hiện tại (Đồng, Bạc, Vàng, Kim Cương)
        ];
    }

    /**
     * Xử lý cộng điểm tích lũy khi một đơn hàng được cập nhật trạng thái "Hoàn tất" (Completed).
     * Sử dụng Giao dịch cơ sở dữ liệu (Database Transaction) để đảm bảo an toàn dữ liệu và tính nhất quán.
     * 
     * @param Order $order Đơn hàng hoàn tất
     * @return array Kết quả xử lý cộng điểm
     */
    public function applyOrderCompletedPoints(Order $order): array
    {
        // Kiểm tra nếu đơn hàng không được liên kết với tài khoản người dùng (khách vãng lai) thì không tích điểm
        if (! $order->user_id) {
            return ['processed' => false, 'reason' => 'missing_user'];
        }

        // Kiểm tra nếu đơn hàng này đã được xử lý cộng điểm rồi thì không cộng thêm để tránh lặp lại (Replay Attack)
        if (($order->points_status ?? 'pending') !== 'pending') {
            return ['processed' => false, 'reason' => 'already_processed'];
        }

        // Lấy số tiền thanh toán cuối cùng của đơn hàng và tính số điểm tích lũy được
        $amount = (int) ($order->final_amount ?? 0);
        $points = $this->calculateEarnedPoints($amount);

        // Nếu số điểm tích lũy được <= 0 (đơn giá trị quá nhỏ), cập nhật trạng thái điểm đơn hàng là đã xử lý và kết thúc
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

        // Thực hiện giao dịch DB để đảm bảo cập nhật đồng thời nhiều bảng mà không bị tranh chấp dữ liệu (Race Condition)
        return DB::transaction(function () use ($order, $points) {
            // Lấy dòng dữ liệu điểm của người dùng và khóa dòng này để cập nhật độc quyền (lockForUpdate)
            $userPoints = DB::table('user_points')
                ->where('user_id', $order->user_id)
                ->lockForUpdate()
                ->first();

            // Nếu người dùng chưa từng có bản ghi tích điểm nào trong hệ thống, thực hiện khởi tạo mới
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

                // Truy vấn lại dòng dữ liệu mới vừa tạo kèm theo khóa dòng
                $userPoints = DB::table('user_points')
                    ->where('user_id', $order->user_id)
                    ->lockForUpdate()
                    ->first();
            }

            // Tính toán các giá trị điểm mới sau khi cộng thêm điểm từ đơn hàng vừa hoàn tất
            $newWallet = (int) $userPoints->wallet_points + $points;
            $newRank = (int) $userPoints->rank_points + $points;
            $newWalletTotalEarned = (int) $userPoints->wallet_total_earned + $points;
            $newRankTotalEarned = (int) $userPoints->rank_total_earned + $points;
            
            // Tự động phân định lại thứ hạng VIP của thành viên dựa theo số điểm hạng mới
            $newRankLevel = $this->resolveRankLevel($newRank);

            // Cập nhật các thông số điểm và hạng thành viên vào bảng `user_points`
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

            // Ghi nhận chi tiết lịch sử biến động điểm tiêu dùng (wallet) vào bảng giao dịch điểm
            $this->insertTransaction($order->user_id, 'wallet', 'earn', $points, $order, 'Tích điểm tiêu dùng từ đơn hàng hoàn tất');
            // Ghi nhận chi tiết lịch sử biến động điểm hạng thành viên (rank) vào bảng giao dịch điểm
            $this->insertTransaction($order->user_id, 'rank', 'earn', $points, $order, 'Tích điểm rank từ đơn hàng hoàn tất');

            // Cập nhật số điểm đã tích lũy thực tế và trạng thái của đơn hàng
            DB::table('orders')
                ->where('order_id', $order->order_id)
                ->update([
                    'wallet_points_earned' => $points,
                    'rank_points_earned' => $points,
                    'points_status' => 'processed',
                    'points_processed_at' => now(),
                ]);

            // Trả về kết quả sau khi cộng điểm thành công
            return [
                'processed' => true,
                'points' => $points,
                'wallet_points' => $newWallet,
                'rank_points' => $newRank,
                'current_rank' => $newRankLevel,
            ];
        });
    }

    /**
     * Thực hiện trừ điểm ví tiêu dùng của khách hàng khi họ mua sắm, áp dụng voucher hoặc tham gia vòng quay.
     * 
     * @param User $user Người dùng bị trừ điểm
     * @param int $points Số điểm cần trừ
     * @param Order|null $order Đơn hàng liên quan (nếu có)
     * @param string $description Mô tả lý do trừ điểm
     * @return array Kết quả trừ điểm thành công
     * @throws RuntimeException Trả về ngoại lệ nếu ví điểm không tồn tại hoặc số dư điểm không đủ
     */
    public function deductWalletPoints(User $user, int $points, ?Order $order = null, string $description = 'Dùng điểm tiêu dùng khi thanh toán'): array
    {
        $points = max(0, $points);
        // Nếu số điểm cần trừ bằng 0, không cần thực thi và trả về số điểm hiện tại của ví
        if ($points === 0) {
            return ['deducted' => true, 'points' => 0, 'remaining' => $this->getBalance($user)['wallet_points']];
        }

        // Chạy Transaction để khóa hàng dữ liệu điểm người dùng tránh bị trừ điểm âm hoặc trùng lặp
        return DB::transaction(function () use ($user, $points, $order, $description) {
            $userPoints = DB::table('user_points')
                ->where('user_id', $user->user_id)
                ->lockForUpdate()
                ->first();

            // Nếu người dùng không tồn tại ví điểm, báo lỗi
            if (! $userPoints) {
                throw new RuntimeException('Người dùng chưa có ví điểm.');
            }

            // Kiểm tra số điểm hiện tại trong ví có đủ để thực hiện giao dịch hay không
            if ((int) $userPoints->wallet_points < $points) {
                throw new RuntimeException('Không đủ điểm tiêu dùng.');
            }

            // Tính số điểm ví mới và tổng số điểm đã tiêu dùng từ trước đến nay
            $newWallet = (int) $userPoints->wallet_points - $points;
            $newWalletTotalUsed = (int) $userPoints->wallet_total_used + $points;

            // Thực hiện cập nhật số điểm ví tiêu dùng mới vào database
            DB::table('user_points')
                ->where('user_id', $user->user_id)
                ->update([
                    'wallet_points' => $newWallet,
                    'wallet_total_used' => $newWalletTotalUsed,
                    'updated_at' => now(),
                ]);

            // Nếu giao dịch trừ điểm gắn liền với một đơn hàng cụ thể, ghi nhận lịch sử giao dịch điểm
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

    /**
     * Xử lý hoàn trả/thu hồi điểm khi đơn hàng bị hủy bỏ (Cancelled).
     * 1. Hoàn lại số điểm tiêu dùng (wallet) mà khách đã sử dụng để mua đơn hàng đó.
     * 2. Thu hồi số điểm tích lũy (cả ví và hạng) đã được cộng cho khách trước đó (nếu đơn hàng đã ở trạng thái hoàn tất).
     * 
     * @param Order $order Đơn hàng bị hủy
     * @return array Kết quả hoàn trả và cập nhật điểm mới
     */
    public function cancelOrderPoints(Order $order): array
    {
        // Bỏ qua nếu đơn hàng không liên kết với tài khoản người dùng
        if (! $order->user_id) {
            return ['processed' => false, 'reason' => 'missing_user'];
        }

        // Lấy số điểm khách đã dùng và số điểm khách được cộng của đơn hàng
        $pointsUsed = (int) ($order->wallet_points_used ?? 0);
        $pointsEarned = (int) ($order->wallet_points_earned ?? 0);

        // Nếu đơn hàng này không liên quan gì đến điểm (không dùng điểm và chưa được cộng điểm), kết thúc luôn
        if ($pointsUsed === 0 && $pointsEarned === 0) {
            return ['processed' => false, 'reason' => 'no_points_action_needed'];
        }

        // Chạy Transaction bảo vệ dữ liệu điểm của người dùng
        return DB::transaction(function () use ($order, $pointsUsed, $pointsEarned) {
            $userPoints = DB::table('user_points')
                ->where('user_id', $order->user_id)
                ->lockForUpdate()
                ->first();

            // Khởi tạo ví điểm mới nếu người dùng chưa từng có
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

            // 1. HOÀN TRẢ ĐIỂM TIÊU DÙNG: Nếu khách hàng đã sử dụng điểm để thanh toán đơn hàng này, cộng hoàn trả lại
            if ($pointsUsed > 0) {
                $newWallet += $pointsUsed;
                $newWalletTotalUsed = max(0, $newWalletTotalUsed - $pointsUsed); // Giảm tổng điểm đã tiêu dùng của ví

                // Ghi nhận giao dịch hoàn điểm (refund)
                $this->insertTransaction(
                    $order->user_id,
                    'wallet',
                    'refund',
                    $pointsUsed,
                    $order,
                    'Hoàn trả điểm tiêu dùng từ đơn hàng đã hủy'
                );
            }

            // 2. THU HỒI ĐIỂM TÍCH LŨY: Nếu đơn hàng đã hoàn tất trước đó và đã cộng điểm, giờ phải trừ thu hồi lại
            if ($pointsEarned > 0 && ($order->points_status ?? 'pending') === 'processed') {
                $newWallet = max(0, $newWallet - $pointsEarned);
                $newRank = max(0, $newRank - $pointsEarned);
                $newWalletTotalEarned = max(0, $newWalletTotalEarned - $pointsEarned);
                $newRankTotalEarned = max(0, $newRankTotalEarned - $pointsEarned);

                // Ghi nhận giao dịch thu hồi điểm ví
                $this->insertTransaction(
                    $order->user_id,
                    'wallet',
                    'use',
                    $pointsEarned,
                    $order,
                    'Thu hồi điểm tiêu dùng từ đơn hàng đã hủy'
                );

                // Ghi nhận giao dịch thu hồi điểm hạng thành viên
                $this->insertTransaction(
                    $order->user_id,
                    'rank',
                    'use',
                    $pointsEarned,
                    $order,
                    'Thu hồi điểm rank từ đơn hàng đã hủy'
                );
            }

            // Đánh giá lại thứ hạng VIP của thành viên sau khi bị giảm điểm hạng
            $newRankLevel = $this->resolveRankLevel($newRank);

            // Cập nhật lại số điểm mới của người dùng trong ví
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

            // Cập nhật trạng thái điểm của đơn hàng thành hủy bỏ (cancelled)
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

    /**
     * Xác định thứ hạng thành viên VIP dựa theo số điểm tích lũy hạng (rank_points) hiện có.
     * Quy tắc hạng:
     * - Dưới 1.000 điểm: Hạng Đồng (Bronze)
     * - Từ 1.000 đến dưới 5.000 điểm: Hạng Bạc (Silver)
     * - Từ 5.000 đến dưới 10.000 điểm: Hạng Vàng (Gold)
     * - Từ 10.000 điểm trở lên: Hạng Kim Cương (Diamond)
     * 
     * @param int $rankPoints Điểm hạng tích lũy
     * @return string Tên hạng tương ứng ('Bronze', 'Silver', 'Gold', 'Diamond')
     */
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

    /**
     * Ghi nhận lịch sử biến động điểm (Lịch sử giao dịch điểm) vào bảng `point_transactions`.
     * 
     * @param int $userId ID khách hàng
     * @param string $pointType Loại điểm ('wallet' hoặc 'rank')
     * @param string $action Hành động ('earn' - cộng, 'use' - trừ, 'refund' - hoàn trả)
     * @param int $points Số điểm biến động
     * @param Order $order Đơn hàng liên quan
     * @param string $description Mô tả chi tiết giao dịch
     * @return void
     */
    protected function insertTransaction(int $userId, string $pointType, string $action, int $points, Order $order, string $description): void
    {
        DB::table('point_transactions')->insert([
            'user_id' => $userId,
            'point_type' => $pointType,
            'action' => $action,
            'points' => $points,
            'reference_type' => Order::class, // Sử dụng tên class Model Order để làm đa hình tham chiếu
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
