<?php

namespace App\Jobs;

use App\Services\AuditHasher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogAuditEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->connection = 'sync';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::transaction(function () {
                // 1. Khóa bi quan dòng cuối để đảm bảo thứ tự hash chain không bị đè (Race Condition)
                $lastLog = DB::table('activity_logs')
                    ->lockForUpdate()
                    ->orderBy('log_id', 'desc')
                    ->first();

                $previousHash = $lastLog ? $lastLog->hash_chain : null;

                // 2. Tạo chuỗi băm bảo mật liên kết với dòng trước đó
                $this->payload['hash_chain'] = AuditHasher::generateHashChain($this->payload, $previousHash);

                // 3. Ghi log vào cơ sở dữ liệu
                DB::table('activity_logs')->insert($this->payload);

                // 4. Kích hoạt thông báo cảnh báo nếu có hành vi xuất file hàng loạt hoặc hành vi đáng ngờ
                $this->evaluateAlertRules();
            });
        } catch (\Throwable $e) {
            Log::error("Failed to write audit log: " . $e->getMessage(), [
                'payload' => $this->payload,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Đánh giá quy tắc cảnh báo chủ động
     */
    protected function evaluateAlertRules(): void
    {
        $event = $this->payload['event'];
        $subjectType = $this->payload['subject_type'];

        // Quy tắc: Nếu có hành động xuất báo cáo (export) liên quan đến User hoặc Order
        if ($event === 'export' && in_array($subjectType, ['App\Models\User', 'App\Models\Order'])) {
            // Đếm số lượng export của Causer trong 1 giờ qua
            $count = DB::table('activity_logs')
                ->where('causer_type', $this->payload['causer_type'])
                ->where('causer_id', $this->payload['causer_id'])
                ->where('event', 'export')
                ->where('created_at', '>=', now()->subHour())
                ->count();

            // Nếu vượt quá 3 lần/giờ, kích hoạt cảnh báo nguy cơ rò rỉ dữ liệu
            if ($count >= 3) {
                $this->sendAlertNotification("Cảnh báo rò rỉ dữ liệu: Nhân sự '{$this->payload['causer_name']}' thực hiện xuất báo cáo {$count} lần trong 1 giờ qua.");
            }
        }
    }

    /**
     * Gửi tin nhắn cảnh báo bảo mật
     */
    protected function sendAlertNotification(string $message): void
    {
        // Có thể mở rộng tích hợp với Telegram/Slack webhook thật
        Log::warning("[SECURITY ALERT] " . $message);

        // Mô phỏng Webhook gọi ra ngoài
        $telegramToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');
        
        if ($telegramToken && $chatId) {
            try {
                \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$telegramToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => "⚠️ *CẢNH BÁO BẢO MẬT HỆ THỐNG DIENMAYPRO*\n\n" . $message,
                    'parse_mode' => 'Markdown'
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send Telegram alert: " . $e->getMessage());
            }
        }
    }
}
