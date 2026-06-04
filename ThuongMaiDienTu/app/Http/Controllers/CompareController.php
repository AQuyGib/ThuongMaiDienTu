<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WishlistRecentlyViewed;
use App\Services\CompareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CompareController
 * 
 * Bộ điều khiển (Controller) quản lý tính năng So sánh sản phẩm.
 * Hỗ trợ lưu trữ danh sách sản phẩm so sánh trong Session (cho khách vãng lai)
 * và Database (cho thành viên đã đăng nhập), đồng thời thực hiện đồng bộ hai nguồn này.
 */
class CompareController extends Controller
{
    // Khóa lưu trữ thông tin sản phẩm so sánh trong Session hoặc LocalStorage
    private const STORAGE_KEY = 'compare_products';
    
    // Loại hành động lưu trữ trong bảng wishlist_recently_vieweds
    private const TYPE = 'Compare';
    
    // Số lượng sản phẩm tối đa được phép so sánh cùng lúc
    private const MAX_ITEMS = 3;

    /**
     * Hiển thị trang giao diện so sánh sản phẩm.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Nếu trên URL có truyền tham số ?ids=1,2,3 thì tiến hành chuẩn hóa và lưu lại
        if ($request->has('ids')) {
            $ids = $this->normalizeIds($request->query('ids'));
            if (!empty($ids)) {
                $this->saveCompareIds($ids);
            }
        }

        // Trả về view so sánh sản phẩm và truyền danh sách ID so sánh hiện có trên Server
        return view('frontend.compare.index', [
            'serverCompareIds' => $this->getServerCompareIds(),
        ]);
    }

    /**
     * API trả về dữ liệu sản phẩm chi tiết phục vụ việc vẽ bảng so sánh bằng JavaScript.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        // Lấy danh sách ID từ tham số truyền lên (?ids=1,2,3), nếu trống thì lấy từ bộ lưu trữ (Session/DB)
        $ids = $this->normalizeIds($request->query('ids', []));
        if (empty($ids)) {
            $ids = $this->getCompareIds();
        }

        // Giới hạn số lượng ID tối đa để tránh lỗi tràn bộ nhớ hoặc DoS database
        $ids = array_slice($ids, 0, self::MAX_ITEMS);

        // Nếu không có sản phẩm nào để so sánh, trả về mảng rỗng ngay lập tức
        if (empty($ids)) {
            return response()->json(['products' => [], 'comparison_data' => []]);
        }

        // Truy vấn dữ liệu chi tiết của các sản phẩm theo danh sách ID (bao gồm danh mục, biến thể, specs)
        $products = Product::with(['category', 'variants', 'productSpecifications'])
            ->whereIn('product_id', $ids)
            ->get()
            // Sắp xếp thứ tự hiển thị sản phẩm đúng với thứ tự ID người dùng đã chọn
            ->sortBy(fn($product) => array_search($product->product_id, $ids))
            ->values();

        // Gọi service xử lý xây dựng ma trận so sánh (phát hiện thuộc tính khác biệt, sắp xếp độ ưu tiên thuộc tính)
        $comparisonData = app(CompareService::class)->buildComparisonData($products);

        // Trả về cấu trúc JSON chứa đầy đủ thông tin hiển thị sản phẩm và bảng thông số kỹ thuật
        return response()->json([
            'products' => $products->map(function ($product) {
                // Giải mã trường specifications dạng JSON trong DB thành dạng mảng PHP
                $specs = $product->specifications;
                if (is_string($specs)) {
                    $specs = json_decode($specs, true) ?? [];
                }
                if (!is_array($specs)) {
                    $specs = [];
                }

                return [
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'thumbnail' => $product->thumbnail,
                    'base_price' => $product->base_price,
                    'old_price' => $product->old_price,
                    'discount_percent' => $product->discount_percent,
                    'rating' => $product->rating,
                    'review_count' => $product->review_count,
                    'category_name' => $product->category->name ?? null,
                    'category_id' => $product->category_id,
                    // Lấy ID danh mục gốc cao nhất (ví dụ: Điện thoại -> Smartphone) để kiểm tra tính hợp lệ khi so sánh chéo
                    'root_category_id' => $product->category ? $product->category->getRootCategoryId() : $product->category_id,
                    'specifications' => $specs,
                ];
            })->values(),
            'comparison_data' => $comparisonData,
        ]);
    }

    /**
     * API Tìm kiếm sản phẩm nhanh để thêm vào bảng so sánh.
     * Thường dùng ở ô tìm kiếm trống trên bảng so sánh khi chưa đủ số lượng sản phẩm.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCompare(Request $request)
    {
        $keyword = $request->get('keyword', '');
        // Danh sách các ID sản phẩm cần loại trừ (vì đã có trên bảng so sánh rồi, tối đa MAX_ITEMS)
        $excludeIds = array_slice($this->normalizeIds($request->get('exclude', [])), 0, self::MAX_ITEMS);

        // Xây dựng câu truy vấn tìm kiếm nhanh
        $query = Product::query()
            ->with('category')
            ->select('product_id', 'name', 'thumbnail', 'base_price', 'category_id');

        // Loại trừ các sản phẩm đang có mặt trên bảng so sánh
        if (!empty($excludeIds)) {
            $query->whereNotIn('product_id', $excludeIds);
        }

        // Lọc theo từ khóa tìm kiếm (LIKE)
        if ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        // Giới hạn trả về tối đa 10 kết quả tìm kiếm để tối ưu hóa hiệu năng
        $results = $query->limit(10)->get();
        
        // Gắn thêm ID danh mục cha cao nhất vào từng sản phẩm tìm được để JS phân loại
        $results->map(function($product) {
            $product->root_category_id = $product->category ? $product->category->getRootCategoryId() : $product->category_id;
            return $product;
        });

        return response()->json($results);
    }

    /**
     * API Đồng bộ hóa danh sách ID sản phẩm so sánh giữa Client (LocalStorage) và Server.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(Request $request)
    {
        $ids = $this->normalizeIds($request->input('ids', []));
        $this->saveCompareIds($ids);

        return response()->json([
            'success' => true,
            'ids' => $this->getCompareIds(),
        ]);
    }

    /**
     * Lấy danh sách ID sản phẩm so sánh hiện tại.
     * Nếu đã đăng nhập: Lấy từ bảng cơ sở dữ liệu `wishlist_recently_vieweds`.
     * Nếu chưa đăng nhập: Lấy từ mảng `compare_list` lưu trữ trong Session Laravel.
     * 
     * @return array
     */
    private function getCompareIds(): array
    {
        if (Auth::check()) {
            // Lấy danh sách ID sản phẩm đã lưu trữ trong DB của thành viên
            $dbIds = WishlistRecentlyViewed::where('user_id', Auth::id())
                ->where('type', self::TYPE)
                ->orderByDesc('id')
                ->pluck('product_id')
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();

            // Nếu DB có dữ liệu, trả về danh sách duy nhất được giới hạn số lượng tối đa
            if (!empty($dbIds)) {
                return array_slice(array_values(array_unique($dbIds)), 0, self::MAX_ITEMS);
            }
        }

        // Fallback: Lấy từ Session của khách vãng lai
        return session('compare_list', []);
    }

    /**
     * Lấy toàn bộ danh sách ID so sánh trên Server của thành viên đã đăng nhập (không giới hạn số lượng).
     * Phục vụ truyền biến khởi tạo xuống giao diện HTML qua window.__SERVER_COMPARE_IDS__.
     * 
     * @return array
     */
    private function getServerCompareIds(): array
    {
        if (!Auth::check()) {
            return [];
        }

        return WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('type', self::TYPE)
            ->orderByDesc('id')
            ->pluck('product_id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Lưu trữ danh sách ID so sánh vào Session và Database (nếu đã đăng nhập).
     * 
     * @param array $ids Danh sách ID sản phẩm cần lưu
     * @return void
     */
    private function saveCompareIds(array $ids): void
    {
        // Loại bỏ ID trùng lặp, lọc giá trị rỗng và giới hạn số lượng tối đa
        $ids = array_slice(array_values(array_unique($ids)), 0, self::MAX_ITEMS);
        session(['compare_list' => $ids]);

        // Nếu chưa đăng nhập thành viên thì dừng lại ở đây (chỉ lưu Session)
        if (!Auth::check()) {
            return;
        }

        // Nếu đã đăng nhập, tiến hành đồng bộ ghi đè vào DB:
        // 1. Xóa các bản ghi cũ của user này
        WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('type', self::TYPE)
            ->delete();

        // 2. Thêm mới các bản ghi mới ứng với danh sách ID
        foreach ($ids as $productId) {
            WishlistRecentlyViewed::create([
                'user_id' => Auth::id(),
                'product_id' => $productId,
                'type' => self::TYPE,
            ]);
        }
    }

    /**
     * Hàm chuẩn hóa các kiểu dữ liệu truyền vào thành mảng các ID dạng số nguyên (integer).
     * Chấp nhận tham số dạng chuỗi phân tách bởi dấu phẩy "1,2,3" hoặc dạng mảng [1, 2, 3].
     * 
     * @param mixed $ids
     * @return array
     */
    private function normalizeIds(mixed $ids): array
    {
        if (is_numeric($ids)) {
            return [(int) $ids];
        }

        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        } elseif (is_array($ids)) {
            $ids = array_values(array_filter(array_map('intval', $ids)));
        } else {
            $ids = [];
        }

        return array_values(array_unique($ids));
    }

    /**
     * Phương thức static chuyển danh sách so sánh từ Session vào Database sau khi đăng nhập.
     * Thường được gọi tại Auth Controllers hoặc Middleware sau khi login thành công.
     * 
     * @return void
     */
    public static function migrateSessionToDb()
    {
        // Chỉ chạy nếu người dùng đã thực hiện đăng nhập thành công
        if (!Auth::check()) {
            return;
        }

        // Lấy danh sách so sánh lưu trong Session tạm thời trước khi đăng nhập
        $sessionIds = session('compare_list', []);
        if (empty($sessionIds)) {
            return;
        }

        $userId = Auth::id();

        // Lấy danh sách ID đã tồn tại sẵn trong DB của người dùng
        $dbIds = WishlistRecentlyViewed::where('user_id', $userId)
            ->where('type', self::TYPE)
            ->pluck('product_id')
            ->toArray();

        // Gộp hai danh sách lại, loại bỏ trùng lặp và giới hạn số lượng
        $allIds = array_unique(array_merge($dbIds, $sessionIds));
        $finalIds = array_slice($allIds, 0, self::MAX_ITEMS);

        // Làm sạch DB cũ và nạp lại danh sách gộp mới
        WishlistRecentlyViewed::where('user_id', $userId)
            ->where('type', self::TYPE)
            ->delete();

        foreach ($finalIds as $productId) {
            WishlistRecentlyViewed::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'type' => self::TYPE,
            ]);
        }

        // Giải phóng và xóa bỏ session tạm thời
        session()->forget('compare_list');
    }
}
