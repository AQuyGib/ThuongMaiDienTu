<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyClaim;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Cashbook;
use App\Models\InventoryItem;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WarrantyClaimController extends Controller
{
    /**
     * Danh sách yêu cầu bảo hành/đổi trả
     */
    public function index(Request $request)
    {
        $query = WarrantyClaim::query();

        // Lọc theo loại claim_type
        if ($request->filled('claim_type')) {
            $query->where('claim_type', $request->claim_type);
        }

        // Lọc theo trạng thái status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo IMEI hoặc tên/sđt khách hàng
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('imei_serial', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%");
            });
        }

        $claims = $query->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.warranty-claims.index', compact('claims'));
    }

    /**
     * Giao diện tạo mới yêu cầu bảo hành/đổi trả
     */
    public function create()
    {
        return view('admin.warranty-claims.create');
    }

    /**
     * Lưu yêu cầu bảo hành/đổi trả mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'imei_serial' => 'required|string|max:50',
            'claim_type' => 'required|in:warranty,return,exchange',
            'reason' => 'required|string',
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
            'refund_amount' => 'nullable|integer|min:0',
            'refund_method' => 'nullable|in:cash,bank_transfer',
        ], [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'imei_serial.required' => 'Vui lòng nhập IMEI/Serial.',
            'claim_type.required' => 'Vui lòng chọn loại yêu cầu.',
            'reason.required' => 'Vui lòng nhập lý do yêu cầu.',
        ]);

        $matchedUser = User::where('phone_number', $request->customer_phone)->first();
        $isReturn = $request->claim_type === 'return';
        $isExchange = $request->claim_type === 'exchange';
        $refundAmount = $isReturn ? (int) $request->input('refund_amount', 0) : 0;
        $refundMethod = $isReturn ? $request->input('refund_method', 'cash') : null;

        $claim = WarrantyClaim::create([
            'user_id' => $matchedUser ? $matchedUser->user_id : null,
            'imei_serial' => $request->imei_serial,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'claim_type' => $request->claim_type,
            'reason' => $request->reason,
            'status' => $request->status,
            'admin_note' => $request->admin_note,
            'bank_name' => $isReturn ? $request->bank_name : null,
            'bank_account_number' => $isReturn ? $request->bank_account_number : null,
            'bank_account_name' => $isReturn ? $request->bank_account_name : null,
            'refund_amount' => $isReturn ? $refundAmount : null,
            'refund_method' => $isReturn ? $refundMethod : null,
            'refunded_at' => ($isReturn && $request->status === 'approved' && $refundAmount > 0) ? Carbon::now() : null,
        ]);

        // Kích hoạt tác vụ phụ khi duyệt trực tiếp lúc tạo mới
        if ($request->status === 'approved') {
            if ($isReturn && $refundAmount > 0) {
                $methodLabel = $refundMethod === 'bank_transfer' ? 'chuyển khoản' : 'tiền mặt';
                Cashbook::create([
                    'type'           => 'Expense',
                    'amount'         => $refundAmount,
                    'reference_id'   => $claim->id,
                    'reference_type' => 'warranty_claim',
                    'description'    => "Hoàn tiền đổi trả IMEI {$claim->imei_serial} – KH: {$claim->customer_name} ({$methodLabel})",
                ]);
            }
            if ($isReturn || $isExchange) {
                $item = InventoryItem::where('imei_serial', $claim->imei_serial)->first();
                if ($item) {
                    $item->update(['status' => 'In_Stock']);
                    Warranty::where('item_id', $item->item_id)
                        ->where('warranty_status', 'active')
                        ->update(['warranty_status' => 'paused']);
                }
            }
        }

        try {
            \App\Traits\HasAuditLog::logManualEvent(
                'created',
                WarrantyClaim::class,
                $claim->id,
                null,
                $claim->getAttributes()
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to log warranty claim creation: " . $e->getMessage());
        }

        return redirect()->route('admin.warranty-claims.index')->with('success', 'Đã tạo yêu cầu bảo hành/đổi trả thành công.');
    }

    /**
     * Giao diện chỉnh sửa yêu cầu bảo hành/đổi trả
     */
    public function edit($id)
    {
        $claim = WarrantyClaim::findOrFail($id);
        return view('admin.warranty-claims.edit', compact('claim'));
    }

    /**
     * Cập nhật yêu cầu bảo hành/đổi trả
     */
    public function update(Request $request, $id)
    {
        $claim = WarrantyClaim::findOrFail($id);
        $oldStatus = $claim->status;

        $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'imei_serial' => 'required|string|max:50',
            'claim_type' => 'required|in:warranty,return,exchange',
            'reason' => 'required|string',
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
            'refund_amount' => 'nullable|integer|min:0',
            'refund_method' => 'nullable|in:cash,bank_transfer',
        ], [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'imei_serial.required' => 'Vui lòng nhập IMEI/Serial.',
            'claim_type.required' => 'Vui lòng chọn loại yêu cầu.',
            'reason.required' => 'Vui lòng nhập lý do yêu cầu.',
        ]);

        $matchedUser = User::where('phone_number', $request->customer_phone)->first();
        $isReturn = $request->claim_type === 'return';
        $isExchange = $request->claim_type === 'exchange';
        $refundAmount = $isReturn ? (int) $request->input('refund_amount', 0) : 0;
        $refundMethod = $isReturn ? $request->input('refund_method', 'cash') : null;

        $claim->update([
            'user_id' => $matchedUser ? $matchedUser->user_id : $claim->user_id,
            'imei_serial' => $request->imei_serial,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'claim_type' => $request->claim_type,
            'reason' => $request->reason,
            'status' => $request->status,
            'admin_note' => $request->admin_note,
            'bank_name' => $isReturn ? $request->bank_name : null,
            'bank_account_number' => $isReturn ? $request->bank_account_number : null,
            'bank_account_name' => $isReturn ? $request->bank_account_name : null,
            'refund_amount' => $isReturn ? $refundAmount : null,
            'refund_method' => $isReturn ? $refundMethod : null,
            'refunded_at' => ($isReturn && $request->status === 'approved' && !$claim->refunded_at && $refundAmount > 0) ? Carbon::now() : $claim->refunded_at,
        ]);

        // Đồng bộ dữ liệu nếu được chuyển sang trạng thái đã duyệt
        if ($request->status === 'approved') {
            $exists = Cashbook::where('reference_id', $claim->id)
                ->where('reference_type', 'warranty_claim')
                ->exists();

            if (!$exists && $isReturn && $refundAmount > 0) {
                $methodLabel = $refundMethod === 'bank_transfer' ? 'chuyển khoản' : 'tiền mặt';
                Cashbook::create([
                    'type'           => 'Expense',
                    'amount'         => $refundAmount,
                    'reference_id'   => $claim->id,
                    'reference_type' => 'warranty_claim',
                    'description'    => "Hoàn tiền đổi trả IMEI {$claim->imei_serial} – KH: {$claim->customer_name} ({$methodLabel})",
                ]);
            }

            if ($oldStatus !== 'approved' && ($isReturn || $isExchange)) {
                $item = InventoryItem::where('imei_serial', $claim->imei_serial)->first();
                if ($item) {
                    $item->update(['status' => 'In_Stock']);
                    Warranty::where('item_id', $item->item_id)
                        ->where('warranty_status', 'active')
                        ->update(['warranty_status' => 'paused']);
                }
            }
        }

        try {
            \App\Traits\HasAuditLog::logManualEvent(
                'updated',
                WarrantyClaim::class,
                $claim->id,
                null,
                $claim->getAttributes()
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to log warranty claim update: " . $e->getMessage());
        }

        return redirect()->route('admin.warranty-claims.index')->with('success', 'Đã cập nhật yêu cầu thành công.');
    }

    /**
     * Xóa yêu cầu bảo hành/đổi trả
     */
    public function destroy(Request $request, $id)
    {
        $claim = WarrantyClaim::findOrFail($id);
        $imei = $claim->imei_serial;

        $claim->delete();

        try {
            \App\Traits\HasAuditLog::logManualEvent(
                'deleted',
                WarrantyClaim::class,
                $id,
                $claim->getAttributes(),
                null
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to log warranty claim delete: " . $e->getMessage());
        }

        return redirect()->route('admin.warranty-claims.index')->with('success', 'Đã xóa yêu cầu thành công.');
    }

    /**
     * Phê duyệt nhanh yêu cầu từ danh sách
     */
    public function approve(Request $request, $id)
    {
        $claim = WarrantyClaim::findOrFail($id);

        // Chỉ duyệt được khi đang ở trạng thái pending
        if ($claim->status !== 'pending') {
            return redirect()->back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $request->validate([
            'admin_note'    => 'nullable|string|max:500',
            'refund_amount' => 'nullable|integer|min:0',
            'refund_method' => 'nullable|in:cash,bank_transfer',
        ]);

        DB::transaction(function () use ($claim, $request) {
            $isReturn   = $claim->claim_type === 'return';
            $isExchange = $claim->claim_type === 'exchange';
            $isRefund   = $isReturn; // Chỉ return mới hoàn tiền
            $refundAmount = $isRefund ? (int) $request->input('refund_amount', 0) : 0;
            $refundMethod = $isRefund ? $request->input('refund_method', 'cash') : null;

            // ① Cập nhật trạng thái yêu cầu
            $claim->update([
                'status'        => 'approved',
                'admin_note'    => $request->input('admin_note'),
                'refund_amount' => $refundAmount ?: null,
                'refund_method' => $refundMethod,
                'refunded_at'   => $isRefund && $refundAmount > 0 ? Carbon::now() : null,
            ]);

            // ② Ghi sổ thu chi: chỉ khi đổi trả hoàn tiền
            if ($isRefund && $refundAmount > 0) {
                $methodLabel = $refundMethod === 'bank_transfer' ? 'chuyển khoản' : 'tiền mặt';
                Cashbook::create([
                    'type'           => 'Expense',
                    'amount'         => $refundAmount,
                    'reference_id'   => $claim->id,
                    'reference_type' => 'warranty_claim',
                    'description'    => "Hoàn tiền đổi trả IMEI {$claim->imei_serial} – KH: {$claim->customer_name} ({$methodLabel})",
                ]);
            }

            // ③ Cập nhật kho + tạm dừng BH: cho cả return và exchange
            if ($isReturn || $isExchange) {
                $item = InventoryItem::where('imei_serial', $claim->imei_serial)->first();
                if ($item) {
                    $item->update(['status' => 'In_Stock']); // Hàng về kho

                    // ④ Tạm dừng bảo hành thiết bị
                    Warranty::where('item_id', $item->item_id)
                        ->where('warranty_status', 'active')
                        ->update(['warranty_status' => 'paused']);
                }
            }
        });

        try {
            \App\Traits\HasAuditLog::logManualEvent(
                'updated',
                WarrantyClaim::class,
                $claim->id,
                null,
                $claim->getAttributes()
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to log warranty claim approve: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đã duyệt thành công!');
    }

    /**
     * Từ chối nhanh yêu cầu từ danh sách
     */
    public function reject(Request $request, $id)
    {
        $claim = WarrantyClaim::findOrFail($id);

        $claim->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note'),
        ]);

        try {
            \App\Traits\HasAuditLog::logManualEvent(
                'updated',
                WarrantyClaim::class,
                $claim->id,
                null,
                $claim->getAttributes()
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to log warranty claim reject: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đã từ chối yêu cầu thành công.');
    }

    /**
     * Hiển thị giao diện in hóa đơn bảo hành, đổi trả, trả hàng/hoàn tiền
     */
    public function printInvoice($id)
    {
        // Truy vấn yêu cầu bảo hành/đổi trả theo ID hoặc ném lỗi 404 nếu không tìm thấy
        $claim = WarrantyClaim::findOrFail($id);

        // Truy vấn thông tin sản phẩm vật lý tương ứng qua số IMEI
        $item = InventoryItem::with('variant.product')->where('imei_serial', $claim->imei_serial)->first();

        // Trả về view hóa đơn chuyên biệt dành cho in ấn
        return view('admin.warranty-claims.invoice', compact('claim', 'item'));
    }
}
