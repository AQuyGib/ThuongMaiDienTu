<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TwoFactorController extends Controller
{
    /**
     * Trang cài đặt bảo mật 2FA
     */
    public function securityPage()
    {
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($session) {
                $agent = $this->parseUserAgent($session->user_agent);
                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'is_current_device' => $session->id === request()->session()->getId(),
                    'device' => $agent['device'],
                    'platform' => $agent['os'],
                    'browser' => $agent['browser'],
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];
            });

        return view('Auth.security', compact('sessions'));
    }

    /**
     * Parse User Agent for basic device info
     */
    private function parseUserAgent($userAgent)
    {
        $os = "Unknown OS";
        $browser = "Unknown Browser";
        $device = "Máy tính";

        if (preg_match('/windows|win32/i', $userAgent)) $os = 'Windows';
        elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $os = 'Mac OS';
        elseif (preg_match('/linux/i', $userAgent)) $os = 'Linux';
        elseif (preg_match('/iphone/i', $userAgent)) { $os = 'iOS'; $device = 'iPhone'; }
        elseif (preg_match('/android/i', $userAgent)) { $os = 'Android'; $device = 'Điện thoại Android'; }

        if (preg_match('/firefox/i', $userAgent)) $browser = 'Firefox';
        elseif (preg_match('/chrome/i', $userAgent)) $browser = 'Chrome';
        elseif (preg_match('/safari/i', $userAgent)) $browser = 'Safari';
        elseif (preg_match('/msie/i', $userAgent)) $browser = 'Internet Explorer';
        elseif (preg_match('/edge/i', $userAgent)) $browser = 'Edge';
        
        return [
            'os' => $os,
            'browser' => $browser,
            'device' => $device
        ];
    }

    /**
     * Đăng xuất một phiên đăng nhập
     */
    public function logoutSession($id)
    {
        DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', 'Đã đăng xuất thiết bị thành công.');
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
        $user->is_2fa_enabled = !$user->is_2fa_enabled;
        $user->save();

        $status = $user->is_2fa_enabled ? 'bật' : 'tắt';
        return back()->with('success', "Đã $status xác thực hai bước thành công.");
    }
}
