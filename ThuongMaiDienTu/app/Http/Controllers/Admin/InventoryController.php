<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Danh sách tất cả IMEI/Serial
     */
    public function index(Request $request)
    {
        $query = InventoryItem::with(['variant.product', 'purchaseOrder.supplier']);

        // Lọc theo trạng thái
        if ($request->status && in_array($request->status, ['In_Stock', 'Sold', 'Defective'])) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo IMEI
        if ($request->search) {
            $query->where('imei_serial', 'like', '%' . $request->search . '%');
        }

        $items = $query->orderBy('item_id', 'desc')->paginate(15)->onEachSide(1)->appends($request->query());

        // Thống kê
        $totalInStock = InventoryItem::where('status', 'In_Stock')->count();
        $totalSold = InventoryItem::where('status', 'Sold')->count();
        $totalDefective = InventoryItem::where('status', 'Defective')->count();
        $totalProducts = Product::count();

        $productStats = Product::query()
            ->leftJoin('product_variants', 'products.product_id', '=', 'product_variants.product_id')
            ->leftJoin('inventory_items', 'product_variants.variant_id', '=', 'inventory_items.variant_id')
            ->select('products.product_id', 'products.name', 'products.safe_stock')
            ->selectRaw('COUNT(DISTINCT product_variants.variant_id) as variant_count')
            ->selectRaw("COUNT(CASE WHEN inventory_items.status = 'In_Stock' THEN 1 END) as in_stock_count")
            ->selectRaw("COUNT(CASE WHEN inventory_items.status = 'Sold' THEN 1 END) as sold_count")
            ->selectRaw("COUNT(CASE WHEN inventory_items.status = 'Defective' THEN 1 END) as defective_count")
            ->selectRaw('COUNT(inventory_items.item_id) as total_items')
            ->groupBy('products.product_id', 'products.name', 'products.safe_stock')
            ->orderByDesc('total_items')
            ->get();

        return view('admin.inventory.index', compact(
            'items',
            'totalInStock',
            'totalSold',
            'totalDefective',
            'totalProducts',
            'productStats'
        ));
    }

    /**
     * Cảnh báo tồn kho an toàn
     */
    public function warningList(Request $request)
    {
        // Lấy tất cả biến thể có số lượng tồn kho thực tế nhỏ hơn hoặc bằng tồn an toàn
        $lowStockVariants = \App\Models\ProductVariant::with(['product.category'])
            ->withCount(['inventoryItems as in_stock_count' => function ($q) {
                $q->where('status', 'In_Stock');
            }])
            ->get()
            ->filter(function ($variant) {
                return $variant->in_stock_count <= ($variant->safe_stock ?? 5);
            });

        // Lấy sản phẩm không có biến thể và có tồn kho <= safe_stock
        $lowStockProductsWithoutVariants = Product::with('category')
            ->withCount(['variants'])
            ->whereRaw('(select count(*) from product_variants where product_variants.product_id = products.product_id) = 0')
            ->get()
            ->filter(function ($product) {
                return 0 <= ($product->safe_stock ?? 5);
            });

        // Thống kê
        $totalInStock = InventoryItem::where('status', 'In_Stock')->count();
        $totalSold = InventoryItem::where('status', 'Sold')->count();
        $totalDefective = InventoryItem::where('status', 'Defective')->count();
        $totalProducts = Product::count();

        return view('admin.inventory.warning', compact(
            'lowStockVariants',
            'lowStockProductsWithoutVariants',
            'totalInStock',
            'totalSold',
            'totalDefective',
            'totalProducts'
        ));
    }

    /**
     * Cập nhật trạng thái IMEI
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:In_Stock,Sold,Defective',
        ]);

        $item = InventoryItem::findOrFail($id);
        $item->update(['status' => $request->status]);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Cập nhật trạng thái IMEI "' . $item->imei_serial . '" thành ' . $request->status . ' thành công!');
    }
}
