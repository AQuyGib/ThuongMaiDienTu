<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Lớp InventoryService: Dịch vụ chịu trách nhiệm xử lý toàn bộ các thao tác liên quan đến quản lý kho hàng (Inventory).
 * Bao gồm các tác vụ:
 *   - Khấu trừ (deduct) và Hoàn trả (restore) tồn kho của Sản phẩm và Biến thể.
 *   - Kiểm tra tồn kho khả dụng trước khi đặt hàng.
 *   - Cập nhật trạng thái chi tiết của từng thiết bị/sản phẩm theo số IMEI/Serial (InventoryItem: In_Stock -> Sold, Sold -> In_Stock).
 *   - Ghi nhận lịch sử biến động kho chi tiết vào bảng `inventory_movements` để phục vụ đối soát, thống kê.
 *   - Tự động đồng bộ kho dựa trên trạng thái đơn hàng (ví dụ: chuyển từ Chờ xử lý sang Đã hủy thì hoàn kho).
 */
class InventoryService
{
    /**
     * Khấu trừ số lượng tồn kho (bán hàng).
     *
     * @param Model $stockable Đối tượng cần trừ kho (Product hoặc ProductVariant)
     * @param int $quantity Số lượng cần trừ
     * @param array $context Thông tin ngữ cảnh bổ sung (mã đơn hàng, người thực hiện, ghi chú)
     */
    public function deductStock(Model $stockable, int $quantity, array $context = []): void
    {
        // Sử dụng giá trị âm của số lượng để thực hiện trừ kho
        $this->changeStock($stockable, -abs($quantity), 'sale', $context);
    }

    /**
     * Hoàn trả số lượng tồn kho (hủy đơn, nhập lại hàng).
     *
     * @param Model $stockable Đối tượng cần cộng kho (Product hoặc ProductVariant)
     * @param int $quantity Số lượng cần hoàn trả
     * @param array $context Thông tin ngữ cảnh bổ sung
     */
    public function restoreStock(Model $stockable, int $quantity, array $context = []): void
    {
        // Sử dụng giá trị dương để thực hiện cộng kho
        $this->changeStock($stockable, abs($quantity), 'restock', $context);
    }

    /**
     * Kiểm tra nhanh xem sản phẩm/biến thể đó có đủ số lượng tồn kho đáp ứng yêu cầu đặt mua hay không.
     */
    public function checkAvailableStock(Model $stockable, int $quantity): bool
    {
        return $this->getCurrentStock($stockable) >= $quantity;
    }

    /**
     * Đánh dấu một sản phẩm cụ thể theo số IMEI/Serial đã được bán (In_Stock -> Sold).
     * Đi kèm với việc tự động trừ số lượng tồn kho của Biến thể tương ứng.
     */
    public function markInventoryItemSold(InventoryItem $item, array $context = []): void
    {
        DB::transaction(function () use ($item, $context) {
            // Khóa dòng dữ liệu của sản phẩm IMEI/Serial này để tránh tranh chấp cập nhật (lockForUpdate)
            $lockedItem = InventoryItem::query()->whereKey($item->getKey())->lockForUpdate()->first();

            if (! $lockedItem) {
                throw ValidationException::withMessages([
                    'inventory_item' => 'Không tìm thấy IMEI/Serial để cập nhật.',
                ]);
            }

            // Nếu sản phẩm đó đã được bán trước đó rồi, báo lỗi
            if ($lockedItem->status !== 'In_Stock') {
                throw ValidationException::withMessages([
                    'inventory_item' => 'IMEI/Serial này không còn ở trạng thái tồn kho.',
                ]);
            }

            // Chuyển trạng thái sang Đã bán
            $lockedItem->status = 'Sold';
            $lockedItem->save();

            // Khấu trừ số lượng tồn kho của biến thể sản phẩm liên quan
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

    /**
     * Đánh dấu hoàn kho một sản phẩm cụ thể theo số IMEI/Serial từ trạng thái Đã bán về Còn hàng (Sold -> In_Stock).
     * Sử dụng khi đơn hàng bị hủy bỏ hoặc khách trả lại hàng.
     */
    public function restoreInventoryItem(InventoryItem $item, array $context = []): void
    {
        DB::transaction(function () use ($item, $context) {
            // Khóa dòng để cập nhật an toàn
            $lockedItem = InventoryItem::query()->whereKey($item->getKey())->lockForUpdate()->first();

            if (! $lockedItem) {
                throw ValidationException::withMessages([
                    'inventory_item' => 'Không tìm thấy IMEI/Serial để hoàn kho.',
                ]);
            }

            // Chỉ thực hiện hoàn kho nếu sản phẩm thực sự đang ở trạng thái đã bán
            if ($lockedItem->status !== 'Sold') {
                return;
            }

            // Hoàn lại trạng thái Còn hàng
            $lockedItem->status = 'In_Stock';
            $lockedItem->save();

            // Cộng lại số lượng tồn kho cho biến thể tương ứng
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

    /**
     * Tự động điều chỉnh kho hàng khi trạng thái đơn hàng thay đổi.
     * Hỗ trợ xác định khi nào cần trừ kho (khi đơn hàng chuyển từ dự phòng sang chờ thanh toán/đang giao)
     * và khi nào cần hoàn kho (khi đơn hàng bị hủy bỏ).
     */
    public function syncOrderByStatus(Order $order, ?string $previousStatus, ?string $currentStatus): void
    {
        // Nạp trước danh sách chi tiết đơn hàng kèm theo IMEI/Serial và biến thể để tránh truy vấn N+1
        if (! $order->relationLoaded('details')) {
            $order->load('details.inventoryItem.variant');
        }

        // Trường hợp 1: Chuyển sang trạng thái cần giữ/trừ kho thực tế
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

        // Trường hợp 2: Chuyển sang trạng thái Hủy đơn, thực hiện hoàn trả lại kho hàng
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

    /**
     * Phương thức cốt lõi xử lý thay đổi tồn kho (Tăng/Giảm) của sản phẩm hoặc biến thể.
     * Sử dụng Pessimistic Locking (lockForUpdate) kết hợp Transaction để đảm bảo không bị cập nhật sai lệch số lượng.
     * Đồng thời tự động ghi nhận nhật ký vào bảng `inventory_movements` để quản lý biến động.
     */
    protected function changeStock(Model $stockable, int $quantityChange, string $type, array $context = []): void
    {
        $stockable = $this->resolveStockable($stockable);

        if (! $stockable) {
            throw ValidationException::withMessages([
                'stockable' => 'Không tìm thấy đối tượng tồn kho hợp lệ.',
            ]);
        }

        DB::transaction(function () use ($stockable, $quantityChange, $type, $context) {
            // Khóa dòng dữ liệu của sản phẩm/biến thể cần thay đổi
            $locked = $stockable->newQuery()->whereKey($stockable->getKey())->lockForUpdate()->first();

            if (! $locked) {
                throw ValidationException::withMessages([
                    'stockable' => 'Không tìm thấy bản ghi tồn kho để cập nhật.',
                ]);
            }

            $currentStock = (int) ($locked->stock ?? 0);
            $nextStock = $currentStock + $quantityChange;

            // Kiểm tra an toàn: Tồn kho sau cập nhật không được phép âm
            if ($nextStock < 0) {
                throw ValidationException::withMessages([
                    'stock' => 'Số lượng tồn kho không đủ để thực hiện thao tác này.',
                ]);
            }

            // Cập nhật giá trị tồn kho mới
            $locked->stock = $nextStock;
            $locked->save();

            // Nếu bảng nhật ký biến động kho tồn tại, ghi nhận lịch sử thay đổi chi tiết
            if (DB::getSchemaBuilder()->hasTable('inventory_movements')) {
                DB::table('inventory_movements')->insert([
                    'product_id' => $context['product_id'] ?? $this->getProductId($locked),
                    'variant_id' => $context['variant_id'] ?? $this->getVariantId($locked),
                    'order_id' => $context['order_id'] ?? null,
                    'reference_type' => $context['reference_type'] ?? null,
                    'reference_id' => $context['reference_id'] ?? null,
                    'type' => $type, // 'sale', 'restock', 'import', 'return',...
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

    /**
     * Điều chỉnh trực tiếp tồn kho cho một Biến thể sản phẩm cụ thể.
     */
    protected function adjustVariantStock(ProductVariant $variant, int $quantityChange, array $context = []): void
    {
        $this->changeStock($variant, $quantityChange, $quantityChange < 0 ? 'sale' : 'restock', $context);
    }

    /**
     * Lấy số lượng tồn kho hiện tại của đối tượng.
     */
    protected function getCurrentStock(Model $stockable): int
    {
        $stockable = $this->resolveStockable($stockable);

        if (! $stockable) {
            return 0;
        }

        return (int) ($stockable->stock ?? 0);
    }

    /**
     * Đảm bảo đối tượng truyền vào là một Model Eloquent hợp lệ (Product hoặc ProductVariant).
     */
    protected function resolveStockable($stockable): ?Model
    {
        if ($stockable instanceof Product || $stockable instanceof ProductVariant) {
            return $stockable;
        }

        return null;
    }

    /**
     * Lấy ID sản phẩm từ model.
     */
    protected function getProductId(Model $model): ?int
    {
        return property_exists($model, 'product_id') ? (int) $model->product_id : null;
    }

    /**
     * Lấy ID biến thể sản phẩm từ model.
     */
    protected function getVariantId(Model $model): ?int
    {
        return property_exists($model, 'variant_id') ? (int) $model->variant_id : null;
    }

    /**
     * Điều kiện xác định khi nào đơn hàng chuyển sang trạng thái cần khấu trừ tồn kho thực tế.
     * Ví dụ: Chuyển từ trạng thái khác sang một trong các trạng thái (Delivered, Shipping, Processing, Pending).
     */
    protected function shouldDeductStock(?string $previousStatus, ?string $currentStatus): bool
    {
        return in_array($currentStatus, ['Delivered', 'Shipping', 'Processing', 'Pending'], true)
            && ! in_array($previousStatus, ['Delivered', 'Shipping', 'Processing', 'Pending'], true);
    }

    /**
     * Điều kiện xác định khi nào đơn hàng chuyển sang trạng thái cần hoàn kho (khi đơn hàng bị hủy bỏ).
     */
    protected function shouldRestoreStock(?string $previousStatus, ?string $currentStatus): bool
    {
        return in_array($previousStatus, ['Delivered', 'Shipping', 'Processing', 'Pending'], true)
            && in_array($currentStatus, ['Cancelled'], true);
    }
}

