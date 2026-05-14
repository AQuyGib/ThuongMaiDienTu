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
        $product = Product::with(['category', 'variants', 'crossSells'])->findOrFail($id);
        
        // Lấy tất cả sản phẩm khác để admin có thể chọn bán kèm (loại trừ chính nó)
        // Trong thực tế nếu SP quá nhiều thì nên dùng AJAX search, ở đây ta lấy danh sách đơn giản
        $allProducts = Product::where('product_id', '!=', $id)
            ->orderBy('name')
            ->get(['product_id', 'name', 'base_price']);

        return view('admin.products.ProductDetail', compact('product', 'allProducts'));
    }

    /**
     * Cập nhật danh sách sản phẩm bán kèm (Cross-sell)
     */
    public function syncCrossSells(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        // Sync các product_id được chọn vào bảng trung gian
        // Xóa cache cũ để hiển thị mới ngay lập tức
        $product->crossSells()->sync($request->cross_sell_ids ?? []);
        
        // Xóa cache của cả guest và user (vì cache key có chứa user_id/guest)
        // Cách nhanh nhất là xóa theo prefix hoặc đơn giản là flush nếu hệ thống nhỏ
        // Ở đây ta xóa key cụ thể của guest và user hiện tại
        cache()->forget("cross_sell_v2_{$id}_user_" . (auth()->id() ?? 'guest'));
        cache()->forget("cross_sell_v2_{$id}_user_guest");

        return redirect()->route('admin.products.show', $id)
            ->with('success', 'Cập nhật danh sách bán kèm thành công!');
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
            'cpu_chip' => 'nullable|string|max:100',
            'gpu_chip' => 'nullable|string|max:100',
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
            'cpu_chip' => $request->cpu_chip ?: null,
            'gpu_chip' => $request->gpu_chip ?: null,
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
            'cpu_chip' => 'nullable|string|max:100',
            'gpu_chip' => 'nullable|string|max:100',
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
            'cpu_chip' => $request->cpu_chip ?: null,
            'gpu_chip' => $request->gpu_chip ?: null,
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
