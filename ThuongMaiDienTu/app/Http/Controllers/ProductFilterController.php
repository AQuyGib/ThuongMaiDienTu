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
            $query = Product::with(['category', 'variants', 'productSpecifications'])->whereNull('deleted_at');

            // Áp dụng các Scope lọc
            $query->filterCategory($request->category_id, $request->category_slug)
                ->finalPriceBetween($request->min_price, $request->max_price)
                ->searchKeyword($request->q)
                ->sortBy($request->get('sort', 'newest'));

            // Lấy tất cả thông số gửi lên từ URL thay vì hardcode ram, rom
            // Bỏ các params không phải là thông số kỹ thuật
            $nonSpecKeys = ['category_id', 'category_slug', 'min_price', 'max_price', 'q', 'sort', 'needs', 'eco_friendly', 'high_repairability', 'page'];
            $specs = $request->except($nonSpecKeys);

            // Lọc bỏ các mảng/giá trị rỗng
            $specs = array_filter($specs, function ($val) {
                return !empty($val);
            });

            // Xử lý các tag nhu cầu đặc biệt (Độc quyền DIENMAY PRO)
            if ($request->filled('needs')) {
                // Ví dụ: tag 'gaming' -> cần RAM >= 16GB, GPU mạnh, ...
                // Ở đây mô phỏng bằng việc map tag sang các điều kiện cấu hình
                $needs = is_array($request->needs) ? $request->needs : explode(',', $request->needs);
                if (in_array('gaming', $needs)) {
                    $query->where(function ($q) {
                        $q->orWhereJsonContains('specifications->ram', '16GB')
                            ->orWhereJsonContains('specifications->ram', '32GB')
                            ->orWhereJsonContains('specifications->ram', '64GB');
                    });
                }
                if (in_array('student', $needs)) {
                    $query->where('base_price', '<=', 15000000);
                }
            }

            // Lọc Kinh tế tuần hoàn (Repairability, Eco-friendly)
            if ($request->filled('eco_friendly') && $request->eco_friendly == '1') {
                $query->whereJsonContains('specifications->Eco-friendly', 'Yes'); // Mô phỏng
            }
            if ($request->filled('high_repairability') && $request->high_repairability == '1') {
                $query->where('rating', '>=', 4.5); // Tạm thời dùng rating mô phỏng điểm dễ sửa chữa
            }

            $query->filterBySpecs($specs);

            // Thực thi và phân trang
            $products = $query->paginate(12)->withQueryString();

            // Trả về View dạng Partial để AJAX render
            return view('frontend.products.partials.product_grid', compact('products'))->render();

        } catch (\Exception $e) {
            Log::error('Error in ProductFilterController@filterProducts: ' . $e->getMessage());
            return response('Có lỗi xảy ra trong quá trình lọc sản phẩm. Vui lòng thử lại.', 500);
        }
    }

    /**
     * API: Trả về cấu hình bộ lọc động theo Danh mục
     */
    public function getCategoryFilters($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([]);
        }

        // Lấy config từ database (cột filter_config kiểu JSON)
        $config = $category->filter_config ?? [];
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        // Luôn có bộ lọc giá mặc định nếu chưa có
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

        return response()->json($config);
    }
}
