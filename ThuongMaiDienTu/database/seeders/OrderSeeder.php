<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder tạo dữ liệu mẫu đơn hàng cho admin.
 * Mỗi đơn hàng lấy ngẫu nhiên 1-3 inventory item (In_Stock) và gắn cho 1 khách hàng ngẫu nhiên.
 */
class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh sách khách hàng (role_id = 3)
        $customers = User::where('role_id', 3)->get();
        if ($customers->isEmpty()) {
            $this->command->warn('OrderSeeder: Không có khách hàng nào (role_id=3). Bỏ qua.');
            return;
        }

        // Lấy toàn bộ inventory item In_Stock kèm variant + product
        $availableItems = InventoryItem::with('variant.product')
            ->where('status', 'In_Stock')
            ->get()
            ->shuffle();

        if ($availableItems->count() < 5) {
            $this->command->warn('OrderSeeder: Không đủ inventory items In_Stock. Bỏ qua.');
            return;
        }

        $statuses = ['Pending', 'Pending', 'BaoCK', 'Shipping', 'Shipping', 'Delivered', 'Delivered', 'Delivered', 'Delivered', 'Cancelled'];
        $paymentMethods = ['COD', 'VNPAY', 'MoMo', 'COD', 'COD'];
        $addresses = [
            'Bình Định', 'Linh Trung, Thủ Đức', 'Quận 1, TP.HCM', 'Quận 7, TP.HCM',
            'Hà Đông, Hà Nội', 'Cầu Giấy, Hà Nội', 'Đà Nẵng', 'Huế',
            'Nha Trang', 'Biên Hòa, Đồng Nai', 'Long Xuyên, An Giang',
        ];
        $notes = [
            null, null, null,
            'Giao giờ hành chính',
            'Gọi trước khi giao',
            '[Hệ thống ghi nhận khách bấm xác nhận thủ công]',
            '[Khách tự hủy trên web]',
            'Giao nhanh giúp em',
        ];

        $itemIndex = 0;
        $totalItems = $availableItems->count();
        $orderCount = min(15, intdiv($totalItems, 2)); // Tạo tối đa 15 đơn

        for ($i = 0; $i < $orderCount; $i++) {
            // Random 1-3 sản phẩm cho mỗi đơn
            $numProducts = rand(1, 3);
            if ($itemIndex + $numProducts > $totalItems) break;

            $customer = $customers->random();
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $status = $statuses[array_rand($statuses)];
            $address = $addresses[array_rand($addresses)];
            $note = $notes[array_rand($notes)];

            // Tính tổng tiền từ sản phẩm thực tế
            $totalAmount = 0;
            $orderItems = [];
            for ($j = 0; $j < $numProducts; $j++) {
                $item = $availableItems[$itemIndex];
                $itemIndex++;

                $variant = $item->variant;
                $product = $variant ? $variant->product : null;
                $price = ($variant && $variant->price > 0)
                    ? $variant->price
                    : ($product ? $product->base_price : rand(1, 50) * 1000000);

                $totalAmount += $price;
                $orderItems[] = [
                    'item_id' => $item->item_id,
                    'price' => $price,
                    'product_name' => $product->name ?? 'Sản phẩm',
                ];
            }

            $shippingFee = collect([0, 30000, 50000])->random();
            $finalAmount = $totalAmount + $shippingFee;
            $createdAt = Carbon::now()
                ->subDays(rand(0, 7))
                ->subHours(rand(0, 23))
                ->subMinutes(rand(0, 59));

            // Tạo mã đơn hàng dạng 8 chữ số
            $orderCode = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

            // Tạo đơn hàng (tắt events để không trigger notification khi seeding)
            $order = Order::withoutEvents(function () use (
                $customer, $orderCode, $paymentMethod, $status, $address, $note,
                $totalAmount, $shippingFee, $finalAmount, $createdAt
            ) {
                return Order::create([
                    'order_code' => $orderCode,
                    'user_id' => $customer->user_id,
                    'customer_name' => $customer->full_name,
                    'customer_phone' => $customer->phone_number ?: '0' . rand(300000000, 999999999),
                    'shipping_address' => $address,
                    'note' => $note,
                    'order_type' => 'Online',
                    'total_amount' => $totalAmount,
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => 0,
                    'final_amount' => $finalAmount,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $status === 'Delivered' ? 'paid' : 'pending',
                    'status' => $status,
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

                // Cập nhật trạng thái inventory item thành Sold
                InventoryItem::where('item_id', $oi['item_id'])->update(['status' => 'Sold']);
            }
        }

        // Đảm bảo Quản trị viên (email: admin@dienmaypro.com.vn) có ít nhất 2 đơn hàng trong lịch sử để test
        $adminUser = User::where('role_id', 1)->first();
        if ($adminUser && $itemIndex + 4 <= $totalItems) {
            $adminOrderScenarios = [
                [
                    'status' => 'Delivered',
                    'payment_status' => 'paid',
                    'note' => 'Đơn hàng hoàn thành của Quản trị viên.',
                    'days_ago' => 5
                ],
                [
                    'status' => 'Pending',
                    'payment_status' => 'pending',
                    'note' => 'Đơn hàng mới đang xử lý của Quản trị viên.',
                    'days_ago' => 0
                ]
            ];

            foreach ($adminOrderScenarios as $scenario) {
                // Lấy 1 hoặc 2 sản phẩm cho đơn
                $numProducts = rand(1, 2);
                $totalAmount = 0;
                $orderItems = [];
                for ($j = 0; $j < $numProducts; $j++) {
                    if ($itemIndex >= $totalItems) break;
                    $item = $availableItems[$itemIndex];
                    $itemIndex++;

                    $variant = $item->variant;
                    $product = $variant ? $variant->product : null;
                    $price = ($variant && $variant->price > 0)
                        ? $variant->price
                        : ($product ? $product->base_price : rand(1, 5) * 1000000);

                    $totalAmount += $price;
                    $orderItems[] = [
                        'item_id' => $item->item_id,
                        'price' => $price,
                        'product_name' => $product->name ?? 'Sản phẩm',
                    ];
                }

                if (empty($orderItems)) continue;

                $shippingFee = 30000;
                $finalAmount = $totalAmount + $shippingFee;
                $createdAt = Carbon::now()->subDays($scenario['days_ago'])->subHours(2);
                $orderCode = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

                // Tạo đơn hàng (Đồng bộ kích hoạt bảo hành tự động)
                $order = Order::create([
                    'order_code' => $orderCode,
                    'user_id' => $adminUser->user_id,
                    'customer_name' => $adminUser->full_name,
                    'customer_phone' => $adminUser->phone_number ?: '0905123456',
                    'shipping_address' => '123 Đường Admin, TP. Hồ Chí Minh',
                    'note' => $scenario['note'],
                    'order_type' => 'Online',
                    'total_amount' => $totalAmount,
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => 0,
                    'final_amount' => $finalAmount,
                    'payment_method' => 'COD',
                    'payment_status' => $scenario['payment_status'],
                    'status' => $scenario['status'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                foreach ($orderItems as $oi) {
                    OrderDetail::create([
                        'order_id' => $order->order_id,
                        'item_id' => $oi['item_id'],
                        'price' => $oi['price'],
                        'product_name' => $oi['product_name'],
                    ]);

                    // Cập nhật trạng thái inventory item thành Sold
                    InventoryItem::where('item_id', $oi['item_id'])->update(['status' => 'Sold']);
                }
            }
        }

        $this->command->info("OrderSeeder created {$orderCount} sample orders.");
    }
}
