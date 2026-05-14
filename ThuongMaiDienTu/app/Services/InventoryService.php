<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function deductStock(Model $stockable, int $quantity, array $context = []): void
    {
        $this->changeStock($stockable, -abs($quantity), 'sale', $context);
    }

    public function restoreStock(Model $stockable, int $quantity, array $context = []): void
    {
        $this->changeStock($stockable, abs($quantity), 'restock', $context);
    }

    public function checkAvailableStock(Model $stockable, int $quantity): bool
    {
        return $this->getCurrentStock($stockable) >= $quantity;
    }

    public function markInventoryItemSold(InventoryItem $item, array $context = []): void
    {
        DB::transaction(function () use ($item, $context) {
            $lockedItem = InventoryItem::query()->whereKey($item->getKey())->lockForUpdate()->first();

            if (! $lockedItem) {
                throw ValidationException::withMessages([
                    'inventory_item' => 'Không tìm thấy IMEI/Serial để cập nhật.',
                ]);
            }

            if ($lockedItem->status !== 'In_Stock') {
                throw ValidationException::withMessages([
                    'inventory_item' => 'IMEI/Serial này không còn ở trạng thái tồn kho.',
                ]);
            }

            $lockedItem->status = 'Sold';
            $lockedItem->save();

            $variant = $lockedItem->variant;
            if ($variant) {
                $this->adjustVariantStock($variant, -1, $context + [
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->variant_id,
                    'note' => $context['note'] ?? 'Trừ kho khi chốt đơn',
                ]);
            }
        });
    }

    public function restoreInventoryItem(InventoryItem $item, array $context = []): void
    {
        DB::transaction(function () use ($item, $context) {
            $lockedItem = InventoryItem::query()->whereKey($item->getKey())->lockForUpdate()->first();

            if (! $lockedItem) {
                throw ValidationException::withMessages([
                    'inventory_item' => 'Không tìm thấy IMEI/Serial để hoàn kho.',
                ]);
            }

            if ($lockedItem->status !== 'Sold') {
                return;
            }

            $lockedItem->status = 'In_Stock';
            $lockedItem->save();

            $variant = $lockedItem->variant;
            if ($variant) {
                $this->adjustVariantStock($variant, 1, $context + [
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->variant_id,
                    'note' => $context['note'] ?? 'Hoàn kho khi hủy đơn',
                ]);
            }
        });
    }

    public function syncOrderByStatus(Order $order, ?string $previousStatus, ?string $currentStatus): void
    {
        if (! $order->relationLoaded('details')) {
            $order->load('details.inventoryItem.variant');
        }

        if ($this->shouldDeductStock($previousStatus, $currentStatus)) {
            foreach ($order->details as $detail) {
                $item = $detail->inventoryItem;
                if (! $item) {
                    continue;
                }

                $this->markInventoryItemSold($item, [
                    'order_id' => $order->order_id,
                    'reference_type' => 'order',
                    'reference_id' => $order->order_id,
                    'created_by' => $order->staff_id ?? $order->user_id,
                    'note' => 'Trừ kho theo đơn hàng #' . $order->order_id,
                ]);
            }
            return;
        }

        if ($this->shouldRestoreStock($previousStatus, $currentStatus)) {
            foreach ($order->details as $detail) {
                $item = $detail->inventoryItem;
                if (! $item) {
                    continue;
                }

                $this->restoreInventoryItem($item, [
                    'order_id' => $order->order_id,
                    'reference_type' => 'order',
                    'reference_id' => $order->order_id,
                    'created_by' => $order->staff_id ?? $order->user_id,
                    'note' => 'Hoàn kho theo đơn hàng #' . $order->order_id,
                ]);
            }
        }
    }

    protected function changeStock(Model $stockable, int $quantityChange, string $type, array $context = []): void
    {
        $stockable = $this->resolveStockable($stockable);

        if (! $stockable) {
            throw ValidationException::withMessages([
                'stockable' => 'Không tìm thấy đối tượng tồn kho hợp lệ.',
            ]);
        }

        DB::transaction(function () use ($stockable, $quantityChange, $type, $context) {
            $locked = $stockable->newQuery()->whereKey($stockable->getKey())->lockForUpdate()->first();

            if (! $locked) {
                throw ValidationException::withMessages([
                    'stockable' => 'Không tìm thấy bản ghi tồn kho để cập nhật.',
                ]);
            }

            $currentStock = (int) ($locked->stock ?? 0);
            $nextStock = $currentStock + $quantityChange;

            if ($nextStock < 0) {
                throw ValidationException::withMessages([
                    'stock' => 'Số lượng tồn kho không đủ để thực hiện thao tác này.',
                ]);
            }

            $locked->stock = $nextStock;
            $locked->save();

            if (DB::getSchemaBuilder()->hasTable('inventory_movements')) {
                DB::table('inventory_movements')->insert([
                    'product_id' => $context['product_id'] ?? $this->getProductId($locked),
                    'variant_id' => $context['variant_id'] ?? $this->getVariantId($locked),
                    'order_id' => $context['order_id'] ?? null,
                    'reference_type' => $context['reference_type'] ?? null,
                    'reference_id' => $context['reference_id'] ?? null,
                    'type' => $type,
                    'quantity_change' => $quantityChange,
                    'before_stock' => $currentStock,
                    'after_stock' => $nextStock,
                    'note' => $context['note'] ?? null,
                    'created_by' => $context['created_by'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    protected function adjustVariantStock(ProductVariant $variant, int $quantityChange, array $context = []): void
    {
        $this->changeStock($variant, $quantityChange, $quantityChange < 0 ? 'sale' : 'restock', $context);
    }

    protected function getCurrentStock(Model $stockable): int
    {
        $stockable = $this->resolveStockable($stockable);

        if (! $stockable) {
            return 0;
        }

        return (int) ($stockable->stock ?? 0);
    }

    protected function resolveStockable($stockable): ?Model
    {
        if ($stockable instanceof Product || $stockable instanceof ProductVariant) {
            return $stockable;
        }

        return null;
    }

    protected function getProductId(Model $model): ?int
    {
        return property_exists($model, 'product_id') ? (int) $model->product_id : null;
    }

    protected function getVariantId(Model $model): ?int
    {
        return property_exists($model, 'variant_id') ? (int) $model->variant_id : null;
    }

    protected function shouldDeductStock(?string $previousStatus, ?string $currentStatus): bool
    {
        return in_array($currentStatus, ['Delivered', 'Shipping', 'Processing', 'Pending'], true)
            && ! in_array($previousStatus, ['Delivered', 'Shipping', 'Processing', 'Pending'], true);
    }

    protected function shouldRestoreStock(?string $previousStatus, ?string $currentStatus): bool
    {
        return in_array($previousStatus, ['Delivered', 'Shipping', 'Processing', 'Pending'], true)
            && in_array($currentStatus, ['Cancelled'], true);
    }
}
