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

        // Kiểm tra xem sản phẩm đã có trong Flash Sale nào khác trùng thời gian không
        $overlapping = FlashSaleProduct::where('product_id', $product->product_id)
            ->where('flash_sale_id', '!=', $flash_sale->flash_sale_id)
            ->whereHas('flashSale', function ($query) use ($flash_sale) {
                $query->where('is_active', true)
                      ->where('end_at', '>', now())
                      ->where(function ($q) use ($flash_sale) {
                          $q->whereBetween('start_at', [$flash_sale->start_at, $flash_sale->end_at])
                            ->orWhereBetween('end_at', [$flash_sale->start_at, $flash_sale->end_at])
                            ->orWhere(function ($q2) use ($flash_sale) {
                                $q2->where('start_at', '<=', $flash_sale->start_at)
                                   ->where('end_at', '>=', $flash_sale->end_at);
                            });
                      });
            })->exists();

        if ($overlapping) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Sản phẩm này đã nằm trong một chương trình Flash Sale khác có thời gian trùng lặp.'], 422);
            }
            return back()->with('error', 'Sản phẩm này đã nằm trong một chương trình Flash Sale khác có thời gian trùng lặp.');
        }

        $flashSaleProduct = FlashSaleProduct::updateOrCreate(
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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã gán sản phẩm vào Flash Sale.',
                'product' => [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'thumbnail' => $product->thumbnail,
                    'sale_price' => number_format($flashSaleProduct->sale_price, 0, ',', '.') . 'đ',
                    'stock_limit' => $flashSaleProduct->stock_limit,
                    'sold_quantity' => $flashSaleProduct->sold_quantity,
                    'delete_url' => route('admin.flash-sales.products.destroy', [$flash_sale->flash_sale_id, $flashSaleProduct->id])
                ]
            ]);
        }

        return redirect()->route('admin.flash-sales.index', ['edit' => $flash_sale->flash_sale_id])->with('success', 'Đã gán sản phẩm vào Flash Sale.');
    }

    public function destroy(FlashSale $flash_sale, FlashSaleProduct $flash_sale_product)
    {
        abort_unless($flash_sale_product->flash_sale_id === $flash_sale->flash_sale_id, 404);

        $flash_sale_product->delete();

        return redirect()->route('admin.flash-sales.index')->with('success', 'Đã gỡ sản phẩm khỏi Flash Sale.');
    }
}
