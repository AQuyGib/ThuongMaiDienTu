<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\User;

/**
 * RepairAIService - Dịch vụ chẩn đoán lỗi thiết bị tự động và Smart Job Dispatching bằng Google Gemini API.
 */
class RepairAIService
{
    /**
     * Thực hiện chẩn đoán lỗi thiết bị và phân công kỹ thuật viên.
     *
     * @param string $issueDesc Mô tả lỗi thiết bị của khách hàng
     * @param string|null $imagePath Đường dẫn tệp ảnh đính kèm (vision diagnosis)
     * @return array Kết quả chẩn đoán và phân công
     */
    public function diagnoseFault(string $issueDesc, ?string $imagePath = null): array
    {
        $apiKey = env('GEMINI_API_KEY');

        // Lấy danh sách kỹ thuật viên thực tế từ DB để AI phân công
        $technicians = User::where('role_id', 4)->get()->map(function($user) {
            $skills = 'Kiểm tra và sửa chữa điện máy tổng hợp.';
            if (str_contains(strtolower($user->full_name), 'nam')) {
                $skills = 'Chuyên sâu sửa chữa phần cứng điện thoại/laptop, hàn bo mạch, ép kính, thay thế màn hình hiển thị, sửa lỗi nguồn phức tạp.';
            } elseif (str_contains(strtolower($user->full_name), 'hùng') || str_contains(strtolower($user->full_name), 'hung')) {
                $skills = 'Chuyên sửa chữa phần mềm, cài đặt hệ điều hành Android/iOS, khôi phục dữ liệu, thay thế pin, sửa lỗi kết nối wifi/bluetooth, bảo dưỡng vệ sinh máy.';
            }
            return [
                'id' => $user->user_id,
                'name' => $user->full_name,
                'skills' => $skills
            ];
        })->toArray();

        // Fallback technician mặc định nếu DB trống
        if (empty($technicians)) {
            $defaultTech = User::whereIn('role_id', [1, 2])->first();
            if ($defaultTech) {
                $technicians[] = [
                    'id' => $defaultTech->user_id,
                    'name' => $defaultTech->full_name,
                    'skills' => 'Quản trị viên / Quản lý - hỗ trợ tiếp nhận tổng hợp.'
                ];
            }
        }

        if (empty($apiKey)) {
            Log::warning('GEMINI_API_KEY chưa được cấu hình. Sử dụng dữ liệu chẩn đoán mô phỏng fallback.');
            return $this->getFallbackDiagnosis($issueDesc, $technicians);
        }

        // Đọc ảnh và encode base64 nếu có ảnh đính kèm
        $imageBase64 = null;
        $imageMimeType = null;
        if ($imagePath && file_exists($imagePath)) {
            try {
                $fileData = file_get_contents($imagePath);
                $imageBase64 = base64_encode($fileData);
                $imageMimeType = mime_content_type($imagePath);
            } catch (\Throwable $e) {
                Log::error('Lỗi khi đọc file ảnh chẩn đoán AI: ' . $e->getMessage());
            }
        }

        $prompt = $this->buildPrompt($issueDesc, $technicians, $imageBase64 !== null);
        $responseJson = $this->callGeminiApi($apiKey, $prompt, $imageBase64, $imageMimeType);

        if (!$responseJson) {
            Log::warning('Gemini API không phản hồi khi chẩn đoán thiết bị. Sử dụng dữ liệu fallback.');
            return $this->getFallbackDiagnosis($issueDesc, $technicians);
        }

        try {
            $cleanJson = preg_replace('/^```(?:json)?\s*|```\s*$/', '', trim($responseJson));
            $data = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON không hợp lệ từ Gemini.');
            }

            // Đồng bộ dữ liệu đầu ra an toàn
            return [
                'ai_fault_type'         => (string) ($data['fault_type'] ?? 'Phần cứng'),
                'ai_probable_causes'    => (array) ($data['probable_causes'] ?? ['Chưa rõ nguyên nhân']),
                'ai_risk_warnings'      => (array) ($data['risk_warnings'] ?? ['Không tự ý tháo mở thiết bị khi chưa có chuyên môn']),
                'ai_replacement_parts'  => (string) ($data['replacement_parts'] ?? 'Cần kiểm tra linh kiện trực tiếp'),
                'ai_estimated_cost_min' => (int) ($data['estimated_cost_min'] ?? 200000),
                'ai_estimated_cost_max' => (int) ($data['estimated_cost_max'] ?? 1000000),
                'ai_complexity_level'   => (string) ($data['complexity_level'] ?? 'Trung bình'),
                'ai_recommended_skills' => (array) ($data['recommended_skills'] ?? ['Kiểm tra tổng quát']),
                'assigned_technician_id'=> (int) ($data['assigned_technician_id'] ?? ($technicians[0]['id'] ?? null)),
                'ai_dispatch_reason'    => (string) ($data['dispatch_reason'] ?? 'Được phân công ngẫu nhiên.'),
                'ai_diagnosed'          => true,
                'ai_diagnosed_at'       => now()->toDateTimeString()
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi phân tích JSON phản hồi từ Gemini: ' . $e->getMessage() . '. Raw: ' . $responseJson);
            return $this->getFallbackDiagnosis($issueDesc, $technicians);
        }
    }

    /**
     * Xây dựng prompt chẩn đoán lỗi & dispatch kỹ thuật viên.
     */
    private function buildPrompt(string $issueDesc, array $technicians, bool $hasImage): string
    {
        $techListStr = '';
        foreach ($technicians as $tech) {
            $techListStr .= "- Kỹ thuật viên ID: {$tech['id']}, Tên: {$tech['name']}, Tay nghề/Kỹ năng: {$tech['skills']}\n";
        }

        $visionText = $hasImage 
            ? "Khách hàng có đính kèm ảnh chụp tình trạng lỗi của thiết bị ở bên dưới. Hãy phân tích hình ảnh này (AI Vision) kết hợp với mô tả chữ để phát hiện hư hỏng vật lý (nứt vỡ, sọc màn, cháy nổ) hoặc mã lỗi hiển thị."
            : "Khách hàng không đính kèm hình ảnh. Hãy chẩn đoán hoàn toàn dựa trên mô tả văn bản bên dưới.";

        return "Bạn là một trợ lý AI kỹ thuật chuyên nghiệp, đóng vai trò Kỹ sư chẩn đoán lỗi thiết bị điện tử, điện máy (Điện thoại, Laptop, TV, Máy giặt, Điều hòa...) và là Quản đốc phân công công việc thông minh tại cửa hàng DienMayPro.

HÃY PHÂN TÍCH TÌNH TRẠNG LỖI THIẾT BỊ SAU ĐÂY:
- Mô tả lỗi của khách hàng: \"{$issueDesc}\"
- Hình ảnh đính kèm: {$visionText}

DANH SÁCH KỸ THUẬT VIÊN HIỆN CÓ CỦA CỬA HÀNG:
{$techListStr}

NHIỆM VỤ CỦA BẠN:
1. Chẩn đoán phân loại lỗi (fault_type): Xác định lỗi thuộc về 'Phần cứng' (Hardware) hoặc 'Phần mềm' (Software) hoặc 'Chưa rõ' (Unknown).
2. Liệt kê 2-3 nguyên nhân chính có khả năng xảy ra nhất (probable_causes) bằng tiếng Việt.
3. Đưa ra 2-3 cảnh báo rủi ro (risk_warnings) bằng tiếng Việt (ví dụ: máy rơi nước có nguy cơ chập mạch cháy nổ, tránh cắm sạc pin, tránh tự ý sấy bằng máy sấy tóc v.v.).
4. Dự đoán linh kiện hoặc giải pháp cần sửa chữa/thay thế (replacement_parts) bằng tiếng Việt (ví dụ: màn hình linh kiện mới, IC nguồn, cài lại hệ điều hành).
5. Ước lượng chi phí sửa chữa tối thiểu (estimated_cost_min) và tối đa (estimated_cost_max) dạng số nguyên VND. Ví dụ: từ 500.000đ đến 1.500.000đ thì ghi 500000 và 1500000. Hãy đưa ra khoảng giá hợp lý sát với thị trường Việt Nam (vd: thay màn hình iPhone từ 1-4 triệu, cài win/phần mềm 100k-300k).
6. Đánh giá mức độ phức tạp của ca sửa chữa (complexity_level): 'Dễ', 'Trung bình' hoặc 'Khó'.
7. Liệt kê 2 kỹ năng cốt lõi cần có (recommended_skills) để xử lý ca này (ví dụ: 'thay màn hình', 'đo dòng bo mạch').
8. PHÂN CÔNG THÔNG MINH (Smart Job Dispatching): Chọn ra 1 Kỹ thuật viên phù hợp nhất từ danh sách có sẵn ở trên dựa trên tay nghề của họ so với tính chất ca lỗi, trả về ID (assigned_technician_id) và lý do phân công ngắn gọn bằng tiếng Việt (dispatch_reason).

ĐỊNH DẠNG ĐẦU RA BẮT BUỘC:
Bạn bắt buộc phải phản hồi bằng một chuỗi định dạng JSON duy nhất. KHÔNG bao gồm các thẻ markdown như ```json hay ```, KHÔNG viết thêm bất kỳ ký tự nào ngoài chuỗi JSON.
Cấu trúc JSON chính xác như sau:
{
  \"fault_type\": \"Phần cứng\" | \"Phần mềm\" | \"Chưa rõ\",
  \"probable_causes\": [\"Nguyên nhân 1\", \"Nguyên nhân 2\"],
  \"risk_warnings\": [\"Cảnh báo rủi ro 1\", \"Cảnh báo rủi ro 2\"],
  \"replacement_parts\": \"Tên linh kiện dự kiến thay thế hoặc giải pháp\",
  \"estimated_cost_min\": <Số nguyên VND>,
  \"estimated_cost_max\": <Số nguyên VND>,
  \"complexity_level\": \"Dễ\" | \"Trung bình\" | \"Khó\",
  \"recommended_skills\": [\"Kỹ năng 1\", \"Kỹ năng 2\"],
  \"assigned_technician_id\": <ID kỹ thuật viên được chọn>,
  \"dispatch_reason\": \"<Lý do AI phân công cho kỹ thuật viên này ngắn gọn bằng tiếng Việt>\"
}
";
    }

    /**
     * Thực hiện gọi API cURL đến Gemini.
     */
    private function callGeminiApi(string $apiKey, string $prompt, ?string $imageBase64 = null, ?string $imageMimeType = null): ?string
    {
        $models = [
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
        ];

        $parts = [];
        $parts[] = ['text' => $prompt];

        if ($imageBase64 && $imageMimeType) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $imageMimeType,
                    'data' => $imageBase64
                ]
            ];
        }

        foreach ($models as $model) {
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . trim($apiKey);
            $postData = json_encode([
                'contents' => [
                    ['parts' => $parts],
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
                Log::warning("Gemini model {$model} thất bại với mã HTTP {$httpCode} khi chẩn đoán sửa chữa. Đang thử model tiếp theo.");
            }
        }

        return null;
    }

    /**
     * Tạo kết quả chẩn đoán fallback mô phỏng khi không có kết nối API.
     */
    private function getFallbackDiagnosis(string $issueDesc, array $technicians): array
    {
        $issueLower = mb_strtolower($issueDesc);
        
        $faultType = 'Phần cứng';
        $probableCauses = ['Linh kiện hư hỏng vật lý', 'Mài mòn do quá trình sử dụng'];
        $riskWarnings = ['Hạn chế cắm sạc liên tục khi máy có dấu hiệu bất thường', 'Tránh đè ép mạnh lên màn hình bị hư hại'];
        $replacementParts = 'Linh kiện màn hình hoặc pin chính hãng';
        $costMin = 350000;
        $costMax = 1500000;
        $complexity = 'Trung bình';
        $skills = ['Thay thế linh kiện', 'Đo nguồn cơ bản'];
        $techId = !empty($technicians) ? $technicians[0]['id'] : null;
        $dispatchReason = 'Được chỉ định phụ trách tự động dựa trên mức độ sẵn sàng.';

        // Nhận diện một số từ khóa phổ biến để cho kết quả khớp hơn
        if (str_contains($issueLower, 'sọc') || str_contains($issueLower, 'vỡ') || str_contains($issueLower, 'màn hình') || str_contains($issueLower, 'nứt')) {
            $probableCauses = ['Màn hình bị tác động ngoại lực chấn động', 'Cáp màn hình bị lỏng hoặc đứt mạch'];
            $replacementParts = 'Cụm màn hình hiển thị mới';
            $costMin = 1200000;
            $costMax = 3500000;
            $skills = ['Thay màn hình hiển thị', 'Khử ẩm bo mạch'];
            // Gán cho Nam nếu có
            foreach ($technicians as $t) {
                if (str_contains(strtolower($t['name']), 'nam')) {
                    $techId = $t['id'];
                    $dispatchReason = 'Phân công tự động cho Trần Kỹ Thuật Nam vì đây là ca sửa chữa phần cứng và thay màn hình phức tạp.';
                    break;
                }
            }
        } elseif (str_contains($issueLower, 'pin') || str_contains($issueLower, 'sạc') || str_contains($issueLower, 'nóng') || str_contains($issueLower, 'nguồn')) {
            $faultType = 'Phần cứng';
            $probableCauses = ['Pin chai hoặc phồng cell pin', 'Chập mạch IC nguồn sạc'];
            $riskWarnings = ['Pin có dấu hiệu phồng/quá nhiệt có nguy cơ chập cháy cao. KHÔNG cắm sạc tiếp tục.', 'Đặt thiết bị ở nơi thoáng mát và mang tới trung tâm ngay lập tức.'];
            $replacementParts = 'Pin tiêu chuẩn nhà sản xuất';
            $costMin = 400000;
            $costMax = 1200000;
            $skills = ['Thay pin thiết bị', 'Kiểm tra dòng điện nguồn'];
        } elseif (str_contains($issueLower, 'win') || str_contains($issueLower, 'phần mềm') || str_contains($issueLower, 'hệ điều hành') || str_contains($issueLower, 'treo') || str_contains($issueLower, 'chậm') || str_contains($issueLower, 'virus')) {
            $faultType = 'Phần mềm';
            $probableCauses = ['Xung đột hệ điều hành hoặc phần mềm bên thứ ba', 'Nhiễm mã độc virus làm quá tải hệ thống'];
            $riskWarnings = ['Sao lưu dữ liệu quan trọng trước khi cài đặt lại hệ điều hành', 'Hạn chế đăng nhập các tài khoản ngân hàng khi máy bị nhiễm độc'];
            $replacementParts = 'Cài đặt lại hệ điều hành sạch và cài driver bản quyền';
            $costMin = 150000;
            $costMax = 350000;
            $complexity = 'Dễ';
            $skills = ['Xử lý phần mềm hệ điều hành', 'Khôi phục dữ liệu'];
            // Gán cho Hùng nếu có
            foreach ($technicians as $t) {
                if (str_contains(strtolower($t['name']), 'hùng') || str_contains(strtolower($t['name']), 'hung')) {
                    $techId = $t['id'];
                    $dispatchReason = 'Phân công tự động cho Phạm Kỹ Thuật Hùng vì ca này thuộc lĩnh vực phần mềm, tối ưu hóa hệ thống.';
                    break;
                }
            }
        }

        return [
            'ai_fault_type'         => $faultType,
            'ai_probable_causes'    => $probableCauses,
            'ai_risk_warnings'      => $riskWarnings,
            'ai_replacement_parts'  => $replacementParts,
            'ai_estimated_cost_min' => $costMin,
            'ai_estimated_cost_max' => $costMax,
            'ai_complexity_level'   => $complexity,
            'ai_recommended_skills' => $skills,
            'assigned_technician_id'=> $techId,
            'ai_dispatch_reason'    => $dispatchReason,
            'ai_diagnosed'          => true,
            'ai_diagnosed_at'       => now()->toDateTimeString()
        ];
    }
}
