<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\FlashSaleService;

/**
 * Class HomeController
 * 
 * Bộ điều khiển (Controller) quản lý trang chủ (Homepage) của website DienMayPRO.
 * Có nhiệm vụ tải danh mục sản phẩm đa cấp, tính toán chiến dịch Flash Sale đang hoạt động,
 * hiển thị các vùng nội dung động (HomeSections) được thiết lập bởi Admin, và hiển thị bài viết tin tức mới nhất.
 */
class HomeController extends Controller
{
    /**
     * Khởi tạo Controller với FlashSaleService để tính toán giá và lượng tồn kho ưu đãi.
     * 
     * @param FlashSaleService $flashSaleService
     */
    public function __construct(private readonly FlashSaleService $flashSaleService)
    {
    }

    /**
     * Hiển thị trang chủ website.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 1. TRUY VẤN DANH MỤC SẢN PHẨM:
        // Lấy toàn bộ các danh mục cha cao nhất (parent_id = null) và tự động tải kèm theo (eager load) danh mục con cấp dưới.
        $categories = Category::whereNull('parent_id')->with('children')->get();

        // 2. XỬ LÝ SẢN PHẨM FLASH SALE:
        // Gọi service lấy danh sách các chiến dịch Flash Sale đang diễn ra (chưa kết thúc và đã bắt đầu).
        $activeFlashSales = $this->flashSaleService->getActiveFlashSales()->map(function ($sale) {
            // Với mỗi chiến dịch, truy vấn danh sách sản phẩm đăng ký tham gia và đang kích hoạt (is_active)
            $sale->mapped_products = $sale->products()
                ->with('product.category')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($fsp) {
                    $product = $fsp->product;
                    // Thiết lập tạm thời các thông số Flash Sale lên đối tượng product để View Blade hiển thị trực quan
                    $product->flash_sale_price = $fsp->sale_price; // Giá bán Flash Sale ưu đãi
                    $product->flash_sale_stock = $fsp->stock_limit - $fsp->sold_quantity; // Số lượng tồn kho Flash Sale còn lại
                    $product->flash_sale_sold = $fsp->sold_quantity; // Số lượng đã bán
                    $product->flash_sale_limit = $fsp->stock_limit; // Hạn mức tồn kho Flash Sale ban đầu
                    return $product;
                });
            return $sale;
        });

        // TẠO FALLBACK (DỰ PHÒNG CHỈ TIÊU KHUYẾN MÃI):
        // Nếu hiện tại hệ thống không chạy chiến dịch Flash Sale nào, tự động lấy 10 sản phẩm có phần trăm giảm giá sâu nhất (old_price > base_price) để lấp đầy khung hiển thị.
        $flashSaleProducts = collect();
        if ($activeFlashSales->isEmpty()) {
            $flashSaleProducts = Product::with('category')
                ->whereNotNull('old_price')
                ->whereColumn('old_price', '>', 'base_price')
                ->orderByRaw('(old_price - base_price) DESC')
                ->take(10)
                ->get();
        }

        // 3. TẢI CÁC KHUNG HIỂN THỊ DỮ LIỆU ĐỘNG (HOME SECTIONS):
        // Lấy các khu vực hiển thị sản phẩm được Admin tùy biến riêng cho trang chủ (bật status = true).
        // Sắp xếp thứ tự hiển thị ưu tiên theo cột order.
        $homeSections = \App\Models\HomeSection::where('status', true)
            ->with(['category.children', 'products.category']) // Eager load thông tin danh mục con và sản phẩm để tránh lỗi N+1 Query
            ->orderBy('order', 'asc')
            ->get()
            ->map(function($section) {
                if ($section instanceof \App\Models\HomeSection) {
                    // Nếu khung hiển thị được chọn theo kiểu Danh mục cụ thể (type = category)
                    if ($section->type === 'category' && $section->category) {
                        // Gom toàn bộ ID danh mục con và bản thân danh mục cha để truy vấn toàn bộ sản phẩm thuộc nhánh danh mục đó
                        $catIds = $section->category->children->pluck('category_id')->push($section->category_id);
                        $section->products_list = Product::with('category')
                            ->whereIn('category_id', $catIds)
                            ->orderBy('product_id', 'desc')
                            ->take($section->limit)
                            ->get();
                    } 
                    // Nếu khung hiển thị được chọn thủ công các sản phẩm cụ thể bởi admin (type = manual)
                    elseif ($section->type === 'manual') {
                        // Lấy danh sách sản phẩm được chọn thủ công, giới hạn số lượng theo cấu hình limit
                        $section->products_list = $section->products->take($section->limit);
                    } 
                    // Nếu khung hiển thị được chọn mặc định lấy sản phẩm mới nhất hệ thống (type = latest)
                    else {
                        $section->products_list = Product::with('category')
                            ->orderBy('product_id', 'desc')
                            ->take($section->limit)
                            ->get();
                    }
                }
                return $section;
            });

        // 4. TIN TỨC & BÀI VIẾT:
        // Lấy danh sách 5 bài viết mới nhất đã được phê duyệt đăng tải để hiển thị ở góc tin tức công nghệ/Lifestyle trang chủ.
        $latestArticles = \App\Models\Article::where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 5. Render và hiển thị trang chủ Blade Template với các thông số tương ứng
        return view('home', compact(
            'categories',
            'activeFlashSales',
            'flashSaleProducts',
            'homeSections',
            'latestArticles'
        ));
    }
}
