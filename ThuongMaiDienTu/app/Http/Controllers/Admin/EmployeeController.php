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

        // Khởi tạo query và loại trừ Khách hàng (role_id = 3)
        $query = User::with('role')->where('role_id', '!=', 3);

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
            $query->where('role_id', $roleId);
        }

        // Lọc theo trạng thái (status)
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sắp xếp
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':  $query->orderBy('user_id', 'ASC'); break;
            case 'name_az': $query->orderBy('full_name', 'ASC'); break;
            case 'name_za': $query->orderBy('full_name', 'DESC'); break;
            default:        $query->orderBy('user_id', 'DESC'); break;
        }

        // Phân trang
        $employees = $query->paginate(15)->withQueryString();

        // Lấy danh sách vai trò hệ thống (loại trừ Khách hàng)
        $roles = Role::where('role_id', '!=', 3)->get();

        // Số liệu thống kê thời gian thực
        $stats = [
            'total' => User::where('role_id', '!=', 3)->count(),
            'active' => User::where('role_id', '!=', 3)->where('status', 'Active')->count(),
            'banned' => User::where('role_id', '!=', 3)->where('status', 'Banned')->count(),
            'by_role' => [
                'admin' => User::where('role_id', 1)->count(),
                'manager' => User::where('role_id', 2)->count(),
                'staff' => User::where('role_id', 4)->count(),
            ]
        ];

        // Trả về JSON được định dạng qua API Resource cho yêu cầu AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'employees' => EmployeeResource::collection($employees)->response()->getData(true),
                'stats' => $stats
            ]);
        }

        // Trả về view Blade với dữ liệu phân trang được định dạng
        $formattedEmployees = EmployeeResource::collection($employees)->response()->getData(true);
        return view('admin.employee.index', [
            'employees' => $formattedEmployees,
            'roles' => $roles,
            'stats' => $stats
        ]);
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
}
