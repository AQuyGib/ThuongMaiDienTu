<?php
// database/seeders/InventoryAuditSeeder.php

namespace Database\Seeders;

use App\Models\InventoryAudit;
use App\Models\InventoryAuditDetail;
use App\Models\InventoryItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventoryAuditSeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::take(5)->get();
        $admin = User::where('role_id', 1)->first();
        $adminId = $admin ? $admin->user_id : 1;

        if ($variants->isEmpty()) {
            return;
        }

        $warehouses = [
            'Kho A - HCM',
            'Kho B - HCM',
            'Kho C - Hà Nội',
        ];

        // 1. Tạo Phiếu kiểm kê 1: Trạng thái Completed (Đã cân bằng)
        $audit1 = InventoryAudit::create([
            'audit_code'    => 'AUD-00001',
            'warehouse_loc' => $warehouses[0],
            'status'        => 'Completed',
            'notes'         => 'Kiểm kê định kỳ đầu tháng',
            'created_by'    => $adminId,
            'completed_at'  => now()->subDays(10),
            'created_at'    => now()->subDays(10)->subHours(3),
        ]);

        foreach ($variants->take(3) as $variant) {
            $systemQty = InventoryItem::where('variant_id', $variant->variant_id)
                ->where('warehouse_loc', $warehouses[0])
                ->where('status', 'In_Stock')
                ->count();
            
            // Giả lập lệch thiếu 1 máy
            $actualQty = max(0, $systemQty - 1);
            $discrepancy = $actualQty - $systemQty;

            InventoryAuditDetail::create([
                'audit_id'        => $audit1->audit_id,
                'variant_id'      => $variant->variant_id,
                'system_qty'      => $systemQty,
                'actual_qty'      => $actualQty,
                'discrepancy_qty' => $discrepancy,
                'notes'           => 'Lệch hao hụt thực tế',
            ]);
        }

        // 2. Tạo Phiếu kiểm kê 2: Trạng thái Draft (Chờ duyệt cân bằng)
        $audit2 = InventoryAudit::create([
            'audit_code'    => 'AUD-00002',
            'warehouse_loc' => $warehouses[1],
            'status'        => 'Draft',
            'notes'         => 'Kiểm kho đột xuất giữa tháng',
            'created_by'    => $adminId,
            'created_at'    => now()->subDays(2),
        ]);

        foreach ($variants->skip(2)->take(3) as $variant) {
            $systemQty = InventoryItem::where('variant_id', $variant->variant_id)
                ->where('warehouse_loc', $warehouses[1])
                ->where('status', 'In_Stock')
                ->count();
            
            // Giả lập lệch thừa 2 máy
            $actualQty = $systemQty + 2;
            $discrepancy = 2;

            InventoryAuditDetail::create([
                'audit_id'        => $audit2->audit_id,
                'variant_id'      => $variant->variant_id,
                'system_qty'      => $systemQty,
                'actual_qty'      => $actualQty,
                'discrepancy_qty' => $discrepancy,
                'notes'           => 'Hàng thừa chưa ghi nhận PO',
            ]);
        }
    }
}
