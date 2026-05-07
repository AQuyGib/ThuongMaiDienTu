<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Hiển thị trang đăng nhập/đăng ký
     */
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('Auth.login_register');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password_hash)) {
            if ($user->status === 'Banned') {
                return back()->withErrors(['login_error' => 'Tài khoản của bạn đã bị khóa.'])->withInput();
            }

            // Kiểm tra 2FA
            if ($user->is_2fa_enabled) {
                // Sinh OTP và gửi email
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $user->two_factor_code = $otp;
                $user->two_factor_expires_at = now()->addMinutes(5);
                $user->save();

                Mail::send('emails.two_factor', ['user' => $user, 'otp' => $otp], function ($m) use ($user) {
                    $m->to($user->email)->subject('[DienMayPro] Mã xác thực đăng nhập (2FA)');
                });

                // Lưu vào session để TwoFactorController xử lý
                session(['2fa_user_id' => $user->user_id, '2fa_remember' => $request->has('remember')]);

                return redirect()->route('2fa.show');
            }

            // Không có 2FA → đăng nhập bình thường
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();
            return redirect()->route('home');
        }

        return back()->withErrors(['login_error' => 'Email hoặc mật khẩu không chính xác.'])->withInput();
    }


    /**
     * Xử lý đăng ký
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'Email này đã được sử dụng.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('active_tab', 'register');
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'is_2fa_enabled' => 0,
            'role_id' => 3, // Khách hàng
            'status' => 'Active',
            'member_tier' => 'Dong'
        ]);

        Auth::login($user);
        return redirect()->route('home');
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
