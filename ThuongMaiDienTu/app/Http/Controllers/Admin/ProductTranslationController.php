<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Http\Request;

class ProductTranslationController extends Controller
{
    public function edit(Product $product)
    {
        $translation = ProductTranslation::query()->firstOrNew([
            'product_id' => $product->product_id,
            'locale' => 'en',
        ]);

        return view('admin.products.translation-edit', compact('product', 'translation'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
        ]);

        ProductTranslation::updateOrCreate(
            [
                'product_id' => $product->product_id,
                'locale' => 'en',
            ],
            $data + [
                'product_id' => $product->product_id,
                'locale' => 'en',
            ]
        );

        return back()->with('success', 'Đã lưu bản dịch EN thủ công cho sản phẩm.');
    }
}
