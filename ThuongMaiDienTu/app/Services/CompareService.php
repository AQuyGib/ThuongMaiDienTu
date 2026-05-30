<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Class CompareService
 * 
 * Lớp dịch vụ (Service) chịu trách nhiệm xử lý nghiệp vụ so sánh thông số kỹ thuật của sản phẩm.
 * Nhiệm vụ chính là xây dựng ma trận so sánh từ thuộc tính specifications dạng JSON của các sản phẩm,
 * sắp xếp thứ tự hiển thị ưu tiên của các thông số kỹ thuật và đánh dấu các hàng có sự khác biệt.
 */
class CompareService
{
    /**
     * Xây dựng cấu trúc dữ liệu so sánh chi tiết và gắn cờ is_different cho từng thông số kỹ thuật.
     *
     * @param Collection $products Danh sách các đối tượng sản phẩm cần so sánh
     * @return array Mảng kết quả dạng: [ ['key' => ..., 'label' => ..., 'values' => [...], 'is_different' => true/false], ... ]
     */
    public function buildComparisonData(Collection $products): array
    {
        // 1. Thu thập tất cả các khóa thông số kỹ thuật (spec keys) từ cột JSON `specifications` của các sản phẩm
        $allKeys = [];
        $productSpecs = [];

        foreach ($products as $product) {
            // Lấy dữ liệu specifications thô từ DB
            $rawSpecs = $product->getRawOriginal('specifications');
            
            // Giải mã chuỗi JSON thành mảng PHP để tiện truy xuất
            if (is_string($rawSpecs)) {
                $specs = json_decode($rawSpecs, true) ?? [];
            } elseif (is_array($rawSpecs)) {
                $specs = $rawSpecs;
            } else {
                $specs = [];
            }
            
            $productSpecs[$product->product_id] = $specs;
            // Gộp tất cả các key thuộc tính kỹ thuật lại để xây dựng danh sách thuộc tính chung cần hiển thị
            $allKeys = array_merge($allKeys, array_keys($specs));
        }

        // Loại bỏ các key thuộc tính trùng lặp
        $allKeys = array_unique($allKeys);

        // 3. Sắp xếp thứ tự hiển thị thuộc tính: ưu tiên các key phổ biến trước theo cấu hình
        $priorityOrder = config('specs.priority', []);
        usort($allKeys, function ($a, $b) use ($priorityOrder) {
            $posA = array_search($a, $priorityOrder);
            $posB = array_search($b, $priorityOrder);
            // Nếu thuộc tính không nằm trong danh sách ưu tiên, gán vị trí rất lớn để đẩy xuống cuối
            if ($posA === false) $posA = 999;
            if ($posB === false) $posB = 999;
            return $posA - $posB;
        });

        // 4. Duyệt qua từng khóa thông số, thu thập giá trị của từng sản phẩm và kiểm tra sự khác biệt
        $result = [];
        foreach ($allKeys as $key) {
            $values = [];
            foreach ($products as $product) {
                $value = null;

                // Lấy giá trị thông số kỹ thuật từ mảng specs đã phân tích của sản phẩm
                if (isset($productSpecs[$product->product_id][$key])) {
                    $val = $productSpecs[$product->product_id][$key];
                    // Nếu là mảng (ví dụ: nhiều màu sắc hoặc cổng kết nối), gộp thành chuỗi cách nhau bởi dấu phẩy
                    $value = is_array($val) ? implode(', ', $val) : (string) $val;
                }

                // Nếu sản phẩm không có thuộc tính này, trả về dấu gạch ngang biểu thị không có thông tin
                $values[] = $value ?? '—';
            }

            // Kiểm tra tính khác biệt: Chuẩn hóa chữ thường, cắt khoảng trắng rồi tìm các giá trị độc nhất
            $normalized = array_map(fn($v) => mb_strtolower(trim($v)), $values);
            $unique = array_unique($normalized);

            // Thêm thông tin của dòng thông số kỹ thuật này vào kết quả trả về
            $result[] = [
                'key'          => $key,
                'label'        => $this->getLabel($key), // Nhãn hiển thị tiếng Việt/tiếng Anh thân thiện
                'values'       => $values,
                'is_different' => count($unique) > 1, // Đánh dấu true nếu có ít nhất 2 sản phẩm có thông số khác nhau
            ];
        }

        return $result;
    }

    /**
     * Lấy nhãn (Label) hiển thị thân thiện với người dùng của thông số kỹ thuật.
     * Cố gắng dịch thông qua file ngôn ngữ `resources/lang/vi/specs.php`.
     * Nếu không tìm thấy bản dịch, sẽ tự động chuyển đổi key thành dạng chữ thường viết hoa chữ cái đầu.
     * 
     * @param string $key
     * @return string
     */
    private function getLabel(string $key): string
    {
        // Thử tìm kiếm bản dịch trong file ngôn ngữ specs.php của Laravel
        $translated = __("specs.$key");
        if ($translated !== "specs.$key") {
            return $translated;
        }
        
        // Cần dự phòng: Thay thế dấu gạch dưới thành dấu cách và viết hoa chữ cái đầu (ví dụ: screen_size -> Screen size)
        return ucfirst(str_replace('_', ' ', $key));
    }
}
