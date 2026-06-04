<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WishlistRecentlyViewed;
use Illuminate\Support\Facades\Auth;

/**
 * =================================================================================
 * BỘ ĐIỀU KHIỂN: QUẢN LÝ DANH SÁCH SẢN PHẨM YÊU THÍCH (WISHLIST CONTROLLER)
 * ---------------------------------------------------------------------------------
 * Đây là nơi xử lý các thao tác khi người dùng tương tác với tính năng "Yêu thích sản phẩm"
 * (ví dụ như khi nhấn vào nút hình trái tim). Hệ thống sẽ tự động lưu lại các sản phẩm 
 * khách hàng thích vào tài khoản để họ có thể xem lại sau, hoặc xóa chúng khi không còn nhu cầu.
 * =================================================================================
 */
class WishlistController extends Controller
{
    /**
     * HÀM: toggle
     * Ý NGHĨA: Bật/Tắt trạng thái yêu thích của sản phẩm (Thêm nếu chưa có, Xóa nếu đã có).
     */
    public function toggle(Request $request)
    {
        // 1. Kiểm tra xem người dùng đã đăng nhập chưa, nếu chưa thì báo lỗi
        if (!Auth::check()) {
            return response()->json(['status' => 'unauthenticated', 'error' => 'Vui lòng đăng nhập'], 401);
        }

        // 2. Lấy mã sản phẩm từ giao diện gửi lên và lấy ID của người dùng đang đăng nhập
        $productId = $request->input('product_id');
        $userId = Auth::id();

        // 3. Tìm kiếm xem sản phẩm này đã được người dùng này lưu vào danh sách yêu thích trước đó chưa
        $wishlist = WishlistRecentlyViewed::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('type', 'Wishlist')
            ->first();

        // 4. Nếu sản phẩm ĐÃ TỒN TẠI trong danh sách yêu thích -> Tiến hành XÓA nó đi (Hủy yêu thích)
        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['status' => 'removed']);
        } else {
            // 5. Nếu sản phẩm CHƯA CÓ trong danh sách yêu thích -> Tiến hành THÊM MỚI sản phẩm này vào danh sách
            WishlistRecentlyViewed::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'type' => 'Wishlist',
                'created_at' => now()
            ]);
            return response()->json(['status' => 'added']);
        }
    }

    /**
     * HÀM: removeFromWishlist
     * Ý NGHĨA: Xóa một sản phẩm cụ thể ra khỏi danh sách yêu thích khi xem trong trang cá nhân.
     */
    public function removeFromWishlist($id)
    {
        // 1. Yêu cầu người dùng phải đăng nhập trước
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        // 2. Tìm bản ghi sản phẩm yêu thích tương ứng với ID được chọn và của chính người dùng đó
        $wishlistItem = WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        // 3. Nếu tìm thấy bản ghi thì thực hiện xóa khỏi cơ sở dữ liệu
        if ($wishlistItem) {
            $wishlistItem->delete();
            return response()->json(['success' => true]);
        }

        // 4. Trả về lỗi nếu không tìm thấy bản ghi cần xóa
        return response()->json(['error' => 'Không tìm thấy sản phẩm'], 404);
    }

    /**
     * HÀM: clearWishlist
     * Ý NGHĨA: Xóa toàn bộ danh sách sản phẩm đã yêu thích của người dùng hiện tại (Làm sạch danh sách).
     */
    public function clearWishlist()
    {
        // 1. Kiểm tra đăng nhập
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        // 2. Xóa hàng loạt tất cả các bản ghi có loại là 'Wishlist' của người dùng hiện tại
        WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('type', 'Wishlist')
            ->delete();

        // 3. Phản hồi kết quả thành công về cho giao diện hiển thị
        return response()->json(['success' => true]);
    }
}
