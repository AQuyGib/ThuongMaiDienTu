<?php
// database/seeders/InventoryMovementSeeder.php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class InventoryMovementSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả biến thể sản phẩm
        $variants = ProductVariant::with('product')->get();
        $admin = User::where('role_id', 1)->first();
        $adminId = $admin ? $admin->user_id : 1;
        $orders = Order::all();

        if ($variants->isEmpty()) {
            return;
        }

        // Tạo dữ liệu biến động cho từng biến thể
        foreach ($variants as $variant) {
            // Thiết lập tồn kho ban đầu ngẫu nhiên từ 20 đến 50
            $currentStock = rand(20, 50);
            $date = Carbon::now()->subDays(30);

            // 1. Giao dịch nhập kho ban đầu
            InventoryMovement::create([
                'product_id' => $variant->product_id,
                'variant_id' => $variant->variant_id,
                'type' => 'import',
                'quantity_change' => $currentStock,
                'before_stock' => 0,
                'after_stock' => $currentStock,
                'note' => 'Nhập kho ban đầu khai trương cơ sở',
                'created_by' => $adminId,
                'created_at' => $date->copy(),
            ]);

            // Sinh từ 5 đến 12 giao dịch ngẫu nhiên tiếp theo trong 30 ngày qua
            $numTransactions = rand(5, 12);
            for ($i = 0; $i < $numTransactions; $i++) {
                // Tăng ngày lên ngẫu nhiên
                $date->addHours(rand(12, 48));
                if ($date->isAfter(Carbon::now())) {
                    break;
                }

                $type = $this->getRandomType();
                $qtyChange = 0;
                $note = '';
                $orderId = null;

                switch ($type) {
                    case 'sale':
                        $qtyChange = -rand(1, 3);
                        // Đảm bảo không bị âm kho
                        if ($currentStock + $qtyChange < 0) {
                            $type = 'import';
                            $qtyChange = rand(10, 20);
                            $note = 'Nhập bổ sung hàng hóa bán chạy từ nhà cung cấp';
                        } else {
                            if ($orders->isEmpty()) {
                                $orderId = null;
                                $note = 'Xuất kho bán lẻ theo Đơn hàng';
                            } else {
                                $order = $orders->random();
                                $orderId = $order->order_id;
                                $note = "Xuất kho bán lẻ theo Đơn hàng #" . $order->order_code;
                            }
                        }
                        break;

                    case 'import':
                        $qtyChange = rand(5, 15);
                        $note = 'Nhập hàng định kỳ từ nhà phân phối';
                        break;

                    case 'restock':
                        $qtyChange = rand(1, 2);
                        $note = 'Hoàn hàng về kho do đơn hàng bị hủy bỏ';
                        break;

                    case 'return':
                        $qtyChange = rand(1, 2);
                        $note = 'Khách trả hàng bảo hành/lỗi kỹ thuật';
                        break;

                    case 'adjustment':
                        // Có thể tăng hoặc giảm nhẹ do kiểm kê
                        $qtyChange = rand(0, 1) ? -1 : 1;
                        $note = 'Cân chỉnh tồn kho sau khi kiểm kê đối chiếu thực tế';
                        break;
                }

                $before = $currentStock;
                $currentStock += $qtyChange;

                InventoryMovement::create([
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->variant_id,
                    'type' => $type,
                    'quantity_change' => $qtyChange,
                    'before_stock' => $before,
                    'after_stock' => $currentStock,
                    'note' => $note,
                    'order_id' => $orderId,
                    'created_by' => $adminId,
                    'created_at' => $date->copy(),
                ]);
            }
        }
    }

    /**
     * Lấy ngẫu nhiên loại biến động theo tỷ lệ thực tế (Bán hàng chiếm tỷ trọng lớn nhất)
     */
    private function getRandomType(): string
    {
        $rand = rand(1, 100);

        if ($rand <= 55) {
            return 'sale'; // 55% bán hàng
        } elseif ($rand <= 75) {
            return 'import'; // 20% nhập hàng
        } elseif ($rand <= 85) {
            return 'restock'; // 10% hoàn kho đơn hủy
        } elseif ($rand <= 92) {
            return 'return'; // 7% khách trả hàng lỗi
        } else {
            return 'adjustment'; // 8% cân bằng kho
        }
    }
}
