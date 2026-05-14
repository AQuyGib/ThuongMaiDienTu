<?php

namespace App\Services;

use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FlashSaleService
{
    public function getActiveFlashSale(): ?FlashSale
    {
        $now = Carbon::now();

        return FlashSale::query()
            ->with(['products' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->where('is_active', true)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->orderByDesc('start_at')
            ->first();
    }

    public function getFlashSaleProductFor(Product $product): ?FlashSaleProduct
    {
        return $this->getActiveFlashSale()?->products->firstWhere('product_id', $product->product_id);
    }

    public function getEffectivePrice(Product $product): int
    {
        $flashSaleProduct = $this->getFlashSaleProductFor($product);

        return $flashSaleProduct && $this->canApplySale($flashSaleProduct)
            ? (int) $flashSaleProduct->sale_price
            : (int) $product->base_price;
    }

    public function isFlashSaleProduct(Product $product): bool
    {
        return (bool) $this->getFlashSaleProductFor($product);
    }

    public function getRemainingQuantity(FlashSaleProduct $flashSaleProduct): int
    {
        return max(0, (int) $flashSaleProduct->stock_limit - (int) $flashSaleProduct->sold_quantity);
    }

    public function canApplySale(FlashSaleProduct $flashSaleProduct): bool
    {
        return $flashSaleProduct->is_active && $this->getRemainingQuantity($flashSaleProduct) > 0;
    }

    public function reserveQuantity(FlashSaleProduct $flashSaleProduct, int $quantity): bool
    {
        if ($quantity <= 0) {
            return true;
        }

        return DB::transaction(function () use ($flashSaleProduct, $quantity) {
            $locked = FlashSaleProduct::query()
                ->whereKey($flashSaleProduct->getKey())
                ->lockForUpdate()
                ->first();

            if (! $locked || ! $this->canApplySale($locked) || $this->getRemainingQuantity($locked) < $quantity) {
                return false;
            }

            $locked->increment('sold_quantity', $quantity);
            return true;
        });
    }

    public function releaseQuantity(FlashSaleProduct $flashSaleProduct, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($flashSaleProduct, $quantity) {
            $locked = FlashSaleProduct::query()
                ->whereKey($flashSaleProduct->getKey())
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                return;
            }

            $locked->sold_quantity = max(0, (int) $locked->sold_quantity - $quantity);
            $locked->save();
        });
    }

    public function lockCartFlashSale(array $cart): bool
    {
        $lockedItems = [];

        foreach ($this->flashSaleCartItems($cart) as $productId => $item) {
            $product = Product::find($productId);
            $flashSaleProduct = $product ? $this->getFlashSaleProductFor($product) : null;
            $quantity = (int) ($item['quantity'] ?? 0);

            if (! $flashSaleProduct || ! $this->reserveQuantity($flashSaleProduct, $quantity)) {
                $this->releaseLockedItems($lockedItems);
                return false;
            }

            $lockedItems[] = [$flashSaleProduct, $quantity];
        }

        return true;
    }

    public function confirmCartFlashSale(array $cart): void
    {
        // Quantities are already reserved in `lockCartFlashSale()`.
        // Keep this hook for future order persistence integration.
    }

    public function releaseCartFlashSale(array $cart): void
    {
        foreach ($this->flashSaleCartItems($cart) as $productId => $item) {
            $product = Product::find($productId);
            $flashSaleProduct = $product ? $this->getFlashSaleProductFor($product) : null;

            if ($flashSaleProduct) {
                $this->releaseQuantity($flashSaleProduct, (int) ($item['quantity'] ?? 0));
            }
        }
    }

    private function flashSaleCartItems(array $cart): array
    {
        return array_filter($cart, static fn (array $item) => isset($item['flash_sale_price']));
    }

    private function releaseLockedItems(array $lockedItems): void
    {
        foreach ($lockedItems as [$lockedProduct, $lockedQuantity]) {
            $this->releaseQuantity($lockedProduct, $lockedQuantity);
        }
    }
}
