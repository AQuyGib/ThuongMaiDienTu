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
            \Illuminate\Support\Facades\Log::error('Social Login Error: ' . $e->getMessage());
            return redirect()->route('login_register')->with('error', 'Đăng nhập thất bại: ' . $e->getMessage());
        }


        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'full_name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password_hash' => bcrypt(Str::random(16)),
                'role_id' => 2, // 2 là Khách hàng
                'status' => 'Active',
                'member_tier' => 'Dong',
            ]);
        }

        Auth::login($user);

        return redirect()->intended('/');
    }
}
