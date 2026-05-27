<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->withTranslation()
            ->latest('product_id')
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated() + [
            'slug' => $request->filled('slug') ? $request->slug : Str::slug($request->name),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.products.edit', $product->product_id)
            ->with('success', 'Đã tạo sản phẩm và tự động đồng bộ bản dịch.');
    }

    public function edit(Product $product)
    {
        $product->loadMissing('translations');
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        $product->update($request->validated() + [
            'slug' => $request->filled('slug') ? $request->slug : Str::slug($request->name),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Đã cập nhật sản phẩm và tự động đồng bộ bản dịch.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Đã xóa sản phẩm.');
    }
}
