<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WishlistRecentlyViewed;
use App\Services\CompareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompareController extends Controller
{
    protected CompareService $compareService;

    public function __construct(CompareService $compareService)
    {
        $this->compareService = $compareService;
    }

    /**
     * Lấy danh sách ID sản phẩm đang so sánh (từ Session hoặc DB)
     */
    private function getCompareIds(): array
    {
        if (Auth::check()) {
            return WishlistRecentlyViewed::where('user_id', Auth::id())
                ->where('type', 'Compare')
                ->pluck('product_id')
                ->toArray();
        }
        return session('compare_list', []);
    }

    /**
     * Lưu danh sách so sánh vào Session (hoặc DB nếu đã login)
     */
    private function saveCompareIds(array $ids): void
    {
        if (Auth::check()) {
            // Xóa cũ, thêm mới
            WishlistRecentlyViewed::where('user_id', Auth::id())
                ->where('type', 'Compare')
                ->delete();
            foreach ($ids as $productId) {
                WishlistRecentlyViewed::create([
                    'user_id'    => Auth::id(),
                    'product_id' => $productId,
                    'type'       => 'Compare',
                ]);
            }
        }
        session(['compare_list' => $ids]);
    }

    /**
     * POST /compare/add — Thêm sản phẩm vào khay so sánh
     */
    public function add(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);
        $productId = (int) $request->product_id;

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
        }

        $compareIds = $this->getCompareIds();

        // Đã có trong khay?
        if (in_array($productId, $compareIds)) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm đã có trong khay so sánh.'], 400);
        }

        // Tối đa 3 sản phẩm
        if (count($compareIds) >= 3) {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể so sánh tối đa 3 sản phẩm.'], 400);
        }

        // Validate cùng danh mục
        if (!empty($compareIds)) {
            $existingProduct = Product::find($compareIds[0]);
            if ($existingProduct && $existingProduct->category_id !== $product->category_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể so sánh các sản phẩm cùng loại.'
                ], 400);
            }
        }

        $compareIds[] = $productId;
        $this->saveCompareIds($compareIds);

        // Trả về thông tin sản phẩm vừa thêm để JS render
        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào khay so sánh.',
            'product' => [
                'product_id' => $product->product_id,
                'name'       => $product->name,
                'thumbnail'  => $product->thumbnail,
                'base_price' => $product->base_price,
                'category_id' => $product->category_id,
            ],
            'compare_count' => count($compareIds),
        ]);
    }

    /**
     * DELETE /compare/remove/{id} — Xóa sản phẩm khỏi khay so sánh
     */
    public function remove($id)
    {
        $compareIds = $this->getCompareIds();
        $compareIds = array_values(array_filter($compareIds, fn($pid) => $pid != $id));
        $this->saveCompareIds($compareIds);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa khỏi khay so sánh.',
            'compare_count' => count($compareIds),
        ]);
    }

    /**
     * POST /compare/clear — Xóa toàn bộ khay so sánh
     */
    public function clear()
    {
        $this->saveCompareIds([]);
        return response()->json(['success' => true, 'message' => 'Đã xóa toàn bộ khay so sánh.']);
    }

    /**
     * GET /compare — Hiển thị trang so sánh đầy đủ
     */
    public function index()
    {
        $compareIds = $this->getCompareIds();

        if (empty($compareIds)) {
            return view('frontend.products.compare', [
                'products'       => collect(),
                'comparisonData' => [],
            ]);
        }

        $products = Product::with(['category', 'variants', 'productSpecifications'])
            ->whereIn('product_id', $compareIds)
            ->get();

        $comparisonData = $this->compareService->buildComparisonData($products);

        return view('frontend.products.compare', compact('products', 'comparisonData'));
    }

    /**
     * GET /compare/data — Trả dữ liệu khay so sánh dạng JSON (cho floating bar)
     */
    public function data()
    {
        $compareIds = $this->getCompareIds();

        if (empty($compareIds)) {
            return response()->json(['products' => [], 'category_id' => null]);
        }

        $products = Product::whereIn('product_id', $compareIds)
            ->get(['product_id', 'name', 'thumbnail', 'base_price', 'category_id'])
            ->map(fn($p) => [
                'product_id' => $p->product_id,
                'name'       => $p->name,
                'thumbnail'  => $p->thumbnail,
                'base_price' => $p->base_price,
                'category_id' => $p->category_id,
            ]);

        return response()->json([
            'products'    => $products,
            'category_id' => $products->first()['category_id'] ?? null,
        ]);
    }

    /**
     * GET /api/products/search-compare — Tìm kiếm sản phẩm cùng danh mục để thêm vào so sánh
     */
    public function searchCompare(Request $request)
    {
        $keyword    = $request->get('keyword', '');
        $categoryId = $request->get('category_id');
        $excludeIds = $request->get('exclude', []);

        if (is_string($excludeIds)) {
            $excludeIds = array_filter(explode(',', $excludeIds));
        }

        $query = Product::whereNull('deleted_at')
            ->select('product_id', 'name', 'thumbnail', 'base_price', 'category_id');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($excludeIds)) {
            $query->whereNotIn('product_id', $excludeIds);
        }

        if ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        $products = $query->limit(10)->get();

        return response()->json($products);
    }

    /**
     * Migrate compare list từ Session vào DB khi user đăng nhập
     */
    public static function migrateSessionToDb(): void
    {
        $sessionList = session('compare_list', []);
        if (empty($sessionList) || !Auth::check()) {
            return;
        }

        // Lấy danh sách compare hiện tại trong DB
        $dbList = WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('type', 'Compare')
            ->pluck('product_id')
            ->toArray();

        // Gộp, loại bỏ trùng lặp, giữ tối đa 3 sản phẩm mới nhất (session trước DB)
        $mergedList = array_slice(array_unique(array_merge($sessionList, $dbList)), 0, 3);

        // Xóa cũ và chèn danh sách mới
        WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('type', 'Compare')
            ->delete();
        foreach ($mergedList as $productId) {
            WishlistRecentlyViewed::create([
                'user_id'    => Auth::id(),
                'product_id' => $productId,
                'type'       => 'Compare',
            ]);
        }
    }
}
