<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\AIOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProcessOrderWithAI - Job chạy ngầm phân tích rủi ro đơn hàng bằng AI
 */
class ProcessOrderWithAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $orderId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(AIOrderService $aiOrderService): void
    {
        Log::info("Bắt đầu phân tích đơn hàng #{$this->orderId} bằng AI...");

        // Tìm đơn hàng kèm thông tin chi tiết
        $order = Order::with(['user', 'details.inventoryItem.variant.product'])->find($this->orderId);

        if (!$order) {
            Log::warning("Không tìm thấy đơn hàng #{$this->orderId} để phân tích AI.");
            return;
        }

        try {
            $result = $aiOrderService->analyzeOrder($order);
            Log::info("Phân tích đơn hàng #{$this->orderId} thành công: Trạng thái = {$result['status']}, Điểm rủi ro = {$result['risk_score']}");
        } catch (\Throwable $e) {
            Log::error("Lỗi khi phân tích đơn hàng #{$this->orderId} bằng AI: " . $e->getMessage());
            
            // Cập nhật trạng thái lỗi
            $order->update([
                'ai_status' => 'flagged',
                'ai_analysis' => 'Lỗi hệ thống khi gọi AI: ' . $e->getMessage()
            ]);
        }
    }
}
