<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lớp Dịch Vụ Dịch Thuật (TranslationService)
 * Hỗ trợ dịch tự động thông qua Google Cloud API, tích hợp bộ từ điển ghi đè tĩnh (Static override dictionary)
 * và tự động fallback sang Google GTX API miễn phí nếu không cấu hình API Key trong file .env.
 */
class TranslationService
{
    /**
     * Dịch một chuỗi văn bản đơn lẻ.
     *
     * @param string $text Chuỗi cần dịch
     * @param string $sourceLocale Ngôn ngữ gốc (mặc định: 'vi')
     * @param string $targetLocale Ngôn ngữ đích cần dịch sang (mặc định: 'en')
     * @return string Bản dịch trả về hoặc chuỗi gốc nếu gặp lỗi
     */
    public function translate(string $text, string $sourceLocale = 'vi', string $targetLocale = 'en'): string
    {
        $text = trim($text);

        // Bỏ qua nếu chuỗi rỗng hoặc ngôn ngữ gốc trùng ngôn ngữ đích
        if ($text === '' || $sourceLocale === $targetLocale) {
            return $text;
        }

        // BỘ TỪ ĐIỂN GHI ĐÈ BẢN DỊCH TĨNH:
        // Đảm bảo các thuật ngữ chuyên ngành Quản trị/Thương mại điện tử hiển thị chính xác theo chuẩn UI/UX quốc tế.
        if ($sourceLocale === 'vi' && $targetLocale === 'en') {
            $dictionary = [
                'bảng điều khiển' => 'Dashboard',
                'bảng điều khiển hệ thống' => 'System Dashboard',
                'sổ quỹ & thu chi' => 'Cashbook & Expenses',
                'phiếu sửa chữa' => 'Repair Tickets',
                'hóa đơn dịch vụ' => 'Service Invoices',
                'tùy biến giao diện' => 'Theme Customization',
                'quản lý trang chủ' => 'Home Management',
                'nhật ký hoạt động' => 'Activity Logs',
                'cài đặt hệ thống' => 'System Settings',
                'điều chuyển kho' => 'Warehouse Transfer',
                'đổi thưởng' => 'Rewards',
                'chưa kích hoạt' => 'Not activated',
                'còn bảo hành' => 'Still under warranty',
                'hết hạn bảo hành' => 'Warranty expired',
                'tạm dừng bảo hành' => 'Warranty paused',
                'từ chối bảo hành' => 'Warranty rejected',
                'quản lý kho' => 'Inventory Management',
                'bài viết & cms' => 'Articles & CMS',
                'nhà cung cấp' => 'Suppliers',
                'danh mục' => 'Categories',
                'đơn hàng' => 'Orders',
                'khách hàng' => 'Customers',
                'sản phẩm' => 'Products',
                'tổng quan' => 'Overview',
                'kinh doanh' => 'Business',
                'sản phẩm & kho' => 'Products & Inventory',
                'thiết lập' => 'Settings',
                'tài khoản' => 'Accounts',
                'thống kê kpi' => 'KPI Statistics',
                'khởi tạo sidebar...' => 'Initializing Sidebar...',
                'thông báo' => 'Notifications',
                'tạo mới' => 'Create New',
                'xem tất cả đơn hàng' => 'View All Orders',
                'chưa có đơn hàng nào.' => 'No orders yet.',
                'đơn hàng mới nhất' => 'Latest Orders',
                'danh sách 5 giao dịch gần đây nhất trên hệ thống' => 'List of the 5 most recent transactions on the system',
                'mã đơn' => 'Order Code',
                'tổng thanh toán' => 'Total Payment',
                'trạng thái' => 'Status',
                'thành công' => 'Success',
                'lỗi hệ thống' => 'System Error',
                'tổng thu nhập' => 'Total Income',
                'tổng chi phí' => 'Total Expenses',
                'thêm sản phẩm' => 'Add Product',
                'quản lý user' => 'Manage Users',
                'nhập kho' => 'Purchase Orders',
                'sổ quỹ' => 'Cashbook',
                'chào' => 'Hello',
            ];
            
            // So khớp không phân biệt chữ hoa chữ thường
            $lowerText = mb_strtolower($text);
            if (isset($dictionary[$lowerText])) {
                return $dictionary[$lowerText];
            }
        }

        // Gọi driver dịch được cấu hình trong file config/translatable.php
        return match (config('translatable.provider', 'google_api')) {
            'package' => $this->translateWithPackage($text, $sourceLocale, $targetLocale),
            default => $this->translateWithGoogleApi($text, $sourceLocale, $targetLocale),
        };
    }

    /**
     * Dịch nhiều chuỗi văn bản cùng một lúc (Array).
     */
    public function translateMany(array $texts, string $sourceLocale = 'vi', string $targetLocale = 'en'): array
    {
        $result = [];

        foreach ($texts as $key => $value) {
            $result[$key] = is_string($value)
                ? $this->translate($value, $sourceLocale, $targetLocale)
                : $value;
        }

        return $result;
    }

    /**
     * Dịch thông qua Google Cloud API chính thức.
     */
    protected function translateWithGoogleApi(string $text, string $sourceLocale, string $targetLocale): string
    {
        $config = config('translatable.google_api', []);

        // Nếu API Key để trống, tự động chuyển sang dùng bản Google GTX API miễn phí
        if (blank(data_get($config, 'api_key'))) {
            return $this->translateWithFreeGoogleApi($text, $sourceLocale, $targetLocale);
        }

        try {
            $request = Http::timeout((int) data_get($config, 'timeout', 20));
            
            // Trên localhost (XAMPP/Laragon), tắt xác minh SSL chứng chỉ tự ký để tránh lỗi HTTPS
            if (config('app.env') === 'local') {
                $request = $request->withoutVerifying();
            }

            $response = $request->get(
                data_get($config, 'endpoint'),
                [
                    'q' => $text,
                    'source' => $sourceLocale,
                    'target' => $targetLocale,
                    'format' => 'text',
                    'key' => data_get($config, 'api_key'),
                ]
            );

            if (! $response->successful()) {
                Log::warning('Google Translate API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $text;
            }

            return (string) data_get($response->json(), 'data.translations.0.translatedText', $text);
        } catch (\Throwable $e) {
            Log::error('TranslationService google_api error', [
                'message' => $e->getMessage(),
            ]);

            return $text;
        }
    }

    /**
     * Fallback: Dịch qua Google Translate API GTX (Miễn phí, không cần đăng ký API Key).
     */
    protected function translateWithFreeGoogleApi(string $text, string $sourceLocale, string $targetLocale): string
    {
        try {
            $request = Http::timeout(15);
            if (config('app.env') === 'local') {
                $request = $request->withoutVerifying();
            }

            $response = $request->get(
                'https://translate.googleapis.com/translate_a/single',
                [
                    'client' => 'gtx',
                    'sl' => $sourceLocale,
                    'tl' => $targetLocale,
                    'dt' => 't',
                    'q' => $text,
                ]
            );

            if (! $response->successful()) {
                Log::warning('Free Google Translate API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $text;
            }

            $data = $response->json();
            // Google single API trả về dạng mảng lồng nhau chứa từng phân đoạn câu
            if (is_array($data) && isset($data[0]) && is_array($data[0])) {
                $translated = '';
                foreach ($data[0] as $sentence) {
                    if (is_array($sentence) && isset($sentence[0])) {
                        $translated .= $sentence[0];
                    }
                }

                return trim($translated) !== '' ? $translated : $text;
            }

            return $text;
        } catch (\Throwable $e) {
            Log::error('TranslationService free_google_api error', [
                'message' => $e->getMessage(),
            ]);

            return $text;
        }
    }

    /**
     * Dịch thông qua thư viện PHP Package được cấu hình tùy chỉnh.
     */
    protected function translateWithPackage(string $text, string $sourceLocale, string $targetLocale): string
    {
        try {
            $class = config('translatable.package.class');

            if (! class_exists($class)) {
                return $text;
            }

            $translator = app($class);

            if (method_exists($translator, 'setSource')) {
                $translator->setSource($sourceLocale);
            }

            if (method_exists($translator, 'setTarget')) {
                $translator->setTarget($targetLocale);
            }

            if (method_exists($translator, 'translate')) {
                return (string) $translator->translate($text);
            }

            return $text;
        } catch (\Throwable $e) {
            Log::error('TranslationService package error', [
                'message' => $e->getMessage(),
            ]);

            return $text;
        }
    }
}
