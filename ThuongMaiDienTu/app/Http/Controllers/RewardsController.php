<?php

namespace App\Http\Controllers;

use App\Models\RewardCatalog;
use App\Services\RewardsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardsController extends Controller
{
    public function index(RewardsService $rewardsService)
    {
        $user = Auth::user();
        $balance = $user ? $rewardsService->getWalletBalance($user) : 0;
        $catalog = $rewardsService->getCatalog(['active_only' => true]);

        $luckyWheelsSetting = \App\Models\Setting::where('setting_key', 'lucky_wheels_config')->value('setting_value');
        $wheels = json_decode($luckyWheelsSetting ?? '[]', true);
        if (empty($wheels)) {
            $wheels = [
                ['key' => 'standard', 'name' => 'Vòng Thường', 'name_en' => 'Standard Wheel', 'points_cost' => 10],
                ['key' => 'silver', 'name' => 'Vòng Bạc', 'name_en' => 'Silver Wheel', 'points_cost' => 20],
                ['key' => 'gold', 'name' => 'Vòng Vàng', 'name_en' => 'Gold Wheel', 'points_cost' => 50]
            ];
        }

        return view('frontend.rewards.index', compact('balance', 'catalog', 'wheels'));
    }

    public function show(RewardCatalog $reward)
    {
        return view('frontend.rewards.show', compact('reward'));
    }

    public function redeem(Request $request, RewardsService $rewardsService)
    {
        $data = $request->validate([
            'reward_id' => ['required', 'integer', 'exists:reward_catalog,reward_id'],
        ]);

        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        try {
            $result = $rewardsService->redeemVoucher(Auth::user(), (int) $data['reward_id']);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function spin(Request $request, RewardsService $rewardsService)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        $wheelType = $request->input('wheel_type', 'standard');

        try {
            $result = $rewardsService->spinWheel(Auth::user(), $wheelType);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
