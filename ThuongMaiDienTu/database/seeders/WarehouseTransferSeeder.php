<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WarehouseTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Cập nhật safe_stock cho các sản phẩm và biến thể
        Product::query()->update(['safe_stock' => 5]);
        ProductVariant::query()->update(['safe_stock' => 5]);

        // Đặt mức safe_stock ngẫu nhiên cao cho khoảng 5 sản phẩm để kích hoạt cảnh báo đỏ
        $lowStockProducts = Product::take(5)->get();
        foreach ($lowStockProducts as $prod) {
            $prod->update(['safe_stock' => rand(15, 35)]);
        }

        $lowStockVariants = ProductVariant::take(8)->get();
        foreach ($lowStockVariants as $var) {
            $var->update(['safe_stock' => rand(12, 28)]);
        }

        // 2. Lấy hoặc tạo Supplier/Purchase Order
        $po = \App\Models\PurchaseOrder::first();
        if (!$po) {
            $supplier = \App\Models\Supplier::first();
            if (!$supplier) {
                $supplier = \App\Models\Supplier::create([
                    'name' => 'Công ty Cổ phần Bán lẻ FPT',
                    'phone' => '18006601',
                    'email' => 'fptshop@fpt.com',
                    'address' => '261 - 263 Khánh Hội, P.5, Q.4, TP. Hồ Chí Minh',
                ]);
            }
            $po = \App\Models\PurchaseOrder::create([
                'supplier_id' => $supplier->supplier_id,
                'total_cost' => 150000000,
            ]);
        }
        $poId = $po->po_id;

        $warehouses = [
            'Kho tổng (Main)',
            'Kho cửa hàng (Store)',
            'Kho A - HCM',
            'Kho B - HCM',
            'Kho C - Hà Nội',
            'Kho Trung Tâm - Đà Nẵng',
            'Kho Kỹ Thuật - TP. Thủ Đức',
        ];

        // Tạo 150 IMEI In_Stock phân bổ đều các kho để thoải mái chọn lựa
        $variants = ProductVariant::all();
        if ($variants->isNotEmpty()) {
            for ($i = 0; $i < 150; $i++) {
                $v = $variants->random();
                $serial = 'IMEI' . strtoupper(Str::random(7)) . rand(100, 999);
                InventoryItem::create([
                    'variant_id' => $v->variant_id,
                    'po_id' => $poId,
                    'warehouse_loc' => $warehouses[array_rand($warehouses)],
                    'status' => 'In_Stock',
                    'imei_serial' => $serial,
                ]);
            }
        }

        // 3. Tạo các phiếu điều chuyển mẫu (15 phiếu)
        $statuses = ['Pending', 'Completed', 'Cancelled'];
        $notes = [
            'Pending' => [
                'Điều chuyển gấp trưng bày sự kiện mở bán sản phẩm mới.',
                'Yêu cầu chi nhánh Hà Nội bổ sung hàng cho kho HCM.',
                'Chuyển hàng bảo hành sang trung tâm kỹ thuật sửa chữa.',
                'Khách đặt cọc cần gom máy từ các kho lẻ về kho trung tâm.',
                'Bổ sung tồn kho dự trữ cho chiến dịch Flash Sale cuối tuần.'
            ],
            'Completed' => [
                'Luân chuyển định kỳ đầu tháng giữa kho tổng và showroom.',
                'Đã chuyển máy cho nhân viên kỹ thuật kiểm tra phần cứng.',
                'Đã bàn giao đầy đủ máy mẫu cho bộ phận marketing chụp ảnh sản phẩm.',
                'Chuyển kho hoàn tất theo yêu cầu quản lý chi nhánh Quận 1.',
                'Chuyển hàng thành công phục vụ đổi trả cho khách hàng VIP.'
            ],
            'Cancelled' => [
                'Hủy do khách hàng hủy đơn hàng không lấy máy nữa.',
                'Sai lệch số lượng IMEI thực tế và phiếu tạo, làm lại phiếu mới.',
                'Hủy phiếu, chuyển sang phương án điều chuyển từ kho B thay vì kho A.',
                'Hủy do kho đích báo hết chỗ chứa dòng máy này.',
                'Người lập phiếu chọn nhầm địa chỉ kho đích.'
            ]
        ];

        for ($k = 1; $k <= 15; $k++) {
            $status = $statuses[array_rand($statuses)];
            $from = $warehouses[array_rand($warehouses)];
            
            // Lọc kho đến khác kho đi
            $to = $from;
            while ($to === $from) {
                $to = $warehouses[array_rand($warehouses)];
            }

            $noteList = $notes[$status];
            $note = $noteList[array_rand($noteList)];

            $transfer = WarehouseTransfer::create([
                'transfer_code' => 'TF-' . str_pad($k, 5, '0', STR_PAD_LEFT),
                'from_warehouse' => $from,
                'to_warehouse' => $to,
                'status' => $status,
                'notes' => $note,
                'created_by' => 1,
                'created_at' => now()->subDays(15 - $k)->subHours(rand(1, 12)),
            ]);

            // Liên kết một số items còn hàng ở kho nguồn vào phiếu
            $itemCount = rand(2, 5);
            $items = InventoryItem::where('warehouse_loc', $from)
                ->where('status', 'In_Stock')
                ->take($itemCount)
                ->get();

            foreach ($items as $item) {
                WarehouseTransferItem::create([
                    'transfer_id' => $transfer->transfer_id,
                    'item_id' => $item->item_id,
                ]);

                // Nếu là Completed, thực hiện chuyển kho thực tế của item đó sang kho đích
                if ($status === 'Completed') {
                    $item->update(['warehouse_loc' => $to]);
                }
            }
        }
    }
}
