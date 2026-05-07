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
    public function index()
    {
        $products = Product::paginate(12);
        $categories = Category::all();
        
        return view('frontend.products.index', compact('products', 'categories'));
    }

    /**
     * Hiển thị chi tiết sản phẩm.
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        
        return view('frontend.products.show', compact('product'));
    }
}
