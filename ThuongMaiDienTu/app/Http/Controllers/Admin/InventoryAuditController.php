<?php
// app/Http/Controllers/Admin/InventoryAuditController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAudit;
use App\Models\InventoryAuditDetail;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryAuditController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Danh sách phiếu kiểm kê kho
     */
    public function index()
    {
        $audits = InventoryAudit::with(['creator'])
            ->withCount('details')
            ->orderBy('audit_id', 'desc')
            ->paginate(10);

        return view('admin.inventory.audits.index', compact('audits'));
    }

    /**
     * Form tạo phiếu kiểm kê kho
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

        // Lấy danh sách sản phẩm cùng các biến thể
        $products = Product::with('variants')->orderBy('name')->get();

        return view('admin.inventory.audits.create', compact('warehouses', 'products'));
    }

    /**
     * Lưu phiếu kiểm kê kho mới (Trạng thái Draft)
     */
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_loc' => 'required|string|max:100',
            'items'         => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'items.*.actual_qty' => 'required|integer|min:0|max:1000000',
            'notes'         => 'nullable|string|max:255',
        ], [
            'warehouse_loc.required' => 'Vui lòng chọn địa điểm kho cần kiểm kê.',
            'items.required'         => 'Vui lòng thêm ít nhất một sản phẩm để kiểm kê.',
            'items.*.actual_qty.required' => 'Vui lòng nhập số lượng thực tế kiểm đếm.',
            'items.*.actual_qty.max'      => 'Số lượng thực tế kiểm đếm không được vượt quá 1.000.000.',
        ]);

        DB::beginTransaction();
        try {
            // Tạo mã phiếu kiểm kê duy nhất
            $lastAudit = InventoryAudit::orderBy('audit_id', 'desc')->first();
            $nextId = $lastAudit ? $lastAudit->audit_id + 1 : 1;
            $auditCode = 'AUD-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            $audit = InventoryAudit::create([
                'audit_code'    => $auditCode,
                'warehouse_loc' => $request->warehouse_loc,
                'status'        => 'Draft',
                'notes'         => $request->notes,
                'created_by'    => Auth::id() ?: 1,
            ]);

            foreach ($request->items as $item) {
                // Tính toán số lượng tồn hệ thống hiện tại trong kho đã chọn
                $systemQty = InventoryItem::where('variant_id', $item['variant_id'])
                    ->where('warehouse_loc', $request->warehouse_loc)
                    ->where('status', 'In_Stock')
                    ->count();

                $actualQty = (int) $item['actual_qty'];
                $discrepancy = $actualQty - $systemQty;

                InventoryAuditDetail::create([
                    'audit_id'        => $audit->audit_id,
                    'variant_id'      => $item['variant_id'],
                    'system_qty'      => $systemQty,
                    'actual_qty'      => $actualQty,
                    'discrepancy_qty' => $discrepancy,
                    'notes'           => $item['notes'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.inventory.audits.index')
                ->with('success', "Tạo phiếu kiểm kê kho {$auditCode} thành công ở trạng thái Chờ duyệt.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Xem chi tiết phiếu kiểm kê
     */
    public function show($id)
    {
        $audit = InventoryAudit::with(['creator', 'details.variant.product'])->findOrFail($id);

        return view('admin.inventory.audits.show', compact('audit'));
    }

    /**
     * Duyệt phiếu và Cân bằng tồn kho (Reconciliation)
     */
    public function reconcile($id)
    {
        $audit = InventoryAudit::with('details.variant')->findOrFail($id);

        if ($audit->status !== 'Draft') {
            return back()->withErrors(['error' => 'Phiếu kiểm kê này đã được duyệt và cân bằng từ trước.']);
        }

        DB::beginTransaction();
        try {
            foreach ($audit->details as $detail) {
                $variant = $detail->variant;
                if (! $variant) {
                    continue;
                }

                $discrepancy = $detail->discrepancy_qty;

                if ($discrepancy < 0) {
                    // Thiếu hàng (System > Actual) -> Cần giảm tồn kho
                    $qtyToDeduct = abs($discrepancy);

                    // 1. Chuyển trạng thái các IMEI tương ứng trong kho này sang Defective/Hao hụt
                    $itemsToUpdate = InventoryItem::where('variant_id', $variant->variant_id)
                        ->where('warehouse_loc', $audit->warehouse_loc)
                        ->where('status', 'In_Stock')
                        ->take($qtyToDeduct)
                        ->get();

                    foreach ($itemsToUpdate as $item) {
                        $item->update([
                            'status' => 'Defective',
                        ]);
                    }

                    // 2. Ghi nhận biến động kho thông qua InventoryService (để cập nhật ProductVariant->stock và log movements)
                    $this->inventoryService->deductStock($variant, $qtyToDeduct, [
                        'product_id' => $variant->product_id,
                        'variant_id' => $variant->variant_id,
                        'reference_type' => 'audit',
                        'reference_id' => $audit->audit_id,
                        'created_by' => Auth::id() ?: 1,
                        'note' => "Trừ tồn kho lệch kiểm kê theo phiếu {$audit->audit_code}",
                    ]);

                } elseif ($discrepancy > 0) {
                    // Thừa hàng (Actual > System) -> Cần tăng tồn kho
                    $qtyToAdd = $discrepancy;

                    // 1. Tạo thêm các IMEI ảo tương ứng trong kho này để khớp số lượng
                    for ($i = 0; $i < $qtyToAdd; $i++) {
                        $serial = 'SYS-AUD-' . strtoupper(Str::random(8)) . sprintf('%03d', $i + 1);
                        InventoryItem::create([
                            'variant_id'    => $variant->variant_id,
                            'warehouse_loc' => $audit->warehouse_loc,
                            'imei_serial'   => $serial,
                            'status'        => 'In_Stock',
                            'notes'         => "Thừa kiểm kê phiếu {$audit->audit_code}",
                        ]);
                    }

                    // 2. Ghi nhận biến động kho thông qua InventoryService (để cập nhật ProductVariant->stock và log movements)
                    $this->inventoryService->restoreStock($variant, $qtyToAdd, [
                        'product_id' => $variant->product_id,
                        'variant_id' => $variant->variant_id,
                        'reference_type' => 'audit',
                        'reference_id' => $audit->audit_id,
                        'created_by' => Auth::id() ?: 1,
                        'note' => "Cộng tồn kho lệch kiểm kê theo phiếu {$audit->audit_code}",
                    ]);
                }
            }

            // Cập nhật trạng thái phiếu kiểm kê
            $audit->update([
                'status'       => 'Completed',
                'completed_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('admin.inventory.audits.show', $id)
                ->with('success', "Xác nhận và cân bằng kho theo phiếu {$audit->audit_code} thành công!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
