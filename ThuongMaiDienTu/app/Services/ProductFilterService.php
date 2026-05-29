<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductFilterService
{
    public function filter(array $params, int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['category', 'variants', 'productSpecifications'])
            ->whereNull('deleted_at');

        $query->filterCategory($params['category_id'] ?? null, $params['category_slug'] ?? null)
            ->finalPriceBetween($params['min_price'] ?? null, $params['max_price'] ?? null)
            ->searchKeyword($params['q'] ?? null)
            ->sortBy($params['sort'] ?? 'newest');

        if (!empty($params['brand'])) {
            $brands = is_array($params['brand']) ? $params['brand'] : explode(',', $params['brand']);
            $query->whereIn('brand', array_filter($brands));
        }

        $category = $this->resolveCategory($params['category_id'] ?? null, $params['category_slug'] ?? null);
        $specs = $this->extractSpecsParams($params);
        $specs = $this->normalizeSpecsForCategory($specs, $category?->filter_config ?? []);

        $needs = $this->normalizeArrayParam($params['needs'] ?? null);
        if (!empty($needs)) {
            $this->applyNeedsRules($query, $needs);
        }

        if (($params['eco_friendly'] ?? null) === '1') {
            $query->whereJsonContains('specifications->eco_friendly', 'Yes');
        }

        if (($params['high_repairability'] ?? null) === '1') {
            $query->where('rating', '>=', 4.5);
        }

        // Lọc sản phẩm còn hàng (có ít nhất 1 inventory_item In_Stock)
        if (($params['in_stock'] ?? null) === '1') {
            $query->whereHas('variants', function ($vq) {
                $vq->whereHas('inventoryItems', function ($iq) {
                    $iq->where('status', 'In_Stock');
                });
            });
        }

        // Lọc hàng mới về (top 20% product_id cao nhất)
        if (($params['new_arrival'] ?? null) === '1') {
            $maxId = DB::table('products')->whereNull('deleted_at')->max('product_id');
            $minId = DB::table('products')->whereNull('deleted_at')->min('product_id');
            if ($maxId && $minId) {
                $threshold = $maxId - (int)(($maxId - $minId) * 0.2);
                $query->where('product_id', '>=', $threshold);
            }
        }

        $query->filterBySpecs($specs);

        return $query->paginate($perPage)->withQueryString();
    }

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

    private function extractSpecsParams(array $params): array
    {
        $nonSpecKeys = [
            'category_id', 'category_slug', 'min_price', 'max_price', 'q', 'sort',
            'needs', 'eco_friendly', 'high_repairability', 'page', 'brand',
            'in_stock', 'new_arrival'
        ];

        $specs = array_diff_key($params, array_flip($nonSpecKeys));

        return array_filter($specs, static function ($val) {
            return $val !== null && $val !== '' && $val !== [];
        });
    }

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
            if (($config['enabled'] ?? true) === false) {
                continue;
            }
            if (($config['type'] ?? null) === 'meta') {
                continue;
            }
            $allowedKeys[] = $key;
        }

        if (empty($allowedKeys)) {
            return $specs;
        }

        return array_intersect_key($specs, array_flip($allowedKeys));
    }

    private function applyNeedsRules(Builder $query, array $needs): void
    {
        foreach ($needs as $need) {
            $ruleQuery = DB::table('filter_rules');

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

            $conditions = $this->normalizeJson($rule->conditions ?? null);
            $this->applyConditions($query, $conditions);
        }
    }

    private function applyConditions(Builder $query, array $conditions): void
    {
        if (isset($conditions['price_max'])) {
            $query->where('base_price', '<=', (int) $conditions['price_max']);
        }

        if (isset($conditions['price_min'])) {
            $query->where('base_price', '>=', (int) $conditions['price_min']);
        }

        if (isset($conditions['ram_gb_min'])) {
            $query->where('ram_gb', '>=', (int) $conditions['ram_gb_min']);
        }

        if (isset($conditions['rating_min'])) {
            $query->where('rating', '>=', (float) $conditions['rating_min']);
        }

        if (!empty($conditions['spec_contains']) && is_array($conditions['spec_contains'])) {
            foreach ($conditions['spec_contains'] as $specKey => $specValues) {
                $query->where(function (Builder $subQuery) use ($specKey, $specValues) {
                    foreach ((array) $specValues as $specValue) {
                        $subQuery->orWhereJsonContains('specifications->' . $specKey, $specValue);
                    }
                });
            }
        }
    }

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

    private function filterRulesHasColumns(array $columns): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = collect(DB::select('SHOW COLUMNS FROM filter_rules'))->pluck('Field')->all();
        }

        return empty(array_diff($columns, $cache));
    }
}
