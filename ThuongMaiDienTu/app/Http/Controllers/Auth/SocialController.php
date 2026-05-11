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
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login_register')->with('error', 'Đăng nhập thất bại.');
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $roleId = 3; // Khách hàng
            if ($socialUser->getEmail() === 'prodienmay@gmail.com') {
                $roleId = 1; // Admin
            }

            $user = User::create([
                'full_name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password_hash' => bcrypt(Str::random(16)),
                'role_id' => $roleId,
                'status' => 'Active',
                'member_tier' => 'Dong',
            ]);
        }

        if ($user->status === 'Banned' || $user->status === 'Inactive') {
            return redirect()->route('login')->withErrors(['login_error' => 'Tài khoản của bạn đã bị khóa hoặc ngừng hoạt động.']);
        }

        Auth::login($user);

        return redirect()->intended('/');
    }
}
