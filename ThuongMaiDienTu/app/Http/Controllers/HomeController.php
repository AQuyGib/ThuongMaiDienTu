<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Danh mục cha (parent_id = null) kèm danh mục con
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->get();

        // Flash Sale: 10 sản phẩm có giá cũ (đang giảm giá), sắp theo % giảm
        $flashSaleProducts = Product::whereNotNull('old_price')
            ->whereColumn('old_price', '>', 'base_price')
            ->orderByRaw('(old_price - base_price) DESC')
            ->take(10)
            ->get();

        // Điện thoại nổi bật: 10 sản phẩm thuộc danh mục "Điện thoại"
        $catDienThoai = Category::where('name', 'Điện thoại')->first();
        $phoneProducts = $catDienThoai
            ? Product::where('category_id', $catDienThoai->category_id)
                ->orderBy('product_id', 'desc')
                ->take(10)
                ->get()
            : collect();

        // Laptop nổi bật: 5 sản phẩm thuộc danh mục "Laptop"
        $catLaptop = Category::where('name', 'Laptop')->first();
        $laptopProducts = $catLaptop
            ? Product::where('category_id', $catLaptop->category_id)
                ->orderBy('product_id', 'desc')
                ->take(5)
                ->get()
            : collect();

        // Góc Tin tức & Lifestyle: 5 bài viết mới nhất đã duyệt
        $latestArticles = \App\Models\Article::where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('home', compact(
            'categories',
            'flashSaleProducts',
            'phoneProducts',
            'laptopProducts',
            'latestArticles'
        ));
    }
}
