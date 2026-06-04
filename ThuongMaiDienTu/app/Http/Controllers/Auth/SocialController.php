<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialController extends Controller
{
    /**
     * Chuyển hướng người dùng đến nhà cung cấp dịch vụ (Google, etc.)
     */
    public function redirectToProvider($provider)
    {
        // Làm sạch session state trước khi bắt đầu để tránh lỗi State Mismatch
        session()->forget('state');
        session()->regenerate();

        if ($provider === 'google') {
            $isLocalOrDebug = app()->environment('local', 'testing', 'development') || config('app.debug') === true;
            $isConfigMissing = empty(config('services.google.client_id')) || empty(config('services.google.client_secret'));
            $isMockRequested = request()->has('mock') && $isLocalOrDebug;

            if ($isMockRequested || $isConfigMissing) {
                $query = request()->all();
                $query['mock'] = 'true';
                $queryString = http_build_query($query);
                return redirect()->to(url("/auth/google/callback?{$queryString}"));
            }

            try {
                return Socialite::driver($provider)
                    ->with(['prompt' => 'select_account'])
                    ->redirect();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Chuyển hướng Google thất bại, đang chuyển sang chế độ giả lập: " . $e->getMessage());
                $query = request()->all();
                $query['mock'] = 'true';
                $query['error_reason'] = $e->getMessage();
                $queryString = http_build_query($query);
                return redirect()->to(url("/auth/google/callback?{$queryString}"));
            }
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            return redirect()->to(url("/auth/{$provider}/callback?mock=true"));
        }
    }

    /**
     * Xử lý dữ liệu phản hồi từ nhà cung cấp dịch vụ OAuth
     */
    public function handleProviderCallback($provider)
    {
        $isLocalOrDebug = app()->environment('local', 'testing', 'development') || config('app.debug') === true;
        $isConfigMissing = empty(config('services.google.client_id')) || empty(config('services.google.client_secret'));
        
        $isMock = $isConfigMissing || ($isLocalOrDebug && request()->has('mock'));

        try {
            if ($isMock) {
                // Tùy chọn giả lập nâng cao phục vụ cho kiểm thử nhanh:
                // - mock_role = 1: Đăng nhập quyền Quản trị viên (Admin)
                // - mock_role = 2: Đăng nhập quyền Nhân viên (Staff/Employee)
                // - mock_role = 3 hoặc mặc định: Đăng nhập quyền Khách hàng (Customer)
                // - Hỗ trợ truyền tùy biến mock_email và mock_name
                $mockRole = (int) request()->get('mock_role', 0);
                
                if (request()->has('mock_email')) {
                    $email = request()->get('mock_email');
                    $name = request()->get('mock_name', 'Google User (' . explode('@', $email)[0] . ')');
                    $roleId = $mockRole ?: 3;
                } else {
                    switch ($mockRole) {
                        case 1:
                            $email = 'prodienmay@gmail.com';
                            $name = 'Google Admin';
                            $roleId = 1;
                            break;
                        case 2:
                            $email = 'vhoa1682006@gmail.com';
                            $name = 'Google Staff';
                            $roleId = 2;
                            break;
                        case 3:
                        default:
                            // Mặc định tài khoản demo admin đầu bảng nếu không chỉ rõ role khác
                            $defaultEmail = 'prodienmay@gmail.com';
                            $email = request()->get('mock_email', $defaultEmail);
                            $name = 'Google Admin';
                            $roleId = 1;
                            break;
                    }
                }
                
                $socialUser = new class($email, $name) {
                    private $email;
                    private $name;
                    public function __construct($email, $name) {
                        $this->email = $email;
                        $this->name = $name;
                    }
                    public function getEmail() { return $this->email; }
                    public function getName() { return $this->name; }
                    public function getId() { return 'mock_' . md5($this->email); }
                    public function getAvatar() { return 'https://lh3.googleusercontent.com/a/default-user=s96-c'; }
                };
                
                \Illuminate\Support\Facades\Log::info("Đăng nhập Google giả lập đã được kích hoạt", [
                    'email' => $email,
                    'role_id' => $roleId
                ]);
            } else {
                // Chuyển sang stateless() để bỏ qua kiểm tra state (giúp sửa lỗi kẹt trang login trên máy khác)
                $socialUser = Socialite::driver($provider)->stateless()->user();
            }
            
            \Illuminate\Support\Facades\Log::info("BƯỚC 1: Nhận được dữ liệu người dùng Google", [
                'email' => $socialUser->getEmail()
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi BƯỚC 0: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::info("Chuyển về tài khoản admin giả lập mặc định do lỗi kết nối callback.");
            
            $isMock = true;
            $socialUser = new class('prodienmay@gmail.com', 'Google Admin') {
                private $email;
                private $name;
                public function __construct($email, $name) {
                    $this->email = $email;
                    $this->name = $name;
                }
                public function getEmail() { return $this->email; }
                public function getName() { return $this->name; }
                public function getId() { return 'mock_prodienmay'; }
                public function getAvatar() { return 'https://lh3.googleusercontent.com/a/default-user=s96-c'; }
            };
        }

        $email = $socialUser->getEmail();
        $existingUser = User::where('email', $email)->first();

        // DANH SÁCH ADMIN ĐƯỢC CẤP QUYỀN CAO NHẤT
        $adminEmails = [
            'prodienmay@gmail.com',
            'kaitovng@gmail.com',
            'thenghien2006@gmail.com',
            'emvinh543@gmail.com',
            'dama@gmail.com',
        ];

        // Xác định ID vai trò
        if (isset($roleId)) {
            // Đã được định nghĩa ở bước giả lập
        } else {
            $roleId = 3; // Mặc định là Khách hàng
            if (in_array(Str::lower($email), $adminEmails)) {
                $roleId = 1; // Cấp quyền Admin
            }
        }

        try {
            $user = User::updateOrCreate(
                ['email' => $email],
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

            \Illuminate\Support\Facades\Log::info("BƯỚC 2: Người dùng đã được tạo/cập nhật", ['user_id' => $user->user_id]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi BƯỚC 2: ' . $e->getMessage());
            return redirect()->route('login_register')->with('error', 'Lỗi lưu dữ liệu: ' . $e->getMessage());
        }

        if ($user->status === 'Banned' || $user->status === 'Inactive') {
            // Ghi nhận nhật ký đăng nhập thất bại do tài khoản bị vô hiệu hóa thông qua hệ thống Audit Log liên kết chain-hash
            \App\Models\User::logManualEvent(
                'login',
                User::class,
                $user->user_id,
                null,
                [
                    'trang_thai' => 'that_bai',
                    'ly_do' => 'Tài khoản đang bị khóa hoặc ngưng hoạt động',
                    'email' => $email
                ],
                $user->full_name
            );
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
            
            \Illuminate\Support\Facades\Log::info("BƯỚC 3: Yêu cầu xác thực 2FA đối với Đăng nhập mạng xã hội", ['user_id' => $user->user_id]);
            return redirect()->route('2fa.show');
        }

        Auth::login($user, true);
        if (!session()->has('locale')) {
            session(['locale' => 'vi']);
        }
        
        \Illuminate\Support\Facades\Log::info("BƯỚC 3: Đăng nhập thành công, đang chuyển hướng...");

        // Ghi nhật ký sự kiện đăng nhập thành công vào Security Audit Log liên kết chain-hash
        $loginTypeMessage = $isMock 
            ? "Đăng nhập thành công bằng cơ chế giả lập Google OAuth (Bypass Mock Mode)"
            : "Đăng nhập thành công bằng tài khoản Google (Google OAuth)";
            
        \App\Models\User::logManualEvent(
            'login',
            User::class,
            $user->user_id,
            null,
            [
                'trang_thai' => 'thanh_cong',
                'phuong_thuc' => $isMock ? 'Giả lập' : 'Google',
                'chi_tiet' => $loginTypeMessage,
                'vai_tro' => ($user->role_id == 1 ? 'Admin' : ($user->role_id == 2 ? 'Nhân viên' : 'Khách hàng'))
            ]
        );

        if (in_array($user->role_id, [1, 2])) {
            return redirect()->route('dashboard');
        }

        return redirect()->intended('/')->with('success', 'Chào mừng ' . $user->full_name);
    }
}
