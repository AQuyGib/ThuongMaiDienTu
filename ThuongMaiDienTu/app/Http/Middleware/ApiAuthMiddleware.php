<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return response()->json([
                'message' => app()->getLocale() === 'en' 
                    ? 'Authorization token is missing or malformed.' 
                    : 'Token xác thực không hợp lệ hoặc bị thiếu.',
                'locale' => app()->getLocale(),
            ], 401);
        }

        // Xác thực qua Sanctum Guard
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => app()->getLocale() === 'en' 
                    ? 'Invalid or expired authentication token.' 
                    : 'Token xác thực không chính xác hoặc đã hết hạn.',
                'locale' => app()->getLocale(),
            ], 401);
        }

        // Kiểm tra xem tài khoản có bị khóa không
        if ($user->status === 'Banned') {
            $user->currentAccessToken()->delete(); // Hủy token hiện tại khi tài khoản bị khóa
            return response()->json([
                'message' => app()->getLocale() === 'en' 
                    ? 'Your account has been banned.' 
                    : 'Tài khoản của bạn đã bị khóa.',
                'locale' => app()->getLocale(),
            ], 403);
        }

        // Cấu hình authentication cho Laravel Auth Manager và Request User Resolver
        Auth::setUser($user);
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
