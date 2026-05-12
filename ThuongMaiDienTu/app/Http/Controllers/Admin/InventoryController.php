<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
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
        $totalInStock  = InventoryItem::where('status', 'In_Stock')->count();
        $totalSold     = InventoryItem::where('status', 'Sold')->count();
        $totalDefective = InventoryItem::where('status', 'Defective')->count();

        return view('admin.inventory.index', compact(
            'items', 'totalInStock', 'totalSold', 'totalDefective'
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
