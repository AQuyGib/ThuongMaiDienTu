<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Warranty;
use Carbon\Carbon;

class WarrantySeeder extends Seeder
{
    public function run()
    {
        // Chỉ tạo bảo hành cho các inventory item đã bán trong đơn hàng Delivered
        $deliveredOrders = Order::where('status', 'Delivered')->get();

        $count = 0;
        foreach ($deliveredOrders as $order) {
            $orderDetails = OrderDetail::where('order_id', $order->order_id)->get();

            foreach ($orderDetails as $detail) {
                // Bỏ qua nếu item đã có warranty
                if (Warranty::where('item_id', $detail->item_id)->exists()) {
                    continue;
                }

                // Ngày bắt đầu bảo hành = ngày giao hàng (delivered_at) hoặc ngày tạo đơn
                $startDate = $order->delivered_at
                    ? Carbon::parse($order->delivered_at)
                    : Carbon::parse($order->created_at);

                $endDate = (clone $startDate)->addMonths(12);
                $isExpired = $endDate->isPast();

                Warranty::create([
                    'item_id'         => $detail->item_id,
                    'start_date'      => $startDate->toDateString(),
                    'end_date'        => $endDate->toDateString(),
                    'warranty_status' => $isExpired ? 'expired' : 'active',
                    'warranty_type'   => 'manufacturer',
                    'note'            => 'Bảo hành chính hãng 12 tháng. Không áp dụng cho hư hỏng do rơi vỡ, vào nước.',
                ]);
                $count++;
            }
        }

        $this->command->info("WarrantySeeder: Đã tạo bảo hành cho {$count} sản phẩm đã giao thành công.");
    }
}
