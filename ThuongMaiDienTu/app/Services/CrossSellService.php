<?php

namespace App\Services;

use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CrossSellService
{
    /**
     * Tầng 1: Tìm sản phẩm thường được mua cùng nhau (Frequently Bought Together).
     * Logic: JOIN order_details → inventory_items → product_variants → products
     * để tìm các product_id xuất hiện trong cùng đơn hàng với $productId.
     */
    public function getFrequentlyBoughtTogether(int $productId, int $limit = 6): Collection
    {
        // Tìm các variant_id thuộc sản phẩm hiện tại
        $variantIds = DB::table('product_variants')
            ->where('product_id', $productId)
            ->pluck('variant_id');

        if ($variantIds->isEmpty()) {
            return collect();
        }

        // Tìm tất cả order_id chứa sản phẩm hiện tại
        $orderIds = DB::table('order_details')
            ->join('inventory_items', 'order_details.item_id', '=', 'inventory_items.item_id')
            ->whereIn('inventory_items.variant_id', $variantIds)
            ->pluck('order_details.order_id')
            ->unique();

        if ($orderIds->isEmpty()) {
            return collect();
        }

        // Tìm các product_id khác cũng xuất hiện trong những đơn hàng đó
        $coProductIds = DB::table('order_details')
            ->join('inventory_items', 'order_details.item_id', '=', 'inventory_items.item_id')
            ->join('product_variants', 'inventory_items.variant_id', '=', 'product_variants.variant_id')
            ->whereIn('order_details.order_id', $orderIds)
            ->where('product_variants.product_id', '!=', $productId)
            ->select('product_variants.product_id', DB::raw('COUNT(*) as co_count'))
            ->groupBy('product_variants.product_id')
            ->orderByDesc('co_count')
            ->limit($limit)
            ->pluck('product_variants.product_id');

        if ($coProductIds->isEmpty()) {
            return collect();
        }

        return Product::with('variants')
            ->whereIn('product_id', $coProductIds)
            ->whereNull('deleted_at')
            ->get()
            // Giữ thứ tự theo co_count
            ->sortBy(fn($p) => $coProductIds->search($p->product_id));
    }

    /**
     * Tầng 2: Gợi ý theo cùng thương hiệu (brand), ưu tiên khác danh mục (accessories).
     * Ví dụ: đang xem iPhone → gợi AirPods, Apple Watch.
     */
    public function getCrossSellByBrand(Product $product, int $limit = 4): Collection
    {
        if (empty($product->brand)) {
            return collect();
        }

        return Product::where('brand', $product->brand)
            ->where('product_id', '!=', $product->product_id)
            ->whereNull('deleted_at')
            // Ưu tiên khác category trước (accessories)
            ->orderByRaw('CASE WHEN category_id != ? THEN 0 ELSE 1 END', [$product->category_id])
            ->orderByDesc('product_id')
            ->limit($limit)
            ->get();
    }

    /**
     * Tầng 3: Flash Sale cùng phân khúc giá (±40% so với sản phẩm hiện tại).
     */
    public function getCrossSellByFlashSale(Product $product, int $limit = 4): Collection
    {
        if (!$product->base_price) {
            return collect();
        }

        $minPrice = $product->base_price * 0.6;
        $maxPrice = $product->base_price * 1.4;

        // Lấy flash sale đang active
        $activeFlashSale = FlashSale::where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->first();

        if (!$activeFlashSale) {
            return collect();
        }

        $flashProductIds = $activeFlashSale->products()
            ->where('flash_sale_products.is_active', true)
            ->pluck('flash_sale_products.product_id');

        if ($flashProductIds->isEmpty()) {
            return collect();
        }

        return Product::whereIn('product_id', $flashProductIds)
            ->where('product_id', '!=', $product->product_id)
            ->whereBetween('base_price', [$minPrice, $maxPrice])
            ->whereNull('deleted_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Danh sách các ID danh mục được coi là phụ kiện hoặc sản phẩm bổ trợ.
     */
    private const ACCESSORY_CATEGORY_IDS = [4, 5, 6, 18, 19, 20, 21, 22]; // Âm thanh, Đồng hồ, Phụ kiện, Tai nghe, Sạc...

    /**
     * Phương thức chính: Kết hợp các tầng gợi ý, tích hợp Personalization, Admin Control và Caching.
     */
    public function getFullCrossSellList(Product $product, int $limit = 8): Collection
    {
        $userId = auth()->id();
        $cacheKey = "cross_sell_v2_{$product->product_id}_user_" . ($userId ?? 'guest');

        return cache()->remember($cacheKey, now()->addMinutes(30), function () use ($product, $limit, $userId) {
            $result = collect();

            // --- TẦNG 0: Admin Picks (Ưu tiên tuyệt đối từ Admin) ---
            $adminPicks = $product->crossSells()->whereNull('deleted_at')->limit($limit)->get();
            $result = $result->merge($adminPicks);

            // --- TẦNG 1: Personalization (Dựa trên hàng đã xem) ---
            if ($userId && $result->count() < $limit) {
                $personalized = $this->getPersonalizedAccessories($userId, $product->product_id, $limit - $result->count());
                $result = $result->merge($personalized);
            }

            // --- TẦNG 2: Frequently Bought Together (Dữ liệu mua hàng thực tế) ---
            if ($result->count() < $limit) {
                $fbt = $this->getFrequentlyBoughtTogether($product->product_id, $limit - $result->count());
                $result = $result->merge($fbt);
            }

            // --- TẦNG 3: Cùng brand nhưng khác danh mục (Ưu tiên phụ kiện của hãng) ---
            if ($result->count() < $limit) {
                $byBrand = $this->getCrossSellByBrand($product, $limit - $result->count());
                $result = $result->merge($byBrand);
            }

            // --- TẦNG 4: Ưu tiên các danh mục phụ kiện ngẫu nhiên ---
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

            // --- TẦNG 5: Flash Sale ---
            if ($result->count() < $limit) {
                $byFlashSale = $this->getCrossSellByFlashSale($product, $limit - $result->count());
                $result = $result->merge($byFlashSale);
            }

            // --- TẦNG 6 (Fallback): Cùng danh mục ---
            if ($result->count() < $limit) {
                $excludeIds = $result->pluck('product_id')->push($product->product_id)->toArray();
                $byCategory = Product::where('category_id', $product->category_id)
                    ->whereNotIn('product_id', $excludeIds)
                    ->whereNull('deleted_at')
                    ->limit($limit - $result->count())
                    ->get();
                $result = $result->merge($byCategory);
            }

            // Loại trùng và lấy đủ limit
            $final = $result
                ->unique('product_id')
                ->filter(fn($p) => $p->product_id !== $product->product_id)
                ->take($limit)
                ->values();

            // Gắn thông tin Flash Sale
            $this->attachFlashSaleInfo($final);

            return $final;
        });
    }

    /**
     * Tầng Personalization: Tìm phụ kiện người dùng đã xem gần đây.
     */
    private function getPersonalizedAccessories(int $userId, int $currentProductId, int $limit): Collection
    {
        return Product::whereIn('category_id', self::ACCESSORY_CATEGORY_IDS)
            ->whereHas('wishlistRecentlyViewed', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('type', 'Viewed');
            })
            ->where('product_id', '!=', $currentProductId)
            ->whereNull('deleted_at')
            ->orderByDesc(DB::table('wishlists_recently_viewed')
                ->select('id')
                ->whereColumn('wishlists_recently_viewed.product_id', 'products.product_id')
                ->limit(1)
            )
            ->limit($limit)
            ->get();
    }

    /**
     * Gắn thêm thuộc tính flash_sale_price vào collection sản phẩm nếu đang sale.
     */
    private function attachFlashSaleInfo(Collection $products): void
    {
        if ($products->isEmpty()) {
            return;
        }

        $activeFlashSale = FlashSale::where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->first();

        if (!$activeFlashSale) {
            return;
        }

        $flashPriceMap = DB::table('flash_sale_products')
            ->where('flash_sale_id', $activeFlashSale->flash_sale_id)
            ->where('is_active', true)
            ->whereIn('product_id', $products->pluck('product_id'))
            ->pluck('sale_price', 'product_id');

        foreach ($products as $product) {
            if (isset($flashPriceMap[$product->product_id])) {
                $product->flash_sale_price = $flashPriceMap[$product->product_id];
            }
        }
    }
}
