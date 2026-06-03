<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class ProductFilterService
 * 
 * Dịch vụ cốt lõi xử lý logic lọc sản phẩm nâng cao ở tầng Backend.
 * Phân tích và xây dựng các câu truy vấn động (Eloquent Builder) dựa trên nhiều tiêu chí:
 * Danh mục, giá cả, từ khóa tìm kiếm, hãng sản xuất, nhu cầu sử dụng, trạng thái tồn kho, và các thông số kỹ thuật lưu dạng JSON.
 */
class ProductFilterService
{
    /**
     * Thực hiện lọc sản phẩm dựa trên các tham số gửi lên từ Client và trả về kết quả phân trang.
     *
     * @param array $params Mảng các tham số lọc gửi từ client (ví dụ: category_id, min_price, brand, needs...)
     * @param int $perPage Số lượng sản phẩm hiển thị trên mỗi trang (mặc định là 12)
     * @return LengthAwarePaginator Đối tượng phân trang của Laravel chứa danh sách sản phẩm thỏa mãn
     */
    public function filter(array $params, int $perPage = 12): LengthAwarePaginator
    {
        // Khởi tạo câu truy vấn Product, nạp sẵn quan hệ category, variants và specifications để tránh lỗi N+1 Query
        $query = Product::query()
            ->with(['category', 'variants', 'productSpecifications'])
            ->whereNull('deleted_at'); // Chỉ lấy các sản phẩm chưa bị xóa mềm

        // 1. Áp dụng các bộ lọc cơ bản thông qua Local Scopes định nghĩa trên Model Product:
        // - Lọc theo Danh mục (filterCategory)
        // - Lọc theo Khoảng giá thực tế sau khi tính khuyến mại (finalPriceBetween)
        // - Tìm kiếm theo từ khóa (searchKeyword)
        // - Sắp xếp kết quả (sortBy): Mới nhất, giá tăng dần, giá giảm dần, bán chạy...
        $query->filterCategory($params['category_id'] ?? null, $params['category_slug'] ?? null)
            ->finalPriceBetween($params['min_price'] ?? null, $params['max_price'] ?? null)
            ->searchKeyword($params['q'] ?? null)
            ->sortBy($params['sort'] ?? 'newest');

        // 2. Lọc theo danh sách Thương hiệu (Hãng sản xuất) nếu được chọn (Checkbox)
        if (!empty($params['brand'])) {
            // Chấp nhận cả tham số dạng mảng hoặc chuỗi phân tách bằng dấu phẩy
            $brands = is_array($params['brand']) ? $params['brand'] : explode(',', $params['brand']);
            $query->whereIn('brand', array_filter($brands));
        }

        // 3. Lấy thông tin danh mục hiện tại và phân tích lọc thông số kỹ thuật (Specifications)
        $category = $this->resolveCategory($params['category_id'] ?? null, $params['category_slug'] ?? null);
        $specs = $this->extractSpecsParams($params);
        // Loại bỏ các thuộc tính specifications không thuộc cấu hình cho phép lọc của danh mục này (tránh giả mạo tham số F12)
        $specs = $this->normalizeSpecsForCategory($specs, $category?->filter_config ?? []);

        // 4. Lọc theo Nhu cầu sử dụng (Needs) (Ví dụ: Học tập - văn phòng, Chơi game cấu hình cao...)
        $needs = $this->normalizeArrayParam($params['needs'] ?? null);
        if (!empty($needs)) {
            // Áp dụng các quy tắc lọc tương ứng với nhu cầu được cấu hình trong bảng `filter_rules`
            $this->applyNeedsRules($query, $needs);
        }

        // 5. Lọc sản phẩm Thân thiện với môi trường (Eco friendly) lưu trong specifications JSON
        if (($params['eco_friendly'] ?? null) === '1') {
            $query->whereJsonContains('specifications->eco_friendly', 'Yes');
        }

        // 6. Lọc sản phẩm Dễ sửa chữa (Đánh giá cao >= 4.5)
        if (($params['high_repairability'] ?? null) === '1') {
            $query->where('rating', '>=', 4.5);
        }

        // 7. Lọc sản phẩm còn hàng (Status của ít nhất 1 inventory_item trong các biến thể là 'In_Stock')
        if (($params['in_stock'] ?? null) === '1') {
            $query->whereHas('variants', function ($vq) {
                $vq->whereHas('inventoryItems', function ($iq) {
                    $iq->where('status', 'In_Stock');
                });
            });
        }

        // 8. Lọc hàng mới về (Định nghĩa là top 20% các sản phẩm có ID cao nhất trong hệ thống)
        if (($params['new_arrival'] ?? null) === '1') {
            $maxId = DB::table('products')->whereNull('deleted_at')->max('product_id');
            $minId = DB::table('products')->whereNull('deleted_at')->min('product_id');
            if ($maxId && $minId) {
                // Tính toán ngưỡng ID bắt đầu xem là hàng mới
                $threshold = $maxId - (int)(($maxId - $minId) * 0.2);
                $query->where('product_id', '>=', $threshold);
            }
        }

        // 9. Lọc theo ma trận thông số kỹ thuật (Specifications) sau khi đã làm sạch và hợp lệ hóa
        $query->filterBySpecs($specs);

        // 10. Thực hiện phân trang và đính kèm (preserve) các tham số query string trên URL để phân trang AJAX hoạt động chuẩn
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Tìm đối tượng Danh mục dựa trên ID hoặc Slug danh mục.
     */
    private function resolveCategory(mixed $categoryId, mixed $categorySlug): ?Category
    {
        if ($categoryId) {
            return Category::find($categoryId);
        }

        if ($categorySlug) {
            return Category::where('slug', $categorySlug)->first();
        }

        return null;
    }

    /**
     * Trích xuất các tham số lọc thông số kỹ thuật đặc thù (Specifications) ra khỏi mảng request.
     * Loại bỏ các tham số hệ thống hoặc bộ lọc mặc định như sort, page, q, min_price...
     */
    private function extractSpecsParams(array $params): array
    {
        $nonSpecKeys = [
            'category_id', 'category_slug', 'min_price', 'max_price', 'q', 'sort',
            'needs', 'eco_friendly', 'high_repairability', 'page', 'brand',
            'in_stock', 'new_arrival'
        ];

        // Loại bỏ các key thuộc tính chung, chỉ giữ lại các key thông số kỹ thuật
        $specs = array_diff_key($params, array_flip($nonSpecKeys));

        // Lọc bỏ các phần tử rỗng hoặc null
        return array_filter($specs, static function ($val) {
            return $val !== null && $val !== '' && $val !== [];
        });
    }

    /**
     * Chuẩn hóa tham số lọc dạng danh sách (ví dụ: checkbox gửi lên dạng mảng hoặc chuỗi phân tách dấu phẩy)
     */
    private function normalizeArrayParam(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), static fn ($v) => $v !== ''));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }

    /**
     * Bảo mật Server-side: Đối chiếu các thông số kỹ thuật gửi lên từ client với cấu hình `filter_config` của danh mục.
     * Chỉ cho phép lọc các thuộc tính kỹ thuật đã được cấu hình hoạt động (enabled) cho danh mục đó,
     * nhằm ngăn chặn việc chèn ép các tham số không tồn tại bằng F12 để phá hoại câu lệnh SQL.
     */
    private function normalizeSpecsForCategory(array $specs, array|string|null $filterConfig): array
    {
        if (is_string($filterConfig)) {
            $filterConfig = json_decode($filterConfig, true) ?: [];
        }

        if (!is_array($filterConfig) || empty($specs)) {
            return $specs;
        }

        $allowedKeys = [];
        foreach ($filterConfig as $key => $config) {
            if (!is_array($config)) {
                continue;
            }
            // Chỉ chấp nhận những thuộc tính đang được cấu hình kích hoạt
            if (($config['enabled'] ?? true) === false) {
                continue;
            }
            // Bỏ qua các cấu hình metadata không liên quan trực tiếp đến lọc specifications
            if (($config['type'] ?? null) === 'meta') {
                continue;
            }
            $allowedKeys[] = $key;
        }

        if (empty($allowedKeys)) {
            return $specs;
        }

        // Chỉ giữ lại các thông số nằm trong mảng allowedKeys được phép
        return array_intersect_key($specs, array_flip($allowedKeys));
    }

    /**
     * Áp dụng quy tắc lọc dựa theo Nhu cầu sử dụng (Needs) lấy từ cơ sở dữ liệu bảng `filter_rules`.
     */
    private function applyNeedsRules(Builder $query, array $needs): void
    {
        foreach ($needs as $need) {
            $ruleQuery = DB::table('filter_rules');

            // Hỗ trợ kiểm tra cấu trúc bảng động (tương thích các phiên bản migration cột khác nhau)
            if ($this->filterRulesHasColumns(['group_key', 'rule_key'])) {
                $ruleQuery->where('group_key', 'needs')->where('rule_key', $need);
            } else {
                $ruleQuery->where('group', 'needs')->where('key', $need);
            }

            $rule = $ruleQuery
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->first();

            if (!$rule) {
                continue;
            }

            // Giải mã cấu hình mảng điều kiện lọc được lưu trữ dưới dạng JSON
            $conditions = $this->normalizeJson($rule->conditions ?? null);
            // Áp dụng chi tiết điều kiện (Ví dụ: RAM >= 8GB, Giá <= 15tr...)
            $this->applyConditions($query, $conditions);
        }
    }

    /**
     * Giải dịch mảng điều kiện JSON của filter_rules thành các điều kiện WHERE trên Eloquent Query Builder.
     */
    private function applyConditions(Builder $query, array $conditions): void
    {
        // Điều kiện giá trần
        if (isset($conditions['price_max'])) {
            $query->where('base_price', '<=', (int) $conditions['price_max']);
        }

        // Điều kiện giá sàn
        if (isset($conditions['price_min'])) {
            $query->where('base_price', '>=', (int) $conditions['price_min']);
        }

        // Điều kiện RAM tối thiểu
        if (isset($conditions['ram_gb_min'])) {
            $query->where('ram_gb', '>=', (int) $conditions['ram_gb_min']);
        }

        // Điều kiện rating tối thiểu
        if (isset($conditions['rating_min'])) {
            $query->where('rating', '>=', (float) $conditions['rating_min']);
        }

        // Điều kiện chứa các thông số kỹ thuật đặc thù (ví dụ: spec_contains -> [ 'cpu' => ['Intel Core i5', 'Intel Core i7'] ])
        if (!empty($conditions['spec_contains']) && is_array($conditions['spec_contains'])) {
            foreach ($conditions['spec_contains'] as $specKey => $specValues) {
                $query->where(function (Builder $subQuery) use ($specKey, $specValues) {
                    foreach ((array) $specValues as $specValue) {
                        // Dùng OR WHERE JSON CONTAINS để tìm kiếm khớp giá trị trong mảng JSON specifications
                        $subQuery->orWhereJsonContains('specifications->' . $specKey, $specValue);
                    }
                });
            }
        }
    }

    /**
     * Chuẩn hóa giá trị JSON (Chuỗi JSON -> Mảng PHP)
     */
    private function normalizeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return json_decode($value, true) ?: [];
        }

        return [];
    }

    /**
     * Kiểm tra cấu trúc cột thực tế của bảng `filter_rules` để sinh query SQL tương thích ngược.
     */
    private function filterRulesHasColumns(array $columns): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = collect(DB::select('SHOW COLUMNS FROM filter_rules'))->pluck('Field')->all();
        }

        return empty(array_diff($columns, $cache));
    }
}
