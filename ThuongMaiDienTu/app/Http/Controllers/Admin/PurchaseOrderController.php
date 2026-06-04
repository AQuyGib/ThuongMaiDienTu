<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Danh sách phiếu nhập kho
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->withCount('inventoryItems')
            ->orderBy('po_id', 'desc')
            ->paginate(10);

        $totalPO = PurchaseOrder::count();
        $totalItems = InventoryItem::count();

        return view('admin.purchase-orders.index', compact(
            'purchaseOrders', 'totalPO', 'totalItems'
        ));
    }

    /**
     * Form tạo phiếu nhập kho
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with('variants')->orderBy('name')->get();

        return view('admin.purchase-orders.create', compact('suppliers', 'products'));
    }

    /**
     * Lưu phiếu nhập kho mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'items'       => 'required|array|min:1',
            'items.*.variant_id'    => 'required|exists:product_variants,variant_id',
            'items.*.imei_serial'   => 'required|string|max:30|distinct',
            'items.*.cost_price'    => 'required|numeric|min:0',
            'items.*.warehouse_loc' => 'nullable|string|max:50',
        ], [
            'supplier_id.required' => 'Vui lòng chọn nhà cung cấp.',
            'items.required'       => 'Vui lòng thêm ít nhất 1 sản phẩm.',
            'items.*.imei_serial.required' => 'Vui lòng nhập IMEI/Serial.',
            'items.*.imei_serial.distinct' => 'IMEI/Serial không được trùng nhau.',
            'items.*.cost_price.required'  => 'Vui lòng nhập giá nhập.',
        ]);

        // Kiểm tra IMEI trùng trong DB
        $imeisInRequest = collect($request->items)->pluck('imei_serial');
        $duplicates = InventoryItem::whereIn('imei_serial', $imeisInRequest)->pluck('imei_serial');
        if ($duplicates->isNotEmpty()) {
            return back()->withInput()->with('error', 'IMEI/Serial đã tồn tại: ' . $duplicates->implode(', '));
        }

        DB::transaction(function () use ($request) {
            $totalCost = collect($request->items)->sum('cost_price');

            $po = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'total_cost'  => $totalCost,
            ]);

            $supplier = Supplier::find($request->supplier_id);
            \App\Models\Cashbook::create([
                'type' => 'Expense',
                'amount' => $totalCost,
                'description' => 'Thanh toán đơn nhập hàng #' . $po->po_id . ' từ nhà cung cấp: ' . ($supplier ? $supplier->name : 'N/A'),
                'reference_id' => $po->po_id,
                'reference_type' => 'purchase_order',
            ]);

            foreach ($request->items as $item) {
                InventoryItem::create([
                    'variant_id'    => $item['variant_id'],
                    'po_id'         => $po->po_id,
                    'imei_serial'   => $item['imei_serial'],
                    'warehouse_loc' => $item['warehouse_loc'] ?? null,
                    'status'        => 'In_Stock',
                ]);
            }
        });

        return redirect()->route('admin.purchase-orders.index')
            ->with('success', 'Tạo phiếu nhập kho thành công! Đã nhập ' . count($request->items) . ' sản phẩm.');
    }

    /**
     * Chi tiết phiếu nhập kho
     */
    public function show($id)
    {
        $po = PurchaseOrder::with('supplier')->withCount('inventoryItems')->findOrFail($id);
        $inventoryItems = $po->inventoryItems()->with('variant.product')->paginate(50);

        return view('admin.purchase-orders.show', compact('po', 'inventoryItems'));
    }

    /**
     * API: Lấy danh sách variants của 1 product (JSON)
     */
    public function getVariants($id)
    {
        $variants = ProductVariant::where('product_id', $id)
            ->get(['variant_id', 'color', 'rom_capacity', 'extra_price']);

        return response()->json($variants);
    }
}
