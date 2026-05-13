<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách tài khoản (có tìm kiếm + phân trang).
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        // Tìm kiếm nâng cao (Tên, Email, ID, SĐT)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('user_id', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
            });
        }

        // Lọc theo vai trò
        if ($roleFilter = $request->input('role_id')) {
            $query->where('role_id', $roleFilter);
        }

        // Lọc theo trạng thái
        if ($statusFilter = $request->input('status')) {
            $query->where('status', $statusFilter);
        }

        // Lọc theo hạng thành viên
        if ($tierFilter = $request->input('tier')) {
            $query->where('member_tier', $tierFilter);
        }

        // Sắp xếp nâng cao
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':  $query->orderBy('user_id', 'ASC'); break;
            case 'name_az': $query->orderBy('full_name', 'ASC'); break;
            case 'name_za': $query->orderBy('full_name', 'DESC'); break;
            case 'id_asc':  $query->orderBy('user_id', 'ASC'); break;
            case 'id_desc': $query->orderBy('user_id', 'DESC'); break;
            default:        $query->orderBy('user_id', 'DESC'); break;
        }

        // Xử lý Xuất CSV (Giữ lại tính năng từ file cũ)
        if ($request->input('export') === 'csv') {
            return $this->exportCsv($query);
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();

        // Thống kê (Dùng cho giao diện mới)
        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'Active')->count(),
            'banned' => User::where('status', 'Banned')->count(),
            'tiers' => [
                'Vang' => User::where('member_tier', 'Vang')->count(),
                'Bac' => User::where('member_tier', 'Bac')->count(),
                'Dong' => User::where('member_tier', 'Dong')->count(),
            ]
        ];

        // Nếu là yêu cầu AJAX/JSON từ React, trả về dữ liệu thuần
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'users' => $users,
                'stats' => $stats
            ]);
        }

        return view('admin.permissions.index', compact('users', 'roles', 'stats'));
    }

    /**
     * Logic xuất CSV từ file cũ chuyển sang
     */
    private function exportCsv($query)
    {
        $all = $query->get();
        $filename = "users_export_" . date('Ymd_His') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        $callback = function() use ($all) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
            fputcsv($file, ['ID', 'Họ tên', 'Email', 'SĐT', 'Vai trò', 'Hạng', 'Trạng thái', 'Ngày tạo']);
            
            foreach ($all as $u) {
                fputcsv($file, [
                    $u->user_id, $u->full_name, $u->email, $u->phone_number, 
                    $u->role->name ?? '', $u->member_tier, $u->status, $u->created_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tạo tài khoản mới.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,role_id',
            'member_tier' => 'required|in:Dong,Bac,Vang',
            'status' => 'required|in:Active,Banned',
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        User::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'member_tier' => $validated['member_tier'],
            'status' => $validated['status'],
        ]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Đã tạo tài khoản thành công!']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã tạo tài khoản "' . $validated['full_name'] . '" thành công!');
    }

    /**
     * Cập nhật thông tin tài khoản (có Optimistic Locking).
     *
     * Cơ chế: Khi admin A mở form sửa, form sẽ gửi kèm `version` hiện tại.
     * Khi submit, server kiểm tra version trong DB có khớp không.
     * - Nếu khớp → update thành công, version tăng lên 1.
     * - Nếu KHÔNG khớp → nghĩa là admin B đã sửa trước đó → từ chối update.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:50',
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|exists:roles,role_id',
            'member_tier' => 'required|in:Dong,Bac,Vang',
            'status' => 'required|in:Active,Banned',
            'version' => 'required|integer', // Optimistic Locking
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã được sử dụng bởi tài khoản khác.',
            'version.required' => 'Thiếu thông tin phiên bản. Vui lòng tải lại trang.',
        ]);

        // Chuẩn bị dữ liệu cần cập nhật
        $updateData = [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'member_tier' => $validated['member_tier'],
            'status' => $validated['status'],
        ];

        // Chỉ cập nhật mật khẩu nếu có nhập
        if (!empty($validated['password'])) {
            $updateData['password_hash'] = Hash::make($validated['password']);
        }

        // Optimistic Locking: kiểm tra version trước khi update
        $success = $user->optimisticUpdate((int) $validated['version'], $updateData);

        if (!$success) {
            // CONFLICT: Một admin khác đã cập nhật trước bạn
            return redirect()->route('admin.users.index')
                ->with('error', '⚠️ Xung đột dữ liệu! Tài khoản "' . $user->full_name . '" đã bị chỉnh sửa bởi một quản trị viên khác. Vui lòng mở lại và thử lần nữa.');
        }

        if ($request->ajax()) {
            return response()->json(['message' => 'Đã cập nhật tài khoản thành công!']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã cập nhật tài khoản "' . $validated['full_name'] . '" thành công!');
    }

    /**
     * Xóa tài khoản.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Không cho phép tự xóa chính mình
        if (auth()->id() == $user->user_id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không thể xóa chính tài khoản đang đăng nhập!');
        }

        $name = $user->full_name;
        $user->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Đã xóa tài khoản thành công!']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã xóa tài khoản "' . $name . '".');
    }
    /**
     * Đăng xuất tất cả các thiết bị của một người dùng (Dùng cho Admin).
     */
    public function revokeSessions($id)
    {
        DB::table('sessions')->where('user_id', $id)->delete();
        
        if (request()->ajax()) {
            return response()->json(['message' => 'Đã đăng xuất tất cả các phiên làm việc thành công.']);
        }

        return redirect()->back()
            ->with('success', 'Đã đăng xuất tất cả các phiên làm việc thành công.');
    }

    /**
     * Xem danh sách chi tiết các thiết bị đang đăng nhập của một user.
     */
    public function showSessions($id)
    {
        $user = User::findOrFail($id);
        $sessions = DB::table('sessions')
            ->where('user_id', $id)
            ->orderBy('last_activity', 'desc')
            ->get();

        foreach ($sessions as $session) {
            $agent = $this->parseUserAgent($session->user_agent);
            $session->browser = $agent['browser'];
            $session->os = $agent['os'];
            $session->device = $agent['device'];
            $session->last_active = \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans();
        }

        return view('admin.permissions.sessions', compact('user', 'sessions'));
    }

    /**
     * Xóa một phiên đăng nhập cụ thể.
     */
    public function deleteSession($sessionId)
    {
        DB::table('sessions')->where('id', $sessionId)->delete();
        if (request()->ajax()) {
            return response()->json(['message' => 'Đã xóa phiên đăng nhập thành công.']);
        }
        return redirect()->back()->with('success', 'Đã xóa phiên đăng nhập thành công.');
    }

    private function parseUserAgent($userAgent)
    {
        $os = "Unknown OS";
        $browser = "Unknown Browser";
        $device = "Máy tính";

        if (preg_match('/windows|win32/i', $userAgent)) $os = 'Windows';
        elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $os = 'Mac OS';
        elseif (preg_match('/linux/i', $userAgent)) $os = 'Linux';
        elseif (preg_match('/iphone/i', $userAgent)) { $os = 'iOS'; $device = 'iPhone'; }
        elseif (preg_match('/android/i', $userAgent)) { $os = 'Android'; $device = 'Điện thoại Android'; }

        if (preg_match('/firefox/i', $userAgent)) $browser = 'Firefox';
        elseif (preg_match('/chrome/i', $userAgent)) $browser = 'Chrome';
        elseif (preg_match('/safari/i', $userAgent)) $browser = 'Safari';
        elseif (preg_match('/msie/i', $userAgent)) $browser = 'Internet Explorer';
        elseif (preg_match('/edge/i', $userAgent)) $browser = 'Edge';

        return ['os' => $os, 'browser' => $browser, 'device' => $device];
    }
}

