<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Danh sách khách hàng (role_id = 3)
     */
    public function index(Request $request)
    {
        $query = User::where('role_id', 3);

        // Tìm kiếm từ khóa
        if ($search = $request->input('q')) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo hạng thành viên
        if ($request->filled('tier')) {
            $query->where('member_tier', $request->tier);
        }

        // Lọc theo ngày đăng ký
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $customers = $query->orderByDesc('created_at')->paginate(15);
        
        // Admin (Role 1) có thể xem logs
        $logs = [];
        if (Auth::user()->role_id == 1) {
            $logs = ActivityLog::with('user')
                ->where('action', 'LIKE', '%khách hàng%')
                ->orderByDesc('created_at')
                ->take(20)
                ->get();
        }

        return view('admin.customers.index', compact('customers', 'logs'));
    }

    public function create()
    {
        return redirect()->route('admin.customers.index')->with('show_create_modal', true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'phone_number' => ['nullable', 'regex:/^0[0-9]{9}$/'],
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Banned',
        ], [
            'phone_number.regex' => 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.',
        ]);

        $customer = User::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
            'address' => $validated['address'] ?? null,
            'role_id' => 3, // Luôn là khách hàng
            'status' => $validated['status'],
        ]);

        // Log hành động
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Thêm mới khách hàng: " . $customer->full_name . " (ID: " . $customer->user_id . ")",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Thêm khách hàng thành công!');
    }

    public function show($id)
    {
        $customer = User::where('role_id', 3)
            ->with(['orders', 'addresses', 'rewardPoints'])
            ->findOrFail($id);

        $totalSpent = $customer->orders->where('status', 'Completed')->sum('total_amount');
        $orderCount = $customer->orders->count();
        $pointBalance = $customer->rewardPoints->sum('points');

        return view('admin.customers.show', compact('customer', 'totalSpent', 'orderCount', 'pointBalance'));
    }

    public function edit($id)
    {
        try {
            $customer = User::where('role_id', 3)->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $isDeleted = User::where('role_id', 3)->onlyTrashed()->where('user_id', $id)->exists();
            if ($isDeleted) {
                return redirect()->route('admin.customers.index')
                    ->with('error', 'Khách hàng này đã bị xóa bởi người khác từ trước!');
            }
            return redirect()->route('admin.customers.index')
                ->with('error', 'Không tìm thấy thông tin khách hàng!');
        }
        return redirect()->route('admin.customers.index')->with('edit_customer', $customer);
    }

    public function update(Request $request, $id)
    {
        try {
            $customer = User::where('role_id', 3)->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $isDeleted = User::where('role_id', 3)->onlyTrashed()->where('user_id', $id)->exists();
            if ($isDeleted) {
                return redirect()->route('admin.customers.index')
                    ->with('error', 'Khách hàng này đã bị xóa bởi người khác. Vui lòng tải lại trang!');
            }
            return redirect()->route('admin.customers.index')
                ->with('error', 'Không tìm thấy thông tin khách hàng. Vui lòng tải lại trang!');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:50',
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($customer->user_id, 'user_id')],
            'password' => 'nullable|string|min:6',
            'phone_number' => ['nullable', 'regex:/^0[0-9]{9}$/'],
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Banned',
            'version' => 'required|integer', // Đối với optimistic locking
        ], [
            'phone_number.regex' => 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.',
        ]);

        $updateData = [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'address' => $validated['address'],
            'status' => $validated['status'],
        ];

        if ($request->filled('password')) {
            $updateData['password_hash'] = Hash::make($request->password);
        }

        // Sử dụng Optimistic Update
        $success = $customer->optimisticUpdate($validated['version'], $updateData);

        if (!$success) {
            return redirect()->back()
                ->with('error', 'Dữ liệu khách hàng đã bị thay đổi bởi người khác. Vui lòng tải lại trang!')
                ->withInput();
        }

        // Log hành động
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Cập nhật khách hàng: " . $customer->full_name . " (ID: " . $customer->user_id . ")",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Cập nhật khách hàng thành công!');
    }

    public function destroy(Request $request, $id)
    {
        // Kiểm tra quyền: Chỉ Admin (1) hoặc Quản lý (2) mới được xóa
        if (!in_array(Auth::user()->role_id, [1, 2])) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa khách hàng!');
        }

        try {
            $customer = User::where('role_id', 3)->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $isDeleted = User::where('role_id', 3)->onlyTrashed()->where('user_id', $id)->exists();
            if ($isDeleted) {
                return redirect()->route('admin.customers.index')
                    ->with('error', 'Khách hàng này đã bị xóa bởi người khác từ trước!');
            }
            return redirect()->route('admin.customers.index')
                ->with('error', 'Không tìm thấy thông tin khách hàng!');
        }
        $name = $customer->full_name;
        
        // Laravel SoftDeletes sẽ tự động xử lý khi gọi delete()
        $customer->delete();

        // Log hành động
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Xóa khách hàng (Soft Delete): " . $name . " (ID: " . $id . ")",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Đã xóa tạm khách hàng thành công!');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Không có khách hàng nào được chọn!']);
        }

        switch ($action) {
            case 'Active':
            case 'Banned':
                User::whereIn('user_id', $ids)->update(['status' => $action]);
                $msg = "Đã cập nhật trạng thái cho " . count($ids) . " khách hàng.";
                break;
            case 'delete':
                if (!in_array(Auth::user()->role_id, [1, 2])) {
                    return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa!']);
                }
                User::whereIn('user_id', $ids)->delete();
                $msg = "Đã xóa tạm " . count($ids) . " khách hàng.";
                break;
            case 'restore':
                User::onlyTrashed()->whereIn('user_id', $ids)->restore();
                $msg = "Đã khôi phục " . count($ids) . " khách hàng.";
                break;
            case 'force-delete':
                if (Auth::user()->role_id != 1) {
                    return response()->json(['success' => false, 'message' => 'Chỉ Admin mới được xóa vĩnh viễn!']);
                }
                User::onlyTrashed()->whereIn('user_id', $ids)->forceDelete();
                $msg = "Đã xóa vĩnh viễn " . count($ids) . " khách hàng.";
                break;
            default:
                return response()->json(['success' => false, 'message' => 'Hành động không hợp lệ!']);
        }

        // Log hành động
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Thao tác hàng loạt ($action) trên các ID: " . implode(', ', $ids),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function trash()
    {
        $customers = User::onlyTrashed()->where('role_id', 3)->orderByDesc('deleted_at')->paginate(15);
        return view('admin.customers.trash', compact('customers'));
    }

    public function restore($id)
    {
        $customer = User::onlyTrashed()->findOrFail($id);
        $customer->restore();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Khôi phục khách hàng: " . $customer->full_name . " (ID: " . $id . ")",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->back()->with('success', 'Đã khôi phục khách hàng thành công!');
    }

    public function forceDelete($id)
    {
        if (Auth::user()->role_id != 1) {
            return redirect()->back()->with('error', 'Chỉ Admin mới có quyền xóa vĩnh viễn!');
        }

        $customer = User::onlyTrashed()->findOrFail($id);
        $name = $customer->full_name;
        $customer->forceDelete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "XÓA VĨNH VIỄN khách hàng: " . $name . " (ID: " . $id . ")",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->back()->with('success', 'Đã xóa vĩnh viễn khách hàng!');
    }

    public function export(Request $request)
    {
        $query = User::where('role_id', 3);

        // Áp dụng bộ lọc
        if ($search = $request->input('q')) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tier')) {
            $query->where('member_tier', $request->tier);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $customers = $query->orderByDesc('created_at')->get();

        $filename = "customers_export_" . date('YmdHis') . ".csv";

        // Sử dụng file tạm trong bộ nhớ để tạo CSV
        $handle = fopen('php://temp', 'r+');

        // Thêm BOM (Byte Order Mark) để Excel nhận dạng đúng font UTF-8 tiếng Việt
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // Tiêu đề cột
        fputcsv($handle, [
            'ID', 
            'Họ và Tên', 
            'Email', 
            'Số điện thoại', 
            'Hạng thành viên', 
            'Địa chỉ', 
            'Trạng thái', 
            'Ngày tạo'
        ]);

        foreach ($customers as $customer) {
            fputcsv($handle, [
                $customer->user_id,
                $customer->full_name,
                $customer->email,
                $customer->phone_number,
                $customer->member_tier,
                $customer->address,
                $customer->status === 'Active' ? 'Đang hoạt động' : 'Khóa tài khoản',
                $customer->created_at ? $customer->created_at->format('d/m/Y H:i:s') : ''
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', '0');
    }
}


