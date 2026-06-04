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
        $orders = $user->orders()->with('details.inventoryItem.variant.product')->orderBy('order_id', 'desc')->get();
        
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
            'full_name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[^0-9!@#$%^&*()_+=\[\]{}|\\:;"\'<>,.?\/~`]+$/u'],
            'phone_number' => ['nullable', 'string', 'regex:/^0[0-9]{8,9}$/'],
            'gender' => ['nullable', 'string', 'max:20'],
            'dob' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'min:10', 'max:150', 'regex:/^[^!@#$%^&*()_+=\[\]{}|\\:;"\'<>?~`]+$/u'],
        ], [
            'full_name.required' => 'Họ và tên là bắt buộc.',
            'full_name.min' => 'Họ và tên phải từ 2 ký tự trở lên.',
            'full_name.max' => 'Họ và tên tối đa 50 ký tự.',
            'full_name.regex' => 'Họ và tên không được chứa số hoặc ký tự đặc biệt.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ (phải từ 9-10 số và bắt đầu bằng số 0).',
            'address.min' => 'Địa chỉ phải từ 10 ký tự trở lên.',
            'address.max' => 'Địa chỉ tối đa 150 ký tự.',
            'address.regex' => 'Địa chỉ không được chứa ký tự đặc biệt lạ.',
        ]);

        $user = Auth::user();

        // Chuẩn hóa dữ liệu đầu vào và dữ liệu cũ để so sánh chính xác
        $inputFullName = trim($request->full_name);
        $inputPhoneNumber = $request->phone_number ? trim($request->phone_number) : null;
        $inputGender = $request->gender ?: null;
        $inputDob = $request->dob ?: null;
        $inputAddress = $request->address ? trim($request->address) : null;

        $userFullName = trim($user->full_name);
        $userPhoneNumber = $user->phone_number ? trim($user->phone_number) : null;
        $userGender = $user->gender ?: null;
        $userDob = $user->dob ?: null;
        $userAddress = $user->address ? trim($user->address) : null;

        if ($inputFullName === $userFullName &&
            $inputPhoneNumber === $userPhoneNumber &&
            $inputGender === $userGender &&
            $inputDob === $userDob &&
            $inputAddress === $userAddress) {
            return back()->withErrors(['no_change' => 'Không có thông tin nào thay đổi so với dữ liệu cũ.'])->withInput();
        }

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

    /**
     * Thêm mới địa chỉ cho người dùng hiện tại
     * 
     * Hàm này nhận dữ liệu từ AJAX request để tạo mới một địa chỉ giao hàng.
     * Quy tắc validation sử dụng dạng mảng (array) thay vì chuỗi dùng dấu pipe "|"
     * để tránh xung đột với các ký tự Regex chứa pipe.
     * 
     * @param Request $request Chứa các thông tin: city, district, ward, street, name (tên gợi nhớ), type, is_default
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAddress(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        // Xác thực dữ liệu đầu vào
        $request->validate([
            'city' => ['required', 'string'],
            'district' => ['required', 'string'],
            'ward' => ['required', 'string'],
            // Loại bỏ quy tắc min:10 để cho phép nhập địa chỉ ngắn. Loại trừ các ký tự đặc biệt lạ bằng Regex
            'street' => ['required', 'string', 'max:150', 'regex:/^[^!@#$%^&*()_+=\[\]{}|\\:;"\'<>?~`]+$/u'],
            // Tên gợi nhớ không bắt buộc, nếu nhập thì giới hạn 50 kí tự và không chứa số hay ký tự đặc biệt
            'name' => ['nullable', 'string', 'max:50', 'regex:/^[^0-9!@#$%^&*()_+=\[\]{}|\\:;"\'<>,.?\/~`]+$/u'],
            'type' => ['required', 'string', 'in:Nhà,Văn phòng'],
            'is_default' => ['boolean']
        ], [
            'street.required' => 'Địa chỉ số nhà, tên đường là bắt buộc.',
            'street.max' => 'Địa chỉ tối đa 150 ký tự.',
            'street.regex' => 'Địa chỉ không được chứa ký tự đặc biệt lạ.',
            'name.max' => 'Tên gợi nhớ tối đa 50 ký tự.',
            'name.regex' => 'Tên gợi nhớ không được chứa số hoặc ký tự đặc biệt.',
        ]);

        $user = Auth::user();
        
        $isDefault = $request->is_default ?? false;
        
        // Nếu là địa chỉ mặc định, reset thuộc tính is_default của các địa chỉ cũ về false
        if ($isDefault) {
            \App\Models\UserAddress::where('user_id', $user->user_id)->update(['is_default' => false]);
            // Đồng bộ luôn chuỗi địa chỉ mặc định vào bảng users để hiển thị nhanh
            $user->address = "{$request->street}, {$request->ward}, {$request->district}, {$request->city}";
            $user->save();
        } elseif (\App\Models\UserAddress::where('user_id', $user->user_id)->count() === 0) {
            // Nếu đây là địa chỉ đầu tiên của tài khoản, bắt buộc phải đặt làm địa chỉ mặc định
            $isDefault = true;
            $user->address = "{$request->street}, {$request->ward}, {$request->district}, {$request->city}";
            $user->save();
        }

        // Tạo bản ghi địa chỉ mới trong DB
        \App\Models\UserAddress::create([
            'user_id' => $user->user_id,
            'city' => $request->city,
            'district' => $request->district,
            'ward' => $request->ward,
            'street' => $request->street,
            // Cắt khoảng trắng thừa ở hai đầu. Nếu bỏ trống thì lưu null để giao diện tự fallback sang Họ và tên tài khoản
            'name' => $request->name ? trim($request->name) : null,
            'type' => $request->type,
            'is_default' => $isDefault
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Cập nhật địa chỉ hiện có của người dùng
     * 
     * Hàm này tìm bản ghi địa chỉ theo ID và user_id của người dùng hiện tại,
     * xác thực dữ liệu và thực hiện cập nhật.
     * 
     * @param Request $request Chứa thông tin chỉnh sửa
     * @param int $id ID của bản ghi địa chỉ cần sửa
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAddress(Request $request, $id)
    {
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        // Tìm địa chỉ thuộc về user, nếu không thấy sẽ ném ra lỗi 404
        $address = \App\Models\UserAddress::where('user_id', Auth::id())->findOrFail($id);

        // Xác thực dữ liệu tương tự lúc thêm mới
        $request->validate([
            'city' => ['required', 'string'],
            'district' => ['required', 'string'],
            'ward' => ['required', 'string'],
            'street' => ['required', 'string', 'max:150', 'regex:/^[^!@#$%^&*()_+=\[\]{}|\\:;"\'<>?~`]+$/u'],
            'name' => ['nullable', 'string', 'max:50', 'regex:/^[^0-9!@#$%^&*()_+=\[\]{}|\\:;"\'<>,.?\/~`]+$/u'],
            'type' => ['required', 'string', 'in:Nhà,Văn phòng'],
            'is_default' => ['boolean']
        ], [
            'street.required' => 'Địa chỉ số nhà, tên đường là bắt buộc.',
            'street.max' => 'Địa chỉ tối đa 150 ký tự.',
            'street.regex' => 'Địa chỉ không được chứa ký tự đặc biệt lạ.',
            'name.max' => 'Tên gợi nhớ tối đa 50 ký tự.',
            'name.regex' => 'Tên gợi nhớ không được chứa số hoặc ký tự đặc biệt.',
        ]);

        $isDefault = $request->is_default ?? false;
        $user = Auth::user();

        // Xử lý logic đặt làm địa chỉ mặc định
        if ($isDefault) {
            // Tắt cờ mặc định của các địa chỉ cũ
            \App\Models\UserAddress::where('user_id', $user->user_id)->update(['is_default' => false]);
            // Đồng bộ chuỗi địa chỉ mặc định vào hồ sơ tài khoản
            $user->address = "{$request->street}, {$request->ward}, {$request->district}, {$request->city}";
            $user->save();
        } elseif ($address->is_default) {
            // Nếu địa chỉ này đang là mặc định nhưng người dùng cố bỏ chọn,
            // ta vẫn giữ nó là mặc định để tránh việc tài khoản không có địa chỉ mặc định nào.
            $isDefault = true;
        }

        // Thực hiện cập nhật bản ghi địa chỉ trong DB
        $address->update([
            'city' => $request->city,
            'district' => $request->district,
            'ward' => $request->ward,
            'street' => $request->street,
            // Cắt khoảng trắng thừa ở hai đầu. Nếu bỏ trống thì lưu null để giao diện tự fallback sang Họ và tên tài khoản
            'name' => $request->name ? trim($request->name) : null,
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
     * AJAX endpoint thực hiện chẩn đoán AI bằng Gemini.
     */
    public function aiDiagnoseRepairTicket(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        $request->validate([
            'issue_desc' => ['required', 'string', 'min:10', 'max:500'],
            'device_image' => ['nullable', 'image', 'max:5120'], // Max 5MB
        ], [
            'issue_desc.required' => 'Vui lòng nhập mô tả tình trạng lỗi.',
            'issue_desc.min' => 'Mô tả lỗi phải từ 10 ký tự trở lên.',
            'issue_desc.max' => 'Mô tả lỗi tối đa 500 ký tự.',
            'device_image.image' => 'File tải lên phải là hình ảnh.',
            'device_image.max' => 'Dung lượng ảnh tối đa 5MB.',
        ]);

        $tempImagePath = null;
        if ($request->hasFile('device_image')) {
            // Lưu ảnh tạm để gửi cho AI chẩn đoán
            $file = $request->file('device_image');
            $tempImagePath = $file->getRealPath();
        }

        $aiService = app(\App\Services\RepairAIService::class);
        $result = $aiService->diagnoseFault($request->issue_desc, $tempImagePath);

        // Lấy thông tin kỹ thuật viên được chỉ định để trả về tên hiển thị
        $techName = 'Đang phân công';
        if (!empty($result['assigned_technician_id'])) {
            $tech = \App\Models\User::find($result['assigned_technician_id']);
            if ($tech) {
                $techName = $tech->full_name;
            }
        }
        $result['technician_name'] = $techName;

        return response()->json($result);
    }

    /**
     * Lưu thông tin đăng ký lịch hẹn sửa chữa trực tuyến từ khách hàng.
     * Logic này thực hiện các bước:
     * 1. Xác thực tài khoản khách hàng đã đăng nhập.
     * 2. Validate dữ liệu đầu vào: thông tin khách hàng, số IMEI, mô tả lỗi thiết bị và hình ảnh.
     * 3. Tạo mới phiếu sửa chữa (RepairTicket) kèm thông tin chẩn đoán AI và kỹ thuật viên phụ trách thông minh.
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
            'customer_name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[^0-9!@#$%^&*()_+=\[\]{}|\\:;"\'<>,.?\/~`]+$/u'],
            'customer_phone' => ['required', 'string', 'regex:/^0[0-9]{8,9}$/'],
            'customer_email' => ['nullable', 'email', 'max:100'],
            'customer_address' => ['nullable', 'string', 'min:10', 'max:150', 'regex:/^[^!@#$%^&*()_+=\[\]{}|\\:;"\'<>?~`]+$/u'],
            'imei_serial' => ['required', 'string', 'min:5', 'max:50', 'unique:repair_tickets,imei_serial'], // Đảm bảo IMEI duy nhất
            'issue_desc' => ['required', 'string', 'min:10', 'max:500'],
            'schedule_date' => ['required', 'date', 'after_or_equal:today'], // Ngày hẹn mang tới phải >= ngày hiện tại
            'device_image' => ['nullable', 'image', 'max:5120'], // Max 5MB
        ], [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'customer_name.min' => 'Họ và tên khách hàng phải từ 2 ký tự trở lên.',
            'customer_name.max' => 'Họ và tên khách hàng tối đa 50 ký tự.',
            'customer_name.regex' => 'Họ và tên không chứa số hoặc ký tự đặc biệt.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'customer_phone.regex' => 'Số điện thoại phải gồm 9-10 số và bắt đầu bằng số 0.',
            'customer_email.email' => 'Địa chỉ email không hợp lệ.',
            'customer_email.max' => 'Email tối đa 100 ký tự.',
            'customer_address.min' => 'Địa chỉ liên hệ phải từ 10 ký tự trở lên.',
            'customer_address.max' => 'Địa chỉ liên hệ tối đa 150 ký tự.',
            'customer_address.regex' => 'Địa chỉ không chứa ký tự đặc biệt lạ.',
            'imei_serial.required' => 'Vui lòng nhập mã IMEI / Serial.',
            'imei_serial.min' => 'Mã IMEI / Serial phải từ 5 ký tự trở lên.',
            'imei_serial.max' => 'Mã IMEI / Serial tối đa 50 ký tự.',
            'imei_serial.unique' => 'Mã IMEI / Serial này đã tồn tại trong hệ thống.',
            'issue_desc.required' => 'Vui lòng nhập mô tả lỗi.',
            'issue_desc.min' => 'Mô tả lỗi phải từ 10 ký tự trở lên.',
            'issue_desc.max' => 'Mô tả lỗi tối đa 500 ký tự.',
            'schedule_date.required' => 'Vui lòng chọn ngày hẹn mang máy tới.',
            'schedule_date.after_or_equal' => 'Ngày hẹn mang máy tới phải từ hôm nay trở đi.',
            'device_image.image' => 'File tải lên phải là hình ảnh.',
            'device_image.max' => 'Dung lượng ảnh tối đa 5MB.',
        ]);

        // Xử lý upload ảnh thiết bị lỗi
        $imagePath = null;
        if ($request->hasFile('device_image')) {
            try {
                $file = $request->file('device_image');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/repairs'), $fileName);
                $imagePath = 'uploads/repairs/' . $fileName;
            } catch (\Throwable $e) {
                \Log::error('Lỗi upload ảnh thiết bị sửa chữa: ' . $e->getMessage());
            }
        }

        // Xử lý dữ liệu chẩn đoán AI
        $aiDiagnosed = $request->boolean('ai_diagnosed', false);
        $aiData = [];

        if ($aiDiagnosed) {
            $aiData = [
                'ai_diagnosed' => true,
                'ai_fault_type' => $request->ai_fault_type,
                'ai_probable_causes' => json_decode($request->ai_probable_causes, true) ?: [],
                'ai_risk_warnings' => json_decode($request->ai_risk_warnings, true) ?: [],
                'ai_replacement_parts' => $request->ai_replacement_parts,
                'ai_estimated_cost_min' => $request->filled('ai_estimated_cost_min') ? (int) $request->ai_estimated_cost_min : null,
                'ai_estimated_cost_max' => $request->filled('ai_estimated_cost_max') ? (int) $request->ai_estimated_cost_max : null,
                'ai_complexity_level' => $request->ai_complexity_level,
                'ai_recommended_skills' => json_decode($request->ai_recommended_skills, true) ?: [],
                'ai_dispatch_reason' => $request->ai_dispatch_reason,
                'technician_id' => $request->filled('assigned_technician_id') ? $request->integer('assigned_technician_id') : null,
                'ai_diagnosed_at' => now(),
            ];
        } else {
            // Tự động chẩn đoán ở backend nếu client không gọi trước
            try {
                $aiService = app(\App\Services\RepairAIService::class);
                $diagResult = $aiService->diagnoseFault($request->issue_desc, $imagePath ? public_path($imagePath) : null);
                
                $aiData = [
                    'ai_diagnosed' => true,
                    'ai_fault_type' => $diagResult['ai_fault_type'],
                    'ai_probable_causes' => $diagResult['ai_probable_causes'],
                    'ai_risk_warnings' => $diagResult['ai_risk_warnings'],
                    'ai_replacement_parts' => $diagResult['ai_replacement_parts'],
                    'ai_estimated_cost_min' => $diagResult['ai_estimated_cost_min'],
                    'ai_estimated_cost_max' => $diagResult['ai_estimated_cost_max'],
                    'ai_complexity_level' => $diagResult['ai_complexity_level'],
                    'ai_recommended_skills' => $diagResult['ai_recommended_skills'],
                    'ai_dispatch_reason' => $diagResult['ai_dispatch_reason'],
                    'technician_id' => $diagResult['assigned_technician_id'],
                    'ai_diagnosed_at' => $diagResult['ai_diagnosed_at'],
                ];
            } catch (\Throwable $e) {
                \Log::error('Lỗi tự động chẩn đoán AI tại backend: ' . $e->getMessage());
                // Fallback nếu có lỗi
                $defaultTech = \App\Models\User::whereIn('role_id', [1, 2, 4])->first();
                $aiData = [
                    'ai_diagnosed' => false,
                    'technician_id' => $defaultTech ? $defaultTech->user_id : null,
                ];
            }
        }

        // Tính chi phí dự toán (trung bình min và max) để gán cho cột estimated_cost cũ
        $estimatedCost = 0;
        if (isset($aiData['ai_estimated_cost_min']) && isset($aiData['ai_estimated_cost_max'])) {
            $estimatedCost = (int) (($aiData['ai_estimated_cost_min'] + $aiData['ai_estimated_cost_max']) / 2);
        }

        // Bước 3: Tạo mới phiếu sửa chữa
        \App\Models\RepairTicket::create([
            'user_id' => $user->user_id,
            'technician_id' => $aiData['technician_id'],
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'customer_address' => $request->customer_address,
            'customer_source' => 'Website',
            'imei_serial' => $request->imei_serial,
            'issue_desc' => $request->issue_desc,
            'schedule_date' => $request->schedule_date,
            'status' => 'Received',
            'estimated_cost' => $estimatedCost,
            'device_image' => $imagePath,
            'ai_diagnosed' => $aiData['ai_diagnosed'] ?? false,
            'ai_fault_type' => $aiData['ai_fault_type'] ?? null,
            'ai_probable_causes' => $aiData['ai_probable_causes'] ?? null,
            'ai_risk_warnings' => $aiData['ai_risk_warnings'] ?? null,
            'ai_replacement_parts' => $aiData['ai_replacement_parts'] ?? null,
            'ai_estimated_cost_min' => $aiData['ai_estimated_cost_min'] ?? null,
            'ai_estimated_cost_max' => $aiData['ai_estimated_cost_max'] ?? null,
            'ai_complexity_level' => $aiData['ai_complexity_level'] ?? null,
            'ai_recommended_skills' => $aiData['ai_recommended_skills'] ?? null,
            'ai_dispatch_reason' => $aiData['ai_dispatch_reason'] ?? null,
            'ai_diagnosed_at' => $aiData['ai_diagnosed_at'] ?? null,
        ]);



        // Bước 4: Điều hướng phản hồi về trang cá nhân của khách hàng
        return redirect()->route('profile.index')->with('repair_success', 'Đã đăng ký lịch hẹn sửa chữa trực tuyến thành công!');
    }
}
