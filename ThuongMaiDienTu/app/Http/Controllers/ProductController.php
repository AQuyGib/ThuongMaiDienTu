<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use App\Models\WishlistRecentlyViewed;
use App\Services\CrossSellService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class ProductController
 * 
 * Bộ điều khiển (Controller) quản lý trang chi tiết sản phẩm phía người dùng (Frontend).
 * Hỗ trợ lưu trữ lịch sử xem sản phẩm, đánh giá từ khách hàng, và hiển thị gợi ý bán chéo (Cross-sell).
 */
class ProductController extends Controller
{
    /**
     * Hiển thị trang chi tiết sản phẩm (Dựa trên ID sản phẩm).
     * Route: /product/{id}
     * 
     * @param int $id ID của sản phẩm cần xem chi tiết
     * @return \Illuminate\View\View
     */
    public function show(int $id)
    {
        // 1. Lấy thông tin chi tiết sản phẩm cùng các quan hệ cơ bản (Danh mục, thông số kỹ thuật, biến thể)
        $product = Product::with(['category', 'productSpecifications', 'variants'])
            ->findOrFail($id);

        // 2. Tính toán tỷ lệ giảm giá (Discount Percent) nếu sản phẩm có giá trị giá cũ (old_price) lớn hơn giá hiện tại
        $discountPercent = null;
        if ($product->old_price && $product->old_price > $product->base_price) {
            $discountPercent = round(
                (($product->old_price - $product->base_price) / $product->old_price) * 100
            );
        }

        // 3. Tự động ghi nhận lịch sử xem sản phẩm (Viewed) nếu người dùng đã đăng nhập tài khoản.
        // Dữ liệu này sẽ là nguồn đầu vào quan trọng phục vụ thuật toán cá nhân hóa bán chéo (Personalization Cross-sell).
        if (Auth::check()) {
            WishlistRecentlyViewed::firstOrCreate([
                'user_id'    => Auth::id(),
                'product_id' => $product->product_id,
                'type'       => 'Viewed',
            ]);
        }

        // 4. Lấy danh sách tối đa 6 sản phẩm liên quan trong cùng danh mục (loại trừ chính sản phẩm đang xem)
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('product_id', '!=', $product->product_id)
            ->orderBy('product_id', 'desc')
            ->take(6)
            ->get();

        // 5. Kiểm tra xem người dùng hiện tại đã lưu sản phẩm này vào danh sách yêu thích (Wishlist) hay chưa
        $isWishlisted = false;
        if (Auth::check()) {
            $isWishlisted = WishlistRecentlyViewed::where('user_id', Auth::id())
                ->where('product_id', $product->product_id)
                ->where('type', 'wishlist')
                ->exists();
        }

        // 6. Truy xuất danh sách đánh giá của sản phẩm.
        // Chỉ hiển thị các đánh giá gốc và câu trả lời của admin/user đã được duyệt (is_approved = 1).
        $reviews = Review::where('product_id', $id)
            ->whereNull('parent_id')
            ->where('is_approved', 1)
            ->with(['user', 'replies' => function ($query) {
                $query->where('is_approved', 1);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // 7. Thống kê tổng số đánh giá gốc và tính toán điểm đánh giá trung bình (Rating Average)
        $reviewCount = Review::where('product_id', $id)->whereNull('parent_id')->where('is_approved', 1)->count();
        $avgRating = Review::where('product_id', $id)->whereNull('parent_id')->where('is_approved', 1)->avg('rating') ?: 5;

        // 8. Kiểm tra xem khách hàng đã đăng nhập đã từng đặt mua và hoàn thành sản phẩm này trước đó chưa.
        // Điều kiện để khách hàng được gửi đánh giá mới ngoài Frontend.
        $hasPurchased = false;
        if (Auth::check()) {
            $hasPurchased = Order::where('user_id', Auth::id())
                ->whereHas('details.inventoryItem.variant', function ($q) use ($product) {
                    $q->where('product_id', $product->product_id);
                })
                ->exists();
        }

        // 9. Lấy danh sách 8 sản phẩm gợi ý bán chéo (Cross-sell) từ CrossSellService.
        // Sử dụng thuật toán 7 tầng: Admin chọn -> Cá nhân hóa -> Mua kèm nhiều -> Cùng hãng -> Phụ kiện -> Flash Sale -> Cùng danh mục.
        $crossSellProducts = app(CrossSellService::class)
            ->getFullCrossSellList($product, 8);

        // 10. Render và trả dữ liệu ra view frontend
        return view('frontend.products.show', compact(
            'product',
            'discountPercent',
            'relatedProducts',
            'isWishlisted',
            'reviews',
            'reviewCount',
            'avgRating',
            'hasPurchased',
            'crossSellProducts'
        ));
    }
}
