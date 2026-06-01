<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AITestOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh sách khách hàng (role_id = 3)
        $customers = User::where('role_id', 3)->get();
        if ($customers->isEmpty()) {
            $this->command->warn('AITestOrderSeeder: Không có khách hàng nào (role_id=3). Sẽ dùng tài khoản admin tạm thời.');
            $customers = User::all();
            if ($customers->isEmpty()) {
                $this->command->error('AITestOrderSeeder: Không tìm thấy người dùng nào trong database.');
                return;
            }
        }

        // Lấy danh sách inventory items (không giới hạn In_Stock để đảm bảo luôn có dữ liệu)
        $availableItems = InventoryItem::with('variant.product')->get();
        if ($availableItems->isEmpty()) {
            $this->command->error('AITestOrderSeeder: Không có sản phẩm (inventory items) nào trong database.');
            return;
        }

        // Danh sách kịch bản test để AI nhận định rủi ro
        $scenarios = [
            [
                'name' => 'Nguyễn Văn An',
                'phone' => '0987654321',
                'address' => '123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh',
                'note' => 'Giao giờ hành chính, gọi trước khi giao 30 phút.',
                'date' => '2026-05-29 09:30:00',
                'status' => 'Pending'
            ],
            [
                'name' => 'Trần Thị Bình',
                'phone' => '0912345678',
                'address' => '456 Đường Nguyễn Trãi, Quận Thanh Xuân, Hà Nội',
                'note' => 'Cần giao gấp trong hôm nay, xin cảm ơn.',
                'date' => '2026-05-29 14:15:00',
                'status' => 'Pending'
            ],
            [
                'name' => 'Khách ảo 1',
                'phone' => '0000000000',
                'address' => 'Địa chỉ không rõ ràng, Tỉnh X',
                'note' => 'Ship nhanh gấp, không cần gọi điện thoại check đơn nhé!',
                'date' => '2026-05-29 23:45:00',
                'status' => 'Pending'
            ],
            [
                'name' => 'Lê Hoàng Cường',
                'phone' => '0905678901',
                'address' => '789 Đường Hùng Vương, Hải Châu, Đà Nẵng',
                'note' => null,
                'date' => '2026-05-30 08:00:00',
                'status' => 'Pending'
            ],
            [
                'name' => 'Phạm Minh Đức',
                'phone' => '0321654987',
                'address' => '999 Đường Cách Mạng Tháng 8, Quận 3, TP. Hồ Chí Minh',
                'note' => 'Đặt hộ người thân, số điện thoại người nhận: 0944332211.',
                'date' => '2026-05-30 11:20:00',
                'status' => 'Pending'
            ],
            [
                'name' => 'Nguyễn Thị E',
                'phone' => '0123456789',
                'address' => 'Hẻm ảo, ngách ảo, Hà Nội',
                'note' => 'Đơn hàng test hệ thống, vui lòng click duyệt ngay giùm tôi.',
                'date' => '2026-05-30 17:35:00',
                'status' => 'Pending'
            ],
            [
                'name' => 'Vũ Hoàng Nam',
                'phone' => '0888888888',
                'address' => 'Biệt thự ABC, Phú Mỹ Hưng, Quận 7, TP. Hồ Chí Minh',
                'note' => 'Giao hàng cao cấp, đóng gói cẩn thận giúp shop nhé.',
                'date' => '2026-05-30 20:10:00',
                'status' => 'Pending'
            ],
            [
                'name' => 'Spam Bot 99',
                'phone' => '0999999999',
                'address' => 'spam spam spam spam spam',
                'note' => 'đơn hàng rác spam hệ thống 123456',
                'date' => '2026-05-31 02:15:00',
                'status' => 'Pending'
            ],
            [
                'name' => 'Hoàng Thu Trang',
                'phone' => '0977112233',
                'address' => '101 Đường Láng, Đống Đa, Hà Nội',
                'note' => 'Giao buổi chiều sau 14h.',
                'date' => '2026-05-31 10:00:00',
                'status' => 'Pending'
            ],
            [
                'name' => 'Đỗ Duy Mạnh',
                'phone' => '0966445566',
                'address' => 'Chợ Đồng Xuân, Hoàn Kiếm, Hà Nội',
                'note' => 'Đến nơi gọi em ra lấy.',
                'date' => '2026-05-31 11:45:00',
                'status' => 'Pending'
            ]
        ];

        $orderCount = 0;
        foreach ($scenarios as $idx => $sc) {
            $customer = $customers->random();
            
            // Lấy 1-2 sản phẩm ngẫu nhiên
            $numItems = rand(1, 2);
            $itemsForOrder = $availableItems->random(min($numItems, $availableItems->count()));
            
            $totalAmount = 0;
            $orderItems = [];
            foreach ($itemsForOrder as $item) {
                $variant = $item->variant;
                $product = $variant ? $variant->product : null;
                $price = ($variant && $variant->price > 0)
                    ? $variant->price
                    : ($product ? $product->base_price : rand(1, 10) * 100000);

                $totalAmount += $price;
                $orderItems[] = [
                    'item_id' => $item->item_id,
                    'price' => $price,
                    'product_name' => $product->name ?? 'Sản phẩm mẫu',
                ];
            }

            $shippingFee = 30000;
            $finalAmount = $totalAmount + $shippingFee;
            $createdAt = Carbon::parse($sc['date']);
            $orderCode = 'TEST' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) . ($idx + 1);

            // Tạo đơn hàng (không phát sự kiện để tránh tự động quét nếu có hook)
            $order = Order::withoutEvents(function () use (
                $customer, $orderCode, $sc, $totalAmount, $shippingFee, $finalAmount, $createdAt
            ) {
                return Order::create([
                    'order_code' => $orderCode,
                    'user_id' => $customer->user_id,
                    'customer_name' => $sc['name'],
                    'customer_phone' => $sc['phone'],
                    'shipping_address' => $sc['address'],
                    'note' => $sc['note'],
                    'order_type' => 'Online',
                    'total_amount' => $totalAmount,
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => 0,
                    'final_amount' => $finalAmount,
                    'payment_method' => 'COD',
                    'payment_status' => 'pending',
                    'status' => $sc['status'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            });

            // Tạo chi tiết đơn hàng
            foreach ($orderItems as $oi) {
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'item_id' => $oi['item_id'],
                    'price' => $oi['price'],
                    'product_name' => $oi['product_name'],
                ]);
            }
            $orderCount++;
        }

        $this->command->info("AITestOrderSeeder đã tạo thành công {$orderCount} đơn hàng thử nghiệm từ ngày 29/05 đến 31/05.");
    }
}
