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
        return view('policy.warranty');
    }

    /**
     * Hiển thị trang chính sách đổi trả
     */
    public function returnPolicy()
    {
        return view('policy.return_policy');
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

    /**
     * Gửi yêu cầu bảo hành hoặc đổi trả
     */
    public function storeClaim(Request $request)
    {
        $request->validate([
            'imei_serial'    => 'required|string|exists:inventory_items,imei_serial',
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'claim_type'     => 'required|in:warranty,return,exchange',
            'reason'         => 'required|string|max:1000',
        ], [
            'imei_serial.required' => 'Vui lòng cung cấp mã IMEI/Serial.',
            'imei_serial.exists'   => 'Mã IMEI/Serial này không tồn tại trong hệ thống.',
            'customer_name.required' => 'Vui lòng nhập họ tên.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'claim_type.required' => 'Vui lòng chọn loại yêu cầu.',
            'claim_type.in' => 'Loại yêu cầu không hợp lệ.',
            'reason.required' => 'Vui lòng nhập lý do cụ thể.',
        ]);

        \App\Models\WarrantyClaim::create([
            'user_id'        => auth()->id(),
            'imei_serial'    => $request->imei_serial,
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'claim_type'     => $request->claim_type,
            'reason'         => $request->reason,
            'status'         => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu của bạn đã được gửi thành công. Ban quản trị sẽ sớm liên hệ duyệt yêu cầu!',
        ]);
    }
}
