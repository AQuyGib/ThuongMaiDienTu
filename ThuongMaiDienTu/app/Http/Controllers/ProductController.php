<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use App\Models\WishlistRecentlyViewed;
use App\Services\CrossSellService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Trang chi tiết sản phẩm.
     * Route: /product/{id}
     */
    public function show(int $id)
    {
        // Lấy sản phẩm kèm quan hệ
        $product = Product::with(['category', 'productSpecifications', 'variants'])
            ->findOrFail($id);

        // Tính % giảm giá (nếu có)
        $discountPercent = null;
        if ($product->old_price && $product->old_price > $product->base_price) {
            $discountPercent = round(
                (($product->old_price - $product->base_price) / $product->old_price) * 100
            );
        }

        // Ghi lịch sử "Đã xem" nếu đã đăng nhập
        if (Auth::check()) {
            WishlistRecentlyViewed::firstOrCreate([
                'user_id'    => Auth::id(),
                'product_id' => $product->product_id,
                'type'       => 'Viewed',
            ]);
        }

        // Sản phẩm liên quan (cùng danh mục, trừ sản phẩm hiện tại, lấy tối đa 6)
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('product_id', '!=', $product->product_id)
            ->orderBy('product_id', 'desc')
            ->take(6)
            ->get();

        // Kiểm tra xem sản phẩm có trong danh sách yêu thích không
        $isWishlisted = false;
        if (Auth::check()) {
            $isWishlisted = WishlistRecentlyViewed::where('user_id', Auth::id())
                ->where('product_id', $product->product_id)
                ->where('type', 'wishlist')
                ->exists();
        }

        // Lấy danh sách đánh giá (chỉ lấy review gốc, không phải reply)
        $reviews = Review::where('product_id', $id)
            ->whereNull('parent_id')
            ->with(['user', 'replies'])
            ->orderBy('created_at', 'desc')
            ->get();

        $reviewCount = Review::where('product_id', $id)->whereNull('parent_id')->count();
        $avgRating = Review::where('product_id', $id)->whereNull('parent_id')->avg('rating') ?: 5;

        // Kiểm tra user đã mua hàng chưa (cho chức năng đánh giá)
        $hasPurchased = false;
        if (Auth::check()) {
            $hasPurchased = Order::where('user_id', Auth::id())
                ->whereHas('details.inventoryItem.variant', function ($q) use ($product) {
                    $q->where('product_id', $product->product_id);
                })
                ->exists();
        }

        // Gợi ý bán chéo (Cross-selling): FBT → Brand → Flash Sale
        $crossSellProducts = app(CrossSellService::class)
            ->getFullCrossSellList($product, 8);

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
