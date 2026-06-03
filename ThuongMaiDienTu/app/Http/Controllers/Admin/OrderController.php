<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\AIOrderLog;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Trang danh sách đơn hàng - Hiển thị bảng + bộ lọc trạng thái + tìm kiếm.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'details.inventoryItem.variant.product']);

        // Lọc theo trạng thái (tab filter)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo mã đơn, tên, SĐT khách hàng
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', '%' . $search . '%')
                    ->orWhere('order_code', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('customer_phone', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', '%' . $search . '%')
                            ->orWhere('phone_number', 'like', '%' . $search . '%');
                    });
            });
        }

        // Lọc theo khoảng ngày tạo đơn hàng
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Đếm số lượng theo từng trạng thái cho các tab filter
        $statusCounts = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalOrders = array_sum($statusCounts);

        $orders = $query->orderByDesc('order_id')->paginate(10)->appends($request->query());

        return view('admin.orders.index', compact('orders', 'statusCounts', 'totalOrders'));
    }

    /**
     * API lấy chi tiết đơn hàng (JSON) - dùng cho Modal AJAX.
     */
    public function show(Request $request, $id)
    {
        $order = Order::with(['user', 'details.inventoryItem.variant.product'])->findOrFail($id);

        // Nếu request AJAX → trả JSON cho modal
        if ($request->ajax() || $request->wantsJson()) {
            $items = $order->details->map(function ($detail) {
                $variant = $detail->inventoryItem->variant ?? null;
                $product = $variant->product ?? null;

                // Lấy ảnh sản phẩm (thumbnail)
                $image = null;
                if ($product && $product->images) {
                    $images = is_string($product->images) ? json_decode($product->images, true) : $product->images;
                    $image = is_array($images) && count($images) > 0 ? $images[0] : null;
                }

                $productName = $detail->product_name
                    ?? ($product->name ?? 'Sản phẩm không xác định');

                // Thêm thông tin biến thể nếu có
                if ($variant && $variant->label) {
                    $productName .= ' - ' . $variant->label;
                }

                return [
                    'product_name' => $productName,
                    'image' => $image,
                    'quantity' => 1, // Mỗi inventory item = 1 đơn vị
                    'price' => (int) $detail->price,
                ];
            });

            // Gom nhóm sản phẩm cùng tên + giá lại thành 1 dòng cộng dồn số lượng
            $groupedItems = $items->groupBy(function ($item) {
                return $item['product_name'] . '_' . $item['price'];
            })->map(function ($group) {
                $first = $group->first();
                return [
                    'product_name' => $first['product_name'],
                    'image' => $first['image'],
                    'quantity' => $group->count(),
                    'price' => $first['price'],
                    'subtotal' => $first['price'] * $group->count(),
                ];
            })->values();

            // Thông tin điểm thưởng
            $pointsInfo = [
                'wallet_points_earned' => (int) ($order->wallet_points_earned ?? 0),
                'rank_points_earned' => (int) ($order->rank_points_earned ?? 0),
                'wallet_points_used' => (int) ($order->wallet_points_used ?? 0),
                'points_status' => $order->points_status ?? 'pending',
                'potential_points' => app(PointsService::class)->calculateEarnedPoints((int) $order->final_amount),
            ];

            // Số dư điểm hiện tại của khách
            $customerBalance = null;
            if ($order->user) {
                $customerBalance = app(PointsService::class)->getBalance($order->user);
            }

            return response()->json([
                'order_id' => $order->order_id,
                'order_code' => $order->order_code,
                'customer_name' => $order->customer_name ?? ($order->user->full_name ?? 'N/A'),
                'customer_phone' => $order->customer_phone ?? ($order->user->phone_number ?? 'N/A'),
                'shipping_address' => $order->shipping_address ?? ($order->user->address ?? 'N/A'),
                'note' => $order->note,
                'payment_method' => $order->payment_method,
                'status' => $order->status,
                'total_amount' => (int) $order->total_amount,
                'shipping_fee' => (int) $order->shipping_fee,
                'discount_amount' => (int) ($order->discount_amount ?? 0),
                'final_amount' => (int) $order->final_amount,
                'items' => $groupedItems,
                'points' => $pointsInfo,
                'customer_balance' => $customerBalance,
                'ai_status' => $order->ai_status ?? 'pending',
                'ai_risk_score' => (int) ($order->ai_risk_score ?? 0),
                'ai_analysis' => $order->ai_analysis,
            ]);
        }

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng (hỗ trợ AJAX).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|max:50',
        ]);

        $order = Order::with('details.inventoryItem.variant')->findOrFail($id);
        $previousStatus = $order->status;
        $currentStatus = $request->status;

        DB::transaction(function () use ($order, $currentStatus) {
            $order->status = $currentStatus;
            $order->save(); // Trigger model events → tự động tích điểm/hủy điểm
        });

        // Lấy lại order sau khi save để có dữ liệu điểm mới nhất
        $order->refresh();

        // Xây dựng thông báo chi tiết về điểm
        $pointsMessage = '';
        if ($previousStatus !== $currentStatus) {
            if (in_array(strtolower($currentStatus), ['delivered', 'completed'])) {
                $earned = (int) ($order->wallet_points_earned ?? 0);
                if ($earned > 0) {
                    $pointsMessage = " Đã tích +{$earned} điểm cho khách hàng.";
                }
            } elseif (strtolower($currentStatus) === 'cancelled') {
                $refunded = (int) ($order->wallet_points_used ?? 0);
                $revoked = (int) ($order->wallet_points_earned ?? 0);
                if ($refunded > 0 || $revoked > 0) {
                    $pointsMessage = ' Đã xử lý hoàn/thu hồi điểm.';
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công.' . $pointsMessage,
                'points_earned' => (int) ($order->wallet_points_earned ?? 0),
                'points_used' => (int) ($order->wallet_points_used ?? 0),
                'points_status' => $order->points_status,
            ]);
        }

        return redirect()->route('admin.orders.index')
            ->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    /**
     * Xóa đơn hàng.
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->details()->delete();
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Đã xóa đơn hàng #' . $id . ' thành công.');
    }

    /**
     * Kích hoạt quét lại đơn hàng bằng AI qua AJAX.
     */
    public function reanalyze(Request $request, $id)
    {
        $order = Order::with(['user', 'details.inventoryItem.variant.product'])->findOrFail($id);
        
        $aiOrderService = app(\App\Services\AIOrderService::class);
        $result = $aiOrderService->analyzeOrder($order, 'manual');
        
        return response()->json([
            'success' => true,
            'message' => 'Quét lại bằng AI thành công!',
            'ai_status' => $result['status'],
            'ai_risk_score' => $result['risk_score'],
            'ai_analysis' => $result['reason']
        ]);
    }

    /**
     * Trang lịch sử làm việc của AI (AI Order Logs).
     */
    public function aiLogs(Request $request)
    {
        $query = AIOrderLog::with(['order.user']);

        // Bộ lọc trạng thái AI
        if ($request->filled('ai_status')) {
            $query->where('ai_status', $request->ai_status);
        }

        // Bộ lọc loại kích hoạt
        if ($request->filled('trigger_type')) {
            $query->where('trigger_type', $request->trigger_type);
        }

        $logs = $query->orderByDesc('log_id')->paginate(15)->appends($request->query());

        return view('admin.orders.ai_logs', compact('logs'));
    }

    /**
     * Lấy danh sách ID đơn hàng cần quét theo ngày và điều kiện.
     */
    public function batchGetIds(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'scan_type' => 'required|in:all,unscanned',
        ]);

        $query = Order::query()
            ->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);

        if ($request->scan_type === 'unscanned') {
            $query->where(function($q) {
                $q->whereNull('ai_status')
                  ->orWhere('ai_status', 'pending');
            });
        }

        $orders = $query->select('order_id', 'order_code')->get();

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
}
