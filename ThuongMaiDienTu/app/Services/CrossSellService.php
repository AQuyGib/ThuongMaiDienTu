<?php

namespace App\Services;

use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /**
     * Lấy danh sách Combo sản phẩm mua kèm thông minh cá nhân hóa bằng AI (AI Recommendation & Dynamic Pricing).
     */
    public function getAICrossSellCombos(Product $product, ?\App\Models\User $user, array $cartItems = []): Collection
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            Log::warning('Gemini API key is not configured. Falling back to static comboProducts.');
            return $product->comboProducts;
        }

        $userKey = $user ? $user->user_id : (session()->getId() ?? 'guest');
        $cacheKey = "ai_combo_recs_{$userKey}_{$product->product_id}";

        // Thử lấy từ Cache trước (trong vòng 15 phút) để tăng tốc tải trang
        $cachedCombos = cache()->get($cacheKey);
        if (is_array($cachedCombos)) {
            return $this->buildComboCollection($cachedCombos);
        }

        // 1. Thu thập dữ liệu ngữ cảnh
        $memberTier = $user ? ($user->member_tier ?: 'Dong') : 'Dong';

        // Lịch sử xem sản phẩm gần đây
        $viewedProductNames = [];
        if ($user) {
            $viewedProductNames = DB::table('wishlists_recently_viewed')
                ->join('products', 'wishlists_recently_viewed.product_id', '=', 'products.product_id')
                ->where('wishlists_recently_viewed.user_id', $user->user_id)
                ->where('wishlists_recently_viewed.type', 'Viewed')
                ->orderByDesc('wishlists_recently_viewed.id')
                ->limit(5)
                ->pluck('products.name')
                ->toArray();
        }

        // Lịch sử mua hàng
        $purchasedProductNames = [];
        if ($user) {
            $purchasedProductNames = DB::table('orders')
                ->join('order_details', 'orders.order_id', '=', 'order_details.order_id')
                ->where('orders.user_id', $user->user_id)
                ->whereIn('orders.status', ['Processing', 'Completed'])
                ->orderByDesc('orders.order_id')
                ->limit(5)
                ->pluck('order_details.product_name')
                ->toArray();
        }

        // Các sản phẩm trong giỏ hàng hiện tại
        $cartProductNames = [];
        foreach ($cartItems as $cItem) {
            if (isset($cItem['name'])) {
                $cartProductNames[] = $cItem['name'];
            }
        }

        // 2. Thu thập danh sách ứng viên đề xuất (Candidates)
        // Ứng viên cùng danh mục
        $sameCat = Product::where('category_id', $product->category_id)
            ->where('product_id', '<>', $product->product_id)
            ->whereNull('deleted_at')
            ->limit(10)
            ->get(['product_id', 'name', 'base_price', 'brand']);

        // Ứng viên là các mặt hàng phụ kiện phổ biến
        $accessories = Product::whereIn('category_id', self::ACCESSORY_CATEGORY_IDS)
            ->where('product_id', '<>', $product->product_id)
            ->whereNull('deleted_at')
            ->limit(10)
            ->get(['product_id', 'name', 'base_price', 'brand']);

        $candidates = $sameCat->concat($accessories)->unique('product_id')->take(15);

        if ($candidates->isEmpty()) {
            return $product->comboProducts;
        }

        $candidatesText = "";
        foreach ($candidates as $cand) {
            $candidatesText .= "- ID: {$cand->product_id} | Tên: {$cand->name} | Giá: " . number_format($cand->base_price, 0, ',', '.') . "đ | Thương hiệu: {$cand->brand}\n";
        }

        // 3. Xây dựng prompt gửi tới Gemini
        $prompt = "Bạn là chuyên gia gợi ý bán kèm (Cross-sell Recommendation) và tối ưu hóa giá bán (Dynamic Pricing) cho website thương mại điện tử Điện Máy.
Nhiệm vụ của bạn là phân tích thông tin khách hàng và sản phẩm hiện tại để đề xuất gói combo 2-3 phụ kiện hoặc sản phẩm bổ sung tối ưu nhất từ danh sách ứng viên được cung cấp.

THÔNG TIN SẢN PHẨM HIỆN TẠI ĐANG XEM:
- Tên sản phẩm: {$product->name}
- Giá bán: " . number_format($product->base_price, 0, ',', '.') . "đ
- Thương hiệu: {$product->brand}
- Danh mục ID: {$product->category_id}

THÔNG TIN KHÁCH HÀNG & NGỮ CẢNH:
- Hạng thành viên (Member Tier): {$memberTier} (Cực kỳ quan trọng để định giá)
- Lịch sử xem sản phẩm gần đây: " . (empty($viewedProductNames) ? "Không có" : implode(', ', $viewedProductNames)) . "
- Lịch sử mua hàng đã hoàn thành: " . (empty($purchasedProductNames) ? "Không có" : implode(', ', $purchasedProductNames)) . "
- Các sản phẩm hiện có trong giỏ hàng: " . (empty($cartProductNames) ? "Trống" : implode(', ', $cartProductNames)) . "

DANH SÁCH ỨNG VIÊN SẢN PHẨM ĐỀ XUẤT (CÓ THỂ DÙNG ĐỂ BÁN KÈM):
{$candidatesText}

QUY TẮC ĐỀ XUẤT VÀ ĐỊNH GIÁ (AI DYNAMIC PRICING):
1. Bạn phải chọn ra chính xác từ 2 đến 3 sản phẩm phù hợp nhất làm phụ kiện hoặc combo mua kèm từ danh sách ứng viên trên.
2. Không đề xuất sản phẩm trùng với sản phẩm đang xem hoặc không có trong danh sách ứng viên.
3. Tính toán mức chiết khấu thông minh (discount_type là 'percentage' hoặc 'fixed') cho từng sản phẩm được chọn:
   - Hãy điều chỉnh mức chiết khấu dựa trên hạng thành viên của khách hàng:
     + Khách vãng lai hoặc hạng Đồng (Dong): Ưu đãi chiết khấu cơ bản (ví dụ: giảm 5% - 8% hoặc từ 30.000đ - 70.000đ).
     + Hạng Bạc (Bac): Ưu đãi trung bình (ví dụ: giảm 8% - 10% hoặc từ 70.000đ - 120.000đ).
     + Hạng Vàng (Vang): Ưu đãi cao (ví dụ: giảm 10% - 12% hoặc từ 120.000đ - 180.000đ).
     + Hạng Kim Cương (KimCuong): Ưu đãi tối đa (ví dụ: giảm 12% - 15% hoặc từ 180.000đ - 250.000đ).
   - Hãy đảm bảo mức chiết khấu hợp lý để tăng AOV (giá trị trung bình đơn hàng) nhưng vẫn bảo toàn biên lợi nhuận (không giảm giá quá sâu, không giảm quá 20% hoặc không quá 300.000đ).
4. Cung cấp một câu giải thích bằng tiếng Việt (reason) ngắn gọn, thuyết phục và hướng tới khách hàng một cách lịch sự, thân thiện. Tuyệt đối KHÔNG nêu rõ tên hạng thành viên cụ thể (như 'hạng Đồng', 'hạng Bạc', 'hạng Vàng', 'hạng Kim Cương') trong câu giải thích để tránh cảm giác phân biệt đối xử gây khó chịu cho khách hàng. Hãy viết theo dạng ấm áp, cá nhân hóa hướng đến người dùng, ví dụ: 'Ưu đãi đặc biệt dành riêng cho bạn: Giảm 12% tai nghe AirPods Pro để đồng bộ trải nghiệm âm thanh chất lượng cao của bạn'.

ĐỊNH DẠNG ĐẦU RA BẮT BUỘC:
Bạn bắt buộc phải phản hồi bằng một chuỗi định dạng JSON duy nhất. KHÔNG bao gồm các thẻ markdown như ```json hay ```, KHÔNG viết thêm bất kỳ ký tự nào ngoài chuỗi JSON.
Cấu trúc JSON chính xác như sau:
{
  \"combos\": [
    {
      \"product_id\": <ID sản phẩm được chọn>,
      \"discount_type\": \"percentage\" | \"fixed\",
      \"discount_value\": <giá trị giảm giá, là số nguyên hoặc số thập phân>,
      \"reason\": \"<Lý do đề xuất & ưu đãi cá nhân hóa ngắn gọn bằng tiếng Việt>\"
    }
  ]
}
";

        // 4. Gọi API Gemini
        $responseJson = $this->callGeminiApiForRecommendation($apiKey, $prompt);
        if (!$responseJson) {
            Log::warning('Gemini API call returned null for AI recommendation. Falling back.');
            return $product->comboProducts;
        }

        try {
            $cleanJson = preg_replace('/^```(?:json)?\s*|```\s*$/', '', trim($responseJson));
            $data = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['combos']) || !is_array($data['combos'])) {
                throw new \Exception('JSON không hợp lệ hoặc thiếu trường combos: ' . $responseJson);
            }

            // Lưu vào Cache 15 phút
            cache()->put($cacheKey, $data['combos'], now()->addMinutes(15));

            return $this->buildComboCollection($data['combos']);
        } catch (\Throwable $e) {
            Log::error('Error parsing AI recommendation: ' . $e->getMessage() . '. Raw response: ' . $responseJson);
            return $product->comboProducts;
        }
    }

    /**
     * Xây dựng tập hợp sản phẩm kèm thuộc tính pivot giả lập từ mảng combo được đề xuất.
     */
    private function buildComboCollection(array $combos): Collection
    {
        $productIds = collect($combos)->pluck('product_id')->toArray();
        $products = Product::whereIn('product_id', $productIds)
            ->whereNull('deleted_at')
            ->get();

        $result = collect();
        foreach ($combos as $combo) {
            $prod = $products->firstWhere('product_id', $combo['product_id']);
            if ($prod) {
                // Tạo pivot giả lập dạng stdClass
                $pivot = new \stdClass();
                $pivot->discount_type = $combo['discount_type'];
                $pivot->discount_value = (float) $combo['discount_value'];
                $pivot->is_ai_optimized = true;
                $pivot->ai_reason = $combo['reason'] ?? '';

                $prod->pivot = $pivot;
                $result->push($prod);
            }
        }

        return $result;
    }

    /**
     * Gọi Gemini API phục vụ gợi ý sản phẩm
     */
    private function callGeminiApiForRecommendation(string $apiKey, string $prompt): ?string
    {
        $models = [
            'gemini-3.1-flash-lite',
            'gemini-3.5-flash',
            'gemini-3-flash-preview',
            'gemini-2.5-flash',
        ];

        foreach ($models as $model) {
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . trim($apiKey);
            $postData = json_encode([
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 15,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response !== false && $httpCode === 200) {
                $resData = json_decode($response, true);
                if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                    return $resData['candidates'][0]['content']['parts'][0]['text'];
                }
            }
        }

        return null;
    }
}
