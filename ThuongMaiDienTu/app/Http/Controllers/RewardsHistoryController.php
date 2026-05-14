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

        // Lấy toàn bộ lịch sử giao dịch điểm (Tích điểm & Sử dụng)
        $transactions = \App\Models\PointTransaction::where('user_id', $user->user_id)
            ->when($type, function($q) use ($type) {
                if ($type === 'earn') return $q->where('action', 'earn');
                if ($type === 'use') return $q->where('action', 'use');
            })
            ->latest('transaction_id')
            ->paginate(15)
            ->withQueryString();

        $redemptions = RewardRedemption::with('reward')
            ->where('user_id', $user->user_id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('redemption_id')
            ->paginate(10)
            ->withQueryString();

        $spins = LuckyWheelSpin::with('reward')
            ->where('user_id', $user->user_id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('spin_id')
            ->paginate(10)
            ->withQueryString();

        return view('frontend.rewards.history', compact('transactions', 'redemptions', 'spins', 'status', 'type'));
    }
}
