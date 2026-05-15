<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class TwoFactorController extends Controller
{
    /**
     * Trang cài đặt bảo mật 2FA và Quản lý phiên đăng nhập
     */
    public function securityPage()
    {
        $user = Auth::user();
        
        // 1. Lấy và phân tích danh sách các phiên đăng nhập
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
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
            $isGmail = str_contains(strtolower($user->email), '@gmail.com');
            $details['email'] = ['status' => 'pass', 'label' => 'Email đã liên kết' . ($isGmail ? ' (Gmail)' : '')];
        }

        // Yếu tố 4: Độ tươi mới của mật khẩu
        if ($user->password_changed_at && Carbon::parse($user->password_changed_at)->diffInDays() < 90) {
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
     * Parse User Agent for basic device info
     */
    private function parseUserAgent($userAgent = null)
    {
        $agent = new Agent();

        if ($userAgent) {
            $agent->setUserAgent($userAgent);
        }

        $device = 'Máy tính'; 
        if ($agent->isMobile()) {
            $device = 'Điện thoại';
        } elseif ($agent->isTablet()) {
            $device = 'Máy tính bảng';
        }

        $os = $agent->platform() ?: 'Unknown OS';
        $browser = $agent->browser() ?: 'Unknown Browser';

        return [
            'os'      => $os,
            'browser' => $browser,
            'device'  => $device
        ];
    }

    /**
     * Đăng xuất một phiên đăng nhập
     */
    public function logoutSession(Request $request, $id)
    {
        Log::info('logoutSession called', ['id' => $id, 'is_current_device' => $request->boolean('is_current_device'), 'session_id' => $request->session()->getId()]);

        // Kiểm tra xem frontend có báo đây là phiên hiện tại không (hoặc ID trùng khớp)
        if ($request->boolean('is_current_device') || $id === $request->session()->getId()) {
            Log::info('Logging out current device');
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } else {
            Log::info('Logging out other device');
            // Xóa phiên của thiết bị khác
            DB::table('sessions')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->delete();
            
            // Xóa remember_token để ngăn thiết bị đó tự động đăng nhập lại
            if (Auth::check()) {
                $user = Auth::user();
                $user->remember_token = null;
                $user->save();
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã đăng xuất thiết bị thành công.'
            ]);
        }

        return back()->with('success', 'Đã đăng xuất thiết bị thành công.');
    }

    /**
     * Hiển thị form xác minh 2FA
     */
    public function show()
    {
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

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->two_factor_code = $otp;
        $user->two_factor_expires_at = now()->addMinutes(5);
        $user->save();

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

        if (!$user->two_factor_code || $user->two_factor_code !== $request->otp) {
            return back()->withErrors(['otp' => 'Mã OTP không chính xác.']);
        }

        if (now()->isAfter($user->two_factor_expires_at)) {
            return back()->withErrors(['otp' => 'Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.']);
        }

        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        Auth::loginUsingId($userId, session('2fa_remember', false));
        session()->forget(['2fa_user_id', '2fa_remember']);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    /**
     * Yêu cầu Bật/Tắt 2FA (Gửi mã OTP xác nhận)
     */
    public function toggleRequest(Request $request)
    {
        $user = Auth::user();
        $intendedState = $request->boolean('is_2fa_enabled');

        // Tạo mã OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->two_factor_code = $otp;
        $user->two_factor_expires_at = now()->addMinutes(5);
        $user->save();

        // Gửi OTP qua email
        try {
            Mail::send('emails.two_factor', ['user' => $user, 'otp' => $otp], function ($m) use ($user) {
                $m->to($user->email)
                  ->subject('[DienMayPro] Mã xác nhận thao tác Bảo mật 2 Lớp');
            });

            return response()->json([
                'success' => true,
                'message' => 'Mã OTP đã được gửi đến email ' . $user->email . '. Vui lòng kiểm tra hộp thư.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi email OTP. Vui lòng kiểm tra lại cấu hình mail.',
            ], 500);
        }
    }

    /**
     * Xác nhận Bật/Tắt 2FA với mã OTP
     */
    public function toggleConfirm(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);
        $user = Auth::user();
        $intendedState = $request->boolean('is_2fa_enabled');

        if (!$user->two_factor_code || $user->two_factor_code !== $request->otp) {
            return response()->json(['success' => false, 'message' => 'Mã OTP không chính xác.'], 400);
        }

        if (now()->isAfter($user->two_factor_expires_at)) {
            return response()->json(['success' => false, 'message' => 'Mã OTP đã hết hạn. Vui lòng thử lại.'], 400);
        }

        // OTP hợp lệ, tiến hành bật/tắt 2FA
        $user->is_2fa_enabled = $intendedState;
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        $status = $user->is_2fa_enabled ? 'BẬT' : 'TẮT';
        $message = "Đã $status xác thực hai bước thành công.";

        return response()->json([
            'success' => true,
            'is_enabled' => $user->is_2fa_enabled,
            'message' => $message,
            'status_text' => $user->is_2fa_enabled ? 'Hoạt động' : 'Vô hiệu',
            'type' => $user->is_2fa_enabled ? 'success' : 'danger'
        ]);
    }
}
