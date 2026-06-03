<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\CompareController;

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
        ], [
            'email.required' => __('ui.error_email_required'),
            'email.email' => __('ui.error_email_required'),
            'password.required' => __('ui.error_password_min'),
            'password.min' => __('ui.error_password_min'),
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password_hash)) {
            if ($user->status === 'Banned') {
                return back()->withErrors(['login_error' => __('ui.error_banned')])->withInput();
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
            $currentLocale = session('locale', 'vi');
            $request->session()->regenerate();
            session(['locale' => $currentLocale]);
            CompareController::migrateSessionToDb();
            return redirect()->route('home');
        }

        return back()->withErrors(['login_error' => __('ui.error_invalid_credentials')])->withInput();
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
            'full_name.required' => __('ui.error_fullname_required'),
            'email.required' => __('ui.error_email_required'),
            'email.email' => __('ui.error_email_required'),
            'email.unique' => __('ui.error_email_unique'),
            'password.required' => __('ui.error_password_min'),
            'password.min' => __('ui.error_password_min'),
            'password.confirmed' => __('ui.error_password_confirmed'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('active_tab', 'register');
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'is_2fa_enabled' => 0,
            'role_id' => 3, // 3 là Khách hàng
            'status' => 'Active',
            'member_tier' => 'Dong'
        ]);

        Auth::login($user);
        if (!session()->has('locale')) {
            session(['locale' => 'vi']);
        }
        CompareController::migrateSessionToDb();
        return redirect()->route('home');
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        // Đăng xuất người dùng khỏi hệ thống
        Auth::logout();
        
        // Hủy bỏ và xóa sạch toàn bộ dữ liệu trong session hiện tại
        $request->session()->invalidate();
        
        // Làm mới (regenerate) CSRF token để ngăn chặn tấn công giả mạo yêu cầu
        $request->session()->regenerateToken();
        
        // Quay về trang chủ sau khi đã đăng xuất thành công
        return redirect()->route('home');
    }
}
