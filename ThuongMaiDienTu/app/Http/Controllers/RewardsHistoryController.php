<?php

namespace App\Http\Controllers;

use App\Models\LuckyWheelSpin;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardsHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();

        $redemptions = RewardRedemption::with('reward')
            ->where('user_id', $user->user_id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($type === 'redeem', fn ($q) => $q)
            ->latest('redemption_id')
            ->paginate(10)
            ->withQueryString();

        $spins = LuckyWheelSpin::with('reward')
            ->where('user_id', $user->user_id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($type === 'spin', fn ($q) => $q)
            ->latest('spin_id')
            ->paginate(10)
            ->withQueryString();

        return view('frontend.rewards.history', compact('redemptions', 'spins', 'status', 'type'));
    }
}
