<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * AIOrderService - Dịch vụ sử dụng AI Gemini để tự động đánh giá rủi ro đơn hàng
 */
class AIOrderService
{
    /**
     * Danh sách các model Gemini dự phòng
     */
    private array $models = [
        'gemini-3.1-flash-lite',
        'gemini-3.5-flash',
        'gemini-3-flash-preview',
        'gemini-2.5-flash',
    ];

    /**
     * Phân tích một đơn hàng và cập nhật kết quả vào DB
     * 
     * @param Order $order
     * @return array Trả về mảng kết quả phân tích
     */
    public function analyzeOrder(Order $order, string $triggerType = 'auto'): array
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            $errorMsg = 'Chưa cấu hình API Key cho Gemini (services.gemini.api_key)';
            Log::warning($errorMsg);
            $this->updateOrderAI($order, 'pending', 0, 'Lỗi: ' . $errorMsg);
            \App\Models\AIOrderLog::create([
                'order_id' => $order->order_id,
                'ai_status' => 'failed',
                'risk_score' => 0,
                'analysis' => $errorMsg,
                'trigger_type' => $triggerType,
            ]);
            return ['status' => 'pending', 'risk_score' => 0, 'reason' => $errorMsg];
        }

        // 1. Thu thập thông tin chi tiết đơn hàng
        $customerName = $order->customer_name ?? ($order->user->full_name ?? 'Khách vãng lai');
        $customerPhone = $order->customer_phone ?? ($order->user->phone_number ?? 'Không có');
        $shippingAddress = $order->shipping_address ?? ($order->user->address ?? 'Không có');
        $note = $order->note ?? 'Không có ghi chú';
        $paymentMethod = $order->payment_method ?? 'COD';
        $finalAmount = number_format($order->final_amount ?? 0, 0, ',', '.') . 'đ';
        $orderCode = $order->order_code ?? '#' . $order->order_id;

        // Định dạng danh sách sản phẩm
        $itemsText = '';
        if ($order->relationLoaded('details') || $order->details()->exists()) {
            foreach ($order->details as $detail) {
                $productName = $detail->product_name ?? ($detail->inventoryItem->variant->product->name ?? 'Sản phẩm không rõ');
                $qty = $detail->quantity ?? 1;
                $price = number_format($detail->price ?? 0, 0, ',', '.') . 'đ';
                $itemsText .= "- {$productName} (SL: {$qty}, Đơn giá: {$price})\n";
            }
        } else {
            $itemsText = "Không có thông tin chi tiết mặt hàng.\n";
        }

        // 2. Thiết lập prompt phân tích rủi ro
        $prompt = "Bạn là chuyên gia phân tích rủi ro đơn hàng thương mại điện tử chuyên nghiệp.
Nhiệm vụ của bạn là đánh giá mức độ rủi ro (Risk Assessment) của đơn hàng sau đây và quyết định xem đơn hàng này là An toàn (Approved), Nghi ngờ cần kiểm tra tay (Flagged) hay Gian lận/Đơn ảo cần hủy (Cancelled).

THÔNG TIN ĐƠN HÀNG:
- Mã đơn hàng: {$orderCode}
- Tên khách hàng: {$customerName}
- Số điện thoại: {$customerPhone}
- Địa chỉ giao hàng: {$shippingAddress}
- Ghi chú khách hàng: {$note}
- Phương thức thanh toán: {$paymentMethod}
- Tổng số tiền thanh toán: {$finalAmount}
- Các mặt hàng đã mua:
{$itemsText}

QUY TẮC ĐÁNH GIÁ RỦI RO (RẤT QUAN TRỌNG):
1. Đánh giá Tên Khách Hàng:
   - Nếu tên chứa ký tự rác bừa bãi (ví dụ: 'asdada', '123123', 'đâsdad'), hoặc tên có ký tự đặc biệt vô nghĩa -> Rủi ro cao (Cancelled hoặc Flagged).
   - Nếu tên quá ngắn (chỉ 1-2 chữ cái vô nghĩa) -> Rủi ro trung bình-cao.
2. Đánh giá Số Điện Thoại:
   - SĐT hợp lệ ở Việt Nam thường bắt đầu bằng đầu số 0 và có độ dài 9-10 chữ số.
   - Nếu số điện thoại là giả lập rõ ràng (ví dụ: '0123456789', '0000000000', hoặc chữ số lặp lại quá nhiều) -> Rủi ro cao.
3. Đánh giá Địa Chỉ:
   - Địa chỉ phải có cấu trúc hợp lý (đầy đủ số nhà/đường, phường/xã, quận/huyện, tỉnh/thành).
   - Nếu địa chỉ quá ngắn, chung chung vô nghĩa (ví dụ: 'Hà Nội', 'vietnam', 'nhà riêng', 'đá sda') -> Rủi ro cao.
4. Đánh giá Ghi Chú & Hành Vi:
   - Nếu ghi chú chứa các từ như 'đơn test', 'test đơn hàng', 'hủy đơn này', 'chạy thử hệ thống', hoặc mang tính chất trêu đùa -> Rủi ro cao (Cancelled).
   - Nếu ghi chú bình thường hoặc dặn dò giao hàng (ví dụ: 'gọi điện trước khi giao') -> An toàn.
5. Đánh giá Tổng tiền:
   - Đơn hàng có giá trị rất lớn (ví dụ trên 50 triệu) thanh toán COD có thể cần tăng nhẹ điểm rủi ro để đưa vào trạng thái cần gọi điện xác nhận (Flagged).

ĐỊNH DẠNG ĐẦU RA BẮT BUỘC:
Bạn bắt buộc phải phản hồi bằng một chuỗi định dạng JSON duy nhất. KHÔNG bao gồm các thẻ markdown như ```json hay ```, KHÔNG viết thêm bất kỳ ký tự nào ngoài chuỗi JSON.
Cấu trúc JSON chính xác như sau:
{
  \"status\": \"approved\" | \"flagged\" | \"cancelled\",
  \"risk_score\": <số nguyên từ 0 đến 100>,
  \"reason\": \"<Giải thích rõ ràng lý do đánh giá bằng tiếng Việt, viết ngắn gọn trong 1-2 câu>\"
}

Ví dụ phản hồi an toàn:
{\"status\": \"approved\", \"risk_score\": 10, \"reason\": \"Thông tin người nhận và địa chỉ rõ ràng, đầy đủ. Đơn hàng hợp lệ.\"}

Ví dụ phản hồi đơn test/gian lận:
{\"status\": \"cancelled\", \"risk_score\": 95, \"reason\": \"Tên khách hàng chứa ký tự rác và ghi chú ghi rõ là đơn test.\"}";

        // 3. Gọi Gemini API
        $responseJson = $this->callGeminiApi($apiKey, $prompt);

        if (!$responseJson) {
            $errorMsg = 'Không nhận được phản hồi từ API Gemini hoặc lỗi kết nối.';
            $this->updateOrderAI($order, 'pending', 0, 'Lỗi: ' . $errorMsg);
            \App\Models\AIOrderLog::create([
                'order_id' => $order->order_id,
                'ai_status' => 'failed',
                'risk_score' => 0,
                'analysis' => $errorMsg,
                'trigger_type' => $triggerType,
            ]);
            return ['status' => 'pending', 'risk_score' => 0, 'reason' => $errorMsg];
        }

        // 4. Parse kết quả trả về
        try {
            // Loại bỏ các thẻ code block ```json ... ``` nếu AI trả về ngoài ý muốn
            $cleanJson = preg_replace('/^```(?:json)?\s*|```\s*$/', '', trim($responseJson));
            $data = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['status'])) {
                throw new \Exception('JSON không hợp lệ hoặc thiếu trường status: ' . $responseJson);
            }

            $status = in_array($data['status'], ['approved', 'flagged', 'cancelled'], true) ? $data['status'] : 'flagged';
            $riskScore = isset($data['risk_score']) ? (int) $data['risk_score'] : 50;
            $reason = $data['reason'] ?? 'Không có giải thích chi tiết.';

            $this->updateOrderAI($order, $status, $riskScore, $reason);

            // Ghi lịch sử phân tích của AI
            \App\Models\AIOrderLog::create([
                'order_id' => $order->order_id,
                'ai_status' => $status,
                'risk_score' => $riskScore,
                'analysis' => $reason,
                'trigger_type' => $triggerType,
            ]);

            // Tự động duyệt hoặc hủy đơn hàng dựa trên điểm rủi ro AI (chỉ áp dụng khi đơn hàng đang ở trạng thái Pending)
            if ($order->status === 'Pending') {
                if ($riskScore < 30) {
                    $order->update(['status' => 'Processing']);
                    Log::info("AI Auto-Approved order #{$order->order_id} (Risk Score: {$riskScore}%)");
                } elseif ($riskScore >= 80) {
                    $order->update(['status' => 'Cancelled']);
                    Log::info("AI Auto-Cancelled order #{$order->order_id} (Risk Score: {$riskScore}%)");
                }
            }

            // Gửi thông báo đến Quản trị viên
            try {
                $actionLabel = '';
                if ($riskScore < 30) {
                    $actionLabel = 'Tự động duyệt (An toàn)';
                } elseif ($riskScore >= 80) {
                    $actionLabel = 'Tự động hủy (Rủi ro cao)';
                } else {
                    $actionLabel = 'Gắn cờ nghi ngờ (Cần duyệt tay)';
                }

                app(\App\Services\NotificationService::class)->notifyAdmins([
                    'type' => 'admin.ai.order_processed',
                    'title' => 'AI Đơn hàng #' . ($order->order_code ?? $order->order_id),
                    'content' => "Kết quả: {$actionLabel}. Điểm rủi ro: {$riskScore}%. Nhận định: {$reason}",
                    'action_url' => url('/admin/orders'),
                    'data' => [
                        'order_id' => $order->order_id,
                        'ai_status' => $status,
                        'ai_risk_score' => $riskScore,
                    ]
                ]);
            } catch (\Throwable $ne) {
                Log::error("Lỗi gửi thông báo AI: " . $ne->getMessage());
            }

            return [
                'status' => $status,
                'risk_score' => $riskScore,
                'reason' => $reason
            ];
        } catch (\Throwable $e) {
            $errorMsg = 'Lỗi parse kết quả AI: ' . $e->getMessage();
            Log::error($errorMsg . ' Raw response: ' . $responseJson);
            $this->updateOrderAI($order, 'flagged', 50, 'Lỗi phân tích cú pháp AI: ' . $e->getMessage() . '. Phản hồi thô: ' . $responseJson);
            \App\Models\AIOrderLog::create([
                'order_id' => $order->order_id,
                'ai_status' => 'failed',
                'risk_score' => 50,
                'analysis' => $errorMsg,
                'trigger_type' => $triggerType,
            ]);
            return ['status' => 'flagged', 'risk_score' => 50, 'reason' => $errorMsg];
        }
    }

    /**
     * Cập nhật trạng thái AI vào cơ sở dữ liệu
     */
    private function updateOrderAI(Order $order, string $status, int $riskScore, string $analysis): void
    {
        $order->update([
            'ai_status' => $status,
            'ai_risk_score' => $riskScore,
            'ai_analysis' => $analysis
        ]);
    }

    /**
     * Thực hiện gọi HTTP POST lên API Gemini (Fallback)
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
