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
}