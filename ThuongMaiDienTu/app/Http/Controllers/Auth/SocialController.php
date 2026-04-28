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
            $user = User::create([
                'full_name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password_hash' => bcrypt(Str::random(16)),
                'role_id' => 2, // Giả sử 2 là Role khách hàng
                'status' => 'Active',
            ]);
        }

        Auth::login($user);

        return redirect()->intended('/');
    }
}
