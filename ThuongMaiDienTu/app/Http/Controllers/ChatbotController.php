<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ChatbotController - API Chatbot RAG (Retrieval-Augmented Generation)
 *
 * Luồng xử lý:
 * 1. Nhận câu hỏi từ Frontend (AJAX POST)
 * 2. Trích xuất từ khóa & tìm kiếm sản phẩm liên quan trong DB (LIKE)
 * 3. Gửi câu hỏi + ngữ cảnh (sản phẩm tìm được) lên Gemini API
 * 4. Trả JSON response cho Frontend
 */
class ChatbotController extends Controller
{
    /**
     * Danh sách các model Gemini fallback (thử lần lượt)
     */
    private array $models = [
        'gemini-3.1-flash-lite',
        'gemini-3.5-flash',
        'gemini-3-flash-preview',
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
    ];

    /**
     * Các từ vô nghĩa (stop words) sẽ bị lọc bỏ khi trích xuất từ khóa
     */
    private array $stopwords = [
        'là', 'gì', 'cho', 'tôi', 'hỏi', 'có', 'không', 'giá', 'bao', 'nhiêu',
        'tư', 'vấn', 'cái', 'này', 'xin', 'chào', 'mua', 'bán', 'nào', 'của',
        'và', 'hay', 'hoặc', 'thì', 'mà', 'với', 'được', 'các', 'một', 'những',
        'đây', 'đó', 'kia', 'ạ', 'nhé', 'nha', 'ơi', 'thế', 'sao',
    ];

    /**
     * Xử lý request chat từ Frontend
     */
    public function chat(Request $request)
    {
        $prompt = trim($request->input('prompt', ''));
        $currentProductContext = trim($request->input('context', ''));

        if (!$prompt) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập câu hỏi.',
            ]);
        }

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa cấu hình API Key cho AI.',
            ]);
        }

        // BƯỚC 1: RAG - Trích xuất từ khóa & tìm kiếm sản phẩm trong DB
        $productKnowledge = $this->searchProducts($prompt);

        // BƯỚC 2: Chuẩn bị prompt & gọi Gemini API
        $contextInstruction = 'Khách hàng đang ở Trang chủ hoặc xem danh mục chung. Hãy tư vấn tổng quan.';
        if ($currentProductContext) {
            $contextInstruction = "ĐẶC BIỆT LƯU Ý: Khách hàng ĐANG XEM SẢN PHẨM NÀY:\n{$currentProductContext}\n-> Ưu tiên dùng thông tin sản phẩm này để trả lời.";
        }

        $fullPrompt = "BỐI CẢNH: Bạn là Trợ lý bán hàng thông minh của DIENMAY PRO - hệ thống bán lẻ điện thoại, laptop, phụ kiện công nghệ.
NGỮ CẢNH KHO HÀNG (Dữ liệu từ Database):
{$productKnowledge}

QUY TẮC PHẢN HỒI (CỰC KỲ QUAN TRỌNG):
1. PHẢI TRẢ LỜI CHI TIẾT, GIÀU THÔNG TIN VÀ CÓ PHÂN TÍCH SÂU: 
   - Ví dụ khi khách hỏi laptop cho sinh viên, phải phân tích chi tiết theo nhu cầu học tập/văn phòng nhẹ nhàng (Acer, Asus, Lenovo, HP) và nhu cầu kỹ thuật/gaming/đồ họa nặng (Dell, MacBook, laptop gaming, laptop đồ họa).
   - Hãy đưa ra các gợi ý thương hiệu và phân loại cụ thể tương tự như kiểu tư vấn chuyên nghiệp lúc đầu.

2. QUY TẮC CHÈN LINK (KHÔNG ĐƯỢC ĐỂ HỎNG LINK):
   - Để chèn link xem chi tiết cho THƯƠNG HIỆU, DANH MỤC hoặc PHÂN LOẠI SẢN PHẨM, bạn PHẢI sử dụng link tìm kiếm với định dạng: <a href=\"/search?q=từ_khóa\" class=\"chatbot-product-link\">Xem chi tiết</a> hoặc <a href=\"/search?q=từ_khóa\" class=\"chatbot-product-link\">tên_từ_khóa</a> (Ví dụ: /search?q=Asus, /search?q=Dell, /search?q=MacBook, /search?q=laptop+gaming, /search?q=laptop+van+phong).
   - Đối với sản phẩm CỤ THỂ có trong NGỮ CẢNH KHO HÀNG ở trên: sử dụng đúng link /san-pham/ID (Ví dụ: <a href=\"/san-pham/12\" class=\"chatbot-product-link\">Tên sản phẩm</a>).
   - TUYỆT ĐỐI KHÔNG tự bịa link dạng /san-pham/tên-thương-hiệu hay /san-pham/tên-danh-mục. Tất cả các danh mục và thương hiệu bắt buộc phải qua link /search?q=từ_khóa.

3. TUYỆT ĐỐI KHÔNG DÙNG MARKDOWN: Không dùng các ký tự **, -, #, * hay bất kỳ cú pháp Markdown nào trong câu trả lời.
4. DÙNG THẺ HTML ĐỂ ĐỊNH DẠNG:
   - In đậm tiêu đề hoặc từ khóa quan trọng: <b>nội dung</b>
   - Xuống dòng: <br>
5. QUY TẮC ĐỊNH DẠNG CÂU TRẢ LỜI (ĐỂ KHÔNG DÍNH CỤC VÀ KHÔNG XUỐNG DÒNG TRẮNG QUÁ NHIỀU):
   - Giữa các đoạn ý chính, dùng ĐÚNG 1 thẻ <br> để cách dòng. KHÔNG dùng nhiều thẻ <br> liên tiếp làm thừa khoảng trắng.
   - Hãy sắp xếp các phân loại sản phẩm bằng các emoji ở đầu dòng (ví dụ: 👉, ✅, 📱, 💻) để tạo bố cục thoáng đãng, chuyên nghiệp, đẹp mắt và dễ đọc.
   - Tránh viết một khối văn bản dài dính cục, chia nhỏ thành 3-4 đoạn ngắn, dễ theo dõi.
6. NGÔN NGỮ: Khách hỏi tiếng gì, đáp tiếng đó.
7. PHONG CÁCH: Thân thiện, chu đáo, nhiệt tình tư vấn như một nhân viên bán hàng chuyên nghiệp.

{$contextInstruction}

CÂU HỎI CỦA KHÁCH: {$prompt}";

        // BƯỚC 3: Gọi Gemini API (fallback qua nhiều model)
        $result = $this->callGeminiApi($apiKey, $fullPrompt);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'response' => $result['text'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Lỗi kết nối AI (' . $result['error'] . ')',
        ]);
    }

    /**
     * Trích xuất từ khóa từ câu hỏi & tìm sản phẩm liên quan trong DB
     */
    private function searchProducts(string $prompt): string
    {
        $keywords = explode(' ', mb_strtolower($prompt, 'UTF-8'));
        $searchTerms = [];

        foreach ($keywords as $word) {
            $word = trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $word));
            if (mb_strlen($word) >= 2 && !in_array($word, $this->stopwords)) {
                $searchTerms[] = $word;
            }
        }

        if (empty($searchTerms)) {
            return 'Khách hàng đang hỏi câu hỏi chung chung, không chứa từ khóa sản phẩm rõ ràng.';
        }

        try {
            // Thử tìm kiếm khớp đồng thời tất cả các từ khóa (AND) trước để đạt độ chính xác cao nhất
            $query = DB::table('products')->whereNull('deleted_at');
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where('name', 'LIKE', "%{$term}%");
                }
            });

            $foundProducts = $query->select('product_id', 'name', 'base_price')
                ->limit(10)
                ->get();

            // Nếu không có sản phẩm nào khớp tất cả các từ khóa, fallback sang khớp một trong các từ khóa (OR)
            if ($foundProducts->isEmpty()) {
                $query = DB::table('products')->whereNull('deleted_at');
                $query->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $q->orWhere('name', 'LIKE', "%{$term}%");
                    }
                });
                $foundProducts = $query->select('product_id', 'name', 'base_price')
                    ->limit(10)
                    ->get();
            }

            if ($foundProducts->isEmpty()) {
                return "Hệ thống không tìm thấy sản phẩm nào khớp chính xác với từ khóa.\n";
            }

            $knowledge = "KẾT QUẢ TÌM KIẾM TRONG KHO HÀNG LIÊN QUAN ĐẾN CÂU HỎI:\n";
            foreach ($foundProducts as $p) {
                $price = number_format($p->base_price, 0, ',', '.');
                $knowledge .= "- {$p->name}: Giá {$price}đ (Link: /san-pham/{$p->product_id})\n";
            }

            return $knowledge;
        } catch (\Exception $e) {
            Log::error('Chatbot DB search error: ' . $e->getMessage());
            return 'Lỗi truy vấn kho hàng.';
        }
    }

    /**
     * Gọi Gemini API với cơ chế fallback qua nhiều model
     */
    private function callGeminiApi(string $apiKey, string $prompt): array
    {
        $lastError = '';

        foreach ($this->models as $model) {
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . trim($apiKey);

            $postData = json_encode([
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 30,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response !== false && $httpCode === 200) {
                $resData = json_decode($response, true);
                if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                    return [
                        'success' => true,
                        'text' => $resData['candidates'][0]['content']['parts'][0]['text'],
                    ];
                }
            }

            if ($response !== false) {
                $resData = json_decode($response, true);
                if (isset($resData['error']['message'])) {
                    $lastError = "Gemini API Error: " . $resData['error']['message'];
                } else {
                    $lastError = $curlError ? "CURL Error: {$curlError}" : "HTTP {$httpCode} - Response: {$response}";
                }
            } else {
                $lastError = "CURL Error: {$curlError}";
            }
        }

        Log::warning('Chatbot Gemini API failed: ' . $lastError);

        return [
            'success' => false,
            'error' => $lastError,
        ];
    }
}
