<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WarrantyController extends Controller
{
    /**
     * Hiển thị trang tra cứu bảo hành
     */
    public function index()
    {
        return view('frontend.policy.warranty');
    }

    /**
     * Hiển thị trang chính sách đổi trả
     */
    public function returnPolicy()
    {
        return view('frontend.policy.return_policy');
    }

    /**
     * Xử lý tra cứu bảo hành theo IMEI (AJAX)
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'imei' => 'required|string|min:8|max:30',
        ]);

        $imei = trim($request->input('imei'));

        // Tìm thiết bị theo IMEI
        $item = InventoryItem::where('imei_serial', $imei)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thiết bị với mã IMEI/Serial này trong hệ thống.',
            ], 404);
        }

        // Lấy thông tin sản phẩm qua variant
        $variant = $item->variant;
        $product = $variant ? $variant->product : null;

        // Tìm bảo hành
        $warranty = Warranty::where('item_id', $item->item_id)
            ->orderBy('end_date', 'desc')
            ->first();

        $now = Carbon::now();

        // Xây dựng response
        $result = [
            'success'        => true,
            'imei'           => $item->imei_serial,
            'product_name'   => $product ? $product->name : 'Không xác định',
            'product_image'  => $product ? $product->thumbnail : null,
            'variant_label'  => $variant ? $variant->label : '',
            'device_status'  => $item->status,
        ];

        if ($warranty) {
            $isExpired = $now->greaterThan($warranty->end_date);
            $daysLeft  = $isExpired ? 0 : (int) $now->diffInDays($warranty->end_date);

            $result['has_warranty']    = true;
            $result['start_date']     = $warranty->start_date->format('d/m/Y');
            $result['end_date']       = $warranty->end_date->format('d/m/Y');
            $result['warranty_status'] = $isExpired ? 'expired' : $warranty->warranty_status;
            $result['warranty_type']   = $warranty->warranty_type;
            $result['days_left']       = $daysLeft;
            $result['note']            = $warranty->note;
        } else {
            $result['has_warranty']    = false;
            $result['warranty_status'] = 'none';
            $result['note']            = 'Thiết bị này chưa được kích hoạt bảo hành.';
        }

        // Lịch sử sửa chữa liên quan
        $repairHistory = \App\Models\RepairTicket::where('imei_serial', $imei)
            ->orderBy('ticket_id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'ticket_id'   => $ticket->ticket_id,
                    'status'      => $ticket->status,
                    'issue'       => $ticket->issue_desc,
                    'cost'        => $ticket->estimated_cost,
                ];
            });

        $result['repair_history'] = $repairHistory;

        return response()->json($result);
    }
}
