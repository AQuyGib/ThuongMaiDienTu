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

        $luckyWheelsSetting = \App\Models\Setting::where('setting_key', 'lucky_wheels_config')->value('setting_value');
        $wheels = json_decode($luckyWheelsSetting ?? '[]', true);
        if (empty($wheels)) {
            $wheels = [
                ['key' => 'standard', 'name' => 'Vòng Thường', 'name_en' => 'Standard Wheel', 'points_cost' => 10],
                ['key' => 'silver', 'name' => 'Vòng Bạc', 'name_en' => 'Silver Wheel', 'points_cost' => 20],
                ['key' => 'gold', 'name' => 'Vòng Vàng', 'name_en' => 'Gold Wheel', 'points_cost' => 50]
            ];
        }

        return view('admin.rewards.index', compact('catalog', 'recentRedemptions', 'recentSpins', 'stats', 'wheels'));
    }

    public function store(Request $request)
    {
        $data = $this->validateReward($request);
        $imagePath = $this->handleImageUpload($request);
        unset($data['image']);

        RewardCatalog::create([
            ...$data,
            'image_path' => $imagePath,
            'thumbnail_path' => $imagePath,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'shipping_discount_amount' => $data['shipping_discount_amount'] ?? 0,
            'stock' => $data['stock'] ?? null,
            'max_per_user' => $data['max_per_user'] ?? 1,
            'min_rank_points' => 0,
            'requires_rank_check' => $request->boolean('requires_rank_check'),
            'is_active' => $request->boolean('is_active'),
            'metadata' => [
                'created_by' => auth()->id(),
                'slug' => Str::slug($data['name']),
                'winning_rate' => (int) $request->input('winning_rate', 10),
                'wheel_type' => $request->input('wheel_type', 'standard'),
                'min_rank' => $data['min_rank'] ?? 'none',
                'wheel_prize_type' => $request->input('wheel_prize_type', 'voucher'),
            ],
        ]);

        return back()->with('success', 'Đã tạo phần thưởng mới.');
    }

    public function update(Request $request, RewardCatalog $reward)
    {
        $data = $this->validateReward($request, $reward->reward_id);
        $imagePath = $this->handleImageUpload($request, $reward);
        unset($data['image']);

        $updateData = [
            ...$data,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'shipping_discount_amount' => $data['shipping_discount_amount'] ?? 0,
            'stock' => $data['stock'] ?? null,
            'max_per_user' => $data['max_per_user'] ?? $reward->max_per_user ?? 1,
            'min_rank_points' => 0,
            'requires_rank_check' => $request->boolean('requires_rank_check'),
            'is_active' => $request->boolean('is_active'),
            'metadata' => array_merge($reward->metadata ?? [], [
                'updated_by' => auth()->id(),
                'slug' => Str::slug($data['name']),
                'winning_rate' => (int) $request->input('winning_rate', 10),
                'wheel_type' => $request->input('wheel_type', 'standard'),
                'min_rank' => $data['min_rank'] ?? 'none',
                'wheel_prize_type' => $request->input('wheel_prize_type', 'voucher'),
            ]),
        ];

        if ($imagePath) {
            $updateData['image_path'] = $imagePath;
            $updateData['thumbnail_path'] = $imagePath;
        }

        $reward->update($updateData);

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
        $codeUnique = 'unique:reward_catalog,code';
        if ($ignoreId) {
            $codeUnique .= ',' . $ignoreId . ',reward_id';
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_\-]+$/', $codeUnique],
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'reward_type' => ['required', 'in:voucher,shipping,product,wheel_prize'],
            'reward_category' => ['required', 'in:free_ship,discount,gift,wheel'],
            'points_cost' => ['required', 'integer', 'min:0', 'max:1000000'],
            'discount_amount' => ['nullable', 'integer', 'min:0', 'max:50000000'],
            'shipping_discount_amount' => ['nullable', 'integer', 'min:0', 'max:5000000'],
            'stock' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'max_per_user' => ['nullable', 'integer', 'min:1', 'max:100'],
            'min_rank' => ['nullable', 'string', 'in:none,Dong,Bac,Vang,KimCuong'],
            'is_active' => ['nullable', 'boolean'],
            'requires_rank_check' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'winning_rate' => ['nullable', 'integer', 'min:1', 'max:100'],
            'wheel_type' => ['nullable', 'string', 'in:standard,silver,gold'],
            'wheel_prize_type' => ['nullable', 'string', 'in:voucher,shipping,product'],
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

    public function updateSetting(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:50',
            'value' => 'nullable|string'
        ]);

        \App\Models\Setting::updateOrCreate(
            ['setting_key' => $request->input('key')],
            ['setting_value' => $request->input('value')]
        );

        return response()->json(['success' => true]);
    }

    public function updateLuckyWheels(Request $request)
    {
        $request->validate([
            'wheels' => 'required|array',
            'wheels.*.key' => 'required|string|max:50',
            'wheels.*.name' => 'required|string|max:100',
            'wheels.*.name_en' => 'required|string|max:100',
            'wheels.*.points_cost' => 'required|integer|min:0',
            'wheels.*.min_rank' => 'nullable|string|in:none,Dong,Bac,Vang,KimCuong',
        ]);

        $wheels = $request->input('wheels');

        \App\Models\Setting::updateOrCreate(
            ['setting_key' => 'lucky_wheels_config'],
            ['setting_value' => json_encode($wheels)]
        );

        return response()->json(['success' => true, 'wheels' => $wheels]);
    }

    public function history(Request $request)
    {
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        // 1. Lấy toàn bộ lịch sử đổi thưởng
        $redemptions = \App\Models\RewardRedemption::with(['reward', 'user'])
            ->when($status && in_array($status, ['issued', 'approved', 'pending', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(function ($sub) use ($search) {
                $sub->where('redemption_code', 'like', '%' . $search . '%')
                    ->orWhereHas('reward', fn ($rq) => $rq->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%'));
            }))
            ->latest('redemption_id')
            ->paginate(15, ['*'], 'redemptions_page')
            ->withQueryString();

        // 2. Lấy toàn bộ lịch sử quay thưởng
        $spins = \App\Models\LuckyWheelSpin::with(['reward', 'user'])
            ->when($status && in_array($status, ['won', 'lost', 'pending', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(function ($sub) use ($search) {
                $sub->where('spin_code', 'like', '%' . $search . '%')
                    ->orWhereHas('reward', fn ($rq) => $rq->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%'));
            }))
            ->latest('spin_id')
            ->paginate(15, ['*'], 'spins_page')
            ->withQueryString();

        return view('admin.rewards.history', compact('redemptions', 'spins', 'type', 'status', 'search'));
    }
}
