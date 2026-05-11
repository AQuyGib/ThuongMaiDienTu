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
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login_register')->with('error', 'Đăng nhập thất bại.');
        }

        $existingUser = User::where('email', $socialUser->getEmail())->first();

        $roleId = 3; // Mặc định là Khách hàng (theo RoleSeeder)
        if ($socialUser->getEmail() === 'prodienmay@gmail.com') {
            $roleId = 1; // Admin cho tài khoản chính
        }

        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'full_name' => $socialUser->getName(),
                'google_id' => $socialUser->getId(),
                'provider' => $provider,
                'avatar' => $socialUser->getAvatar(),
                'password_hash' => $existingUser ? $existingUser->password_hash : bcrypt(Str::random(16)),
                'role_id' => $existingUser ? $existingUser->role_id : $roleId,
                'status' => $existingUser ? $existingUser->status : 'Active',
                'member_tier' => $existingUser ? $existingUser->member_tier : 'Dong',
            ]
        );

        Auth::login($user);

        return redirect()->intended('/');
    }
}
