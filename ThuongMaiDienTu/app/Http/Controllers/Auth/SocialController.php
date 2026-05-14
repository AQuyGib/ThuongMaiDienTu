<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialController extends Controller
{
    public function redirectToProvider($provider)
    {
        if ($provider === 'google') {
            return Socialite::driver($provider)
                ->with(['prompt' => 'select_account'])
                ->redirect();
        }
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            // Cấu hình SSL chặt chẽ cho Guzzle (qua Socialite)
            $socialUser = Socialite::driver($provider)->user();
            
            // KIỂM SOÁT DỮ LIỆU: Log lại thông tin nhận được từ Google (ngoại trừ token)
            \Illuminate\Support\Facades\Log::info("Google Login Success for: " . $socialUser->getEmail(), [
                'id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
                'avatar' => $socialUser->getAvatar(),
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SSL or OAuth Error: ' . $e->getMessage());
            return redirect()->route('login_register')->with('error', 'Lỗi chứng thực bảo mật: ' . $e->getMessage());
        }

        $existingUser = User::where('email', $socialUser->getEmail())->first();

        // Kiểm soát quyền Admin đặc biệt
        $roleId = 3; 
        if (Str::lower($socialUser->getEmail()) === 'prodienmay@gmail.com') {
            $roleId = 1; // Luôn là Admin cho email này
        }

        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'full_name' => $socialUser->getName() ?: 'Người dùng Google',
                'google_id' => $socialUser->getId(),
                'provider' => $provider,
                'avatar' => $socialUser->getAvatar(),
                'password_hash' => $existingUser ? $existingUser->password_hash : bcrypt(Str::random(24)),
                'role_id' => $existingUser ? $existingUser->role_id : $roleId,
                'status' => $existingUser ? $existingUser->status : 'Active',
                'member_tier' => $existingUser ? $existingUser->member_tier : 'Dong',
            ]
        );

        if ($user->status === 'Banned' || $user->status === 'Inactive') {
            return redirect()->route('login')->withErrors(['login_error' => 'Tài khoản của bạn đã bị giới hạn truy cập.']);
        }

        Auth::login($user, true); // Ghi nhớ đăng nhập

        // ĐIỀU HƯỚNG THÔNG MINH: Admin/Manager vào Dashboard, Khách vào Home
        if (in_array($user->role_id, [1, 2])) {
            return redirect()->route('dashboard')->with('success', 'Chào mừng Quản trị viên quay trở lại!');
        }

        return redirect()->intended('/')->with('success', 'Đăng nhập thành công! Chào mừng ' . $user->full_name);
    }
}
