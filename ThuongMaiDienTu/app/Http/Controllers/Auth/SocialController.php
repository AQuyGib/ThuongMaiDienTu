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
        // Làm sạch session state trước khi bắt đầu để tránh lỗi State Mismatch
        session()->forget('state');
        session()->regenerate();

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
            // Chuyển sang stateless() để bỏ qua kiểm tra state (giúp sửa lỗi kẹt trang login trên máy khác)
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            \Illuminate\Support\Facades\Log::info("STEP 1: Google User Data Received", [
                'email' => $socialUser->getEmail()
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('STEP 0 Error: ' . $e->getMessage());
            return redirect()->route('login_register')->with('error', 'Lỗi kết nối Google: ' . $e->getMessage());
        }

        $existingUser = User::where('email', $socialUser->getEmail())->first();

        // DANH SÁCH ADMIN ĐƯỢC CẤP QUYỀN CAO NHẤT
        $adminEmails = [
            'prodienmay@gmail.com',
            'kaitovng@gmail.com',
            'thenghien2006@gmail.com',
        ];

        $roleId = 3; // Mặc định là Khách hàng
        if (in_array(Str::lower($socialUser->getEmail()), $adminEmails)) {
            $roleId = 1; // Cấp quyền Admin
        }

        try {
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

            \Illuminate\Support\Facades\Log::info("STEP 2: User Created/Updated", ['user_id' => $user->user_id]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('STEP 2 Error: ' . $e->getMessage());
            return redirect()->route('login_register')->with('error', 'Lỗi lưu dữ liệu: ' . $e->getMessage());
        }

        if ($user->status === 'Banned' || $user->status === 'Inactive') {
            return redirect()->route('login_register')->withErrors(['login_error' => 'Tài khoản đã bị khóa.']);
        }

        if ($user->is_2fa_enabled) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->two_factor_code = $otp;
            $user->two_factor_expires_at = now()->addMinutes(5);
            $user->save();

            \Illuminate\Support\Facades\Mail::send('emails.two_factor', ['user' => $user, 'otp' => $otp], function ($m) use ($user) {
                $m->to($user->email)->subject('[DienMayPro] Mã xác thực đăng nhập (2FA)');
            });

            session(['2fa_user_id' => $user->user_id, '2fa_remember' => true]);
            
            \Illuminate\Support\Facades\Log::info("STEP 3: 2FA required for Social Login", ['user_id' => $user->user_id]);
            return redirect()->route('2fa.show');
        }

        Auth::login($user, true);
        
        \Illuminate\Support\Facades\Log::info("STEP 3: Login Success, Redirecting...");

        if (in_array($user->role_id, [1, 2])) {
            return redirect()->route('dashboard');
        }

        return redirect()->intended('/')->with('success', 'Chào mừng ' . $user->full_name);
    }
}
