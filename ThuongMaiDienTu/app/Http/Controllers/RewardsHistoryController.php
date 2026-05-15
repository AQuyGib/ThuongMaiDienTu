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
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        $redemptions = RewardRedemption::with('reward')
            ->where('user_id', $user->user_id)
            ->when($status && in_array($status, ['issued', 'approved', 'pending', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(function ($sub) use ($search) {
                $sub->where('redemption_code', 'like', '%' . $search . '%')
                    ->orWhereHas('reward', fn ($rq) => $rq->where('name', 'like', '%' . $search . '%'));
            }))
            ->latest('redemption_id')
            ->paginate(10)
            ->withQueryString();

        $spins = LuckyWheelSpin::with('reward')
            ->where('user_id', $user->user_id)
            ->when($status && in_array($status, ['won', 'lost', 'pending', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(function ($sub) use ($search) {
                $sub->where('spin_code', 'like', '%' . $search . '%')
                    ->orWhereHas('reward', fn ($rq) => $rq->where('name', 'like', '%' . $search . '%'));
            }))
            ->latest('spin_id')
            ->paginate(10)
            ->withQueryString();

        return view('frontend.rewards.history', compact('redemptions', 'spins', 'type', 'status', 'search'));
    }
}
