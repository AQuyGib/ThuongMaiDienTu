<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WarehouseTransferController extends Controller
{
    /**
     * Danh sách phiếu điều chuyển
     */
    public function index()
    {
        $transfers = WarehouseTransfer::with(['creator'])
            ->withCount('items')
            ->orderBy('transfer_id', 'desc')
            ->paginate(10)
            ->onEachSide(1);

        return view('admin.warehouse-transfers.index', compact('transfers'));
    }

    /**
     * Form tạo phiếu điều chuyển
     */
    public function create()
    {
        // Lấy danh sách các kho hiện tại từ bảng inventory_items
        $existingWarehouses = InventoryItem::whereNotNull('warehouse_loc')
            ->where('warehouse_loc', '<>', '')
            ->distinct()
            ->pluck('warehouse_loc')
            ->toArray();

        // Bổ sung các kho mặc định nếu chưa có
        $defaultWarehouses = [
            'Kho tổng (Main)',
            'Kho cửa hàng (Store)',
            'Kho A - HCM',
            'Kho B - HCM',
            'Kho C - Hà Nội',
            'Kho Trung Tâm - Đà Nẵng',
            'Kho Kỹ Thuật - TP. Thủ Đức',
        ];

        $warehouses = array_unique(array_merge($defaultWarehouses, $existingWarehouses));

        return view('admin.warehouse-transfers.create', compact('warehouses'));
    }

    /**
     * API lấy danh sách IMEI còn trong kho theo địa điểm kho
     */
    public function getInventoryByWarehouse(Request $request)
    {
        $warehouse = $request->query('warehouse');

        if (!$warehouse) {
            return response()->json([]);
        }

        $items = InventoryItem::with(['variant.product'])
            ->where('warehouse_loc', $warehouse)
            ->where('status', 'In_Stock')
            ->get()
            ->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'imei_serial' => $item->imei_serial,
                    'product_name' => $item->variant->product->name ?? 'Sản phẩm không rõ',
                    'variant_name' => $item->variant ? ($item->variant->color ?? 'Mặc định') . ($item->variant->rom_capacity ? ' - ' . $item->variant->rom_capacity : '') : 'Mặc định',
                ];
            });

        return response()->json($items);
    }

    /**
     * Lưu phiếu điều chuyển mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_warehouse' => 'required|string|max:100',
            'to_warehouse' => 'required|string|max:100|different:from_warehouse',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|integer|exists:inventory_items,item_id',
            'notes' => 'nullable|string|max:255',
            'action_type' => 'required|in:Pending,Completed',
        ], [
            'to_warehouse.different' => 'Kho đến phải khác kho đi.',
            'item_ids.required' => 'Vui lòng chọn ít nhất một mã IMEI để điều chuyển.',
        ]);

        DB::beginTransaction();
        try {
            // Tạo mã phiếu duy nhất
            $lastTransfer = WarehouseTransfer::orderBy('transfer_id', 'desc')->first();
            $nextId = $lastTransfer ? $lastTransfer->transfer_id + 1 : 1;
            $transferCode = 'TF-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            // Tạo phiếu điều chuyển
            $transfer = WarehouseTransfer::create([
                'transfer_code' => $transferCode,
                'from_warehouse' => $request->from_warehouse,
                'to_warehouse' => $request->to_warehouse,
                'status' => $request->action_type,
                'notes' => $request->notes,
                'created_by' => Auth::id() ?: 1, // fallback nếu chưa đăng nhập
            ]);

            // Thêm các mặt hàng vào phiếu
            foreach ($request->item_ids as $itemId) {
                // Kiểm tra xem item có đang ở kho nguồn và trạng thái In_Stock hay không
                $item = InventoryItem::where('item_id', $itemId)
                    ->where('warehouse_loc', $request->from_warehouse)
                    ->where('status', 'In_Stock')
                    ->first();

                if (!$item) {
                    throw new \Exception("Mã IMEI #{$itemId} không khả dụng tại kho {$request->from_warehouse}.");
                }

                WarehouseTransferItem::create([
                    'transfer_id' => $transfer->transfer_id,
                    'item_id' => $itemId,
                ]);

                // Nếu chọn Lưu và Hoàn thành ngay lập tức
                if ($request->action_type === 'Completed') {
                    $item->update([
                        'warehouse_loc' => $request->to_warehouse,
                    ]);
                }
            }

            DB::commit();

            $msg = $request->action_type === 'Completed' 
                ? 'Tạo và hoàn thành phiếu điều chuyển hàng hóa thành công!' 
                : 'Tạo phiếu điều chuyển ở trạng thái Chờ xử lý thành công!';

            return redirect()->route('admin.warehouse-transfers.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Xem chi tiết phiếu
     */
    public function show($id)
    {
        $transfer = WarehouseTransfer::with(['creator', 'items.variant.product'])->findOrFail($id);

        return view('admin.warehouse-transfers.show', compact('transfer'));
    }

    /**
     * Hoàn thành điều chuyển (Duyệt phiếu)
     */
    public function complete($id)
    {
        $transfer = WarehouseTransfer::findOrFail($id);

        if ($transfer->status !== 'Pending') {
            return back()->withErrors(['error' => 'Phiếu này đã hoàn thành hoặc đã bị hủy.']);
        }

        DB::beginTransaction();
        try {
            // Cập nhật vị trí kho của tất cả IMEI trong phiếu sang kho đích
            foreach ($transfer->items as $item) {
                // Kiểm tra xem item có còn trạng thái In_Stock không
                if ($item->status !== 'In_Stock') {
                    throw new \Exception("Mã IMEI {$item->imei_serial} đã được bán hoặc gặp sự cố, không thể thực hiện điều chuyển.");
                }

                $item->update([
                    'warehouse_loc' => $transfer->to_warehouse,
                ]);
            }

            // Cập nhật trạng thái phiếu
            $transfer->update(['status' => 'Completed']);

            DB::commit();
            return redirect()->route('admin.warehouse-transfers.show', $id)
                ->with('success', 'Xác nhận điều chuyển hàng hóa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Hủy phiếu điều chuyển
     */
    public function cancel($id)
    {
        $transfer = WarehouseTransfer::findOrFail($id);

        if ($transfer->status !== 'Pending') {
            return back()->withErrors(['error' => 'Chỉ có thể hủy phiếu đang ở trạng thái Chờ xử lý.']);
        }

        $transfer->update(['status' => 'Cancelled']);

        return redirect()->route('admin.warehouse-transfers.show', $id)
            ->with('success', 'Hủy phiếu điều chuyển thành công!');
    }
}
