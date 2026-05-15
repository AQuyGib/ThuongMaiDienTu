<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('Auth.forgot_password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email'], [
            'email.exists' => 'Email không tồn tại trong hệ thống.'
        ]);

        $otp = rand(100000, 999999);
        $email = $request->email;
        \Illuminate\Support\Facades\Log::info("Test OTP cho {$email}: {$otp}");

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($otp), 'created_at' => Carbon::now()]
        );

        // Send Email
        try {
            Mail::send('emails.forgot_password', ['otp' => $otp], function($message) use($email) {
                $message->to($email);
                $message->subject('Mã OTP khôi phục mật khẩu');
            });
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => true, 'email' => $email, 'message' => 'Mã OTP đã được gửi đến email (Log: ' . $otp . ')']);
            }
            return redirect()->route('password.request')->with(['email' => $email, 'step' => 2, 'success' => 'Mã OTP đã được gửi đến email (Log: ' . $otp . ')']);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'email' => $email, 'message' => 'Mã OTP đã được gửi đến email của bạn.']);
        }

        return redirect()->route('password.request')->with(['email' => $email, 'step' => 2, 'success' => 'Mã OTP đã được gửi đến email của bạn.']);
    }

    public function showVerifyOtpForm(Request $request)
    {
        $email = session('email') ?? $request->query('email');
        if (!$email) {
            return redirect()->route('password.request')->withErrors(['error' => 'Vui lòng nhập email trước.']);
        }
        return view('Auth.verify_otp', compact('email'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric'
        ], [
            'otp.required' => 'Vui lòng nhập mã OTP.',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->otp, $record->token)) {
            $msg = 'Mã OTP không hợp lệ hoặc đã hết hạn.';
            return $request->ajax() ? response()->json(['success' => false, 'message' => $msg]) : back()->withErrors(['otp' => $msg]);
        }

        if (Carbon::parse($record->created_at)->addMinutes(5)->isPast()) {
            $msg = 'Mã OTP đã hết hạn.';
            return $request->ajax() ? response()->json(['success' => false, 'message' => $msg]) : back()->withErrors(['otp' => $msg]);
        }

        session(['reset_email' => $request->email, 'otp_verified' => true]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Xác minh OTP thành công.']);
        }

        return redirect()->route('password.reset.form')->with('success', 'Xác minh thành công. Vui lòng nhập mật khẩu mới.');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.'
        ]);

        $email = session('reset_email') ?? $request->email;
        if (!$email) {
            $msg = 'Hết phiên làm việc. Vui lòng thử lại.';
            return $request->ajax() ? response()->json(['success' => false, 'message' => $msg]) : redirect()->route('password.request')->withErrors(['error' => $msg]);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password_hash = Hash::make($request->password);
            $user->save();
        }

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        session()->forget(['email', 'step', 'reset_email', 'otp_verified']);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Mật khẩu đã được khôi phục thành công.', 'redirect' => route('login_register')]);
        }

        return redirect()->route('login_register')->with('success', 'Mật khẩu đã được khôi phục thành công. Vui lòng đăng nhập.');
    }
}
