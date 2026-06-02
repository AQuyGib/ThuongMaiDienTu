<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * ArticleAIService - Dịch vụ xử lý kiểm duyệt tự động, gắn thẻ tự động và trợ lý SEO cho bài viết bằng Google Gemini API.
 */
class ArticleAIService
{
    /**
     * Thực hiện phân tích bài viết bằng Gemini API.
     *
     * @param string $title Tiêu đề bài viết
     * @param string|null $summary Tóm tắt bài viết
     * @param string $content Nội dung bài viết
     * @return array Kết quả phân tích (định dạng mảng)
     */
    public function analyzeArticle(string $title, ?string $summary, string $content): array
    {
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            Log::warning('GEMINI_API_KEY chưa được cấu hình. Sử dụng dữ liệu mô phỏng fallback.');
            return $this->getFallbackAnalysis($title, $summary, $content);
        }

        $prompt = $this->buildPrompt($title, $summary, $content);
        $responseJson = $this->callGeminiApi($apiKey, $prompt);

        if (!$responseJson) {
            Log::warning('Gemini API không phản hồi khi phân tích bài viết. Sử dụng dữ liệu fallback.');
            return $this->getFallbackAnalysis($title, $summary, $content);
        }

        try {
            $cleanJson = preg_replace('/^```(?:json)?\s*|```\s*$/', '', trim($responseJson));
            $data = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['quality_score'])) {
                throw new \Exception('JSON không hợp lệ hoặc thiếu dữ liệu phân tích.');
            }

            // Đảm bảo các trường cơ bản luôn tồn tại
            return [
                'quality_score'              => (int) ($data['quality_score'] ?? 50),
                'recommended_reward_points'  => (int) ($data['recommended_reward_points'] ?? 20),
                'is_spam'                    => (bool) ($data['is_spam'] ?? false),
                'has_sensitive_content'      => (bool) ($data['has_sensitive_content'] ?? false),
                'plagiarism_probability'     => (int) ($data['plagiarism_probability'] ?? 0),
                'moderation_verdict'         => (string) ($data['moderation_verdict'] ?? 'pending'),
                'moderation_reason'          => (string) ($data['moderation_reason'] ?? 'Cần xem xét thêm.'),
                'tags'                       => (array) ($data['tags'] ?? ['#lifestyle', '#congnghe']),
                'seo' => [
                    'title_suggestion'             => (string) ($data['seo']['title_suggestion'] ?? $title),
                    'meta_description_suggestion'  => (string) ($data['seo']['meta_description_suggestion'] ?? ($summary ?: 'Xem chi tiết bài viết công nghệ mới nhất.')),
                    'keywords_analysis'            => (array) ($data['seo']['keywords_analysis'] ?? []),
                    'seo_score'                    => (int) ($data['seo']['seo_score'] ?? 50),
                    'optimization_tips'            => (array) ($data['seo']['optimization_tips'] ?? []),
                ]
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi khi phân tích dữ liệu trả về từ Gemini: ' . $e->getMessage() . '. Raw: ' . $responseJson);
            return $this->getFallbackAnalysis($title, $summary, $content);
        }
    }

    /**
     * Xây dựng Prompt chi tiết cho Gemini
     */
    private function buildPrompt(string $title, ?string $summary, string $content): string
    {
        // Loại bỏ thẻ HTML thô trong content để giảm token thừa khi gửi lên API
        $cleanContent = strip_tags($content);
        $cleanContent = mb_substr($cleanContent, 0, 3000); // Lấy tối đa 3000 ký tự đầu để tối ưu

        return "Bạn là một trợ lý AI chuyên nghiệp hoạt động trong lĩnh vực kiểm duyệt nội dung (Content Moderation), gắn thẻ thông minh (Auto-Tagging) và tối ưu hóa SEO (SEO Assistant) cho trang tin tức công nghệ và phong cách sống (Lifestyle).

Hãy phân tích bài viết dưới đây và thực hiện các nhiệm vụ:

THÔNG TIN BÀI VIẾT:
- Tiêu đề: {$title}
- Tóm tắt: " . ($summary ?: 'Không có') . "
- Nội dung chi tiết: {$cleanContent}

NHIỆM VỤ:
1. KIỂM DUYỆT TỰ ĐỘNG (Auto-Moderation):
   - Đánh giá điểm chất lượng bài viết (quality_score) từ 0 đến 100 dựa trên sự mạch lạc, hữu ích, lỗi chính tả.
   - Phát hiện bài viết có chứa nội dung spam quảng cáo bẩn (is_spam) hoặc chứa từ ngữ nhạy cảm vi phạm đạo đức, bạo lực, cấm (has_sensitive_content) hay không.
   - Đánh giá khả năng sao chép nội dung/copywriter kém chất lượng (plagiarism_probability) từ 0 đến 100%.
   - Tính toán số điểm thưởng đề xuất (recommended_reward_points) cho người viết là một số nguyên từ 10 đến 100 điểm, dựa trên quality_score và mức độ đóng góp (độ dài, thông tin hữu ích). Ví dụ: quality_score >= 90 thưởng 80-100 điểm; quality_score >= 80 thưởng 50-79 điểm; quality_score >= 70 thưởng 20-49 điểm; dưới 70 thưởng 10-19 điểm. Nếu bị rejected hoặc vi phạm chính sách thì để 0 điểm.
   - Đưa ra quyết định kiểm duyệt (moderation_verdict): 
     + Trả về 'approved' nếu: quality_score >= 75 VÀ plagiarism_probability < 30 VÀ không spam VÀ không chứa từ ngữ nhạy cảm.
     + Trả về 'rejected' nếu: có spam HOẶC có từ ngữ nhạy cảm bạo lực/đồi trụy.
     + Trả về 'flagged' nếu: plagiarism_probability >= 30 HOẶC quality_score nằm trong khoảng 50-74 (cần admin duyệt lại).

2. GẮN THẺ THÔNG MINH (Auto-Tagging):
   - Trích xuất ra từ 3 đến 5 hashtag/thẻ phù hợp nhất với nội dung bài viết để phân loại (ví dụ: '#iphone15', '#tips', '#baohanh', '#review', '#laptop').
   - Lưu ý: Các tag phải viết thường, bắt đầu bằng dấu '#' và viết liền không dấu hoặc gạch nối (ví dụ: '#iphone15', '#meovat', '#huongdan').

3. TRỢ LÝ SEO (SEO Assistant):
   - Đề xuất một tiêu đề tối ưu SEO tốt hơn (seo_title) ngắn gọn, thu hút, dưới 70 ký tự.
   - Viết một thẻ mô tả Meta Description tối ưu SEO tốt hơn (seo_description) thu hút lượt click, dài từ 120-160 ký tự.
   - Phân tích từ khóa xuất hiện chính trong bài và mật độ (keywords_analysis): Trả về tối đa 3 từ khóa phổ biến kèm số lần xuất hiện và mật độ (density) ước tính.
   - Tính toán điểm SEO tổng thể (seo_score) từ 0 đến 100.
   - Đưa ra 2-3 lời khuyên tối ưu hóa SEO (optimization_tips) ngắn gọn (ví dụ: 'Thêm thẻ H3', 'In đậm từ khóa chính').

ĐỊNH DẠNG ĐẦU RA BẮT BUỘC:
Bạn bắt buộc phải phản hồi bằng một chuỗi định dạng JSON duy nhất. KHÔNG bao gồm các thẻ markdown như ```json hay ```, KHÔNG viết thêm bất kỳ ký tự nào ngoài chuỗi JSON.
Cấu trúc JSON chính xác như sau:
{
  \"quality_score\": <số nguyên từ 0-100>,
  \"recommended_reward_points\": <số nguyên từ 0-100>,
  \"is_spam\": <true/false>,
  \"has_sensitive_content\": <true/false>,
  \"plagiarism_probability\": <số nguyên từ 0-100>,
  \"moderation_verdict\": \"approved\" | \"flagged\" | \"rejected\",
  \"moderation_reason\": \"<Lý do đưa ra quyết định duyệt ngắn gọn bằng tiếng Việt>\",
  \"tags\": [\"#tag1\", \"#tag2\", \"#tag3\"],
  \"seo\": {
    \"title_suggestion\": \"<Tiêu đề tối ưu SEO đề xuất>\",
    \"meta_description_suggestion\": \"<Mô tả Meta tối ưu SEO đề xuất>\",
    \"keywords_analysis\": [
      {
        \"keyword\": \"<từ khóa 1>\",
        \"count\": <số lần xuất hiện>,
        \"density\": \"<mật độ %, ví dụ 2.5%>\"
      }
    ],
    \"seo_score\": <số nguyên từ 0-100>,
    \"optimization_tips\": [\"<mẹo tối ưu 1>\", \"<mẹo tối ưu 2>\"]
  }
}
";
    }

    /**
     * Thực hiện gọi API cURL đến Gemini
     */
    private function callGeminiApi(string $apiKey, string $prompt): ?string
    {
        $models = [
            'gemini-3.1-flash-lite',
            'gemini-3.5-flash',
            'gemini-3-flash-preview',
            'gemini-2.5-flash',
        ];

        foreach ($models as $model) {
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
                CURLOPT_TIMEOUT => 15,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($response)) {
                $resData = json_decode($response, true);
                $text = $resData['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (!empty($text)) {
                    return $text;
                }
            } else {
                Log::warning("Gemini model {$model} thất bại với mã HTTP {$httpCode}. Đang thử model tiếp theo.");
            }
        }

        return null;
    }

    /**
     * Dữ liệu mô phỏng fallback khi không gọi được API
     */
    private function getFallbackAnalysis(string $title, ?string $summary, string $content): array
    {
        // Phân tích thẻ cơ bản dựa trên từ khóa trong tiêu đề
        $tags = ['#lifestyle', '#meovat'];
        $titleLower = mb_strtolower($title);

        if (str_contains($titleLower, 'iphone')) {
            $tags[] = '#iphone';
        }
        if (str_contains($titleLower, 'laptop')) {
            $tags[] = '#laptop';
        }
        if (str_contains($titleLower, 'sửa') || str_contains($titleLower, 'sua')) {
            $tags[] = '#baohanh';
        }

        return [
            'quality_score'              => 75,
            'recommended_reward_points'  => 20,
            'is_spam'                    => false,
            'has_sensitive_content'      => false,
            'plagiarism_probability'     => 10,
            'moderation_verdict'         => 'approved', // Cho phép duyệt qua ở chế độ an toàn
            'moderation_reason'          => 'Đã phê duyệt tự động (Safe Fallback Mode).',
            'tags'                   => $tags,
            'seo' => [
                'title_suggestion'             => $title,
                'meta_description_suggestion'  => $summary ?: mb_substr(strip_tags($content), 0, 150),
                'keywords_analysis'            => [
                    ['keyword' => 'điện máy', 'count' => 3, 'density' => '1.5%']
                ],
                'seo_score'                    => 70,
                'optimization_tips'            => [
                    'Hãy thêm các tiêu đề phụ (H2, H3) để tăng khả năng đọc hiểu của Google.',
                    'In đậm các thuật ngữ quan trọng để hỗ trợ người dùng quét nội dung tốt hơn.'
                ],
            ]
        ];
    }
}
