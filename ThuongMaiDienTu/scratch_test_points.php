<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Services\PointsService;
use Illuminate\Support\Facades\DB;

function assertEqual($actual, $expected, $message) {
    if ($actual === $expected) {
        echo "✅ PASS: $message [Giá trị: $actual]\n";
    } else {
        echo "❌ FAIL: $message [Thực tế: $actual | Kỳ vọng: $expected]\n";
        exit(1);
    }
}

echo "=== BẮT ĐẦU CHẠY THỬ NGHIỆM CHỨC NĂNG TÍCH ĐIỂM ===\n";

DB::beginTransaction();

try {
    // 1. Lấy hoặc tạo user test
    $user = User::first();
    if (!$user) {
        // Tạo user mẫu nếu database trống
        $user = User::create([
            'full_name' => 'Test Points User',
            'email' => 'testpoints@techzone.vn',
            'password_hash' => bcrypt('password123'),
            'role_id' => 3, // Khách hàng
            'member_tier' => 'Dong',
        ]);
    }

    echo "User test: {$user->full_name} (ID: {$user->user_id})\n";

    // 2. Reset ví điểm của user test
    DB::table('user_points')->updateOrInsert(
        ['user_id' => $user->user_id],
        [
            'wallet_points' => 50,
            'rank_points' => 100,
            'wallet_total_earned' => 50,
            'wallet_total_used' => 0,
            'rank_total_earned' => 100,
            'current_rank' => 'Bronze',
            'last_rank_updated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    $pointsService = app(PointsService::class);
    $balanceBefore = $pointsService->getBalance($user);
    assertEqual($balanceBefore['wallet_points'], 50, "Số dư ví điểm ban đầu của user");
    assertEqual($balanceBefore['rank_points'], 100, "Điểm rank ban đầu của user");

    // 3. Giả lập trừ điểm tiêu dùng khi mua hàng (Khách dùng 30 điểm)
    echo "Giả lập trừ 30 điểm tiêu dùng...\n";
    $deductResult = $pointsService->deductWalletPoints($user, 30, null, 'Test trừ điểm mua hàng');
    assertEqual($deductResult['deducted'], true, "Kết quả trừ điểm");
    assertEqual($deductResult['remaining'], 20, "Số điểm còn lại trong ví sau khi trừ");

    // 4. Tạo đơn hàng test (Online, dùng 30 điểm, thanh toán thực tế 200,000 VND)
    echo "Tạo đơn hàng test mới...\n";
    $order = Order::create([
        'user_id' => $user->user_id,
        'order_code' => 'TESTORD' . time(),
        'order_type' => 'Online',
        'total_amount' => 230000,
        'shipping_fee' => 0,
        'discount_amount' => 0,
        'wallet_points_used' => 30, // Dùng 30 điểm
        'final_amount' => 200000, // Thanh toán thực tế 200,000 VND
        'payment_method' => 'COD',
        'status' => 'pending',
        'customer_name' => $user->full_name,
        'customer_phone' => '0987654321',
        'shipping_address' => 'Hà Nội, Việt Nam',
        'payment_status' => 'pending',
        'points_status' => 'pending',
    ]);

    // 5. Cập nhật đơn hàng thành Delivered (Hoàn tất) để kích hoạt tích điểm
    echo "Cập nhật đơn hàng sang trạng thái 'delivered'...\n";
    $order->status = 'delivered';
    $order->save(); // Sẽ kích hoạt applyOrderCompletedPoints và syncMemberTierByPoints

    // Kiểm tra điểm tích lũy được cộng: 200,000 VND / 10,000 = 20 điểm.
    $order->refresh();
    assertEqual($order->points_status, 'processed', "Trạng thái tích điểm của đơn hàng sau khi hoàn tất");
    assertEqual((int) $order->wallet_points_earned, 20, "Điểm tiêu dùng được tích lũy từ đơn hàng");
    assertEqual((int) $order->rank_points_earned, 20, "Điểm rank được tích lũy từ đơn hàng");

    // Số dư mới: wallet_points = 20 (cũ sau khi trừ) + 20 (tích lũy) = 40. rank_points = 100 (cũ) + 20 (tích lũy) = 120.
    $balanceAfterDelivered = $pointsService->getBalance($user);
    assertEqual($balanceAfterDelivered['wallet_points'], 40, "Số dư ví điểm sau khi đơn hàng hoàn tất");
    assertEqual($balanceAfterDelivered['rank_points'], 120, "Điểm rank sau khi đơn hàng hoàn tất");

    // 6. Cập nhật đơn hàng thành Cancelled (Hủy đơn) để kích hoạt hoàn điểm & thu hồi điểm
    echo "Cập nhật đơn hàng sang trạng thái 'cancelled'...\n";
    $order->status = 'cancelled';
    $order->save(); // Sẽ kích hoạt cancelOrderPoints và syncMemberTierByPoints

    $order->refresh();
    assertEqual($order->points_status, 'cancelled', "Trạng thái tích điểm của đơn hàng sau khi bị hủy");

    // Điểm phải hồi phục về ban đầu: wallet_points = 40 - 20 (thu hồi) + 30 (hoàn trả) = 50. rank_points = 120 - 20 = 100.
    $balanceAfterCancelled = $pointsService->getBalance($user);
    assertEqual($balanceAfterCancelled['wallet_points'], 50, "Số dư ví điểm sau khi hủy đơn hàng (phải phục hồi về 50)");
    assertEqual($balanceAfterCancelled['rank_points'], 100, "Điểm rank sau khi hủy đơn hàng (phải phục hồi về 100)");

    // 7. Kiểm tra lịch sử giao dịch trong point_transactions
    $txs = DB::table('point_transactions')
        ->where('user_id', $user->user_id)
        ->where('reference_id', $order->order_id)
        ->get();
    
    echo "Danh sách giao dịch điểm liên quan đến đơn hàng này:\n";
    foreach ($txs as $tx) {
        echo " - [{$tx->point_type}] Action: {$tx->action} | Điểm: {$tx->points} | Mô tả: {$tx->description}\n";
    }

    assertEqual($txs->where('point_type', 'wallet')->where('action', 'refund')->count(), 1, "Có giao dịch hoàn trả điểm tiêu dùng");
    
    $walletWithdrawCount = $txs->filter(fn($tx) => $tx->point_type === 'wallet' && $tx->action === 'use' && str_contains($tx->description, 'Thu hồi'))->count();
    assertEqual($walletWithdrawCount, 1, "Có giao dịch thu hồi điểm tiêu dùng");
    
    $rankWithdrawCount = $txs->filter(fn($tx) => $tx->point_type === 'rank' && $tx->action === 'use' && str_contains($tx->description, 'Thu hồi'))->count();
    assertEqual($rankWithdrawCount, 1, "Có giao dịch thu hồi điểm rank");

    echo "\n🎉 MỌI BÀI KIỂM TRA ĐÃ VƯỢT QUA THÀNH CÔNG! 🎉\n";

} catch (\Throwable $e) {
    echo "❌ LỖI HỆ THỐNG: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    // Luôn rollback để không làm bẩn cơ sở dữ liệu thật
    DB::rollBack();
    echo "Đã rollback database transaction test.\n";
}
