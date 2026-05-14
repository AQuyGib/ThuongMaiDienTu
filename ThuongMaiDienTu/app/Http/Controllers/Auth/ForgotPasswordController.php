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
            // Log or ignore if mail is not configured, but OTP is generated
            // For testing purposes locally, we can flash the OTP if mail fails
            return redirect()->route('password.verify.form')->with('email', $email)->with('success', 'Mã OTP đã được gửi đến email (Log: ' . $otp . ')');
        }

        return redirect()->route('password.verify.form')->with('email', $email)->with('success', 'Mã OTP đã được gửi đến email của bạn.');
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
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->otp, $record->token)) {
            return back()->withErrors(['otp' => 'Mã OTP không hợp lệ hoặc đã hết hạn.'])->withInput();
        }

        if (Carbon::parse($record->created_at)->addMinutes(5)->isPast()) {
            return back()->withErrors(['otp' => 'Mã OTP đã hết hạn.'])->withInput();
        }

        session(['reset_email' => $request->email, 'otp_verified' => true]);

        return redirect()->route('password.reset.form')->with('success', 'Xác minh thành công. Vui lòng nhập mật khẩu mới.');
    }

    public function showResetPasswordForm()
    {
        if (!session('otp_verified') || !session('reset_email')) {
            return redirect()->route('password.request')->withErrors(['error' => 'Vui lòng xác minh OTP trước.']);
        }
        return view('Auth.reset_password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.'
        ]);

        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request')->withErrors(['error' => 'Hết phiên làm việc. Vui lòng thử lại.']);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password_hash = Hash::make($request->password);
            $user->save();
        }

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        session()->forget(['reset_email', 'otp_verified']);

        return redirect()->route('login_register')->with('success', 'Mật khẩu đã được thiết lập lại thành công. Vui lòng đăng nhập.');
    }
}
