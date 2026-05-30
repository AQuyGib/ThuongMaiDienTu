<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = Supplier::query()->orderBy('supplier_id')->get();

        if ($suppliers->isEmpty()) {
            $this->command?->warn('PurchaseOrderSeeder skipped: chưa có suppliers.');
            return;
        }

        $seedData = [
            ['supplier_index' => 0, 'total_cost' => 125000000],
            ['supplier_index' => 1, 'total_cost' => 98000000],
            ['supplier_index' => 2, 'total_cost' => 76000000],
            ['supplier_index' => 3, 'total_cost' => 54000000],
            ['supplier_index' => 4, 'total_cost' => 87000000],
            ['supplier_index' => 5, 'total_cost' => 69000000],
            ['supplier_index' => 6, 'total_cost' => 45000000],
            ['supplier_index' => 7, 'total_cost' => 112000000],
        ];

        foreach ($seedData as $index => $data) {
            $supplier = $suppliers[$data['supplier_index'] % $suppliers->count()];

            PurchaseOrder::updateOrCreate(
                [
                    'supplier_id' => $supplier->supplier_id,
                    'total_cost' => $data['total_cost'],
                ],
                [
                    'created_at' => now()->subDays($index * 3),
                ]
            );
        }

        $this->command?->info('PurchaseOrderSeeder created sample purchase orders.');
    }
}
