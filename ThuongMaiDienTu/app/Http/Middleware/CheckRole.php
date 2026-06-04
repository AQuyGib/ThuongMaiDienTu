<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Kiểm tra role_id của người dùng trước khi cho phép truy cập route.
     * Sử dụng: middleware('role:1,2') → chỉ Admin và Quản lý được vào.
     *
     * @param  string  $roles  Danh sách role_id phân tách bằng dấu phẩy
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $allowedRoles = array_map('intval', explode(',', $roles));
        $userRole = (int) Auth::user()->role_id;

        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        // Trả về 403 nếu là request AJAX, redirect nếu là request thường
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
        }

        return redirect()->route('admin.dashboard')->with('error', 'Bạn không có quyền truy cập chức năng này.');
    }
}
