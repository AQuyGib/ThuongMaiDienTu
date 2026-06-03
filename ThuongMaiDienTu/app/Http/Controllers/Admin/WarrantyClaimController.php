<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyClaim;
use App\Models\ActivityLog;
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
     * Phê duyệt yêu cầu
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
     * Từ chối yêu cầu
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
