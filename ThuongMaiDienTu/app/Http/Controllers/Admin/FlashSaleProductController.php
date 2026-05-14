<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class FlashSaleProductController extends Controller
{
    public function store(Request $request, FlashSale $flash_sale)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,product_id',
            'sale_price' => 'required|numeric|min:0',
            'stock_limit' => 'required|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ((float) $validated['sale_price'] >= (float) $product->base_price) {
            return back()->with('error', 'Giá sale phải nhỏ hơn giá gốc của sản phẩm.');
        }

        FlashSaleProduct::updateOrCreate(
            [
                'flash_sale_id' => $flash_sale->flash_sale_id,
                'product_id' => $product->product_id,
            ],
            [
                'sale_price' => $validated['sale_price'],
                'stock_limit' => $validated['stock_limit'],
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return redirect()->route('admin.flash-sales.index')->with('success', 'Đã gán sản phẩm vào Flash Sale.');
    }

    public function destroy(FlashSale $flash_sale, FlashSaleProduct $flash_sale_product)
    {
        abort_unless($flash_sale_product->flash_sale_id === $flash_sale->flash_sale_id, 404);

        $flash_sale_product->delete();

        return redirect()->route('admin.flash-sales.index')->with('success', 'Đã gỡ sản phẩm khỏi Flash Sale.');
    }
}
