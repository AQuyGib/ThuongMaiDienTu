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
        
        // Logic tính hạng thành viên thực tế
        $currentTier = 'Đồng';
        $nextTier = 'Bạc';
        $spendNeeded = 0;

        if ($totalSpent < 5000000) {
            $currentTier = 'Đồng';
            $nextTier = 'Bạc';
            $spendNeeded = 5000000 - $totalSpent;
        } elseif ($totalSpent < 20000000) {
            $currentTier = 'Bạc';
            $nextTier = 'Vàng';
            $spendNeeded = 20000000 - $totalSpent;
        } elseif ($totalSpent < 50000000) {
            $currentTier = 'Vàng';
            $nextTier = 'Kim Cương';
            $spendNeeded = 50000000 - $totalSpent;
        } else {
            $currentTier = 'Kim Cương';
            $nextTier = 'Đã đạt cấp tối đa';
            $spendNeeded = 0;
        }

        // Tính % tiến trình cho thanh bar
        $tierProgress = 0;
        if ($currentTier == 'Kim Cương') {
            $tierProgress = 100;
        } else {
            // Mục tiêu là số tiền cần đạt để lên hạng tiếp theo
            $targetAmount = $totalSpent + $spendNeeded;
            if ($targetAmount > 0) {
                $tierProgress = ($totalSpent / $targetAmount) * 100;
            }
        }

        $wishlist = $user->wishlists()->where('type', 'Wishlist')->with('product')->get();
        $loginHistories = \App\Models\LoginHistory::where('user_id', $user->user_id)
            ->orderBy('login_at', 'desc')
            ->limit(10)
            ->get();

        return view('frontend.profile', compact('user', 'orders', 'totalOrders', 'totalSpent', 'currentTier', 'nextTier', 'spendNeeded', 'tierProgress', 'wishlist', 'loginHistories'));
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
            $user->wishlists()->whereIn('type', ['Wishlist', 'wishlist'])->delete();

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
                ->where('type', 'wishlist')
                ->first();

            if ($wishlist) {
                $wishlist->delete();
                return response()->json(['status' => 'removed']);
            } else {
                $user->wishlists()->create([
                    'product_id' => $productId,
                    'type' => 'wishlist'
                ]);
                return response()->json(['status' => 'added']);
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi khi toggle yêu thích: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
}
