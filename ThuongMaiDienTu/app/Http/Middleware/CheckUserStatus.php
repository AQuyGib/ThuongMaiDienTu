<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            \Log::info("Checking status for user: " . $user->email . " - Status: " . $user->status);
            
            if ($user->status === 'Banned' || $user->status === 'Inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                $message = ($user->status === 'Banned') ? 'Tài khoản của bạn đã bị khóa.' : 'Tài khoản của bạn đang tạm ngưng hoạt động.';
                return redirect()->route('login')->withErrors(['login_error' => $message]);
            }
        }

        return $next($request);
    }
}
