<?php

namespace App\Services;

use App\Models\Installment;
use Illuminate\Support\Facades\Log;

/**
 * InstallmentAIService - Dịch vụ đánh giá rủi ro tín dụng & hồ sơ trả góp bằng Google Gemini API.
 */
class InstallmentAIService
{
    private array $models = [
        'gemini-3.1-flash-lite',
        'gemini-3.5-flash',
        'gemini-3-flash-preview',
        'gemini-2.5-flash',
    ];

    /**
     * Phân tích rủi ro hồ sơ trả góp
     * 
     * @param Installment $installment
     * @return array
     */
    public function analyzeInstallment(Installment $installment): array
    {
        $apiKey = env('GEMINI_API_KEY') ?: config('services.gemini.api_key');
        
        $customerName = $installment->customer_name;
        $customerPhone = $installment->customer_phone;
        $customerIdCard = $installment->customer_id_card ?? 'Không có';
        $partner = $installment->partner;
        $method = $installment->method;
        $loanAmount = number_format($installment->loan_amount, 0, ',', '.') . 'đ';
        $prepayAmount = number_format($installment->prepay_amount, 0, ',', '.') . 'đ';
        $monthlyPayment = number_format($installment->monthly_payment, 0, ',', '.') . 'đ';
        $period = $installment->period . ' tháng';
        $tradeIn = $installment->trade_in ? 'Có' : 'Không';

        // Lấy thông tin lịch sử người dùng nếu có
        $userHistoryText = "Khách vãng lai, không có lịch sử tài khoản.";
        $order = $installment->order;
        if ($order && $order->user) {
            $user = $order->user;
            $createdAt = $user->created_at ? $user->created_at->format('d/m/Y') : 'Không rõ';
            
            // Đếm số đơn hàng trước đó
            $pastOrdersCount = \App\Models\Order::where('user_id', $user->user_id)
                ->where('order_id', '!=', $order->order_id)
                ->count();
            
            $pastDeliveredCount = \App\Models\Order::where('user_id', $user->user_id)
                ->where('order_id', '!=', $order->order_id)
                ->where('status', 'Delivered')
                ->count();

            $pastCancelledCount = \App\Models\Order::where('user_id', $user->user_id)
                ->where('order_id', '!=', $order->order_id)
                ->where('status', 'Cancelled')
                ->count();

            $userHistoryText = "Tài khoản tạo ngày {$createdAt}. Tổng đơn hàng trong lịch sử: {$pastOrdersCount} (Thành công: {$pastDeliveredCount}, Hủy: {$pastCancelledCount}).";
        }

        if (!$apiKey) {
            // Không có API key -> Chạy Heuristic Fallback giả lập thông minh
            Log::warning('GEMINI_API_KEY chưa được cấu hình. Sử dụng phân tích rủi ro trả góp giả lập.');
            $fallbackResult = $this->generateFallbackAnalysis($customerName, $customerPhone, $customerIdCard, $installment->loan_amount, $installment->trade_in);
            $this->saveAIResult($installment, $fallbackResult);
            return $fallbackResult;
        }

        $prompt = "Bạn là chuyên gia thẩm định tín dụng và phát hiện gian lận của hệ thống trả góp e-commerce TechZone.
Hãy phân tích hồ sơ đăng ký trả góp sau đây và đưa ra đánh giá rủi ro tài chính (Risk Assessment) bằng tiếng Việt.

HỒ SƠ KHÁCH HÀNG:
- Họ tên đăng ký: {$customerName}
- Số điện thoại: {$customerPhone}
- Số CCCD/ID Card: {$customerIdCard}
- Đăng ký Thu cũ đổi mới: {$tradeIn}
- Lịch sử tài khoản: {$userHistoryText}

THÔNG TIN GÓI TRẢ GÓP:
- Phương thức: {$method} (Trả góp qua: {$partner})
- Giá trị vay trả góp: {$loanAmount}
- Số tiền trả trước: {$prepayAmount}
- Số tiền góp mỗi tháng: {$monthlyPayment}
- Kỳ hạn vay: {$period}

QUY TẮC ĐÁNH GIÁ RỦI RO CẦN TUÂN THỦ:
1. Đánh giá tính chính xác cá nhân:
   - Họ tên chứa ký tự ngẫu nhiên bừa bãi (như 'asdasd', 'test', '123') -> Rủi ro Cao (Reject).
   - Số điện thoại giả mạo (như lặp số '0000000000', tăng dần '0123456789') -> Rủi ro Cao (Reject).
   - Số CCCD phải đúng định dạng 12 chữ số của Việt Nam. Nếu CCCD thiếu số hoặc chứa chữ cái -> Rủi ro Cao (Reject).
2. Đánh giá khả năng tài chính và uy tín:
   - Nếu lịch sử mua hàng của tài khoản có tỷ lệ hủy đơn cao (> 50%) -> Rủi ro Trung bình-Cao.
   - Nếu khoản vay có giá trị lớn (> 20 triệu đồng) nhưng lịch sử tài khoản là mới tạo -> Đề xuất kiểm tra tay (Review).
   - Đăng ký Thu cũ đổi mới (Trade-in) được xem là yếu tố tích cực (+) giảm bớt số tiền nợ thực tế.
3. Đề xuất:
   - 'Approve' (Đề xuất duyệt): Điểm rủi ro < 30%. Thông tin minh bạch, lịch sử mua hàng tốt.
   - 'Review' (Cần kiểm tra lại): Điểm rủi ro từ 30% đến 75%. Cần nhân viên gọi điện xác minh thông tin.
   - 'Reject' (Từ chối): Điểm rủi ro > 75%. Phát hiện dấu hiệu lừa đảo, CCCD ảo hoặc nợ xấu cao.

ĐỊNH DẠNG ĐẦU RA BẮT BUỘC:
Trả về duy nhất định dạng JSON (không bao gồm các khối ```json hay ```, không thêm bất kỳ ký tự nào ngoài chuỗi JSON).
Cấu trúc JSON như sau:
{
  \"risk_score\": <số nguyên từ 0 đến 100>,
  \"risk_level\": \"Low\" | \"Medium\" | \"High\",
  \"findings\": [
    \"<Nhận xét tích cực hoặc tiêu cực 1>\",
    \"<Nhận xét tích cực hoặc tiêu cực 2>\"
  ],
  \"recommendation\": \"Approve\" | \"Review\" | \"Reject\",
  \"reason\": \"<Mô tả tóm tắt lý do đánh giá bằng tiếng Việt, khoảng 1-2 câu>\"
}
";

        $responseJson = $this->callGeminiApi($apiKey, $prompt);

        if (!$responseJson) {
            Log::warning('Gemini API không phản hồi khi phân tích hồ sơ trả góp. Sử dụng giả lập.');
            $fallbackResult = $this->generateFallbackAnalysis($customerName, $customerPhone, $customerIdCard, $installment->loan_amount, $installment->trade_in);
            $this->saveAIResult($installment, $fallbackResult);
            return $fallbackResult;
        }

        try {
            $cleanJson = preg_replace('/^```(?:json)?\s*|```\s*$/', '', trim($responseJson));
            $data = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['risk_score'])) {
                throw new \Exception('JSON không hợp lệ từ Gemini: ' . $responseJson);
            }

            $result = [
                'risk_score' => (int) $data['risk_score'],
                'risk_level' => $data['risk_level'] ?? 'Medium',
                'findings' => $data['findings'] ?? [],
                'recommendation' => $data['recommendation'] ?? 'Review',
                'reason' => $data['reason'] ?? 'Không có lý do chi tiết.'
            ];

            $this->saveAIResult($installment, $result);
            return $result;

        } catch (\Throwable $e) {
            Log::error('Lỗi phân tích kết quả AI Trả góp: ' . $e->getMessage() . '. Raw: ' . $responseJson);
            $fallbackResult = $this->generateFallbackAnalysis($customerName, $customerPhone, $customerIdCard, $installment->loan_amount, $installment->trade_in);
            $this->saveAIResult($installment, $fallbackResult);
            return $fallbackResult;
        }
    }

    /**
     * Ghi kết quả phân tích AI vào Model Installment
     */
    protected function saveAIResult(Installment $installment, array $result): void
    {
        $installment->update([
            'ai_risk_score' => $result['risk_score'],
            'ai_risk_level' => $result['risk_level'],
            'ai_analysis' => $result
        ]);
    }

    /**
     * Giả lập phân tích rủi ro tín dụng khi không có API Key (Heuristic Fallback)
     */
    private function generateFallbackAnalysis(string $name, string $phone, string $cccd, int $loanAmount, bool $tradeIn): array
    {
        $score = 15; // Mặc định an toàn
        $findings = [];
        
        // 1. Kiểm tra tên rác
        $lowerName = mb_strtolower($name);
        if (preg_match('/(test|asd|123|qwe|abc)/', $lowerName) || mb_strlen($name) < 4) {
            $score += 40;
            $findings[] = "Tên khách hàng chứa ký tự nghi vấn hoặc quá ngắn (-)";
        } else {
            $findings[] = "Họ tên khách hàng hợp lệ (+)";
        }

        // 2. Kiểm tra số điện thoại
        if (preg_match('/(0123456789|000000000|12345678|88888888)/', $phone) || strlen($phone) < 9 || strlen($phone) > 11) {
            $score += 35;
            $findings[] = "Số điện thoại có cấu trúc không hợp lệ hoặc giả lập rõ ràng (-)";
        } else {
            $findings[] = "Số điện thoại đúng định dạng mạng viễn thông Việt Nam (+)";
        }

        // 3. Kiểm tra CCCD
        $cleanCccd = preg_replace('/[^0-9]/', '', $cccd);
        if ($cccd !== 'Không có' && (strlen($cleanCccd) !== 12 || $cccd !== $cleanCccd)) {
            $score += 30;
            $findings[] = "Số CCCD không đúng định dạng 12 chữ số của Việt Nam (-)";
        } elseif ($cccd === 'Không có') {
            $findings[] = "Không có số CCCD (Có thể trả góp qua thẻ tín dụng trực tuyến) (+/-)";
        } else {
            $findings[] = "Số CCCD đúng định dạng 12 chữ số hợp pháp (+)";
        }

        // 4. Giá trị khoản vay
        if ($loanAmount > 20000000) {
            $score += 15;
            $findings[] = "Khoản vay giá trị cao (> 20.000.000đ), cần chú ý thẩm định (-)";
        } else {
            $findings[] = "Khoản nợ nằm trong tầm kiểm soát trung bình (+)";
        }

        // 5. Trade-in thu cũ đổi mới
        if ($tradeIn) {
            $score -= 10;
            $findings[] = "Khách hàng đăng ký Thu cũ lên đời, hỗ trợ giảm áp lực tài chính (+)";
        }

        // Giới hạn điểm từ 0-100
        $score = max(0, min(100, $score));

        // Phân cấp rủi ro và khuyến nghị
        if ($score < 35) {
            $level = 'Low';
            $recommendation = 'Approve';
            $reason = 'Hồ sơ đầy đủ, thông tin cá nhân chính xác và có độ tin cậy cao.';
        } elseif ($score < 75) {
            $level = 'Medium';
            $recommendation = 'Review';
            $reason = 'Có một vài yếu tố cần lưu ý như giá trị khoản vay lớn. Cần liên hệ thẩm định trực tiếp.';
        } else {
            $level = 'High';
            $recommendation = 'Reject';
            $reason = 'Phát hiện dấu hiệu thông tin giả mạo (SĐT hoặc CCCD không đúng quy chuẩn).';
        }

        return [
            'risk_score' => $score,
            'risk_level' => $level,
            'findings' => $findings,
            'recommendation' => $recommendation,
            'reason' => $reason
        ];
    }

    /**
     * Gọi API cURL đến Gemini
     */
    private function callGeminiApi(string $apiKey, string $prompt): ?string
    {
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
                CURLOPT_TIMEOUT => 20,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response !== false && $httpCode === 200) {
                $resData = json_decode($response, true);
                if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                    return $resData['candidates'][0]['content']['parts'][0]['text'];
                }
            }
        }

        return null;
    }
}
