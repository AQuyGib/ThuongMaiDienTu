<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login_register');
        }

        $user = Auth::user();
        
        // Lấy tất cả đơn hàng
        $orders = $user->orders()->orderBy('order_id', 'desc')->get();
        
        // Thống kê thành viên
        $totalOrders = $orders->count();
        $totalSpent = $orders->where('status', 'Delivered')->sum('final_amount');
        
        // Lấy thông tin điểm từ PointsService
        $pointsService = app(\App\Services\PointsService::class);
        $pointsBalance = $pointsService->getBalance($user);
        
        $walletPoints = $pointsBalance['wallet_points'];
        $rankPoints = $pointsBalance['rank_points'];
        
        // Sử dụng member_tier của user làm hạng hiện tại để đồng bộ với Admin Panel và database
        $dbRank = $user->member_tier ?? $pointsBalance['current_rank']; // 'Dong', 'Bac', 'Vang', 'KimCuong'

        $rankNames = [
            'Dong' => 'Đồng',
            'Bac' => 'Bạc',
            'Vang' => 'Vàng',
            'KimCuong' => 'Kim Cương',
            'Bronze' => 'Đồng',
            'Silver' => 'Bạc',
            'Gold' => 'Vàng',
            'Diamond' => 'Kim Cương',
        ];
        $currentTier = $rankNames[$dbRank] ?? 'Đồng';

        // Logic tính hạng và tiến trình thăng hạng theo PointsService
        if (in_array($dbRank, ['Dong', 'Bronze'])) {
            $nextTier = 'Bạc';
            $pointsNeeded = max(0, 1000 - $rankPoints);
            $spendNeeded = $pointsNeeded * \App\Services\PointsService::EARN_RATE;
            $tierProgress = min(100, max(0, ($rankPoints / 1000) * 100));
        } elseif (in_array($dbRank, ['Bac', 'Silver'])) {
            $nextTier = 'Vàng';
            $pointsNeeded = max(0, 5000 - $rankPoints);
            $spendNeeded = $pointsNeeded * \App\Services\PointsService::EARN_RATE;
            $tierProgress = min(100, max(0, (($rankPoints - 1000) / 4000) * 100));
        } elseif (in_array($dbRank, ['Vang', 'Gold'])) {
            $nextTier = 'Kim Cương';
            $pointsNeeded = max(0, 10000 - $rankPoints);
            $spendNeeded = $pointsNeeded * \App\Services\PointsService::EARN_RATE;
            $tierProgress = min(100, max(0, (($rankPoints - 5000) / 5000) * 100));
        } else {
            $nextTier = 'Đã đạt cấp tối đa';
            $spendNeeded = 0;
            $tierProgress = 100;
        }

        $wishlist = $user->wishlists()->where('type', 'Wishlist')->with('product')->get();
        $loginHistories = \App\Models\LoginHistory::where('user_id', $user->user_id)
            ->orderBy('login_at', 'desc')
            ->limit(10)
            ->get();

        $repairTickets = \App\Models\RepairTicket::with(['technician', 'serviceInvoice'])
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->user_id);
                if ($user->phone_number) {
                    $query->orWhere('customer_phone', $user->phone_number);
                }
            })
            ->latest('ticket_id')
            ->get();

        // Lấy danh sách ưu đãi đã đổi từ catalog và vòng quay
        $redemptions = \DB::table('reward_redemptions')
            ->where('user_id', $user->user_id)
            ->orderBy('redemption_id', 'desc')
            ->get();

        $spins = \DB::table('lucky_wheel_spins')
            ->where('user_id', $user->user_id)
            ->orderBy('spin_id', 'desc')
            ->get();

        $pointTransactions = \DB::table('point_transactions')
            ->where('user_id', $user->user_id)
            ->orderBy('point_transaction_id', 'desc')
            ->limit(30)
            ->get();

        return view('frontend.profile', compact('user', 'orders', 'totalOrders', 'totalSpent', 'currentTier', 'nextTier', 'spendNeeded', 'tierProgress', 'wishlist', 'loginHistories', 'repairTickets', 'walletPoints', 'rankPoints', 'redemptions', 'spins', 'pointTransactions'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login_register');
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $user->full_name = $request->full_name;
        $user->phone_number = $request->phone_number;
        $user->gender = $request->gender;
        $user->dob = $request->dob;
        $user->address = $request->address;
        $user->save();

        return redirect()->route('profile.index', ['tab' => 'info-tab'])->with('success', 'Đã cập nhật thông tin thành công!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $user = Auth::user();

        if (!\Hash::check($request->current_password, $user->password_hash)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.'])->withInput(['tab' => 'info-tab']);
        }

        $user->password_hash = \Hash::make($request->new_password);
        $user->password_changed_at = now();
        $user->save();

        return redirect()->route('profile.index', ['tab' => 'info-tab'])->with('password_success', 'Đã cập nhật mật khẩu thành công!');
    }

    public function addAddress(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        $request->validate([
            'city' => 'required|string',
            'district' => 'required|string',
            'ward' => 'required|string',
            'street' => 'required|string',
            'name' => 'nullable|string',
            'type' => 'required|string|in:Nhà,Văn phòng',
            'is_default' => 'boolean'
        ]);

        $user = Auth::user();
        
        $isDefault = $request->is_default ?? false;
        
        // Nếu là địa chỉ mặc định, reset các địa chỉ khác
        if ($isDefault) {
            \App\Models\UserAddress::where('user_id', $user->user_id)->update(['is_default' => false]);
            // Cập nhật luôn chuỗi address cho user
            $user->address = "{$request->street}, {$request->ward}, {$request->district}, {$request->city}";
            $user->save();
        } elseif (\App\Models\UserAddress::where('user_id', $user->user_id)->count() === 0) {
            // Nếu chưa có địa chỉ nào, bắt buộc là mặc định
            $isDefault = true;
            $user->address = "{$request->street}, {$request->ward}, {$request->district}, {$request->city}";
            $user->save();
        }

        \App\Models\UserAddress::create([
            'user_id' => $user->user_id,
            'city' => $request->city,
            'district' => $request->district,
            'ward' => $request->ward,
            'street' => $request->street,
            'name' => $request->name,
            'type' => $request->type,
            'is_default' => $isDefault
        ]);

        return response()->json(['success' => true]);
    }

    public function updateAddress(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        $address = \App\Models\UserAddress::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'city' => 'required|string',
            'district' => 'required|string',
            'ward' => 'required|string',
            'street' => 'required|string',
            'name' => 'nullable|string',
            'type' => 'required|string|in:Nhà,Văn phòng',
            'is_default' => 'boolean'
        ]);

        $isDefault = $request->is_default ?? false;
        $user = Auth::user();

        if ($isDefault) {
            \App\Models\UserAddress::where('user_id', $user->user_id)->update(['is_default' => false]);
            $user->address = "{$request->street}, {$request->ward}, {$request->district}, {$request->city}";
            $user->save();
        } elseif ($address->is_default) {
            // Đang là mặc định mà bỏ tích thì sao? Có thể bỏ qua hoặc yêu cầu phải có 1 cái mặc định
            // Tạm thời vẫn giữ nó là mặc định nếu chưa có cái nào khác
            $isDefault = true;
        }

        $address->update([
            'city' => $request->city,
            'district' => $request->district,
            'ward' => $request->ward,
            'street' => $request->street,
            'name' => $request->name,
            'type' => $request->type,
            'is_default' => $isDefault
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteAddress($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        $address = \App\Models\UserAddress::where('user_id', Auth::id())->findOrFail($id);
        
        $wasDefault = $address->is_default;
        $address->delete();

        // Nếu xóa địa chỉ mặc định, set một địa chỉ khác làm mặc định (nếu có)
        if ($wasDefault) {
            $newDefault = \App\Models\UserAddress::where('user_id', Auth::id())->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
                $user = Auth::user();
                $user->address = "{$newDefault->street}, {$newDefault->ward}, {$newDefault->district}, {$newDefault->city}";
                $user->save();
            } else {
                $user = Auth::user();
                $user->address = null;
                $user->save();
            }
        }

        return response()->json(['success' => true]);
    }

    public function removeFromWishlist($id)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            $user = Auth::user();
            // Sử dụng relationship để đảm bảo tính nhất quán và bảo mật (chỉ xóa của mình)
            $wishlistItem = $user->wishlists()->where('id', $id)->first();

            if ($wishlistItem) {
                $wishlistItem->delete();
                return response()->json(['success' => true]);
            }

            return response()->json(['success' => false, 'error' => 'Không tìm thấy sản phẩm trong danh sách yêu thích.'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi khi xóa sản phẩm yêu thích: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function clearWishlist()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            $user = Auth::user();
            // Xóa tất cả các mục thuộc loại Wishlist của user
            $user->wishlists()->whereIn('type', ['Wishlist'])->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Lỗi khi xóa toàn bộ danh sách yêu thích: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function toggleWishlist(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            $request->validate([
                'product_id' => 'required'
            ]);

            $user = Auth::user();
            $productId = $request->product_id;

            $wishlist = $user->wishlists()
                ->where('product_id', $productId)
                ->where('type', 'Wishlist')
                ->first();

            if ($wishlist) {
                $wishlist->delete();
                return response()->json(['status' => 'removed']);
            } else {
                $user->wishlists()->create([
                    'product_id' => $productId,
                    'type' => 'Wishlist'
                ]);
                return response()->json(['status' => 'added']);
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi khi toggle yêu thích: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function clearAllWishlist()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        \App\Models\WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('type', 'Wishlist')
            ->delete();

        return response()->json(['success' => true, 'count' => 0]);
    }

    /**
     * Lưu thông tin đăng ký lịch hẹn sửa chữa trực tuyến từ khách hàng.
     * Logic này thực hiện các bước:
     * 1. Xác thực tài khoản khách hàng đã đăng nhập.
     * 2. Validate dữ liệu đầu vào: thông tin khách hàng, số IMEI và mô tả lỗi thiết bị.
     * 3. Tạo mới phiếu sửa chữa (RepairTicket) với trạng thái mặc định 'Received'.
     * 4. Chuyển hướng người dùng về tab quản lý sửa chữa kèm thông báo thành công.
     */
    public function storeRepairTicket(Request $request)
    {
        // Bước 1: Kiểm tra người dùng đã đăng nhập chưa
        if (!Auth::check()) {
            return redirect()->route('login_register');
        }

        $user = Auth::user();

        // Bước 2: Thiết lập bộ quy tắc xác thực dữ liệu đầu vào
        $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'imei_serial' => ['required', 'string', 'max:100', 'unique:repair_tickets,imei_serial'], // Đảm bảo IMEI duy nhất
            'issue_desc' => ['required', 'string'],
            'schedule_date' => ['required', 'date', 'after_or_equal:today'], // Ngày hẹn mang tới phải >= ngày hiện tại
        ], [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'customer_phone.regex' => 'Số điện thoại liên hệ phải có đúng 10 chữ số.',
            'customer_email.email' => 'Địa chỉ email không hợp lệ.',
            'imei_serial.required' => 'Vui lòng nhập mã IMEI / Serial.',
            'imei_serial.unique' => 'Mã IMEI / Serial này đã tồn tại trong hệ thống.',
            'issue_desc.required' => 'Vui lòng nhập mô tả lỗi.',
            'schedule_date.required' => 'Vui lòng chọn ngày hẹn mang máy tới.',
            'schedule_date.after_or_equal' => 'Ngày hẹn mang máy tới phải từ hôm nay trở đi.',
        ]);

        // Bước 3: Tìm kỹ thuật viên phụ trách mặc định của cửa hàng (Quản trị viên hoặc quản lý hoặc kỹ thuật viên đầu tiên)
        $defaultTech = \App\Models\User::whereIn('role_id', [1, 2, 4])->first();
        $defaultTechId = $defaultTech ? $defaultTech->user_id : null;

        // Bước 4: Lưu bản ghi phiếu sửa chữa vào cơ sở dữ liệu
        \App\Models\RepairTicket::create([
            'user_id' => $user->user_id, // Liên kết phiếu với ID của tài khoản khách hàng
            'technician_id' => $defaultTechId, // Gán kỹ thuật viên phụ trách mặc định để đảm bảo luôn có kỹ thuật viên phụ trách
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'customer_address' => $request->customer_address,
            'customer_source' => 'Website', // Nguồn đăng ký từ giao diện Frontend Website
            'imei_serial' => $request->imei_serial,
            'issue_desc' => $request->issue_desc,
            'schedule_date' => $request->schedule_date,
            'status' => 'Received', // Gán trạng thái khởi tạo: Đã tiếp nhận (Received)
            'estimated_cost' => 0,  // Chi phí dự kiến mặc định là 0 (kỹ thuật viên sẽ cập nhật khi kiểm tra trực tiếp)
        ]);

        // Bước 4: Điều hướng phản hồi về trang cá nhân của khách hàng, kích hoạt lại tab repair-tab
        return redirect()->route('profile.index', ['tab' => 'repair-tab'])->with('repair_success', 'Đã đăng ký lịch hẹn sửa chữa trực tuyến thành công!');
    }
}
