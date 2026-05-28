<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\CrossSellService;
use App\Services\FlashSaleService;
use App\Services\ProductFilterService;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductFilterService $productFilterService,
        private readonly FlashSaleService     $flashSaleService,
        private readonly CrossSellService     $crossSellService
    ) {
    }

    /**
     * Hiển thị danh sách sản phẩm.
     */
    public function index(Request $request, $categorySlug = null)
    {
        $currentCategory = null;

        if ($categorySlug) {
            $currentCategory = Category::where('slug', $categorySlug)->first();
        } elseif ($request->filled('category_id')) {
            $currentCategory = Category::find($request->category_id);
        }

        $params = $request->all();
        if ($currentCategory && empty($params['category_id'])) {
            $params['category_id'] = $currentCategory->category_id;
        }

        $products = $this->productFilterService->filter($params, 12);
        $categories = Category::whereNull('parent_id')->get();

        return view('frontend.products.index', compact('products', 'categories', 'currentCategory'));
    }

    /**
     * Hiển thị chi tiết sản phẩm.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'productSpecifications', 'variants', 'comboProducts'])->findOrFail($id);
        $flashSaleProduct = $this->flashSaleService->getFlashSaleProductFor($product);
        $effectivePrice = $this->flashSaleService->getEffectivePrice($product);

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('product_id', '<>', $product->product_id)
            ->take(6)
            ->get();

        // Kiểm tra user đăng nhập đã mua sản phẩm này chưa
        $hasPurchased = false;
        if (Auth::check()) {
            $hasPurchased = Order::where('user_id', Auth::id())
                ->whereHas('details.inventoryItem.variant', function ($q) use ($product) {
                    $q->where('product_id', $product->product_id);
                })
                ->exists();
        }

        // Lấy danh sách đánh giá (chỉ lấy review gốc đã duyệt, không phải reply)
        $reviews = Review::where('product_id', $id)
            ->whereNull('parent_id')
            ->where('is_approved', 1)
            ->with(['user', 'replies' => function ($q) {
                $q->where('is_approved', 1);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $reviewCount = Review::where('product_id', $id)->whereNull('parent_id')->where('is_approved', 1)->count();
        $avgRating = Review::where('product_id', $id)->whereNull('parent_id')->where('is_approved', 1)->avg('rating') ?: 5;

        // Gợi ý bán chéo: FBT → Brand → Flash Sale → Category
        $crossSellProducts = $this->crossSellService->getFullCrossSellList($product, 8);

        // Lấy danh sách Combo sản phẩm được cấu hình riêng biệt
        $comboProducts = $product->comboProducts;
        $this->crossSellService->attachFlashSaleInfo($comboProducts);

        return view('frontend.products.show', compact(
            'product', 
            'relatedProducts', 
            'hasPurchased', 
            'flashSaleProduct', 
            'effectivePrice',
            'reviews',
            'reviewCount',
            'avgRating',
            'crossSellProducts',
            'comboProducts'
        ));
    }
}
