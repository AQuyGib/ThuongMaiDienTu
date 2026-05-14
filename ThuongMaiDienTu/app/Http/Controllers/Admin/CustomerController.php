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

        if ($search = $request->input('q')) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
            });
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
        return view('admin.customers.form', [
            'customer' => new User(),
            'title' => 'Thêm mới khách hàng'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'phone_number' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Banned',
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

    public function edit($id)
    {
        $customer = User::where('role_id', 3)->findOrFail($id);
        return view('admin.customers.form', [
            'customer' => $customer,
            'title' => 'Chỉnh sửa khách hàng'
        ]);
    }

    public function update(Request $request, $id)
    {
        $customer = User::where('role_id', 3)->findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:50',
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($customer->user_id, 'user_id')],
            'password' => 'nullable|string|min:6',
            'phone_number' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Banned',
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

        $customer->update($updateData);

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

        $customer = User::where('role_id', 3)->findOrFail($id);
        $name = $customer->full_name;
        $customer->delete();

        // Log hành động
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "Xóa khách hàng: " . $name . " (ID: " . $id . ")",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Đã xóa khách hàng thành công!');
    }
}
