<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommentModerationService
{
    /**
     * Danh sách các từ khóa nhạy cảm, quảng cáo, cờ bạc hoặc tục tĩu tiếng Việt.
     */
    protected static array $blacklist = [
        'đm', 'đkm', 'vcl', 'địt', 'đéo', 'chịch', 'dâm', 'phim sex', 'clm', 'cmn',
        'lừa đảo', 'đánh bạc', 'cờ bạc', 'nhà cái', 'nha cai', 'kubet', 'shbet', 
        'w88', 'fb88', 'fun88', 'm88', 'ae888', '188bet', 'kèo nhà cái', 'soi kèo',
        'mua bán acc', 'hack game', 'auto game', 'viết thuê', 'làm thuê', 'tiền giả',
        'tiền tài', 'vay tiền', 'tín dụng đen', 'bốc bát họ', 'chửi', 'ngu lờ', 'đần', 'cặc'
    ];

    /**
     * Kiểm tra nội dung bình luận có an toàn hay không.
     * Trả về true nếu an toàn (Auto-Approve), trả về false nếu nhạy cảm (Cần kiểm duyệt).
     */
    public function isSafe(string $content): bool
    {
        $contentLower = mb_strtolower($content, 'UTF-8');

        // 1. Kiểm tra chứa các liên kết / link quảng cáo (spam links)
        if (preg_match('/(https?:\/\/|www\.|[a-z0-9\-]+\.(com|net|org|vn|xyz|info|edu|club|biz|site))/i', $contentLower)) {
            return false;
        }

        // 2. Sử dụng Gemini AI nếu có cấu hình API Key
        $apiKey = env('GEMINI_API_KEY');
        if (!empty($apiKey)) {
            $aiResult = $this->checkWithGemini($content, $apiKey);
            if ($aiResult !== null) {
                return $aiResult;
            }
        }

        // 3. Nếu chưa điền API Key hoặc gọi API lỗi, lọc theo từ khóa đen cục bộ
        foreach (self::$blacklist as $word) {
            if (mb_strpos($contentLower, $word) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gọi API Gemini để phân tích nội dung bình luận.
     * Trả về true nếu an toàn, false nếu vi phạm, hoặc null nếu lỗi.
     */
    protected function checkWithGemini(string $content, string $apiKey): ?bool
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            $prompt = "Bạn là hệ thống kiểm duyệt bình luận tự động của website Điện Máy Pro. " .
                      "Nhiệm vụ của bạn là kiểm tra xem bình luận tiếng Việt sau đây có chứa từ ngữ tục tĩu, chửi thề, " .
                      "xúc phạm người khác, spam quảng cáo, cờ bạc, lừa đảo hoặc nội dung phản động không. \n\n" .
                      "Nội dung bình luận: \"{$content}\"\n\n" .
                      "Chỉ phản hồi duy nhất từ 'SAFE' nếu bình luận hoàn toàn sạch và an toàn. " .
                      "Phản hồi từ 'UNSAFE' nếu phát hiện vi phạm. Tuyệt đối không viết thêm lời giải thích hay từ nào khác.";

            $request = Http::timeout(8);
            if (config('app.env') === 'local') {
                $request = $request->withoutVerifying();
            }

            $response = $request->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $resultText = trim($response->json('candidates.0.content.parts.0.text'));
                
                // Chuẩn hóa chuỗi kết quả trả về
                $normalized = strtoupper(preg_replace('/[^a-zA-Z]/', '', $resultText));
                Log::info("Gemini AI Moderation for '{$content}': {$normalized}");

                if ($normalized === 'UNSAFE') {
                    return false;
                }
                if ($normalized === 'SAFE') {
                    return true;
                }
            } else {
                Log::warning("Gemini AI Moderation API failed to respond successfully", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Gemini AI Moderation error occurred: " . $e->getMessage());
        }

        return null; 
    }
}
