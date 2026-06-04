<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     * Cho phép: Admin (1), Quản lý (2), Nhân viên (4).
     * Chặn: Khách hàng (3) và người chưa đăng nhập.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Kiểm tra xem người dùng đã đăng nhập chưa
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập trang này.');
        }

        // 2. Cho phép Admin (1), Quản lý (2), Nhân viên (4) vào trang quản trị
        $user = Auth::user();
        $allowedRoles = [1, 2, 4]; // Admin, Quản lý, Nhân viên
        if (in_array((int) $user->role_id, $allowedRoles)) {
            return $next($request);
        }

        // 3. Khách hàng (role_id = 3) và các role khác → từ chối truy cập
        return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập vào trang quản trị.');
    }
}
