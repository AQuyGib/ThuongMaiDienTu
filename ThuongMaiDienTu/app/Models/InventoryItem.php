<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model {
    protected $primaryKey = 'item_id';
    public $timestamps = false;
    protected $guarded = [];

    protected static function booted()
    {
        static::deleted(function (InventoryItem $item) {
            $variant = $item->variant;
            if ($variant) {
                $stock = $variant->inventoryItems()->count();
                $service = app(\App\Services\NotificationService::class);
                $threshold = $service->getLowStockThreshold();

                if ($stock > 0 && $stock <= $threshold) {
                    $service->notifyAdmins([
                        'type' => 'inventory.low_stock',
                        'title' => 'Tồn kho thấp: ' . ($variant->product->name ?? 'Sản phẩm'),
                        'content' => 'Biến thể ' . $variant->label . ' hiện chỉ còn ' . $stock . ' sản phẩm trong kho.',
                        'action_url' => url('/admin/products/' . ($variant->product->product_id ?? 0)),
                        'data' => [
                            'product_id' => $variant->product_id,
                            'variant_id' => $variant->variant_id,
                            'stock' => $stock,
                            'threshold' => $threshold,
                        ],
                    ]);
                }
            }
        });
    }

    public function variant() {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
    public function warranties() {
        return $this->hasMany(Warranty::class, 'item_id');
    }

    /**
     * Kiểm tra sản phẩm còn trong thời hạn bảo hành hay không.
     * Dựa vào field "Bảo hành" trong specifications của Product (VD: "24 tháng chính hãng").
     * Tính từ ngày đặt hàng (order.created_at).
     */
    public function canClaimWarranty($order = null)
    {
        if ($this->status !== 'Sold') {
            return false;
        }

        // Lấy ngày mua hàng từ order
        $orderDate = $this->resolveOrderDate($order);
        if (!$orderDate) {
            return false;
        }

        // Lấy số tháng bảo hành từ specifications sản phẩm
        $warrantyMonths = $this->getWarrantyMonthsFromProduct();
        if ($warrantyMonths <= 0) {
            return false;
        }

        $now = \Carbon\Carbon::now();
        $warrantyEnd = \Carbon\Carbon::parse($orderDate)->addMonths($warrantyMonths);

        return $now->lessThanOrEqualTo($warrantyEnd);
    }

    /**
     * Kiểm tra sản phẩm còn trong thời hạn đổi trả hay không.
     * Cố định 30 ngày kể từ ngày đặt hàng (order.created_at).
     */
    public function canClaimReturn($order = null)
    {
        if ($this->status !== 'Sold') {
            return false;
        }

        // Nếu sản phẩm đã hết hạn bảo hành, chắc chắn không thể đổi trả
        if (!$this->canClaimWarranty($order)) {
            return false;
        }

        $orderDate = $this->resolveOrderDate($order);
        if (!$orderDate) {
            return false;
        }

        $now = \Carbon\Carbon::now();
        $daysSinceOrder = (int) abs($now->diffInDays(\Carbon\Carbon::parse($orderDate)));

        return ($daysSinceOrder <= 30);
    }

    /**
     * Lấy số tháng bảo hành từ specifications sản phẩm.
     * Parse chuỗi dạng "12 tháng chính hãng", "24 tháng", "18 tháng"...
     */
    public function getWarrantyMonthsFromProduct()
    {
        $product = $this->variant->product ?? null;
        if (!$product) {
            return 12; // Mặc định 12 tháng
        }

        $specs = $product->specifications;
        if (is_string($specs)) {
            $specs = json_decode($specs, true);
        }

        if (!is_array($specs) || !isset($specs['Bảo hành'])) {
            return 12; // Mặc định 12 tháng
        }

        $warrantyStr = $specs['Bảo hành']; // VD: "24 tháng chính hãng"
        if (preg_match('/(\d+)\s*tháng/iu', $warrantyStr, $matches)) {
            return (int) $matches[1];
        }

        return 12;
    }

    private function resolveOrderDate($order = null)
    {
        if ($order) {
            return $order->delivered_at ?: $order->created_at;
        }

        // Fallback: Tìm từ order_details → order
        $detail = \App\Models\OrderDetail::where('item_id', $this->item_id)->first();
        if ($detail) {
            $linkedOrder = $detail->order ?? null;
            if ($linkedOrder) {
                return $linkedOrder->delivered_at ?: $linkedOrder->created_at;
            }
        }

        return null;
    }
}