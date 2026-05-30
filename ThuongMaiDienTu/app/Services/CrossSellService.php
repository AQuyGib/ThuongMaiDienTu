<?php

namespace App\Services;

use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * CrossSellService - Dịch vụ Gợi ý Bán chéo và Cá nhân hóa Sản phẩm (Cross-sell & Recommendation Engine).
 *
 * Nhiệm vụ chính:
 * 1. Khai thác dữ liệu hành vi lịch sử mua sắm để đề xuất các sản phẩm "thường được mua cùng nhau" (Frequently Bought Together - FBT).
 * 2. Đề xuất sản phẩm bổ trợ từ cùng thương hiệu nhưng khác danh mục (ví dụ: đang xem iPhone -> gợi ý AirPods, ốp lưng Apple).
 * 3. Gợi ý các sản phẩm đang có chương trình Flash Sale nằm trong cùng phân khúc giá (±40% giá sản phẩm hiện tại).
 * 4. Xây dựng thuật toán phân tầng (Multi-tier Recommendation Engine) kết quả gợi ý linh hoạt, ưu tiên hàng đầu theo chỉ định của Admin, cá nhân hóa theo lịch sử xem, và fallback an toàn theo cùng danh mục.
 * 5. Hỗ trợ cơ chế lưu Cache 30 phút để giảm tải tối đa cho hệ thống Database.
 * 6. Tự động kiểm tra và đính kèm thông tin giá Flash Sale thời gian thực lên danh sách kết quả gợi ý.
 */
class CrossSellService
{
    /**
     * Tầng 1: Tìm kiếm các sản phẩm "thường được mua cùng nhau" (Frequently Bought Together - FBT).
     * Thuật toán: 
     * 1. Lấy tất cả biến thể (variants) của sản phẩm hiện tại.
     * 2. Quét bảng chi tiết đơn hàng (order_details) để tìm các mã đơn hàng (order_id) có chứa những biến thể này.
     * 3. Tìm các sản phẩm khác (product_id khác) cũng nằm trong danh sách các đơn hàng đó, đếm số lần xuất hiện chung (co_count).
     * 4. Sắp xếp giảm dần theo tần suất xuất hiện chung để lấy ra những gợi ý phù hợp nhất.
     * 
     * @param int $productId ID sản phẩm gốc đang xem
     * @param int $limit Số lượng sản phẩm tối đa cần lấy
     * @return Collection Danh sách sản phẩm gợi ý
     */
    public function getFrequentlyBoughtTogether(int $productId, int $limit = 6): Collection
    {
        // Bước 1: Lấy danh sách ID của toàn bộ biến thể thuộc sản phẩm hiện tại
        $variantIds = DB::table('product_variants')
            ->where('product_id', $productId)
            ->pluck('variant_id');

        if ($variantIds->isEmpty()) {
            return collect();
        }

        // Bước 2: Tìm tất cả order_id của các đơn hàng có mua ít nhất 1 biến thể của sản phẩm hiện tại
        $orderIds = DB::table('order_details')
            ->join('inventory_items', 'order_details.item_id', '=', 'inventory_items.item_id')
            ->whereIn('inventory_items.variant_id', $variantIds)
            ->pluck('order_details.order_id')
            ->unique();

        if ($orderIds->isEmpty()) {
            return collect();
        }

        // Bước 3: Tìm các product_id khác nằm chung trong các đơn hàng này, đếm số lần xuất hiện chung (co_count)
        $coProductIds = DB::table('order_details')
            ->join('inventory_items', 'order_details.item_id', '=', 'inventory_items.item_id')
            ->join('product_variants', 'inventory_items.variant_id', '=', 'product_variants.variant_id')
            ->whereIn('order_details.order_id', $orderIds)
            ->where('product_variants.product_id', '!=', $productId) // Loại trừ chính sản phẩm đang xem
            ->select('product_variants.product_id', DB::raw('COUNT(*) as co_count'))
            ->groupBy('product_variants.product_id')
            ->orderByDesc('co_count') // Sắp xếp sản phẩm nào được mua chung nhiều nhất lên đầu
            ->limit($limit)
            ->pluck('product_variants.product_id');

        if ($coProductIds->isEmpty()) {
            return collect();
        }

        // Tải thông tin sản phẩm và biến thể kèm theo, đồng thời sắp xếp kết quả trả về đúng theo thứ tự tần suất mua chung
        return Product::with('variants')
            ->whereIn('product_id', $coProductIds)
            ->whereNull('deleted_at')
            ->get()
            ->sortBy(fn($p) => $coProductIds->search($p->product_id));
    }

    /**
     * Tầng 2: Gợi ý sản phẩm bổ trợ cùng thương hiệu (Brand), ưu tiên sản phẩm khác danh mục (Accessories).
     * Ví dụ: Nếu khách đang xem điện thoại iPhone (Apple), hệ thống sẽ ưu tiên gợi ý tai nghe AirPods hoặc sạc Apple.
     * 
     * @param Product $product Đối tượng sản phẩm đang xem
     * @param int $limit Số lượng sản phẩm cần lấy
     * @return Collection Danh sách sản phẩm cùng hãng
     */
    public function getCrossSellByBrand(Product $product, int $limit = 4): Collection
    {
        if (empty($product->brand)) {
            return collect();
        }

        return Product::where('brand', $product->brand)
            ->where('product_id', '!=', $product->product_id)
            ->whereNull('deleted_at')
            // Sử dụng ORDER BY RAW để đẩy các sản phẩm có category_id khác với sản phẩm hiện tại lên trước
            ->orderByRaw('CASE WHEN category_id != ? THEN 0 ELSE 1 END', [$product->category_id])
            ->orderByDesc('product_id')
            ->limit($limit)
            ->get();
    }

    /**
     * Tầng 3: Gợi ý sản phẩm đang chạy chương trình Flash Sale có giá trị tương đương (Phân khúc giá ±40%).
     * Giúp kích thích nhu cầu mua hàng của khách bằng những ưu đãi giảm giá mạnh mẽ của sản phẩm tương đồng túi tiền.
     * 
     * @param Product $product
     * @param int $limit
     * @return Collection
     */
    public function getCrossSellByFlashSale(Product $product, int $limit = 4): Collection
    {
        if (!$product->base_price) {
            return collect();
        }

        // Tính khoảng giá sàn (60%) và giá trần (140%) so với giá gốc sản phẩm đang xem
        $minPrice = $product->base_price * 0.6;
        $maxPrice = $product->base_price * 1.4;

        // Truy vấn chiến dịch Flash Sale đang diễn ra (Active) ở thời điểm hiện tại
        $activeFlashSale = FlashSale::where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->first();

        if (!$activeFlashSale) {
            return collect();
        }

        // Lấy danh sách ID các sản phẩm nằm trong chương trình Flash Sale này
        $flashProductIds = $activeFlashSale->products()
            ->where('flash_sale_products.is_active', true)
            ->pluck('flash_sale_products.product_id');

        if ($flashProductIds->isEmpty()) {
            return collect();
        }

        // Lọc các sản phẩm Flash Sale nằm trong phân khúc giá cho phép và loại trừ sản phẩm hiện tại
        return Product::whereIn('product_id', $flashProductIds)
            ->where('product_id', '!=', $product->product_id)
            ->whereBetween('base_price', [$minPrice, $maxPrice])
            ->whereNull('deleted_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Danh sách các ID danh mục thuộc nhóm hàng phụ kiện hoặc sản phẩm bổ trợ bổ sung.
     * (Ví dụ: 4 = Âm thanh, 5 = Đồng hồ, 18 = Phụ kiện, 20 = Tai nghe...)
     */
    private const ACCESSORY_CATEGORY_IDS = [4, 5, 6, 18, 19, 20, 21, 22];

    /**
     * Phương thức chính: Xây dựng danh sách gợi ý bán chéo hoàn chỉnh bằng cách chạy qua 7 tầng đề xuất.
     * Có tích hợp cơ chế cá nhân hóa người dùng, danh sách chỉ định của Admin, lọc trùng lặp và lưu Cache.
     * 
     * @param Product $product
     * @param int $limit
     * @return Collection
     */
    public function getFullCrossSellList(Product $product, int $limit = 8): Collection
    {
        $userId = auth()->id();
        // Tạo cache key định danh độc nhất theo ID sản phẩm và ID người dùng (hoặc khách vãng lai 'guest')
        $cacheKey = "cross_sell_v2_{$product->product_id}_user_" . ($userId ?? 'guest');

        // Lưu cache kết quả gợi ý trong 30 phút để tăng tốc độ tải trang chi tiết sản phẩm
        return cache()->remember($cacheKey, now()->addMinutes(30), function () use ($product, $limit, $userId) {
            $result = collect();

            // --- TẦNG 0: Admin Picks (Ưu tiên hàng đầu) ---
            // Lấy các sản phẩm được người quản trị gán tay trực tiếp đi kèm sản phẩm này trong database
            $adminPicks = $product->crossSells()->whereNull('deleted_at')->limit($limit)->get();
            $result = $result->merge($adminPicks);

            // --- TẦNG 1: Personalization (Cá nhân hóa theo lịch sử) ---
            // Nếu người dùng đã đăng nhập và kết quả chưa đủ số lượng limit, lấy các phụ kiện họ đã xem gần đây
            if ($userId && $result->count() < $limit) {
                $personalized = $this->getPersonalizedAccessories($userId, $product->product_id, $limit - $result->count());
                $result = $result->merge($personalized);
            }

            // --- TẦNG 2: Frequently Bought Together (FBT từ dữ liệu mua hàng thực tế) ---
            if ($result->count() < $limit) {
                $fbt = $this->getFrequentlyBoughtTogether($product->product_id, $limit - $result->count());
                $result = $result->merge($fbt);
            }

            // --- TẦNG 3: Cùng brand hãng nhưng khác danh mục (Ví dụ: xem điện thoại Apple -> gợi ý tai nghe Apple) ---
            if ($result->count() < $limit) {
                $byBrand = $this->getCrossSellByBrand($product, $limit - $result->count());
                $result = $result->merge($byBrand);
            }

            // --- TẦNG 4: Danh mục phụ kiện ngẫu nhiên bổ trợ (Để làm mới hiển thị) ---
            if ($result->count() < $limit) {
                $excludeIds = $result->pluck('product_id')->push($product->product_id)->toArray();
                $byAccessories = Product::whereIn('category_id', self::ACCESSORY_CATEGORY_IDS)
                    ->whereNotIn('product_id', $excludeIds)
                    ->whereNull('deleted_at')
                    ->inRandomOrder()
                    ->limit($limit - $result->count())
                    ->get();
                $result = $result->merge($byAccessories);
            }

            // --- TẦNG 5: Flash Sale cùng phân khúc giá (Kích cầu ưu đãi) ---
            if ($result->count() < $limit) {
                $byFlashSale = $this->getCrossSellByFlashSale($product, $limit - $result->count());
                $result = $result->merge($byFlashSale);
            }

            // --- TẦNG 6 (Fallback an toàn): Cùng danh mục sản phẩm (Đảm bảo luôn lấy đủ số lượng đề xuất) ---
            if ($result->count() < $limit) {
                $excludeIds = $result->pluck('product_id')->push($product->product_id)->toArray();
                $byCategory = Product::where('category_id', $product->category_id)
                    ->whereNotIn('product_id', $excludeIds)
                    ->whereNull('deleted_at')
                    ->limit($limit - $result->count())
                    ->get();
                $result = $result->merge($byCategory);
            }

            // Tiến hành làm sạch kết quả: loại bỏ sản phẩm trùng lặp, loại bỏ chính sản phẩm gốc đang xem và lấy đủ số lượng limit
            $final = $result
                ->unique('product_id')
                ->filter(fn($p) => $p->product_id !== $product->product_id)
                ->take($limit)
                ->values();

            // Thực hiện quét và gán giá trị Flash Sale hiện tại lên các sản phẩm nếu có để hiển thị giá ưu đãi
            $this->attachFlashSaleInfo($final);

            return $final;
        });
    }

    /**
     * Truy vấn tìm danh sách các sản phẩm phụ kiện người dùng đã xem gần đây (Personalization).
     * 
     * @param int $userId ID khách hàng
     * @param int $currentProductId ID sản phẩm đang xem
     * @param int $limit Số lượng cần lấy
     * @return Collection
     */
    private function getPersonalizedAccessories(int $userId, int $currentProductId, int $limit): Collection
    {
        return Product::whereIn('category_id', self::ACCESSORY_CATEGORY_IDS)
            ->whereHas('wishlistRecentlyViewed', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('type', 'Viewed');
            })
            ->where('product_id', '!=', $currentProductId)
            ->whereNull('deleted_at')
            // Sắp xếp các sản phẩm có lịch sử xem mới nhất lên trước bằng cách lấy ID lớn nhất của bảng lịch sử xem tương ứng
            ->orderByDesc(DB::table('wishlists_recently_viewed')
                ->select('id')
                ->whereColumn('wishlists_recently_viewed.product_id', 'products.product_id')
                ->limit(1)
            )
            ->limit($limit)
            ->get();
    }

    /**
     * Gắn thêm thuộc tính `flash_sale_price` trực tiếp vào đối tượng sản phẩm nếu sản phẩm đó đang nằm trong chiến dịch Flash Sale đang chạy.
     * Giúp hiển thị thông tin giá ưu đãi đồng bộ trên trang chi tiết sản phẩm.
     * 
     * @param Collection $products Bộ sưu tập các sản phẩm cần kiểm tra
     * @return void
     */
    public function attachFlashSaleInfo(Collection $products): void
    {
        if ($products->isEmpty()) {
            return;
        }

        // Lấy thông tin chiến dịch Flash Sale đang kích hoạt trong khung giờ hiện tại
        $activeFlashSale = FlashSale::where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->first();

        if (!$activeFlashSale) {
            return;
        }

        // Lấy bản đồ ánh xạ giá Flash Sale: key = product_id, value = sale_price
        $flashPriceMap = DB::table('flash_sale_products')
            ->where('flash_sale_id', $activeFlashSale->flash_sale_id)
            ->where('is_active', true)
            ->whereIn('product_id', $products->pluck('product_id'))
            ->pluck('sale_price', 'product_id');

        // Duyệt qua từng sản phẩm để đính kèm thuộc tính giá sale nếu khớp trong bản đồ ánh xạ
        foreach ($products as $product) {
            if (isset($flashPriceMap[$product->product_id])) {
                $product->flash_sale_price = $flashPriceMap[$product->product_id];
            }
        }
    }
}
