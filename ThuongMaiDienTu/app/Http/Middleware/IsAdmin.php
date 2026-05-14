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
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Kiểm tra xem người dùng đã đăng nhập chưa
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập trang này.');
        }

        // 2. Kiểm tra vai trò (1: Admin, 2: Quản lý, 4: Nhân viên)
        $user = Auth::user();
        if (in_array($user->role_id, [1, 2, 4])) {
            return $next($request);
        }

        // 3. Nếu là Khách hàng (role_id 3) hoặc khác, từ chối truy cập
        return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập vào trang quản trị.');
    }
}
