<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Gợi ý tìm kiếm (Search Suggestions)
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'products' => [],
                'categories' => []
            ]);
        }

        // Tìm kiếm sản phẩm
        $products = Product::whereNull('deleted_at')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhereHas('translations', function ($sub) use ($query) {
                      $sub->where('name', 'LIKE', "%{$query}%");
                  });
            })
            ->select('product_id', 'name', 'thumbnail', 'base_price', 'old_price')
            ->limit(5)
            ->get();

        // Tìm kiếm danh mục liên quan
        $categories = Category::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhereHas('translations', function ($sub) use ($query) {
                      $sub->where('name', 'LIKE', "%{$query}%");
                  });
            })
            ->select('category_id', 'name', 'slug')
            ->limit(3)
            ->get();

        return response()->json([
            'products' => $products,
            'categories' => $categories
        ]);
    }

    /**
     * Trang kết quả tìm kiếm chính
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        
        // Chuyển hướng sang ProductController@index với query 'q'
        return redirect()->route('products.index', ['q' => $query]);
    }

    /**
     * Lấy danh sách sản phẩm theo danh mục (AJAX)
     */
    public function getProductsByCategory($id)
    {
        $category = Category::with('children')->findOrFail($id);
        
        // Lấy toàn bộ ID danh mục bao gồm cả con
        $categoryIds = $category->children->pluck('category_id')->push($category->category_id);
        
        $products = Product::whereIn('category_id', $categoryIds)
            ->whereNull('deleted_at')
            ->orderBy('product_id', 'desc')
            ->limit(10)
            ->get();
            
        return view('partials.product_grid_items', compact('products'))->render();
    }
}
