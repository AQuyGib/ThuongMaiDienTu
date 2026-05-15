<?php

namespace App\Services;

use App\Models\Product;

class CartService
{
    public function normalizeCart(array $cart): array
    {
        return $cart;
    }

    public function getProductPrice(Product $product): int
    {
        return (int) $product->base_price;
    }
}
