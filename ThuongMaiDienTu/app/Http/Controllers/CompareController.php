<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WishlistRecentlyViewed;
use App\Services\CompareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompareController extends Controller
{
    private const STORAGE_KEY = 'compare_products';
    private const TYPE = 'Compare';
    private const MAX_ITEMS = 3;

    public function index()
    {
        return view('frontend.compare.index', [
            'serverCompareIds' => $this->getServerCompareIds(),
        ]);
    }

    public function data(Request $request)
    {
        $ids = $this->normalizeIds($request->query('ids', []));
        if (empty($ids)) {
            $ids = $this->getCompareIds();
        }

        if (empty($ids)) {
            return response()->json(['products' => [], 'comparison_data' => []]);
        }

        $products = Product::with(['category', 'variants', 'productSpecifications'])
            ->whereIn('product_id', $ids)
            ->get()
            ->sortBy(fn($product) => array_search($product->product_id, $ids))
            ->values();

        $comparisonData = app(CompareService::class)->buildComparisonData($products);

        return response()->json([
            'products' => $products->map(function ($product) {
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
                    'specifications' => $specs,
                ];
            })->values(),
            'comparison_data' => $comparisonData,
        ]);
    }

    public function searchCompare(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $excludeIds = $this->normalizeIds($request->get('exclude', []));

        $query = Product::query()->select('product_id', 'name', 'thumbnail', 'base_price', 'category_id');

        if (!empty($excludeIds)) {
            $query->whereNotIn('product_id', $excludeIds);
        }

        if ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        return response()->json($query->limit(10)->get());
    }

    public function sync(Request $request)
    {
        $ids = $this->normalizeIds($request->input('ids', []));
        $this->saveCompareIds($ids);

        return response()->json([
            'success' => true,
            'ids' => $this->getCompareIds(),
        ]);
    }

    private function getCompareIds(): array
    {
        if (Auth::check()) {
            $dbIds = WishlistRecentlyViewed::where('user_id', Auth::id())
                ->where('type', self::TYPE)
                ->orderByDesc('id')
                ->pluck('product_id')
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();

            if (!empty($dbIds)) {
                return array_slice(array_values(array_unique($dbIds)), 0, self::MAX_ITEMS);
            }
        }

        return session('compare_list', []);
    }

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

    private function saveCompareIds(array $ids): void
    {
        $ids = array_slice(array_values(array_unique($ids)), 0, self::MAX_ITEMS);
        session(['compare_list' => $ids]);

        if (!Auth::check()) {
            return;
        }

        WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('type', self::TYPE)
            ->delete();

        foreach ($ids as $productId) {
            WishlistRecentlyViewed::create([
                'user_id' => Auth::id(),
                'product_id' => $productId,
                'type' => self::TYPE,
            ]);
        }
    }

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
}
