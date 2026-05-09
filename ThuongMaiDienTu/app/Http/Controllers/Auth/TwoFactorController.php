<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    /**
     * Trang cài đặt bảo mật 2FA
     */
    public function securityPage()
    {
        $user = Auth::user();
        
        // 1. Lấy danh sách các phiên đăng nhập
        $sessions = \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        // 2. Tính toán điểm bảo mật (Security Score)
        $score = 0;
        $details = [];

        // Yếu tố 1: 2FA (Trọng số lớn nhất)
        if ($user->is_2fa_enabled) {
            $score += 45;
            $details['2fa'] = ['status' => 'pass', 'label' => 'Đã bật xác thực 2 bước'];
        } else {
            $details['2fa'] = ['status' => 'fail', 'label' => 'Chưa bật xác thực 2 bước'];
        }

        // Yếu tố 2: Số điện thoại
        if (!empty($user->phone_number)) {
            $score += 25;
            $details['phone'] = ['status' => 'pass', 'label' => 'Đã cập nhật số điện thoại'];
        } else {
            $details['phone'] = ['status' => 'fail', 'label' => 'Thiếu số điện thoại liên kết'];
        }

        // Yếu tố 3: Email (Gmail)
        if (!empty($user->email)) {
            $score += 20;
            // Giả sử nếu có @gmail.com thì uy tín hơn
            $isGmail = str_contains(strtolower($user->email), '@gmail.com');
            $details['email'] = ['status' => 'pass', 'label' => 'Email đã liên kết' . ($isGmail ? ' (Gmail)' : '')];
        }

        // Yếu tố 4: Độ tươi mới của mật khẩu (giả định password_changed_at)
        if ($user->password_changed_at && \Carbon\Carbon::parse($user->password_changed_at)->diffInDays() < 90) {
            $score += 10;
            $details['password'] = ['status' => 'pass', 'label' => 'Mật khẩu vừa thay đổi gần đây'];
        } else {
            $details['password'] = ['status' => 'warning', 'label' => 'Nên đổi mật khẩu định kỳ'];
        }

        // Phân loại mức độ
        $securityTier = 'Rất thấp';
        $tierColor = 'var(--brand-danger)';
        if ($score >= 90) { $securityTier = 'Rất cao'; $tierColor = 'var(--brand-success)'; }
        elseif ($score >= 70) { $securityTier = 'Khá tốt'; $tierColor = '#3b82f6'; }
        elseif ($score >= 50) { $securityTier = 'Trung bình'; $tierColor = 'var(--brand-warning)'; }

        return view('Auth.security', compact('user', 'sessions', 'score', 'details', 'securityTier', 'tierColor'));
    }

    /**
     * Hiển thị form xác minh 2FA
     */
    public function show()
    {
        // Chỉ cho vào nếu đang trong luồng 2FA
        if (!session('2fa_user_id')) {
            return redirect()->route('login_register');
        }

        $user = User::find(session('2fa_user_id'));
        return view('Auth.two_factor', compact('user'));
    }

    /**
     * Gửi OTP 2FA đến email
     */
    public function send(Request $request)
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login_register');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login_register');
        }

        // Sinh mã OTP 6 số
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->two_factor_code = $otp;
        $user->two_factor_expires_at = now()->addMinutes(5);
        $user->save();

        // Gửi email
        Mail::send('emails.two_factor', ['user' => $user, 'otp' => $otp], function ($m) use ($user) {
            $m->to($user->email)
              ->subject('[DienMayPro] Mã xác thực đăng nhập (2FA)');
        });

        return back()->with('success', 'Mã OTP đã được gửi đến ' . $user->email . '. Vui lòng kiểm tra hộp thư.');
    }

    /**
     * Xác minh OTP 2FA và hoàn tất đăng nhập
     */
    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login_register')->withErrors(['Phiên đăng nhập đã hết hạn.']);
        }

        $user = User::find($userId);

        // Kiểm tra OTP hợp lệ
        if (!$user->two_factor_code || $user->two_factor_code !== $request->otp) {
            return back()->withErrors(['otp' => 'Mã OTP không chính xác.']);
        }

        // Kiểm tra hết hạn (5 phút)
        if (now()->isAfter($user->two_factor_expires_at)) {
            return back()->withErrors(['otp' => 'Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.']);
        }

        // Xóa OTP sau khi dùng
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        // Hoàn tất đăng nhập
        Auth::loginUsingId($userId, session('2fa_remember', false));
        session()->forget(['2fa_user_id', '2fa_remember']);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    /**
     * Bật / Tắt 2FA cho tài khoản hiện tại
     */
    public function toggle(Request $request)
    {
        $user = Auth::user();
        
        // Sử dụng boolean() để lấy đúng giá trị true/false từ JSON body
        $user->is_2fa_enabled = $request->boolean('is_2fa_enabled');
        $user->save();

        $status = $user->is_2fa_enabled ? 'BẬT' : 'TẮT';
        $message = "Đã $status xác thực hai bước thành công.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_enabled' => $user->is_2fa_enabled,
                'message' => $message,
                'status_text' => $user->is_2fa_enabled ? 'Hoạt động' : 'Vô hiệu',
                'type' => $user->is_2fa_enabled ? 'success' : 'danger'
            ]);
        }

        return back()->with('success', $message);
    }
}
