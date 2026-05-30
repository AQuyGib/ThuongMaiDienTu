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

/**
 * Class ProductController
 * 
 * Bộ điều khiển (Controller) quản lý trang danh sách sản phẩm và chi tiết sản phẩm phía người dùng (Frontend).
 * Xử lý tích hợp bộ lọc nâng cao, kiểm tra Flash Sale, hiển thị đánh giá và gợi ý bán chéo thông minh (Cross-sell).
 */
class ProductController extends Controller
{
    /**
     * Khởi tạo các Service phụ thuộc thông qua Dependency Injection.
     * 
     * @param ProductFilterService $productFilterService Dịch vụ lọc sản phẩm nâng cao
     * @param FlashSaleService $flashSaleService Dịch vụ quản lý Flash Sale (kiểm tra tồn kho, khuyến mãi)
     * @param CrossSellService $crossSellService Dịch vụ gợi ý bán chéo (Frequently Bought Together, cùng hãng...)
     */
    public function __construct(
        private readonly ProductFilterService $productFilterService,
        private readonly FlashSaleService     $flashSaleService,
        private readonly CrossSellService     $crossSellService
    ) {
    }

    /**
     * Hiển thị danh sách sản phẩm (Trang cửa hàng / Danh mục).
     * Hỗ trợ phân trang, lọc nâng cao theo danh mục, hãng sản xuất, giá cả và thuộc tính đặc thù.
     * 
     * @param Request $request
     * @param string|null $categorySlug Slug của danh mục (Ví dụ: /danh-muc/dien-thoai)
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $categorySlug = null)
    {
        $currentCategory = null;

        // 1. Xác định danh mục hiện tại dựa trên slug danh mục trên URL hoặc category_id trong request
        if ($categorySlug) {
            $currentCategory = Category::where('slug', $categorySlug)->first();
        } elseif ($request->filled('category_id')) {
            $currentCategory = Category::find($request->category_id);
        }

        // 2. Chuẩn bị tham số truyền vào bộ lọc
        $params = $request->all();
        if ($currentCategory && empty($params['category_id'])) {
            $params['category_id'] = $currentCategory->category_id;
        }

        // 3. Gọi service thực hiện lọc và phân trang (tối đa 12 sản phẩm mỗi trang)
        $products = $this->productFilterService->filter($params, 12);
        
        // 4. Lấy danh sách các danh mục cha cao nhất để hiển thị bộ lọc danh mục bên sidebar
        $categories = Category::whereNull('parent_id')->get();

        // 5. Trả về giao diện danh sách sản phẩm với các dữ liệu đã xử lý
        return view('frontend.products.index', compact('products', 'categories', 'currentCategory'));
    }

    /**
     * Hiển thị trang chi tiết sản phẩm.
     * 
     * @param int $id ID của sản phẩm
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // 1. Lấy thông tin chi tiết sản phẩm kèm theo các bảng liên quan (Danh mục, Specifications, Biến thể, Combo)
        $product = Product::with(['category', 'productSpecifications', 'variants', 'comboProducts'])->findOrFail($id);
        
        // 2. Kiểm tra xem sản phẩm này hiện có đang được chạy trong chương trình Flash Sale nào đang hoạt động không
        $flashSaleProduct = $this->flashSaleService->getFlashSaleProductFor($product);
        
        // 3. Tính toán giá bán thực tế (Giá Flash Sale nếu đang có chương trình, ngược lại dùng giá gốc base_price)
        $effectivePrice = $this->flashSaleService->getEffectivePrice($product);

        // 4. Lấy danh sách 6 sản phẩm cùng danh mục để hiển thị ở mục "Sản phẩm tương tự"
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('product_id', '<>', $product->product_id)
            ->take(6)
            ->get();

        // 5. Kiểm tra xem tài khoản đã đăng nhập từng mua và hoàn thành đơn hàng cho sản phẩm này chưa
        // (Dùng để kiểm soát quyền được đánh giá/viết Review)
        $hasPurchased = false;
        if (Auth::check()) {
            $hasPurchased = Order::where('user_id', Auth::id())
                ->whereHas('details.inventoryItem.variant', function ($q) use ($product) {
                    $q->where('product_id', $product->product_id);
                })
                ->exists();
        }

        // 6. Lấy danh sách đánh giá của sản phẩm (Chỉ lấy các đánh giá gốc đã được duyệt, kèm replies bình luận)
        $reviews = Review::where('product_id', $id)
            ->whereNull('parent_id') // Chỉ lấy review gốc, không lấy comment trả lời trực tiếp ở đây
            ->where('is_approved', 1) // Chỉ hiện review đã duyệt
            ->with(['user', 'replies' => function ($q) {
                $q->where('is_approved', 1); // Chỉ lấy câu trả lời của admin hoặc user đã duyệt
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // 7. Thống kê tổng số đánh giá và điểm số đánh giá trung bình (Rating Average)
        $reviewCount = Review::where('product_id', $id)->whereNull('parent_id')->where('is_approved', 1)->count();
        $avgRating = Review::where('product_id', $id)->whereNull('parent_id')->where('is_approved', 1)->avg('rating') ?: 5;

        // 8. Gọi Service xử lý lấy danh sách 8 sản phẩm gợi ý bán chéo (Cross-sell) cho người dùng.
        // Giải thuật gợi ý xếp theo thứ tự ưu tiên: 
        // Thường mua cùng nhau (Frequently Bought Together) -> Cùng hãng sản xuất (Brand) -> Đang Flash Sale -> Cùng danh mục (Category)
        $crossSellProducts = $this->crossSellService->getFullCrossSellList($product, 8);

        // 9. Lấy danh sách Combo sản phẩm mua kèm giá tốt được cấu hình cho sản phẩm này
        $comboProducts = $product->comboProducts;
        // Đính kèm thông tin Flash Sale (nếu có) vào các sản phẩm trong Combo để cập nhật giá chuẩn xác nhất
        $this->crossSellService->attachFlashSaleInfo($comboProducts);

        // 10. Trả về view chi tiết sản phẩm cùng các dữ liệu tổng hợp
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
