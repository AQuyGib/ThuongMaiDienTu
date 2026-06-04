<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\Installment;
use App\Models\InstallmentPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class InstallmentSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa sạch dữ liệu cũ
        InstallmentPayment::query()->delete();
        Installment::query()->delete();

        $customers = User::where('role_id', 3)->get();
        
        // Lấy danh sách sản phẩm mẫu (có biến thể)
        $availableItems = InventoryItem::with('variant.product')
            ->where('status', 'In_Stock')
            ->get();

        if ($availableItems->isEmpty()) {
            $this->command->warn('InstallmentSeeder: Không có inventory items In_Stock. Tạo tạm thời.');
            // Tạo tạm nếu không có để seeder không bị crash
            $variant = \App\Models\ProductVariant::first();
            if (!$variant) {
                $this->command->error('InstallmentSeeder: Không tìm thấy ProductVariant nào. Bỏ qua.');
                return;
            }
            $po = \App\Models\PurchaseOrder::first() ?: \App\Models\PurchaseOrder::create([
                'supplier_id' => 1,
                'total_cost' => 1000000
            ]);
            $availableItems = collect([
                InventoryItem::create([
                    'variant_id' => $variant->variant_id,
                    'po_id' => $po->po_id,
                    'imei_serial' => 'TG-SEED-' . strtoupper(Str::random(10)),
                    'warehouse_loc' => 'Cửa hàng',
                    'status' => 'In_Stock',
                ])
            ]);
        }

        // Định nghĩa 15 kịch bản trả góp đa dạng (POS tại quầy, Online, và Đăng ký mới tại quầy)
        $scenarios = [
            [
                'customer_name' => 'Nguyễn Văn A',
                'customer_phone' => '0912345678',
                'customer_id_card' => '031095012345',
                'method' => 'financial_company',
                'partner' => 'Home Credit',
                'period' => 6,
                'status' => 'Pending_Approval',
                'trade_in' => false,
                'is_guest' => false,
                'is_counter_register' => false,
                'order_type' => 'POS',
            ],
            [
                'customer_name' => 'Trần Thị B',
                'customer_phone' => '0987654321',
                'customer_id_card' => '031096001234',
                'method' => 'financial_company',
                'partner' => 'Shinhan Finance',
                'period' => 12,
                'status' => 'Approved',
                'trade_in' => true,
                'is_guest' => false,
                'is_counter_register' => false,
                'order_type' => 'Online',
            ],
            [
                'customer_name' => 'Phạm Văn C',
                'customer_phone' => '0905123456',
                'customer_id_card' => '031093005678',
                'method' => 'financial_company',
                'partner' => 'HD Saison',
                'period' => 9,
                'status' => 'Rejected',
                'rejection_reason' => 'Nợ xấu nhóm 3 CIC ngân hàng nhà nước.',
                'trade_in' => false,
                'is_guest' => false,
                'is_counter_register' => false,
                'order_type' => 'POS',
            ],
            [
                'customer_name' => 'Lê Thị D',
                'customer_phone' => '0934567890',
                'customer_id_card' => null,
                'method' => 'credit_card',
                'partner' => 'Vietcombank Credit Card',
                'period' => 6,
                'status' => 'Paying',
                'trade_in' => false,
                'is_guest' => true, // Guest POS
                'is_counter_register' => false,
                'order_type' => 'POS',
            ],
            [
                'customer_name' => 'Hoàng Văn E',
                'customer_phone' => '0978123456',
                'customer_id_card' => '031092004321',
                'method' => 'kredivo',
                'partner' => 'Kredivo',
                'period' => 3,
                'status' => 'Completed',
                'trade_in' => false,
                'is_guest' => false,
                'is_counter_register' => false,
                'order_type' => 'Online',
            ],
            [
                'customer_name' => 'Vũ Thị F',
                'customer_phone' => '0945123789',
                'customer_id_card' => '031099009876',
                'method' => 'financial_company',
                'partner' => 'Mirae Asset',
                'period' => 12,
                'status' => 'Paying',
                'trade_in' => true,
                'is_guest' => true, // Guest POS
                'is_counter_register' => false,
                'order_type' => 'POS',
            ],
            [
                'customer_name' => 'Đỗ Văn G',
                'customer_phone' => '0916123456',
                'customer_id_card' => null,
                'method' => 'credit_card',
                'partner' => 'Techcombank Credit Card',
                'period' => 12,
                'status' => 'Pending_Approval',
                'trade_in' => false,
                'is_guest' => false,
                'is_counter_register' => false,
                'order_type' => 'Online',
            ],
            [
                'customer_name' => 'Ngô Thị H',
                'customer_phone' => '0989234567',
                'customer_id_card' => '031094007654',
                'method' => 'financial_company',
                'partner' => 'Home Credit',
                'period' => 6,
                'status' => 'Cancelled',
                'trade_in' => false,
                'is_guest' => false,
                'is_counter_register' => false,
                'order_type' => 'POS',
            ],
            [
                'customer_name' => 'Lý Văn I',
                'customer_phone' => '0908345678',
                'customer_id_card' => '031091002345',
                'method' => 'financial_company',
                'partner' => 'Shinhan Finance',
                'period' => 12,
                'status' => 'Approved',
                'trade_in' => false,
                'is_guest' => true, // Guest POS
                'is_counter_register' => false,
                'order_type' => 'POS',
            ],
            [
                'customer_name' => 'Bùi Thị K',
                'customer_phone' => '0967456789',
                'customer_id_card' => null,
                'method' => 'credit_card',
                'partner' => 'MB Bank Credit Card',
                'period' => 9,
                'status' => 'Paying',
                'trade_in' => true,
                'is_guest' => false,
                'is_counter_register' => false,
                'order_type' => 'Online',
            ],
            [
                'customer_name' => 'Dương Văn L',
                'customer_phone' => '0912987654',
                'customer_id_card' => '031088012345',
                'method' => 'kredivo',
                'partner' => 'Kredivo',
                'period' => 6,
                'status' => 'Completed',
                'trade_in' => false,
                'is_guest' => true, // Guest POS
                'is_counter_register' => false,
                'order_type' => 'POS',
            ],
            [
                'customer_name' => 'Phan Thị M',
                'customer_phone' => '0938765432',
                'customer_id_card' => '031097008765',
                'method' => 'financial_company',
                'partner' => 'HD Saison',
                'period' => 4,
                'status' => 'Pending_Approval',
                'trade_in' => false,
                'is_guest' => false,
                'is_counter_register' => false,
                'order_type' => 'Online',
            ],
            // 3 Người đăng ký tài khoản mới trực tiếp tại quầy (Counter-registered)
            [
                'customer_name' => 'Đặng Văn POS',
                'customer_phone' => '0981112223',
                'customer_id_card' => '030095001122',
                'method' => 'financial_company',
                'partner' => 'Home Credit',
                'period' => 6,
                'status' => 'Paying',
                'trade_in' => false,
                'is_guest' => false,
                'is_counter_register' => true, // Đăng ký tài khoản tại quầy
                'order_type' => 'POS',
            ],
            [
                'customer_name' => 'Nguyễn Thị Quầy',
                'customer_phone' => '0963334445',
                'customer_id_card' => '035096005566',
                'method' => 'credit_card',
                'partner' => 'Vietcombank Credit Card',
                'period' => 12,
                'status' => 'Approved',
                'trade_in' => true,
                'is_guest' => false,
                'is_counter_register' => true, // Đăng ký tài khoản tại quầy
                'order_type' => 'POS',
            ],
            [
                'customer_name' => 'Trần Minh Counter',
                'customer_phone' => '0907778889',
                'customer_id_card' => '031094009988',
                'method' => 'kredivo',
                'partner' => 'Kredivo',
                'period' => 6,
                'status' => 'Pending_Approval',
                'trade_in' => false,
                'is_guest' => false,
                'is_counter_register' => true, // Đăng ký tài khoản tại quầy
                'order_type' => 'POS',
            ],
        ];

        foreach ($scenarios as $index => $sc) {
            // Chọn ngẫu nhiên sản phẩm còn trong kho
            $item = $availableItems->random();
            $variant = $item->variant;
            $product = $variant ? $variant->product : null;
            
            $productPrice = (int) ($variant ? $variant->total_price : ($product ? $product->base_price : 15000000));
            
            // Tính toán số tiền theo nghiệp vụ
            $interestRate = 0;
            $serviceFee = 0;

            if ($sc['method'] === 'financial_company') {
                if ($sc['partner'] === 'Home Credit') {
                    $interestRate = 0.01;
                    $serviceFee = 50000;
                } elseif ($sc['partner'] === 'HD Saison') {
                    $interestRate = 0.015;
                    $serviceFee = 60000;
                } elseif ($sc['partner'] === 'Mirae Asset') {
                    $interestRate = 0.02;
                    $serviceFee = 70000;
                } else {
                    $interestRate = 0.01;
                    $serviceFee = 50000;
                }
            } elseif ($sc['method'] === 'kredivo') {
                if ($sc['period'] !== 3) {
                    $interestRate = 0.025;
                    $serviceFee = 30000;
                }
            } else {
                $serviceFee = 20000;
            }

            // Trả trước ngẫu nhiên từ 10% đến 40% giá trị sản phẩm (đối với thẻ tín dụng có thể trả trước 0)
            $prepayPercent = ($sc['method'] === 'credit_card' || $sc['method'] === 'kredivo') ? 0 : rand(1, 4) * 10;
            $prepayAmount = (int) round(($productPrice * $prepayPercent) / 100);
            
            // Khoản vay
            $loanAmount = $productPrice - $prepayAmount;

            $monthlyNoInterest = $loanAmount / $sc['period'];
            $monthlyInterest = $loanAmount * $interestRate;
            $monthlyPayment = (int) round($monthlyNoInterest + $monthlyInterest + $serviceFee);
            $totalPayment = $prepayAmount + ($monthlyPayment * $sc['period']);
            $diffAmount = $totalPayment - $productPrice;

            // Xác định hoặc tạo tài khoản user
            $userId = null;
            if ($sc['is_counter_register']) {
                // Tạo tài khoản mới giả lập đăng ký tại quầy
                $user = User::create([
                    'email' => strtolower(Str::slug($sc['customer_name'])) . rand(10, 99) . '@dienmaypro.com.vn',
                    'role_id' => 3, // Customer
                    'full_name' => $sc['customer_name'],
                    'password_hash' => \Hash::make('123456'),
                    'member_tier' => 'Dong',
                    'status' => 'Active',
                    'phone_number' => $sc['customer_phone'],
                ]);
                $userId = $user->user_id;
            } elseif (!$sc['is_guest'] && $customers->isNotEmpty()) {
                $userId = $customers->random()->user_id;
            }

            // Ngày tạo
            $createdAt = Carbon::now()->subDays(rand(5, 30));

            // 1. Tạo đơn hàng (tắt events)
            $orderCode = ($sc['order_type'] === 'POS' ? 'POS' : 'DH') . $createdAt->format('Ymd') . rand(100, 999);
            $order = Order::withoutEvents(function () use (
                $userId, $productPrice, $sc, $orderCode, $createdAt
            ) {
                return Order::create([
                    'order_code' => $orderCode,
                    'user_id' => $userId,
                    'customer_name' => $sc['customer_name'],
                    'customer_phone' => $sc['customer_phone'],
                    'shipping_address' => $sc['order_type'] === 'POS' ? 'Nhận tại cửa hàng' : 'Giao hàng tận nơi',
                    'note' => ($sc['order_type'] === 'POS' ? 'Hợp đồng trả góp tại quầy qua ' : 'Đăng ký trả góp online qua ') . $sc['partner'] . ' kỳ hạn ' . $sc['period'] . ' tháng.',
                    'order_type' => $sc['order_type'],
                    'total_amount' => $productPrice,
                    'shipping_fee' => 0,
                    'discount_amount' => 0,
                    'wallet_points_used' => 0,
                    'final_amount' => $productPrice,
                    'payment_method' => 'Installment',
                    'payment_status' => $sc['status'] === 'Completed' ? 'paid' : 'pending',
                    'status' => in_array($sc['status'], ['Approved', 'Paying', 'Completed']) ? 'Processing' : ($sc['status'] === 'Rejected' ? 'Cancelled' : 'Pending'),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            });

            // 2. Tạo chi tiết đơn hàng
            OrderDetail::create([
                'order_id' => $order->order_id,
                'item_id' => $item->item_id,
                'price' => $productPrice,
                'product_name' => $product->name ?? 'Thiết bị',
            ]);

            // Cập nhật kho
            if (in_array($sc['status'], ['Approved', 'Paying', 'Completed'])) {
                $item->update(['status' => 'Sold']);
            }

            // 3. Tạo hợp đồng trả góp theo mẫu chuyên nghiệp
            if ($sc['order_type'] === 'POS') {
                $installmentCode = 'TGP-HCM-' . $createdAt->format('ymd') . '-' . strtoupper(Str::random(5));
            } else {
                $installmentCode = 'TGO-' . $createdAt->format('ymd') . '-' . strtoupper(Str::random(6));
            }
            
            // Giả lập điểm AI rủi ro ngẫu nhiên
            $aiRiskScore = rand(10, 90);
            $aiRiskLevel = $aiRiskScore < 30 ? 'Low' : ($aiRiskScore < 75 ? 'Medium' : 'High');
            $aiRecommendation = $aiRiskScore < 30 ? 'Approve' : ($aiRiskScore < 75 ? 'Review' : 'Reject');

            $installment = Installment::create([
                'order_id' => $order->order_id,
                'installment_code' => $installmentCode,
                'method' => $sc['method'],
                'partner' => $sc['partner'],
                'period' => $sc['period'],
                'product_price' => $productPrice,
                'prepay_amount' => $prepayAmount,
                'loan_amount' => $loanAmount,
                'monthly_payment' => $monthlyPayment,
                'interest_rate' => $interestRate,
                'service_fee' => $serviceFee,
                'total_payment' => $totalPayment,
                'difference_amount' => $diffAmount > 0 ? $diffAmount : 0,
                'customer_name' => $sc['customer_name'],
                'customer_phone' => $sc['customer_phone'],
                'customer_id_card' => $sc['customer_id_card'],
                'trade_in' => $sc['trade_in'],
                'status' => $sc['status'],
                'rejection_reason' => $sc['rejection_reason'] ?? null,
                'ai_risk_score' => $aiRiskScore,
                'ai_risk_level' => $aiRiskLevel,
                'ai_analysis' => [
                    'risk_score' => $aiRiskScore,
                    'risk_level' => $aiRiskLevel,
                    'findings' => [
                        'Thông tin cá nhân rõ ràng, SĐT trùng khớp.',
                        $sc['trade_in'] ? 'Yếu tố tích cực: Có đăng ký Trade-in thu cũ đổi mới.' : 'Không tham gia chương trình Trade-in.',
                        $aiRiskScore > 70 ? 'Phát hiện lịch sử tín dụng chưa tốt hoặc CCCD có nghi vấn.' : 'Lịch sử giao dịch ổn định.'
                    ],
                    'recommendation' => $aiRecommendation,
                    'reason' => 'Đánh giá rủi ro tự động dựa trên thuật toán AI nội bộ.'
                ],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // 4. Tạo lịch trình thanh toán (đối với Approved, Paying, Completed)
            if (in_array($sc['status'], ['Approved', 'Paying', 'Completed'])) {
                for ($term = 1; $term <= $sc['period']; $term++) {
                    $dueDate = $createdAt->copy()->addMonths($term);
                    
                    // Xác định trạng thái của từng kỳ thanh toán
                    $payStatus = 'Unpaid';
                    $payDate = null;
                    $transCode = null;

                    if ($sc['status'] === 'Completed') {
                        $payStatus = 'Paid';
                        $payDate = $dueDate->copy()->subDays(rand(1, 5));
                        $transCode = 'TX-PAY-' . strtoupper(Str::random(8));
                    } elseif ($sc['status'] === 'Paying') {
                        // Trả trước vài kỳ, các kỳ sau chưa trả
                        if ($term <= 2) {
                            $payStatus = 'Paid';
                            $payDate = $dueDate->copy()->subDays(rand(1, 5));
                            $transCode = 'TX-PAY-' . strtoupper(Str::random(8));
                        } elseif ($dueDate->isPast()) {
                            $payStatus = 'Overdue';
                        }
                    }

                    InstallmentPayment::create([
                        'installment_id' => $installment->id,
                        'term_number' => $term,
                        'amount' => $monthlyPayment,
                        'due_date' => $dueDate->format('Y-m-d'),
                        'payment_date' => $payDate ? $payDate->format('Y-m-d') : null,
                        'status' => $payStatus,
                        'transaction_code' => $transCode,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        }
    }
}
