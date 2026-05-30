<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variants = ProductVariant::query()->orderBy('variant_id')->get();
        $purchaseOrders = PurchaseOrder::query()->orderBy('po_id')->get();

        if ($variants->isEmpty() || $purchaseOrders->isEmpty()) {
            $this->command?->warn('InventorySeeder skipped: thiếu product_variants hoặc purchase_orders.');
            return;
        }

        $warehouses = [
            'Kho A - HCM',
            'Kho B - HCM',
            'Kho C - Hà Nội',
            'Kho Trung Tâm - Đà Nẵng',
            'Kho Kỹ Thuật - TP. Thủ Đức',
        ];

        $statuses = ['In_Stock', 'Sold', 'Defective'];
        $inserted = 0;

        foreach ($variants->take(12) as $index => $variant) {
            $count = rand(2, 5);

            for ($i = 0; $i < $count; $i++) {
                $serial = strtoupper(Str::random(12)) . sprintf('%03d', $index + $i + 1);

                InventoryItem::updateOrCreate(
                    ['imei_serial' => $serial],
                    [
                        'variant_id' => $variant->variant_id,
                        'po_id' => $purchaseOrders->random()->po_id,
                        'warehouse_loc' => $warehouses[array_rand($warehouses)],
                        'status' => $statuses[array_rand($statuses)],
                    ]
                );

                $inserted++;
            }
        }

        $this->command?->info("InventorySeeder created {$inserted} inventory items.");
    }
}
