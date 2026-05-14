<?php

namespace App\Console\Commands;

use App\Models\FlashSale;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncFlashSales extends Command
{
    protected $signature = 'flash-sales:sync';
    protected $description = 'Tự động tắt Flash Sale đã hết hạn';

    public function handle(): int
    {
        $now = Carbon::now();

        $updated = FlashSale::query()
            ->where('is_active', true)
            ->where('end_at', '<', $now)
            ->update(['is_active' => false]);

        $this->info("Đã tắt {$updated} Flash Sale hết hạn.");

        return self::SUCCESS;
    }
}
