<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use App\Exports\EmployeeExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    /**
     * Hiển thị danh sách nhân viên.
     * Bảo mật: Gọi policy viewAny để phân quyền.
     */
    public function index(Request $request)
    {
        // 1. Phân quyền truy cập
        Gate::authorize('viewAny', User::class);

        $query = $this->getFilteredEmployeesQuery($request);

        // Phân trang
        $employees = $query->paginate(10)->withQueryString();

        // Lấy danh sách vai trò hệ thống (loại trừ Khách hàng)
        $roles = Role::where('role_id', '!=', 3)->get();

        // Số liệu thống kê thời gian thực (tối ưu từ 6 query → 2 query)
        $statusCounts = User::where('role_id', '!=', 3)
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');
        $roleCounts = User::whereIn('role_id', [1, 2, 4])
            ->selectRaw("role_id, COUNT(*) as cnt")
            ->groupBy('role_id')
            ->pluck('cnt', 'role_id');

        $stats = [
            'total' => ($statusCounts->get('Active', 0) + $statusCounts->get('Banned', 0)),
            'active' => $statusCounts->get('Active', 0),
            'banned' => $statusCounts->get('Banned', 0),
            'by_role' => [
                'admin' => $roleCounts->get(1, 0),
                'manager' => $roleCounts->get(2, 0),
                'staff' => $roleCounts->get(4, 0),
            ]
        ];

        // Định dạng dữ liệu phân trang làm phẳng cấu trúc
        $formatted = EmployeeResource::collection($employees)->response()->getData(true);
        $flattenedEmployees = [
            'data' => $formatted['data'] ?? [],
            'links' => $formatted['meta']['links'] ?? ($formatted['links'] ?? []),
            'current_page' => $formatted['meta']['current_page'] ?? $employees->currentPage(),
            'last_page' => $formatted['meta']['last_page'] ?? $employees->lastPage(),
            'per_page' => $formatted['meta']['per_page'] ?? $employees->perPage(),
            'total' => $formatted['meta']['total'] ?? $employees->total(),
            'from' => $formatted['meta']['from'] ?? $employees->firstItem(),
            'to' => $formatted['meta']['to'] ?? $employees->lastItem(),
        ];

        // Trả về JSON được định dạng qua API Resource cho yêu cầu AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'employees' => $flattenedEmployees,
                'stats' => $stats
            ]);
        }

        // Trả về view Blade với dữ liệu phân trang được định dạng
        return view('admin.employee.index', [
            'employees' => $flattenedEmployees,
            'roles' => $roles,
            'stats' => $stats
        ]);
    }

    /**
     * Lấy query nhân viên đã được lọc theo bộ lọc hiện tại.
     */
    private function getFilteredEmployeesQuery(Request $request)
    {
        $query = User::with(['role', 'loginHistories' => function ($q) {
            $q->latest('login_at')->limit(1);
        }, 'sessions' => function ($q) {
            $q->where('last_active', '>=', now()->subMinutes(5));
        }])->where('role_id', '!=', 3);

        // Tìm kiếm theo tên, email hoặc số điện thoại
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
            });
        }

        // Lọc theo vai trò (role_id)
        if ($roleId = $request->input('role_id')) {
            if ($roleId === 'senior') {
                $query->whereIn('role_id', [1, 2]);
            } else {
                $query->where('role_id', $roleId);
            }
        }

        // Lọc theo trạng thái (status)
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sắp xếp
        $sort = $request->input('sort', 'oldest');
        switch ($sort) {
            case 'newest':  $query->orderBy('user_id', 'DESC'); break;
            case 'name_az': $query->orderBy('full_name', 'ASC'); break;
            case 'name_za': $query->orderBy('full_name', 'DESC'); break;
            default:        $query->orderBy('user_id', 'ASC'); break;
        }

        return $query;
    }

    /**
     * Xuất Excel danh sách nhân viên đã lọc.
     */
    public function exportExcel(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $employees = $this->getFilteredEmployeesQuery($request)->get();

        // Ghi nhật ký hoạt động
        try {
            \App\Traits\HasAuditLog::logManualEvent('export', User::class, null, null, [
                'format' => 'Excel',
                'record_count' => $employees->count(),
                'export_type' => 'employee',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to log employee exportExcel: " . $e->getMessage());
        }

        $filename = 'danh_sach_nhan_vien_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new EmployeeExport($employees), $filename);
    }

    /**
     * Xuất PDF danh sách nhân viên đã lọc.
     */
    public function exportPdf(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $employees = $this->getFilteredEmployeesQuery($request)->get();

        // Ghi nhật ký hoạt động
        try {
            \App\Traits\HasAuditLog::logManualEvent('export', User::class, null, null, [
                'format' => 'PDF',
                'record_count' => $employees->count(),
                'export_type' => 'employee',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to log employee exportPdf: " . $e->getMessage());
        }

        // Tính toán số liệu thống kê nhanh trên tập dữ liệu đã lọc để đưa vào báo cáo
        $total = $employees->count();
        $active = $employees->where('status', 'Active')->count();
        $banned = $employees->where('status', 'Banned')->count();
        
        $admin = $employees->where('role_id', 1)->count();
        $manager = $employees->where('role_id', 2)->count();
        $staff = $employees->where('role_id', 4)->count();

        $pdfStats = [
            'total' => $total,
            'active' => $active,
            'banned' => $banned,
            'admin' => $admin,
            'manager' => $manager,
            'staff' => $staff,
        ];

        // Xuất PDF dạng Landscape (khổ ngang) để bảng hiển thị rộng rãi, đẹp mắt
        $pdf = Pdf::loadView('admin.employee.pdf_report', compact('employees', 'pdfStats'))
            ->setPaper('a4', 'landscape');

        $filename = 'bao_cao_nhan_su_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Lưu thông tin nhân viên mới (DB Transactions & API Resource).
     */
    public function store(StoreEmployeeRequest $request)
    {
        // 1. Phân quyền truy cập
        Gate::authorize('create', User::class);

        $validated = $request->validated();

        // 2. Thực thi giao dịch nguyên tử DB::transaction bảo vệ tính nhất quán dữ liệu
        $employee = DB::transaction(function () use ($validated) {
            return User::create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone'], // Ánh xạ từ phone của Client vào phone_number trong DB
                'password_hash' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
                'status' => $validated['status'],
                'member_tier' => 'Dong',
                'is_2fa_enabled' => false,
                'version' => 1,
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã tạo tài khoản nhân viên "' . $employee->full_name . '" thành công!',
                'employee' => new EmployeeResource($employee->load('role'))
            ], 201);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Đã thêm mới nhân viên thành công!');
    }

    /**
     * Cập nhật thông tin nhân viên (DB Transactions, Optimistic Locking & API Resource).
     */
    public function update(UpdateEmployeeRequest $request, $id)
    {
        // findOrFail tự động ném ngoại lệ ModelNotFoundException trả về HTTP 404 nếu không tìm thấy (do người khác đã xóa)
        $employee = User::where('role_id', '!=', 3)->findOrFail($id);

        // 1. Phân quyền truy cập bằng Gate
        Gate::authorize('update', $employee);

        $validated = $request->validated();

        // 2. So sánh updated_at của DB với last_updated_at gửi lên (format sang toDateTimeString() để chính xác tuyệt đối)
        $dbUpdatedAt = $employee->updated_at ? $employee->updated_at->toDateTimeString() : null;
        $requestLastUpdatedAt = $request->input('last_updated_at');

        if ($dbUpdatedAt !== $requestLastUpdatedAt) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đã bị thay đổi bởi người khác kể từ lúc bạn mở trang. Vui lòng tải lại trang để xem dữ liệu mới nhất.',
                'latest_employee' => new EmployeeResource($employee->load('role')) // Trả về bản ghi mới nhất để UI cập nhật/đối chiếu
            ], 409); // HTTP 409 Conflict
        }

        $updateData = [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone'],
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password_hash'] = Hash::make($validated['password']);
        }

        // 3. Thực thi cập nhật trong DB Transaction
        $success = DB::transaction(function () use ($employee, $validated, $updateData) {
            // Tăng số version của bản ghi lên 1
            $updateData['version'] = ($employee->version || 1) + 1;
            return $employee->update($updateData);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin nhân viên thành công!',
                'employee' => new EmployeeResource($employee->load('role'))
            ]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Đã cập nhật thông tin nhân viên.');
    }

    /**
     * Xóa mềm nhân viên (Phân quyền & Chặn tự xóa).
     */
    public function destroy(Request $request, $id)
    {
        $employee = User::where('role_id', '!=', 3)->findOrFail($id);

        // 1. Phân quyền truy cập (Catch Exception trả về 403 đẹp mắt cho SPA)
        try {
            Gate::authorize('delete', $employee);
        } catch (AuthorizationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 403); // HTTP 403 Forbidden kèm thông báo lỗi chi tiết
            }
            throw $e;
        }

        $employeeName = $employee->full_name;

        // Xóa mềm an toàn lịch sử bán hàng POS
        $employee->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa mềm nhân viên "' . $employeeName . '" thành công!'
            ]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Đã xóa nhân viên thành công.');
    }

    /**
     * Chuyển đổi nhanh trạng thái hoạt động của nhân viên (Active <-> Banned).
     * Bảo mật: Gọi policy update để phân quyền và ngăn tự khóa chính mình.
     */
    public function toggleStatus(Request $request, $id)
    {
        $employee = User::where('role_id', '!=', 3)->findOrFail($id);

        // 1. Phân quyền truy cập bằng Gate
        Gate::authorize('update', $employee);

        // 2. Ngăn tự khóa chính mình
        if ($employee->user_id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể tự khóa tài khoản của chính mình!'
            ], 403);
        }

        // 3. Đổi trạng thái và tăng số version (Optimistic Locking)
        $newStatus = $employee->status === 'Active' ? 'Banned' : 'Active';
        
        $employee->update([
            'status' => $newStatus,
            'version' => ($employee->version || 1) + 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã chuyển trạng thái nhân viên "' . $employee->full_name . '" sang ' . ($newStatus === 'Active' ? 'Đang làm việc' : 'Tạm dừng') . '!',
            'employee' => new \App\Http\Resources\EmployeeResource($employee->load('role'))
        ]);
    }

    /**
     * Thao tác hàng loạt trên nhiều nhân viên (Batch Actions).
     * Hỗ trợ: activate, ban, delete.
     */
    public function batchAction(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $request->validate([
            'action' => 'required|in:activate,ban,delete',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');
        $authId = auth()->id();

        // Loại bỏ chính mình khỏi danh sách thao tác
        $ids = array_filter($ids, fn($id) => $id != $authId);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có nhân viên nào hợp lệ để thao tác (bạn không thể thao tác trên chính mình).'
            ], 422);
        }

        $employees = User::where('role_id', '!=', 3)->whereIn('user_id', $ids)->get();

        // Kiểm tra phân quyền từng nhân viên
        foreach ($employees as $emp) {
            if ($action === 'delete') {
                Gate::authorize('delete', $emp);
            } else {
                Gate::authorize('update', $emp);
            }
        }

        $count = 0;
        DB::transaction(function () use ($employees, $action, &$count) {
            foreach ($employees as $emp) {
                if ($action === 'activate') {
                    $emp->update(['status' => 'Active', 'version' => ($emp->version ?? 1) + 1]);
                } elseif ($action === 'ban') {
                    $emp->update(['status' => 'Banned', 'version' => ($emp->version ?? 1) + 1]);
                } elseif ($action === 'delete') {
                    $emp->delete();
                }
                $count++;
            }
        });

        $actionLabels = [
            'activate' => 'kích hoạt',
            'ban'      => 'khóa',
            'delete'   => 'xóa mềm',
        ];

        return response()->json([
            'success' => true,
            'message' => "Đã {$actionLabels[$action]} thành công {$count} nhân viên!",
            'count'   => $count,
        ]);
    }
}
