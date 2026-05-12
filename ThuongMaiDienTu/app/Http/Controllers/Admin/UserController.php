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

        // Tìm kiếm theo tên hoặc email
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Lọc theo vai trò (nếu có)
        if ($roleFilter = $request->input('role')) {
            $query->where('role_id', $roleFilter);
        }

        // Lọc theo trạng thái (nếu có)
        if ($statusFilter = $request->input('status')) {
            $query->where('status', $statusFilter);
        }

        $users = $query->orderByDesc('created_at')->paginate(15);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
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

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã xóa tài khoản "' . $name . '".');
    }
    /**
     * Đăng xuất tất cả các thiết bị của một người dùng (Dùng cho Admin).
     */
    public function revokeSessions($id)
    {
        DB::table('sessions')->where('user_id', $id)->delete();
        
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

        return view('admin.users.sessions', compact('user', 'sessions'));
    }

    /**
     * Xóa một phiên đăng nhập cụ thể.
     */
    public function deleteSession($sessionId)
    {
        DB::table('sessions')->where('id', $sessionId)->delete();
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

