<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyClaim;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $claims = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

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
        ], [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'imei_serial.required' => 'Vui lòng nhập IMEI/Serial.',
            'claim_type.required' => 'Vui lòng chọn loại yêu cầu.',
            'reason.required' => 'Vui lòng nhập lý do yêu cầu.',
        ]);

        $matchedUser = User::where('phone_number', $request->customer_phone)->first();

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
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Tạo mới yêu cầu bảo hành/đổi trả tại quầy ID: " . $claim->id . " (IMEI: " . $claim->imei_serial . ")",
            'ip_address' => $request->ip(),
        ]);

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

        $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'imei_serial' => 'required|string|max:50',
            'claim_type' => 'required|in:warranty,return,exchange',
            'reason' => 'required|string',
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string',
        ], [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'imei_serial.required' => 'Vui lòng nhập IMEI/Serial.',
            'claim_type.required' => 'Vui lòng chọn loại yêu cầu.',
            'reason.required' => 'Vui lòng nhập lý do yêu cầu.',
        ]);

        $matchedUser = User::where('phone_number', $request->customer_phone)->first();

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
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Cập nhật yêu cầu bảo hành/đổi trả ID: " . $claim->id . " (IMEI: " . $claim->imei_serial . ")",
            'ip_address' => $request->ip(),
        ]);

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

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Xóa yêu cầu bảo hành/đổi trả ID: " . $id . " (IMEI: " . $imei . ")",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.warranty-claims.index')->with('success', 'Đã xóa yêu cầu thành công.');
    }

    /**
     * Phê duyệt nhanh yêu cầu từ danh sách
     */
    public function approve(Request $request, $id)
    {
        $claim = WarrantyClaim::findOrFail($id);
        
        $claim->update([
            'status' => 'approved',
            'admin_note' => $request->input('admin_note'),
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Phê duyệt yêu cầu bảo hành/đổi trả ID: " . $claim->id . " (IMEI: " . $claim->imei_serial . ")",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Đã duyệt yêu cầu thành công!');
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

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Từ chối yêu cầu bảo hành/đổi trả ID: " . $claim->id . " (IMEI: " . $claim->imei_serial . ")",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Đã từ chối yêu cầu thành công.');
    }
}
