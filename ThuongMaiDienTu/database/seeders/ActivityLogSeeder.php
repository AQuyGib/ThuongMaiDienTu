<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\AuditHasher;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa sạch log cũ trước khi seed mới
        DB::table('activity_logs')->truncate();

        $logsData = [
            [
                'event' => 'login',
                'causer_type' => 'App\Models\User',
                'causer_id' => 1,
                'causer_name' => 'Quản Trị Viên',
                'subject_type' => null,
                'subject_id' => null,
                'old_values' => null,
                'new_values' => null,
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                'created_at' => now()->subDays(4)->setTime(8, 30, 0)->toDateTimeString(),
            ],
            [
                'event' => 'created',
                'causer_type' => 'App\Models\User',
                'causer_id' => 1,
                'causer_name' => 'Quản Trị Viên',
                'subject_type' => 'App\Models\Product',
                'subject_id' => 1,
                'old_values' => null,
                'new_values' => [
                    'name' => 'Điện thoại iPhone 15 Pro Max 256GB',
                    'base_price' => 29500000,
                    'safe_stock' => 50,
                    'is_active' => true,
                ],
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                'created_at' => now()->subDays(4)->setTime(9, 15, 23)->toDateTimeString(),
            ],
            [
                'event' => 'created',
                'causer_type' => 'App\Models\User',
                'causer_id' => 1,
                'causer_name' => 'Quản Trị Viên',
                'subject_type' => 'App\Models\Product',
                'subject_id' => 2,
                'old_values' => null,
                'new_values' => [
                    'name' => 'Smart Tivi Samsung Neo QLED 4K 65 inch',
                    'base_price' => 32000000,
                    'safe_stock' => 15,
                    'is_active' => true,
                ],
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                'created_at' => now()->subDays(4)->setTime(9, 20, 11)->toDateTimeString(),
            ],
            [
                'event' => 'updated',
                'causer_type' => 'App\Models\User',
                'causer_id' => 1,
                'causer_name' => 'Quản Trị Viên',
                'subject_type' => 'App\Models\Product',
                'subject_id' => 1,
                'old_values' => [
                    'base_price' => 29500000,
                ],
                'new_values' => [
                    'base_price' => 28990000,
                ],
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                'created_at' => now()->subDays(3)->setTime(10, 5, 0)->toDateTimeString(),
            ],
            [
                'event' => 'created',
                'causer_type' => 'System',
                'causer_id' => 0,
                'causer_name' => 'System Scheduler',
                'subject_type' => 'App\Models\Order',
                'subject_id' => 101,
                'old_values' => null,
                'new_values' => [
                    'customer_name' => 'Trần Văn An',
                    'customer_phone' => '0901234501',
                    'total_amount' => 28990000,
                    'payment_method' => 'COD',
                    'status' => 'Pending',
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Symfony/HttpClient',
                'created_at' => now()->subDays(3)->setTime(14, 22, 45)->toDateTimeString(),
            ],
            [
                'event' => 'updated',
                'causer_type' => 'App\Models\User',
                'causer_id' => 2,
                'causer_name' => 'Nguyễn Quản Lý',
                'subject_type' => 'App\Models\Order',
                'subject_id' => 101,
                'old_values' => [
                    'status' => 'Pending',
                ],
                'new_values' => [
                    'status' => 'Processing',
                ],
                'ip_address' => '192.168.1.28',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created_at' => now()->subDays(3)->setTime(14, 45, 12)->toDateTimeString(),
            ],
            [
                'event' => 'updated',
                'causer_type' => 'App\Models\User',
                'causer_id' => 1,
                'causer_name' => 'Quản Trị Viên',
                'subject_type' => 'App\Models\Setting',
                'subject_id' => 'theme_color',
                'old_values' => [
                    'setting_value' => '#4f46e5',
                ],
                'new_values' => [
                    'setting_value' => '#3b82f6',
                ],
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(2)->setTime(11, 0, 4)->toDateTimeString(),
            ],
            [
                'event' => 'export',
                'causer_type' => 'App\Models\User',
                'causer_id' => 2,
                'causer_name' => 'Nguyễn Quản Lý',
                'subject_type' => 'App\Models\User',
                'subject_id' => null,
                'old_values' => null,
                'new_values' => [
                    'format' => 'Excel',
                    'scope' => 'all_employees',
                ],
                'ip_address' => '192.168.1.28',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created_at' => now()->subDays(2)->setTime(15, 30, 0)->toDateTimeString(),
            ],
            [
                'event' => 'deleted',
                'causer_type' => 'App\Models\User',
                'causer_id' => 1,
                'causer_name' => 'Quản Trị Viên',
                'subject_type' => 'App\Models\Review',
                'subject_id' => 45,
                'old_values' => [
                    'product_id' => 1,
                    'user_id' => 10,
                    'content' => 'Hàng giả mạo không đúng chất lượng!!',
                    'rating' => 1,
                ],
                'new_values' => null,
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(1)->setTime(9, 40, 15)->toDateTimeString(),
            ],
            [
                'event' => 'updated',
                'causer_type' => 'App\Models\User',
                'causer_id' => 1,
                'causer_name' => 'Quản Trị Viên',
                'subject_type' => 'App\Models\User',
                'subject_id' => 10,
                'old_values' => [
                    'comment_banned_until' => null,
                ],
                'new_values' => [
                    'comment_banned_until' => now()->addDays(3)->toDateTimeString(),
                ],
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(1)->setTime(9, 41, 0)->toDateTimeString(),
            ],
            [
                'event' => 'created',
                'causer_type' => 'App\Models\User',
                'causer_id' => 2,
                'causer_name' => 'Nguyễn Quản Lý',
                'subject_type' => 'App\Models\PurchaseOrder',
                'subject_id' => 5,
                'old_values' => null,
                'new_values' => [
                    'supplier_id' => 2,
                    'total_cost' => 145000000,
                ],
                'ip_address' => '192.168.1.28',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created_at' => now()->subDays(1)->setTime(16, 20, 30)->toDateTimeString(),
            ],
            [
                'event' => 'created',
                'causer_type' => 'App\Models\User',
                'causer_id' => 1,
                'causer_name' => 'Quản Trị Viên',
                'subject_type' => 'App\Models\Installment',
                'subject_id' => 12,
                'old_values' => null,
                'new_values' => [
                    'installment_code' => 'TGP-HCM-260604-AB73C',
                    'partner' => 'Shinhan Finance',
                    'period' => 12,
                    'loan_amount' => 20000000,
                    'customer_name' => 'Lê Hoàng Cường',
                ],
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->setTime(10, 12, 5)->toDateTimeString(),
            ],
            [
                'event' => 'updated',
                'causer_type' => 'App\Models\User',
                'causer_id' => 1,
                'causer_name' => 'Quản Trị Viên',
                'subject_type' => 'App\Models\Installment',
                'subject_id' => 12,
                'old_values' => [
                    'status' => 'Pending_Approval',
                ],
                'new_values' => [
                    'status' => 'Approved',
                ],
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->setTime(10, 15, 30)->toDateTimeString(),
            ],
            [
                'event' => 'created',
                'causer_type' => 'App\Models\User',
                'causer_id' => 2,
                'causer_name' => 'Nguyễn Quản Lý',
                'subject_type' => 'App\Models\RewardCatalog',
                'subject_id' => 8,
                'old_values' => null,
                'new_values' => [
                    'code' => 'WHEEL_IPHONE15',
                    'name' => 'Voucher 50% iPhone 15',
                    'reward_type' => 'wheel_prize',
                    'points_cost' => 0,
                    'stock' => 5,
                    'min_rank' => 'Bac',
                ],
                'ip_address' => '192.168.1.28',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created_at' => now()->setTime(11, 5, 45)->toDateTimeString(),
            ]
        ];

        $computedHash = null;

        foreach ($logsData as $data) {
            $payload = [
                'event' => $data['event'],
                'causer_type' => $data['causer_type'],
                'causer_id' => $data['causer_id'],
                'causer_name' => $data['causer_name'],
                'subject_type' => $data['subject_type'],
                'subject_id' => $data['subject_id'],
                'old_values' => $data['old_values'] ? json_encode($data['old_values'], JSON_UNESCAPED_UNICODE) : null,
                'new_values' => $data['new_values'] ? json_encode($data['new_values'], JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $data['ip_address'],
                'user_agent' => $data['user_agent'],
                'created_at' => $data['created_at'],
            ];

            // Tạo chuỗi băm progressive liên kết
            $payload['hash_chain'] = AuditHasher::generateHashChain($payload, $computedHash);

            DB::table('activity_logs')->insert($payload);

            // Cập nhật computedHash cho lần lặp kế tiếp
            $computedHash = $payload['hash_chain'];
        }
    }
}
