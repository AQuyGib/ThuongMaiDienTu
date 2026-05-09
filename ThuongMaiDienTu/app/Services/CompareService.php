<?php

namespace App\Services;

use Illuminate\Support\Collection;

class CompareService
{
    /**
     * Label map cho các key thông số kỹ thuật
     */
    private array $labelMap = [
        'cpu'        => 'Vi xử lý (CPU)',
        'cpu_chip'   => 'Vi xử lý (CPU)',
        'ram'        => 'RAM',
        'ram_capacity' => 'RAM',
        'rom'        => 'Bộ nhớ trong',
        'gpu'        => 'Card đồ họa (GPU)',
        'screen'     => 'Màn hình',
        'screen_size' => 'Kích thước màn hình',
        'os'         => 'Hệ điều hành',
        'camera'     => 'Camera',
        'battery'    => 'Pin',
        'sim'        => 'SIM',
        'connection' => 'Kết nối',
        'weight'     => 'Trọng lượng',
        'dimensions' => 'Kích thước',
        'material'   => 'Chất liệu',
        'water_resistance' => 'Chống nước',
        'bluetooth'  => 'Bluetooth',
        'wifi'       => 'Wi-Fi',
        'nfc'        => 'NFC',
        'charging'   => 'Sạc',
        'refresh_rate' => 'Tần số quét',
        'resolution' => 'Độ phân giải',
        'storage'    => 'Lưu trữ',
        'capacity'   => 'Dung tích',
        'power'      => 'Công suất',
        'energy_rating' => 'Xếp hạng năng lượng',
        'compressor'  => 'Máy nén',
        'inverter'    => 'Inverter',
    ];

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
        $dbSpecColumns = ['cpu_chip', 'ram_capacity', 'battery', 'screen_size'];
        foreach ($products as $product) {
            $dbSpec = $product->productSpecifications->first();
            if ($dbSpec) {
                foreach ($dbSpecColumns as $col) {
                    if (!empty($dbSpec->{$col})) {
                        $allKeys[] = $col;
                    }
                }
            }
        }

        $allKeys = array_unique($allKeys);

        // 3. Sắp xếp: ưu tiên các key phổ biến trước
        $priorityOrder = ['cpu', 'cpu_chip', 'ram', 'ram_capacity', 'rom', 'storage', 'screen', 'screen_size', 'gpu', 'camera', 'battery', 'os', 'sim', 'connection'];
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

                // Fallback sang bảng product_specifications
                if (empty($value) && in_array($key, $dbSpecColumns)) {
                    $dbSpec = $product->productSpecifications->first();
                    if ($dbSpec && !empty($dbSpec->{$key})) {
                        $value = $dbSpec->{$key};
                    }
                }

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
        return $this->labelMap[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
}
