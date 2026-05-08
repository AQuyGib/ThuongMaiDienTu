<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware tạm thời để auto-login Admin khi dev.
 * SAU KHI build xong module Đăng nhập, XÓA FILE NÀY và thay bằng middleware auth thật.
 */
class AutoLoginAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // Tự đăng nhập bằng admin@techzone.vn (user_id = 1)
            $admin = \App\Models\User::where('email', 'admin@techzone.vn')->first();
            if ($admin) {
                Auth::login($admin);
            }
        }
        return $next($request);
    }
}
