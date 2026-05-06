<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductFilterController extends Controller
{
    /**
     * Xử lý lọc sản phẩm nâng cao qua AJAX
     */
    public function filterProducts(Request $request)
    {
        try {
            // Bắt đầu với query gốc, Eager Load sẵn các quan hệ cần thiết để tránh N+1
            $query = Product::with(['category', 'variants', 'specifications'])->whereNull('deleted_at');

            // 1. Lọc theo Danh mục (Hỗ trợ cả ID và Slug)
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            } elseif ($request->filled('category_slug')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category_slug);
                });
            }

            // 2. Lọc theo Khoảng giá
            if ($request->filled('min_price')) {
                $query->where('base_price', '>=', (float)$request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('base_price', '<=', (float)$request->max_price);
            }

            // 3. Lọc theo RAM (Truy vấn vào bảng Product_Specifications)
            if ($request->filled('ram')) {
                $query->whereHas('specifications', function ($subQuery) use ($request) {
                    $ramValues = is_array($request->ram) ? $request->ram : [$request->ram];
                    $subQuery->whereIn('ram_capacity', $ramValues);
                });
            }

            // 4. Lọc theo ROM (Truy vấn vào bảng Product_Variants)
            if ($request->filled('rom')) {
                $query->whereHas('variants', function ($subQuery) use ($request) {
                $romValues = is_array($request->rom) ? $request->rom : [$request->rom];
                $subQuery->whereIn('rom_capacity', $romValues);
                });
            }

            // 5. Lọc theo từ khóa tìm kiếm
            if ($request->filled('q')) {
                $keyword = $request->q;
                $query->where(function($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('seo_description', 'LIKE', "%{$keyword}%");
                });
            }

            // 6. Sắp xếp (Sorting)
            $sort = $request->get('sort', 'newest');
            switch ($sort) {
                case 'price_asc':
                    $query->orderBy('base_price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('base_price', 'desc');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                default:
                    $query->orderBy('product_id', 'desc');
                    break;
            }

            // Thực thi và phân trang
            $products = $query->paginate(12)->withQueryString();

            // Trả về View dạng Partial để AJAX render
            return view('frontend.products.partials.product_grid', compact('products'))->render();

        } catch (\Exception $e) {
            Log::error('Error in ProductFilterController@filterProducts: ' . $e->getMessage());
            return response('Có lỗi xảy ra trong quá trình lọc sản phẩm. Vui lòng thử lại.', 500);
        }
    }
}
