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

        $items = $query->orderBy('item_id', 'desc')->paginate(15)->appends($request->query());

        // Thống kê
        $totalInStock = InventoryItem::where('status', 'In_Stock')->count();
        $totalSold = InventoryItem::where('status', 'Sold')->count();
        $totalDefective = InventoryItem::where('status', 'Defective')->count();
        $totalProducts = Product::count();

        $productStats = Product::query()
            ->leftJoin('product_variants', 'products.product_id', '=', 'product_variants.product_id')
            ->leftJoin('inventory_items', 'product_variants.variant_id', '=', 'inventory_items.variant_id')
            ->select('products.product_id', 'products.name')
            ->selectRaw('COUNT(DISTINCT product_variants.variant_id) as variant_count')
            ->selectRaw("COUNT(CASE WHEN inventory_items.status = 'In_Stock' THEN 1 END) as in_stock_count")
            ->selectRaw("COUNT(CASE WHEN inventory_items.status = 'Sold' THEN 1 END) as sold_count")
            ->selectRaw("COUNT(CASE WHEN inventory_items.status = 'Defective' THEN 1 END) as defective_count")
            ->selectRaw('COUNT(inventory_items.item_id) as total_items')
            ->groupBy('products.product_id', 'products.name')
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
