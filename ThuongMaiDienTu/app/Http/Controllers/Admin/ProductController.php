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
        $product = Product::with(['category', 'variants'])->findOrFail($id);

        return view('admin.products.ProductDetail', compact('product'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer|exists:categories,category_id',
            'base_price' => 'required|numeric|min:0',
            'seo_description' => 'nullable|string|max:255',
        ]);

        Product::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'seo_description' => $request->seo_description ?: null,
            'safe_stock' => $request->safe_stock !== null ? $request->safe_stock : 5,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm "' . $request->name . '" thành công!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer|exists:categories,category_id',
            'base_price' => 'required|numeric|min:0',
            'seo_description' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($id);

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'seo_description' => $request->seo_description ?: null,
            'safe_stock' => $request->safe_stock !== null ? $request->safe_stock : 5,
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
            'extra_price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string|max:500',
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
            'safe_stock' => $request->safe_stock !== null ? $request->safe_stock : 5,
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
            'extra_price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string|max:500',
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
            'safe_stock' => $request->safe_stock !== null ? $request->safe_stock : 5,
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
