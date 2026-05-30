<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LuckyWheelSpin;
use App\Models\RewardCatalog;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class RewardsController
 * Controller quản trị hệ thống phần thưởng đổi điểm (Points Exchange) và vòng quay may mắn (Lucky Wheel).
 */
class RewardsController extends Controller
{
    /**
     * Hiển thị trang quản trị phần thưởng (danh sách catalog, thống kê và cấu hình vòng quay).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Lấy danh sách toàn bộ các phần thưởng trong catalog, sắp xếp theo ID mới nhất
        $catalog = RewardCatalog::query()->latest('reward_id')->get();
        
        // Lấy 10 lịch sử đổi quà gần nhất, kèm theo thông tin của phần thưởng
        $recentRedemptions = RewardRedemption::with('reward')->latest('redemption_id')->limit(10)->get();
        
        // Lấy 10 lịch sử lượt quay vòng quay gần nhất, kèm theo thông tin của phần thưởng
        $recentSpins = LuckyWheelSpin::with('reward')->latest('spin_id')->limit(10)->get();

        // Thống kê tổng số lượt đổi thưởng, số lượt quay và tổng số điểm người dùng đã tiêu thụ
        $stats = [
            'redemptions' => RewardRedemption::count(),
            'spins' => LuckyWheelSpin::count(),
            'points_spent' => RewardRedemption::sum('points_spent') + LuckyWheelSpin::sum('points_spent'),
        ];

        // Lấy cấu hình các loại vòng quay từ bảng settings
        $luckyWheelsSetting = \App\Models\Setting::where('setting_key', 'lucky_wheels_config')->value('setting_value');
        $wheels = json_decode($luckyWheelsSetting ?? '[]', true);
        
        // Nếu chưa có cấu hình cài đặt, khởi tạo mặc định gồm 3 loại: Vòng Thường, Vòng Bạc, Vòng Vàng
        if (empty($wheels)) {
            $wheels = [
                ['key' => 'standard', 'name' => 'Vòng Thường', 'name_en' => 'Standard Wheel', 'points_cost' => 10],
                ['key' => 'silver', 'name' => 'Vòng Bạc', 'name_en' => 'Silver Wheel', 'points_cost' => 20],
                ['key' => 'gold', 'name' => 'Vòng Vàng', 'name_en' => 'Gold Wheel', 'points_cost' => 50]
            ];
        }

        return view('admin.rewards.index', compact('catalog', 'recentRedemptions', 'recentSpins', 'stats', 'wheels'));
    }

    /**
     * Tạo mới một phần thưởng (hỗ trợ cả phần thưởng đổi điểm và quà của vòng quay).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Kiểm tra hợp lệ dữ liệu nhập vào
        $data = $this->validateReward($request);
        
        // Xử lý upload ảnh đại diện của phần thưởng (nếu có)
        $imagePath = $this->handleImageUpload($request);
        unset($data['image']); // Xóa key image thừa để chèn mảng thẳng vào database

        // Tạo phần thưởng mới trong bảng reward_catalog
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
            // Lưu trữ thông tin metadata phụ thuộc vào loại phần thưởng
            'metadata' => [
                'created_by' => auth()->id(),
                'slug' => Str::slug($data['name']),
                'winning_rate' => (int) $request->input('winning_rate', 10), // Tỉ lệ trúng của quà vòng quay
                'wheel_type' => $request->input('wheel_type', 'standard'), // Tầng vòng quay áp dụng
                'min_rank' => $data['min_rank'] ?? 'none', // Yêu cầu hạng thành viên tối thiểu để đổi quà
                'wheel_prize_type' => $request->input('wheel_prize_type', 'voucher'), // Loại quà vòng quay (voucher, shipping, product)
            ],
        ]);

        return back()->with('success', 'Đã tạo phần thưởng mới.');
    }

    /**
     * Cập nhật thông tin một phần thưởng hiện tại.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RewardCatalog  $reward
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, RewardCatalog $reward)
    {
        // Xác thực dữ liệu đầu vào, bỏ qua kiểm tra trùng code của chính phần thưởng đang sửa
        $data = $this->validateReward($request, $reward->reward_id);
        
        // Xử lý upload ảnh mới (nếu có) và xóa file ảnh cũ để giải phóng bộ nhớ
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
            // Hợp nhất (merge) metadata cũ với các cập nhật cấu hình mới
            'metadata' => array_merge($reward->metadata ?? [], [
                'updated_by' => auth()->id(),
                'slug' => Str::slug($data['name']),
                'winning_rate' => (int) $request->input('winning_rate', 10),
                'wheel_type' => $request->input('wheel_type', 'standard'),
                'min_rank' => $data['min_rank'] ?? 'none',
                'wheel_prize_type' => $request->input('wheel_prize_type', 'voucher'),
            ]),
        ];

        // Nếu có upload ảnh mới thì cập nhật đường dẫn ảnh
        if ($imagePath) {
            $updateData['image_path'] = $imagePath;
            $updateData['thumbnail_path'] = $imagePath;
        }

        $reward->update($updateData);

        return back()->with('success', 'Đã cập nhật phần thưởng.');
    }

    /**
     * Xóa một phần thưởng ra khỏi danh sách catalog.
     *
     * @param  \App\Models\RewardCatalog  $reward
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(RewardCatalog $reward)
    {
        // Kiểm tra xem phần thưởng đã từng được đổi hoặc trúng giải trong lịch sử chưa
        $hasUsage = $reward->redemptions()->exists() || $reward->wheelSpins()->exists();
        if ($hasUsage) {
            return back()->with('error', 'Không thể xóa phần thưởng đã có lịch sử sử dụng.');
        }

        // Xóa file ảnh vật lý trên ổ đĩa nếu tồn tại
        if ($reward->image_path) {
            Storage::disk('public')->delete($reward->image_path);
        }

        $reward->delete();
        return back()->with('success', 'Đã xóa phần thưởng.');
    }

    /**
     * Xác thực (validate) dữ liệu nhập vào cho form phần thưởng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null  $ignoreId
     * @return array
     */
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

    /**
     * Xử lý tải ảnh đại diện lên thư mục lưu trữ công khai của Laravel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RewardCatalog|null  $reward
     * @return string|null
     */
    protected function handleImageUpload(Request $request, ?RewardCatalog $reward = null): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        // Lưu trữ file ảnh vào thư mục public/rewards
        $path = $request->file('image')->store('rewards', 'public');

        // Nếu là thao tác sửa và có ảnh cũ, thực hiện xóa file ảnh cũ
        if ($reward && $reward->image_path) {
            Storage::disk('public')->delete($reward->image_path);
        }

        return $path;
    }

    /**
     * Cập nhật các cấu hình hệ thống cài đặt chung (ví dụ: bật/tắt hiển thị vòng quay).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Cập nhật thông tin cấu hình giá điểm/xếp hạng của 3 tầng Vòng quay may mắn (Thường, Bạc, Vàng).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Truy vấn, lọc tìm kiếm và phân trang lịch sử đổi quà & lịch sử quay số trên toàn hệ thống cho Admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function history(Request $request)
    {
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        // 1. Lấy toàn bộ lịch sử đổi thưởng dựa theo bộ lọc status và tên/email khách hàng hoặc mã quà
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

        // 2. Lấy toàn bộ lịch sử quay thưởng dựa theo bộ lọc status và tìm kiếm thông tin khách hàng
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
