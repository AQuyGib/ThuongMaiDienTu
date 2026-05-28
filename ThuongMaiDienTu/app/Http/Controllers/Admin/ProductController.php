<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductExport;
use App\Http\Controllers\Controller;
use App\Imports\ProductImport;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm (Trang Quản lý Sản phẩm)
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));


        $products = Product::with('category')
            ->withCount('variants')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('seo_description', 'like', '%' . $search . '%')
                        ->orWhereHas('category', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderBy('product_id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $allCategories = Category::orderBy('name')->get();

        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalVariants = ProductVariant::count();

        return view('admin.products.Product', compact(
            'products',
            'allCategories',
            'totalProducts',
            'totalCategories',
            'totalVariants',
            'search'
        ));
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['category_id', 'keyword', 'status']);

        return Excel::download(new ProductExport($filters), 'products-export-' . now()->format('Ymd-His') . '.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductExport(), 'products-template.xlsx');
    }

    public function importForm()
    {
        $allCategories = Category::orderBy('name')->get();

        return view('admin.products.import', compact('allCategories'));
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new ProductImport, $request->file('file'));

        return redirect()->route('admin.products.index')
            ->with('success', 'Import sản phẩm thành công.');
    }

    public function show($id)
    {
        $product = Product::with(['category', 'variants', 'crossSells', 'comboProducts'])->findOrFail($id);
        
        // Lấy tất cả sản phẩm khác để admin có thể chọn bán kèm (loại trừ chính nó)
        // Trong thực tế nếu SP quá nhiều thì nên dùng AJAX search, ở đây ta lấy danh sách đơn giản
        $allProducts = Product::where('product_id', '!=', $id)
            ->orderBy('name')
            ->get(['product_id', 'name', 'base_price', 'thumbnail']);

        return view('admin.products.ProductDetail', compact('product', 'allProducts'));
    }

    /**
     * Cập nhật danh sách sản phẩm bán kèm (Cross-sell)
     */
    public function syncCrossSells(Request $request, $id)
    {
        $request->validate([
            'cross_sell_ids' => 'nullable|array',
            'cross_sell_ids.*' => 'required|integer|exists:products,product_id',
        ], [
            'cross_sell_ids.*.exists' => 'Sản phẩm bán kèm chọn không tồn tại.',
        ]);

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
     * Cập nhật danh sách combo mua kèm
     */
    public function syncCombos(Request $request, $id)
    {
        $request->validate([
            'combo_product_ids' => 'nullable|array',
            'combo_product_ids.*' => 'required|integer|exists:products,product_id',
            'discount_types' => 'nullable|array',
            'discount_types.*' => 'required|string|in:fixed,percentage',
            'discount_values' => 'nullable|array',
            'discount_values.*' => 'required|numeric|min:0|max:999999999',
        ], [
            'combo_product_ids.*.exists' => 'Sản phẩm mua kèm chọn không tồn tại.',
            'discount_types.*.in' => 'Loại giảm giá không hợp lệ.',
            'discount_values.*.min' => 'Giá trị giảm giá không được âm.',
        ]);

        $product = Product::findOrFail($id);
        
        $syncData = [];
        if ($request->has('combo_product_ids')) {
            foreach ($request->combo_product_ids as $index => $comboProductId) {
                $discountType = $request->discount_types[$comboProductId] ?? 'fixed';
                $discountValue = floatval($request->discount_values[$comboProductId] ?? 0);
                
                $syncData[$comboProductId] = [
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'sort_order' => $index
                ];
            }
        }
        
        $product->comboProducts()->sync($syncData);
        
        // Xóa cache hiển thị combo / cross-sell của sản phẩm
        cache()->forget("cross_sell_v2_{$id}_user_" . (auth()->id() ?? 'guest'));
        cache()->forget("cross_sell_v2_{$id}_user_guest");
        cache()->forget("combo_products_{$id}");

        return redirect()->route('admin.products.show', $id)
            ->with('success', 'Cập nhật danh sách Combo mua kèm thành công!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer|exists:categories,category_id',
            'base_price' => 'required|numeric|min:0|max:999999999',
            'seo_description' => 'nullable|string|max:255',
            'safe_stock' => 'nullable|integer|min:0|max:1000000',
        ], [
            'base_price.max' => 'Giá bán không được vượt quá 999.999.999 đ.',
            'safe_stock.integer' => 'Tồn kho an toàn phải là số nguyên.',
            'safe_stock.min' => 'Tồn kho an toàn không được âm.',
        ]);

        Product::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'seo_description' => $request->seo_description ?: null,
            'safe_stock' => $request->safe_stock !== null ? (int)$request->safe_stock : 5,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm "' . $request->name . '" thành công!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer|exists:categories,category_id',
            'base_price' => 'required|numeric|min:0|max:999999999',
            'seo_description' => 'nullable|string|max:255',
            'safe_stock' => 'nullable|integer|min:0|max:1000000',
            'version' => 'required|integer',
        ], [
            'base_price.max' => 'Giá bán không được vượt quá 999.999.999 đ.',
            'version.required' => 'Thiếu thông tin phiên bản sản phẩm.',
            'version.integer' => 'Phiên bản sản phẩm không hợp lệ.',
            'safe_stock.integer' => 'Tồn kho an toàn phải là số nguyên.',
            'safe_stock.min' => 'Tồn kho an toàn không được âm.',
        ]);

        $product = Product::findOrFail($id);

        if ((int)$product->version !== (int)$request->version) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Sản phẩm "' . $product->name . '" đã bị cập nhật bởi một người quản trị khác. Vui lòng tải lại trang.');
        }

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'seo_description' => $request->seo_description ?: null,
            'safe_stock' => $request->safe_stock !== null ? (int)$request->safe_stock : 5,
            'version' => $product->version + 1,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm "' . $request->name . '" thành công!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $name = $product->name;

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Xóa sản phẩm "' . $name . '" thành công!');
    }

    public function storeVariant(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'color' => 'nullable|string|max:30',
            'ram' => 'nullable|string|max:20',
            'rom_capacity' => 'nullable|string|max:20',
            'cpu_chip' => 'nullable|string|max:100',
            'gpu_chip' => 'nullable|string|max:100',
            'extra_price' => 'required|numeric|min:0|max:999999999',
            'image_url' => 'nullable|string|max:500',
            'safe_stock' => 'nullable|integer|min:0|max:1000000',
        ], [
            'extra_price.max' => 'Giá cộng thêm không được vượt quá 999.999.999 đ.',
            'safe_stock.integer' => 'Tồn kho an toàn phải là số nguyên.',
            'safe_stock.min' => 'Tồn kho an toàn không được âm.',
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
            'safe_stock' => $request->safe_stock !== null ? (int)$request->safe_stock : 5,
        ]);

        return redirect()->route('admin.products.show', $productId)
            ->with('success', 'Thêm biến thể thành công!');
    }

    public function updateVariant(Request $request, $productId, $variantId)
    {
        Product::findOrFail($productId);

        $request->validate([
            'color' => 'nullable|string|max:30',
            'ram' => 'nullable|string|max:20',
            'rom_capacity' => 'nullable|string|max:20',
            'cpu_chip' => 'nullable|string|max:100',
            'gpu_chip' => 'nullable|string|max:100',
            'extra_price' => 'required|numeric|min:0|max:999999999',
            'image_url' => 'nullable|string|max:500',
            'safe_stock' => 'nullable|integer|min:0|max:1000000',
            'version' => 'required|integer',
        ], [
            'extra_price.max' => 'Giá cộng thêm không được vượt quá 999.999.999 đ.',
            'version.required' => 'Thiếu thông tin phiên bản biến thể.',
            'version.integer' => 'Phiên bản biến thể không hợp lệ.',
            'safe_stock.integer' => 'Tồn kho an toàn phải là số nguyên.',
            'safe_stock.min' => 'Tồn kho an toàn không được âm.',
        ]);

        $variant = ProductVariant::where('variant_id', $variantId)
            ->where('product_id', $productId)
            ->firstOrFail();

        if ((int)$variant->version !== (int)$request->version) {
            return redirect()->route('admin.products.show', $productId)
                ->with('error', 'Biến thể sản phẩm đã bị cập nhật bởi một người quản trị khác. Vui lòng tải lại trang.');
        }

        $variant->update([
            'color' => $request->color ?: null,
            'ram' => $request->ram ?: null,
            'rom_capacity' => $request->rom_capacity ?: null,
            'cpu_chip' => $request->cpu_chip ?: null,
            'gpu_chip' => $request->gpu_chip ?: null,
            'extra_price' => $request->extra_price,
            'image_url' => $request->image_url ?: null,
            'safe_stock' => $request->safe_stock !== null ? (int)$request->safe_stock : 5,
            'version' => $variant->version + 1,
        ]);

        return redirect()->route('admin.products.show', $productId)
            ->with('success', 'Cập nhật biến thể thành công!');
    }

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
