<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LuckyWheelSpin;
use App\Models\RewardCatalog;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RewardsController extends Controller
{
    public function index()
    {
        $catalog = RewardCatalog::query()->latest('reward_id')->get();
        $recentRedemptions = RewardRedemption::with('reward')->latest('redemption_id')->limit(10)->get();
        $recentSpins = LuckyWheelSpin::with('reward')->latest('spin_id')->limit(10)->get();

        $stats = [
            'redemptions' => RewardRedemption::count(),
            'spins' => LuckyWheelSpin::count(),
            'points_spent' => RewardRedemption::sum('points_spent') + LuckyWheelSpin::sum('points_spent'),
        ];

        return view('admin.rewards.index', compact('catalog', 'recentRedemptions', 'recentSpins', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validateReward($request);
        $imagePath = $this->handleImageUpload($request);

        RewardCatalog::create([
            ...$data,
            'image_path' => $imagePath,
            'thumbnail_path' => $imagePath,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'shipping_discount_amount' => $data['shipping_discount_amount'] ?? 0,
            'stock' => $data['stock'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'metadata' => [
                'created_by' => auth()->id(),
                'slug' => Str::slug($data['name']),
            ],
        ]);

        return back()->with('success', 'Đã tạo phần thưởng mới.');
    }

    public function update(Request $request, RewardCatalog $reward)
    {
        $data = $this->validateReward($request, $reward->reward_id);
        $imagePath = $this->handleImageUpload($request, $reward);

        $reward->update([
            ...$data,
            'image_path' => $imagePath ?? $reward->image_path,
            'thumbnail_path' => $imagePath ?? $reward->thumbnail_path,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'shipping_discount_amount' => $data['shipping_discount_amount'] ?? 0,
            'stock' => $data['stock'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'metadata' => array_merge($reward->metadata ?? [], [
                'updated_by' => auth()->id(),
                'slug' => Str::slug($data['name']),
            ]),
        ]);

        return back()->with('success', 'Đã cập nhật phần thưởng.');
    }

    public function destroy(RewardCatalog $reward)
    {
        $hasUsage = $reward->redemptions()->exists() || $reward->wheelSpins()->exists();
        if ($hasUsage) {
            return back()->with('error', 'Không thể xóa phần thưởng đã có lịch sử sử dụng.');
        }

        if ($reward->image_path) {
            Storage::disk('public')->delete($reward->image_path);
        }

        $reward->delete();
        return back()->with('success', 'Đã xóa phần thưởng.');
    }

    protected function validateReward(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:reward_catalog,code' . ($ignoreId ? ',' . $ignoreId . ',reward_id' : '')],
            'name' => ['required', 'string', 'max:150'],
            'reward_type' => ['required', 'in:voucher,shipping,product,wheel_prize'],
            'reward_category' => ['required', 'in:free_ship,discount,gift,wheel'],
            'points_cost' => ['required', 'integer', 'min:0'],
            'discount_amount' => ['nullable', 'integer', 'min:0'],
            'shipping_discount_amount' => ['nullable', 'integer', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);
    }

    protected function handleImageUpload(Request $request, ?RewardCatalog $reward = null): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('rewards', 'public');

        if ($reward && $reward->image_path) {
            Storage::disk('public')->delete($reward->image_path);
        }

        return $path;
    }
}
