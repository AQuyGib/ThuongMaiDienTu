<?php

namespace App\Http\Controllers;

use App\Models\LuckyWheelSpin;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class RewardsHistoryController
 * Controller quản lý hiển thị danh sách lịch sử đổi quà và lịch sử quay số trúng thưởng của khách hàng ở Frontend.
 */
class RewardsHistoryController extends Controller
{
    /**
     * Hiển thị trang lịch sử đổi quà và quay thưởng của thành viên hiện tại.
     * Chức năng:
     * - Lọc theo từ khóa tìm kiếm (mã đổi thưởng, tên quà tặng).
     * - Lọc theo trạng thái giao dịch (đã duyệt, chờ duyệt, đã hủy, trúng thưởng, trượt...).
     * - Phân trang 10 dòng mỗi bảng kèm giữ nguyên tham số lọc trên URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user(); // Lấy thông tin thành viên đang đăng nhập
        $type = $request->string('type')->toString(); // Xác định tab đang xem ('exchange' hoặc 'wheel')
        $status = $request->string('status')->toString(); // Lọc theo trạng thái
        $search = $request->string('search')->toString(); // Từ khóa tìm kiếm thô

        // 1. Truy vấn lịch sử đổi thưởng (Redemptions) kết hợp nạp eager load thông tin quà tặng tương ứng
        $redemptions = RewardRedemption::with('reward')
            ->where('user_id', $user->user_id)
            ->when($status && in_array($status, ['issued', 'approved', 'pending', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(function ($sub) use ($search) {
                // Hỗ trợ tìm kiếm theo mã giao dịch đổi thưởng hoặc tên phần thưởng
                $sub->where('redemption_code', 'like', '%' . $search . '%')
                    ->orWhereHas('reward', fn ($rq) => $rq->where('name', 'like', '%' . $search . '%'));
            }))
            ->latest('redemption_id')
            ->paginate(10)
            ->withQueryString(); // Giữ nguyên tham số tìm kiếm khi nhấn sang trang 2, 3...

        // 2. Truy vấn lịch sử quay vòng quay may mắn (Lucky Wheel Spins)
        $spins = LuckyWheelSpin::with('reward')
            ->where('user_id', $user->user_id)
            ->when($status && in_array($status, ['won', 'lost', 'pending', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(function ($sub) use ($search) {
                // Hỗ trợ tìm kiếm theo mã lượt quay hoặc tên quà vòng quay trúng được
                $sub->where('spin_code', 'like', '%' . $search . '%')
                    ->orWhereHas('reward', fn ($rq) => $rq->where('name', 'like', '%' . $search . '%'));
            }))
            ->latest('spin_id')
            ->paginate(10)
            ->withQueryString();

        return view('frontend.rewards.history', compact('redemptions', 'spins', 'type', 'status', 'search'));
    }
}

