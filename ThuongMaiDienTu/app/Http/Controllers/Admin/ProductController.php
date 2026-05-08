<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm (Trang Quản lý Sản phẩm)
     */
    public function index()
    {
        // Lấy danh sách sản phẩm có phân trang, kèm relation category + đếm variants
        $products = Product::with('category')
            ->withCount('variants')
            ->orderBy('product_id', 'desc')
            ->paginate(10);

        // Lấy tất cả danh mục (dùng cho dropdown chọn danh mục)
        $allCategories = Category::orderBy('name')->get();

        // Thống kê
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalVariants = ProductVariant::count();

        return view('admin.products.Product', compact(
            'products',
            'allCategories',
            'totalProducts',
            'totalCategories',
            'totalVariants'
        ));
    }

    /**
     * Hiển thị chi tiết sản phẩm + danh sách biến thể
     */
    public function show($id)
    {
        $product = Product::with(['category', 'variants'])->findOrFail($id);

        return view('admin.products.ProductDetail', compact('product'));
    }

    /**
     * Thêm sản phẩm mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer|exists:categories,category_id',
            'base_price' => 'required|numeric|min:0',
            'seo_description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm không được vượt quá 150 ký tự.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'base_price.required' => 'Vui lòng nhập giá bán.',
            'base_price.min' => 'Giá bán phải lớn hơn hoặc bằng 0.',
        ]);

        Product::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'seo_description' => $request->seo_description ?: null,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm "' . $request->name . '" thành công!');
    }

    /**
     * Cập nhật sản phẩm
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer|exists:categories,category_id',
            'base_price' => 'required|numeric|min:0',
            'seo_description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm không được vượt quá 150 ký tự.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'base_price.required' => 'Vui lòng nhập giá bán.',
            'base_price.min' => 'Giá bán phải lớn hơn hoặc bằng 0.',
        ]);

        $product = Product::findOrFail($id);

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'seo_description' => $request->seo_description ?: null,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm "' . $request->name . '" thành công!');
    }

    /**
     * Xóa sản phẩm (soft delete)
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $name = $product->name;

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Xóa sản phẩm "' . $name . '" thành công!');
    }

    // ================================================================
    //  VARIANT MANAGEMENT — CRUD biến thể sản phẩm
    // ================================================================

    /**
     * Thêm biến thể mới cho sản phẩm
     */
    public function storeVariant(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'color' => 'nullable|string|max:30',
            'ram' => 'nullable|string|max:20',
            'rom_capacity' => 'nullable|string|max:20',
            'extra_price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string|max:500',
        ], [
            'extra_price.required' => 'Vui lòng nhập giá cộng thêm.',
            'extra_price.min' => 'Giá cộng thêm phải ≥ 0.',
        ]);

        ProductVariant::create([
            'product_id' => $product->product_id,
            'color' => $request->color ?: null,
            'ram' => $request->ram ?: null,
            'rom_capacity' => $request->rom_capacity ?: null,
            'extra_price' => $request->extra_price,
            'image_url' => $request->image_url ?: null,
        ]);

        return redirect()->route('admin.products.show', $productId)
            ->with('success', 'Thêm biến thể thành công!');
    }

    /**
     * Cập nhật biến thể
     */
    public function updateVariant(Request $request, $productId, $variantId)
    {
        Product::findOrFail($productId);

        $request->validate([
            'color' => 'nullable|string|max:30',
            'ram' => 'nullable|string|max:20',
            'rom_capacity' => 'nullable|string|max:20',
            'extra_price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string|max:500',
        ], [
            'extra_price.required' => 'Vui lòng nhập giá cộng thêm.',
            'extra_price.min' => 'Giá cộng thêm phải ≥ 0.',
        ]);

        $variant = ProductVariant::where('variant_id', $variantId)
            ->where('product_id', $productId)
            ->firstOrFail();

        $variant->update([
            'color' => $request->color ?: null,
            'ram' => $request->ram ?: null,
            'rom_capacity' => $request->rom_capacity ?: null,
            'extra_price' => $request->extra_price,
            'image_url' => $request->image_url ?: null,
        ]);

        return redirect()->route('admin.products.show', $productId)
            ->with('success', 'Cập nhật biến thể thành công!');
    }

    /**
     * Xóa biến thể
     */
    public function destroyVariant($productId, $variantId)
    {
        Product::findOrFail($productId);

        $variant = ProductVariant::where('variant_id', $variantId)
            ->where('product_id', $productId)
            ->firstOrFail();

        $variant->delete();

        return redirect()->route('admin.products.show', $productId)
            ->with('success', 'Xóa biến thể thành công!');
    }
}
