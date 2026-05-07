<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Lấy danh mục cha (Kèm danh mục con)
        $categories = Category::whereNull('parent_id')->with('children')->get();

        // 2. Lấy sản phẩm Flash Sale (Kèm category để tránh N+1 Query)
        $flashSaleProducts = Product::with('category')
            ->whereNotNull('old_price')
            ->whereColumn('old_price', '>', 'base_price')
            ->orderByRaw('(old_price - base_price) DESC')
            ->take(10)
            ->get();

        // 3. Lấy linh động các danh mục muốn hiển thị ra trang chủ (Sử dụng slug để tránh hardcode name)
        
        // Lấy Điện thoại
        $catDienThoai = Category::where('slug', 'dien-thoai')->first();
        $phoneProducts = $catDienThoai 
            ? Product::with('category')->where('category_id', $catDienThoai->category_id)->orderBy('product_id', 'desc')->take(10)->get() 
            : collect();

        // Lấy Laptop
        $catLaptop = Category::where('slug', 'laptop')->first();
        $laptopProducts = $catLaptop 
            ? Product::with('category')->where('category_id', $catLaptop->category_id)->orderBy('product_id', 'desc')->take(5)->get() 
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
