<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;

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
    /**
     * Logic xuất file Excel chuyên nghiệp (HTML Format)
     */
    private function exportCsv($query)
    {
        $all = $query->get();
        $date = date('d-m-Y');
        $filename = "Danh_sach_nguoi_dung_{$date}.xls";
        
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        $callback = function() use ($all) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-type" content="text/html;charset=utf-8" /></head>';
            echo '<body>';
            echo '<table border="1">';
            echo '<tr style="background-color: #1e40af; color: #ffffff; font-weight: bold; text-transform: uppercase;">';
            echo '<th>ID</th>';
            echo '<th>Họ tên</th>';
            echo '<th>Email</th>';
            echo '<th>Số điện thoại</th>';
            echo '<th>Vai trò</th>';
            echo '<th>Hạng thành viên</th>';
            echo '<th>Trạng thái</th>';
            echo '<th>Ngày tham gia</th>';
            echo '</tr>';
            
            foreach ($all as $u) {
                $statusColor = $u->status === 'Active' ? '#059669' : '#e11d48';
                echo '<tr>';
                echo '<td style="text-align: center;">' . $u->user_id . '</td>';
                echo '<td style="font-weight: bold;">' . htmlspecialchars($u->full_name) . '</td>';
                echo '<td>' . htmlspecialchars($u->email) . '</td>';
                echo '<td>' . ($u->phone_number ?? 'N/A') . '</td>';
                echo '<td>' . ($u->role->name ?? 'Customer') . '</td>';
                echo '<td style="text-align: center;">' . $u->member_tier . '</td>';
                echo '<td style="color: ' . $statusColor . '; font-weight: bold;">' . ($u->status === 'Active' ? 'Hoạt động' : 'Bị khóa') . '</td>';
                echo '<td>' . $u->created_at->format('d/m/Y H:i') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</body></html>';
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
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,role_id',
            'member_tier' => 'required|in:Dong,Bac,Vang',
            'status' => 'required|in:Active,Banned',
            'phone_number' => 'nullable|string|max:20',
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
            'phone_number' => $validated['phone_number'],
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
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,role_id',
            'member_tier' => 'required|in:Dong,Bac,Vang',
            'status' => 'required|in:Active,Banned',
            'phone_number' => 'nullable|string|max:20',
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
            'phone_number' => $validated['phone_number'],
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
        $user = User::findOrFail($id);
        
        // 1. Xóa toàn bộ các phiên trong database
        DB::table('sessions')->where('user_id', $id)->delete();
        
        // 2. Quan trọng: Xóa remember_token để ngăn chặn việc tự động đăng nhập lại từ cookie
        $user->remember_token = null;
        $user->save();
        
        if (request()->ajax()) {
            return response()->json(['message' => 'Đã thu hồi toàn bộ phiên làm việc và vô hiệu hóa ghi nhớ đăng nhập thành công.']);
        }

        return redirect()->back()
            ->with('success', 'Đã thu hồi toàn bộ phiên làm việc thành công.');
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
        $session = DB::table('sessions')->where('id', $sessionId)->first();
        if ($session) {
            $userId = $session->user_id;
            DB::table('sessions')->where('id', $sessionId)->delete();
            
            // Xóa remember_token của user để tránh tự động đăng nhập lại
            $user = User::find($userId);
            if ($user) {
                $user->remember_token = null;
                $user->save();
            }
        }

        if (request()->ajax()) {
            return response()->json(['message' => 'Đã thu hồi phiên đăng nhập và vô hiệu hóa ghi nhớ đăng nhập thành công.']);
        }
        return redirect()->back()->with('success', 'Đã xóa phiên đăng nhập thành công.');
    }

    private function parseUserAgent($userAgent = null)
    {
        // Khởi tạo đối tượng Agent
        $agent = new Agent();

        // Nếu bạn có truyền một chuỗi User-Agent cụ thể vào hàm, thiết lập nó cho Agent
        if ($userAgent) {
            $agent->setUserAgent($userAgent);
        }

        // 1. Xác định thiết bị
        $device = 'Máy tính'; // Giá trị mặc định
        if ($agent->isMobile()) {
            $device = 'Điện thoại';
        } elseif ($agent->isTablet()) {
            $device = 'Máy tính bảng';
        }

        // 2. Lấy hệ điều hành và trình duyệt, nếu không nhận diện được sẽ gán giá trị mặc định
        $os = $agent->platform() ?: 'Unknown OS';
        $browser = $agent->browser() ?: 'Unknown Browser';

        // 3. Trả về kết quả dưới dạng mảng
        return [
            'os'      => $os,
            'browser' => $browser,
            'device'  => $device
        ];
    }
}

