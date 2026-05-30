<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class ProductFilterController
 * 
 * Bộ điều khiển (Controller) phụ trách xử lý bộ lọc sản phẩm nâng cao.
 * Cung cấp API lọc sản phẩm thời gian thực qua AJAX (trả về HTML Partial)
 * và API lấy cấu hình bộ lọc động (Dynamic Filters) theo từng Danh mục sản phẩm.
 */
class ProductFilterController extends Controller
{
    /**
     * Khởi tạo Controller với ProductFilterService được inject vào.
     * 
     * @param ProductFilterService $productFilterService Dịch vụ lọc sản phẩm
     */
    public function __construct(private readonly ProductFilterService $productFilterService)
    {
    }

    /**
     * Xử lý lọc sản phẩm nâng cao thông qua yêu cầu AJAX gửi từ Frontend.
     * 
     * @param Request $request Chứa các tham số lọc như category_id, min_price, max_price, brand, specifications...
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function filterProducts(Request $request)
    {
        try {
            // 1. Gọi service để thực hiện truy vấn và lọc dữ liệu sản phẩm, phân trang tối đa 12 bản ghi
            $products = $this->productFilterService->filter($request->all(), 12);

            // 2. Trả về View dạng Partial (chỉ chứa lưới sản phẩm sản phẩm) để JS tiến hành chèn đè vào DOM mà không cần reload trang
            return view('frontend.products.partials.product_grid', compact('products'))->render();
        } catch (\Exception $e) {
            // Ghi nhật ký lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi xảy ra trong ProductFilterController@filterProducts: ' . $e->getMessage());
            return response('Có lỗi xảy ra trong quá trình lọc sản phẩm. Vui lòng thử lại.', 500);
        }
    }

    /**
     * API trả về cấu hình bộ lọc động tương ứng với từng Danh mục sản phẩm.
     * Lưu trữ cấu hình trong Cache để tăng tốc độ tải trang và giảm tải cho Database.
     * 
     * @param int $id ID của Danh mục sản phẩm
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getCategoryFilters($id)
    {
        // Khóa lưu cache cho danh mục tương ứng
        $cacheKey = "category_filters_{$id}";

        // Lưu cache cấu hình bộ lọc trong 3600 giây (1 giờ)
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($id) {
            // 1. Tìm thông tin danh mục tương ứng
            $category = Category::find($id);
            if (!$category) {
                return [];
            }

            // 2. Lấy cấu hình cột `filter_config` được admin thiết lập riêng cho danh mục này
            $config = $category->filter_config ?? [];
            if (is_string($config)) {
                $config = json_decode($config, true) ?: [];
            }

            // 3. THIẾT LẬP MẶC ĐỊNH: Luôn luôn có cấu hình lọc theo Khoảng giá (Price Range)
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

            // 4. THIẾT LẬP MẶC ĐỊNH: Lấy danh sách các thương hiệu (Hãng sản xuất) thực tế có sản phẩm nằm trong danh mục này
            if (!isset($config['brand'])) {
                $brands = Product::where('category_id', $id)
                    ->whereNotNull('brand')
                    ->distinct()
                    ->pluck('brand')
                    ->sort()
                    ->values()
                    ->toArray();

                // Nếu danh mục có chứa sản phẩm có thương hiệu, tự động thêm bộ lọc thương hiệu dạng Checkbox
                if (!empty($brands)) {
                    $config['brand'] = [
                        'label' => 'Hãng sản xuất',
                        'type' => 'checkbox',
                        'inputName' => 'brand[]',
                        'options' => $brands
                    ];
                }
            }

            return $config;
        });
    }
}
