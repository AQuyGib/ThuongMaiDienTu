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

        // 2. Kiểm tra vai trò (role_id 1: Admin, role_id 2: Quản lý)
        $user = Auth::user();
        if ($user->role_id == 1 || $user->role_id == 2) {
            return $next($request);
        }

        // 3. Nếu là Khách hàng (role_id 3) hoặc khác, từ chối truy cập
        return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập vào trang quản trị.');
    }
}
