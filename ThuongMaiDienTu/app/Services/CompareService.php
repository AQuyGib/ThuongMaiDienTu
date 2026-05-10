<?php

namespace App\Services;

use Illuminate\Support\Collection;

class CompareService
{
    

    /**
     * Xây dựng dữ liệu so sánh với cờ is_different cho từng thuộc tính.
     *
     * @param Collection $products Danh sách sản phẩm cần so sánh
     * @return array Mảng các dòng so sánh [key, label, values[], is_different]
     */
    public function buildComparisonData(Collection $products): array
    {
        if ($products->count() < 2) {
            return [];
        }

        // 1. Thu thập tất cả spec keys từ cột JSON `specifications` trên bảng products
        $allKeys = [];
        $productSpecs = [];

        foreach ($products as $product) {
            $rawSpecs = $product->getRawOriginal('specifications') ?? '{}';
            $specs = is_string($rawSpecs) ? (json_decode($rawSpecs, true) ?? []) : [];
            $productSpecs[$product->product_id] = $specs;
            $allKeys = array_merge($allKeys, array_keys($specs));
        }

        // 2. Thêm các key từ bảng product_specifications (nếu có data)
        

        $allKeys = array_unique($allKeys);

        // 3. Sắp xếp: ưu tiên các key phổ biến trước
        $priorityOrder = config('specs.priority', []);
        usort($allKeys, function ($a, $b) use ($priorityOrder) {
            $posA = array_search($a, $priorityOrder);
            $posB = array_search($b, $priorityOrder);
            if ($posA === false) $posA = 999;
            if ($posB === false) $posB = 999;
            return $posA - $posB;
        });

        // 4. Duyệt từng key, thu thập giá trị và cắm cờ is_different
        $result = [];
        foreach ($allKeys as $key) {
            $values = [];
            foreach ($products as $product) {
                $value = null;

                // Ưu tiên lấy từ JSON specs
                if (isset($productSpecs[$product->product_id][$key])) {
                    $val = $productSpecs[$product->product_id][$key];
                    $value = is_array($val) ? implode(', ', $val) : (string) $val;
                }

                // Fallback removed; rely on JSON specifications only
                // If needed, consider extending JSON schema instead of DB fallback

                $values[] = $value ?? '—';
            }

            // So sánh: normalize rồi kiểm tra unique
            $normalized = array_map(fn($v) => mb_strtolower(trim($v)), $values);
            $unique = array_unique($normalized);

            $result[] = [
                'key'          => $key,
                'label'        => $this->getLabel($key),
                'values'       => $values,
                'is_different' => count($unique) > 1,
            ];
        }

        return $result;
    }

    /**
     * Lấy nhãn hiển thị cho key thông số
     */
    private function getLabel(string $key): string
    {
        // Try translation from language files (resources/lang/vi/specs.php). Fallback to human readable title.
        $translated = __("specs.$key");
        if ($translated !== "specs.$key") {
            return $translated;
        }
        return ucfirst(str_replace('_', ' ', $key));
    }
}
