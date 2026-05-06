<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductFilterController extends Controller
{
    public function filterProducts(Request $request)
    {
        // Bắt đầu với query gốc, Eager Load sẵn các quan hệ cần thiết
        $query = Product::with(['category', 'variants', 'specifications'])->whereNull('deleted_at');

        // 1. Lọc theo Danh mục (nếu có)
        $query->when($request->filled('category_id'), function ($q) use ($request) {
            $q->where('category_id', $request->category_id);
        });

        // 2. Lọc theo Khoảng giá
        $query->when($request->filled('min_price'), function ($q) use ($request) {
            $q->where('base_price', '>=', $request->min_price);
        });
        $query->when($request->filled('max_price'), function ($q) use ($request) {
            $q->where('base_price', '<=', $request->max_price);
        });

        // 3. Lọc theo RAM (Truy vấn vào bảng quan hệ Product_Specifications)
        $query->when($request->filled('ram'), function ($q) use ($request) {
            // whereHas giúp tìm các Product có bảng con thỏa mãn điều kiện
            $q->whereHas('specifications', function ($subQuery) use ($request) {
                $ramValues = is_array($request->ram) ? $request->ram : [$request->ram];
                $subQuery->whereIn('ram_capacity', $ramValues);
            });
        });

        // 4. Lọc theo Dung lượng ROM (Truy vấn vào bảng quan hệ Product_Variants)
        $query->when($request->filled('rom'), function ($q) use ($request) {
            $q->whereHas('variants', function ($subQuery) use ($request) {
                $romValues = is_array($request->rom) ? $request->rom : [$request->rom];
                $subQuery->whereIn('rom_capacity', $romValues);
            });
        });

        // Thực thi và phân trang
        $products = $query->orderBy('product_id', 'desc')->paginate(12);

        // Trả về View dạng Partial (Chỉ chứa HTML của lưới sản phẩm) để AJAX render
        return view('frontend.products.partials.product_grid', compact('products'))->render();
    }
}
