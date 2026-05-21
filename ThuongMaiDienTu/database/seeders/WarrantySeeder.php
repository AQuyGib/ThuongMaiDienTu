<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\Warranty;
use Carbon\Carbon;

class WarrantySeeder extends Seeder
{
    public function run()
    {
        $items = InventoryItem::all();

        foreach ($items as $item) {
            // Skip if already has warranty
            if (Warranty::where('item_id', $item->item_id)->exists()) {
                continue;
            }

            // Random start date within last 2 years
            $startDate = Carbon::now()->subDays(rand(0, 730));
            $endDate = (clone $startDate)->addMonths(12); // 12 months warranty

            $isExpired = $endDate->isPast();

            Warranty::create([
                'item_id'         => $item->item_id,
                'start_date'      => $startDate->toDateString(),
                'end_date'        => $endDate->toDateString(),
                'warranty_status' => $isExpired ? 'expired' : 'active',
                'warranty_type'   => 'manufacturer',
                'note'            => 'Bảo hành chính hãng 12 tháng. Không áp dụng cho hư hỏng do rơi vỡ, vào nước.',
            ]);
        }

        $this->command->info('Seeded warranties for ' . $items->count() . ' inventory items.');
    }
}
