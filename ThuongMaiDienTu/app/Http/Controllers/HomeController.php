<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

use App\Services\FlashSaleService;

class HomeController extends Controller
{
    public function __construct(private readonly FlashSaleService $flashSaleService)
    {
    }

    public function index()
    {
        // 1. Lấy danh mục cha (Kèm danh mục con)
        $categories = Category::whereNull('parent_id')->with('children')->get();

        // 2. Lấy tất cả Flash Sale đang hoạt động
        $activeFlashSales = $this->flashSaleService->getActiveFlashSales()->map(function ($sale) {
            $sale->mapped_products = $sale->products()
                ->with('product.category')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($fsp) {
                    $product = $fsp->product;
                    $product->flash_sale_price = $fsp->sale_price;
                    $product->flash_sale_stock = $fsp->stock_limit - $fsp->sold_quantity;
                    $product->flash_sale_sold = $fsp->sold_quantity;
                    $product->flash_sale_limit = $fsp->stock_limit;
                    return $product;
                });
            return $sale;
        });

        // Giữ lại fallback cũ cho flashSaleProducts (nếu không có chiến dịch nào)
        $flashSaleProducts = collect();
        if ($activeFlashSales->isEmpty()) {
            $flashSaleProducts = Product::with('category')
                ->whereNotNull('old_price')
                ->whereColumn('old_price', '>', 'base_price')
                ->orderByRaw('(old_price - base_price) DESC')
                ->take(10)
                ->get();
        }

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
            'activeFlashSales',
            'flashSaleProducts',
            'phoneProducts',
            'laptopProducts',
            'latestArticles'
        ));
    }
}
