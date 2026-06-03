<?php

namespace App\Http\Controllers;

use App\Models\RewardCatalog;
use App\Services\RewardsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class RewardsController
 * Controller xử lý các chức năng ở phía giao diện người dùng (Frontend): Xem danh sách phần thưởng, đổi điểm và quay vòng quay may mắn.
 */
class RewardsController extends Controller
{
    /**
     * Hiển thị trang chủ phần thưởng phía frontend.
     * Lấy số dư điểm tích lũy của user, catalog quà đổi điểm và danh sách cấu hình các tầng vòng quay.
     *
     * @param  \App\Services\RewardsService  $rewardsService
     * @return \Illuminate\View\View
     */
    public function index(RewardsService $rewardsService)
    {
        $user = Auth::user();
        // Lấy số dư ví điểm tích lũy của khách hàng
        $balance = $user ? $rewardsService->getWalletBalance($user) : 0;
        // Lấy danh sách catalog phần thưởng chỉ đang ở trạng thái hoạt động (active)
        $catalog = $rewardsService->getCatalog(['active_only' => true]);

        // Lấy cấu hình vòng quay may mắn
        $luckyWheelsSetting = \App\Models\Setting::where('setting_key', 'lucky_wheels_config')->value('setting_value');
        $wheels = json_decode($luckyWheelsSetting ?? '[]', true);
        
        // Khởi tạo giá trị mặc định cho 3 tầng vòng quay nếu cấu hình trống
        if (empty($wheels)) {
            $wheels = [
                ['key' => 'standard', 'name' => 'Vòng Thường', 'name_en' => 'Standard Wheel', 'points_cost' => 10],
                ['key' => 'silver', 'name' => 'Vòng Bạc', 'name_en' => 'Silver Wheel', 'points_cost' => 20],
                ['key' => 'gold', 'name' => 'Vòng Vàng', 'name_en' => 'Gold Wheel', 'points_cost' => 50]
            ];
        }

        return view('frontend.rewards.index', compact('balance', 'catalog', 'wheels'));
    }

    /**
     * Xem chi tiết một phần thưởng cụ thể.
     *
     * @param  \App\Models\RewardCatalog  $reward
     * @return \Illuminate\View\View
     */
    public function show(RewardCatalog $reward)
    {
        return view('frontend.rewards.show', compact('reward'));
    }

    /**
     * Thực hiện đổi điểm thành phần thưởng/voucher qua AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\RewardsService  $rewardsService
     * @return \Illuminate\Http\JsonResponse
     */
    public function redeem(Request $request, RewardsService $rewardsService)
    {
        // Xác thực ID phần thưởng
        $data = $request->validate([
            'reward_id' => ['required', 'integer', 'exists:reward_catalog,reward_id'],
        ]);

        // Kiểm tra đăng nhập
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        try {
            // Thực hiện logic đổi thưởng từ service xử lý
            $result = $rewardsService->redeemVoucher(Auth::user(), (int) $data['reward_id']);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            // Trả về thông báo lỗi nếu số dư điểm không đủ hoặc lỗi tồn kho
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Thực hiện quay Vòng quay may mắn tiêu tốn điểm tích lũy qua AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\RewardsService  $rewardsService
     * @return \Illuminate\Http\JsonResponse
     */
    public function spin(Request $request, RewardsService $rewardsService)
    {
        // Yêu cầu đăng nhập trước khi quay thưởng
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        // Xác thực loại vòng quay để tránh F12 hack
        $data = $request->validate([
            'wheel_type' => ['required', 'string', 'in:standard,silver,gold'],
        ]);

        $wheelType = $data['wheel_type'];

        try {
            // Chạy thuật toán quay thưởng và trừ điểm tương ứng
            $result = $rewardsService->spinWheel(Auth::user(), $wheelType);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            // Báo lỗi nếu thiếu điểm hoặc cấu hình vòng quay lỗi
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
