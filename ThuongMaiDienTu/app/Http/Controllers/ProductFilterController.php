<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductFilterController extends Controller
{
    public function __construct(private readonly ProductFilterService $productFilterService)
    {
    }

    /**
     * Xử lý lọc sản phẩm nâng cao qua AJAX
     */
    public function filterProducts(Request $request)
    {
        try {
            $products = $this->productFilterService->filter($request->all(), 12);

            // Trả về View dạng Partial để AJAX render
            return view('frontend.products.partials.product_grid', compact('products'))->render();
        } catch (\Exception $e) {
            Log::error('Error in ProductFilterController@filterProducts: ' . $e->getMessage());
            return response('Có lỗi xảy ra trong quá trình lọc sản phẩm. Vui lòng thử lại.', 500);
        }
    }

    /**
     * API: Trả về cấu hình bộ lọc động theo Danh mục
     */
    public function getCategoryFilters($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([]);
        }

        $config = $category->filter_config ?? [];
        if (is_string($config)) {
            $config = json_decode($config, true) ?: [];
        }

        if (!isset($config['price'])) {
            $config['price'] = [
                'label' => 'Khoảng giá',
                'type' => 'range',
                'fields' => [
                    ['label' => 'Từ', 'name' => 'min_price', 'placeholder' => '0'],
                    ['label' => 'Đến', 'name' => 'max_price', 'placeholder' => '∞']
                ]
            ];
        }

        if (!isset($config['brand'])) {
            $brands = Product::where('category_id', $id)
                ->pluck('name')
                ->map(function ($name) {
                    return explode(' ', $name)[0];
                })
                ->unique()
                ->values()
                ->toArray();

            if (!empty($brands)) {
                $config['brand'] = [
                    'label' => 'Hãng sản xuất',
                    'type' => 'checkbox',
                    'inputName' => 'brand[]',
                    'options' => $brands
                ];
            }
        }

        return response()->json($config);
    }
}
