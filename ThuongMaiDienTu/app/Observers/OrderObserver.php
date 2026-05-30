<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\InventoryService;

class OrderObserver
{
    public function saved(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $previousStatus = $order->getOriginal('status');
        $currentStatus = $order->status;

        app(InventoryService::class)->syncOrderByStatus($order, $previousStatus, $currentStatus);
    }
}
