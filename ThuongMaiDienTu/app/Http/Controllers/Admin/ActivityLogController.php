<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\AuditHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * Hiển thị danh sách Nhật ký hoạt động
     */
    public function index(Request $request)
    {
        $query = ActivityLog::query();

        // Lọc theo Sự kiện (event)
        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        // Lọc theo Causer Name
        if ($request->filled('causer_name')) {
            $query->where('causer_name', 'like', '%' . $request->input('causer_name') . '%');
        }

        // Lọc theo IP
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->input('ip_address') . '%');
        }

        // Lọc theo Subject Type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'like', '%' . $request->input('subject_type') . '%');
        }

        // Lọc theo khoảng ngày (created_at)
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        $logs = $query->orderBy('log_id', 'desc')->paginate(15)->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }

    /**
     * Xác minh tính toàn vẹn của toàn bộ chuỗi log (Integrity Verification)
     */
    public function verify(Request $request)
    {
        // Tải toàn bộ log xếp tăng dần theo log_id để verify lũy tiến từ đầu đến cuối
        $logs = DB::table('activity_logs')->orderBy('log_id', 'asc')->get();
        
        $computedHash = null;
        $failedLogId = null;
        $breachDetails = "";

        foreach ($logs as $log) {
            // Chuẩn bị payload tương ứng cấu trúc generateHashChain yêu cầu
            $payload = [
                'event' => $log->event,
                'causer_type' => $log->causer_type,
                'causer_id' => $log->causer_id,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at,
            ];

            // Tính toán mã băm đúng cho dòng này dựa trên hash chain trước
            $expectedHash = AuditHasher::generateHashChain($payload, $computedHash);

            if ($expectedHash !== $log->hash_chain) {
                $failedLogId = $log->log_id;
                $breachDetails = "Sai lệch mã hash tại log ID #{$log->log_id}. Mã hash trong DB: '{$log->hash_chain}', Mã hash tính toán thực tế: '{$expectedHash}'";
                break;
            }

            // Lưu vết mã băm hiện tại làm mã băm cho dòng tiếp theo
            $computedHash = $log->hash_chain;
        }

        if ($failedLogId !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Phát hiện sự cố giả mạo dữ liệu!',
                'log_id' => $failedLogId,
                'details' => $breachDetails
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Toàn bộ chuỗi nhật ký hoạt động được xác minh toàn vẹn an toàn tuyệt đối!'
        ]);
    }
}
