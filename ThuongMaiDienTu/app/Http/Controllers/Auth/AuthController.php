<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

            // Đăng nhập và lưu session
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();

            // Luôn về trang chủ theo yêu cầu của bạn
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
            'role_id' => 2, // 2 là Khách hàng
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
