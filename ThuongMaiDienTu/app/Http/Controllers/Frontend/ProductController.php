<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm.
     */
    public function index($categorySlug = null)
    {
        $currentCategory = null;

        if ($categorySlug) {
            $currentCategory = Category::where('slug', $categorySlug)->first();
        } elseif (request('category_id')) {
            $currentCategory = Category::find(request('category_id'));
        }

        $query = Product::whereNull('deleted_at');
        if ($currentCategory) {
            $query->where('category_id', $currentCategory->category_id);
        }

        $products = $query->paginate(12);
        $categories = Category::whereNull('parent_id')->get();

        return view('frontend.products.index', compact('products', 'categories', 'currentCategory'));
    }

    /**
     * Hiển thị chi tiết sản phẩm.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'productSpecifications', 'variants'])->findOrFail($id);

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('product_id', '<>', $product->product_id)
            ->take(6)
            ->get();

        return view('frontend.products.show', compact('product', 'relatedProducts'));
    }
}
