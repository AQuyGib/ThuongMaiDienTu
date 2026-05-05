<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

$error_message = '';
$success_message = '';

$active_tab = 'login'; // Luôn mặc định là đăng nhập khi vào trang hoặc load lại (GET)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $active_tab = isset($_POST['register_submit']) ? 'login' : 'register';
}

if (request()->has('registered')) {
    $success_message = "Đăng ký thành công! Vui lòng đăng nhập.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. XỬ LÝ ĐĂNG NHẬP
    if (isset($_POST['login_submit'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $active_tab = 'login';

        $user = User::where('email', $email)->first();

        if ($user && Hash::check($password, $user->password_hash)) {
            if ($user->status === 'Banned') {
                $error_message = "Tài khoản của bạn đã bị khóa.";
            } elseif ($user->is_2fa_enabled) {
                session(['temp_user_id' => $user->user_id]);
                // Ép chuyển hướng trong View
                redirect()->route('auth.sms')->send();
                exit; 
            } else {
                Auth::login($user);
                // Ép chuyển hướng về trang home
                redirect()->route('home')->send();
                exit; 
            }
        } else {
            $error_message = "Email hoặc mật khẩu không chính xác.";
        }
    }

    // 2. XỬ LÝ ĐĂNG KÝ
    if (isset($_POST['register_submit'])) {
        $active_tab = 'register';
        
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $password_confirmation = $_POST['password_confirmation'];

        // Kiểm tra tính hợp lệ cơ bản
        if (empty($full_name) || empty($email) || empty($password)) {
            $error_message = "Vui lòng điền đầy đủ các trường bắt buộc.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Định dạng email không hợp lệ.";
        } elseif (strlen($password) < 8) {
            $error_message = "Mật khẩu phải có ít nhất 8 ký tự.";
        } elseif ($password !== $password_confirmation) {
            $error_message = "Mật khẩu xác nhận không khớp.";
        } elseif (User::where('email', $email)->exists()) {
            $error_message = "Email này đã được sử dụng. Vui lòng chọn email khác.";
        } else {
            try {
                $user = User::create([
                    'full_name' => $full_name,
                    'email' => $email,
                    'password_hash' => Hash::make($password),
                    'is_2fa_enabled' => 0,
                    'role_id' => 2, // Mặc định là khách hàng
                    'status' => 'Active',
                    'member_tier' => 'Dong'
                ]);

                // Đăng nhập ngay lập tức sau khi đăng ký thành công
                Auth::login($user);

                // Ép chuyển hướng thẳng sang trang Home
                redirect()->route('home')->send();
                exit;
            } catch (\Exception $e) {
                $error_message = "Đã có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại sau.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập / Đăng Ký</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            background-color: #e2e8f0;
            background-image: url('{{ asset('assets/img/background_login_register.avif') }}');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px);
            display: flex; justify-content: center; align-items: center; z-index: 1000;
        }

        .modal-container {
            background-color: #ffffff; width: 100%; max-width: 500px;
            border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            padding: 32px; position: relative;
        }

        .tabs { display: flex; margin-bottom: 24px; border-bottom: 1px solid #e5e7eb; padding-right: 20px; }
        .tab {
            flex: 1; text-align: center; padding: 10px 0; font-size: 16px;
            color: #6b7280; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.3s;
        }
        .tab.active { color: #0b5ed7; font-weight: 700; border-bottom: 2px solid #0b5ed7; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 14px; color: #374151; font-weight: 500; }
        .form-control {
            width: 100%; padding: 10px 12px; border: 1px solid #d1d5db;
            border-radius: 6px; font-size: 14px; transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-control:focus { outline: none; border-color: #0b5ed7; box-shadow: 0 0 0 3px rgba(11, 94, 215, 0.1); }

        .btn-submit {
            width: 100%; padding: 12px; background-color: #0b5ed7; color: white;
            border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 8px;
        }
        .btn-submit:hover { background-color: #0a53be; }

        .btn-submit-yellow {
            width: 100%; padding: 12px; background-color: #ffc107; color: #0b5ed7;
            border: none; border-radius: 6px; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 8px;
        }
        .btn-submit-yellow:hover { background-color: #e0a800; }

        .divider { display: flex; align-items: center; text-align: center; margin: 20px 0; color: #6b7280; font-size: 13px; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #e5e7eb; }
        .divider:not(:empty)::before { margin-right: .25em; }
        .divider:not(:empty)::after { margin-left: .25em; }

        .social-login { display: flex; gap: 10px; }
        .btn-social {
            flex: 1; padding: 10px; border: 1px solid #d1d5db; background: white; border-radius: 6px;
            cursor: pointer; font-size: 14px; font-weight: 500; color: #374151; display: flex; justify-content: center; align-items: center; gap: 8px;
        }
        .btn-social:hover { background: #f9fafb; }
        
        .alert { padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; text-align: center; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #f87171;}
        .alert-success { background: #d1fae5; color: #047857; border: 1px solid #34d399;}
        
        .hidden { display: none !important; }
    </style>
</head>
<body>

    <div id="loginModal" class="overlay">
        <div class="modal-container">
            
            <div class="tabs">
                <div class="tab" id="tabLogin">Đăng nhập</div>
                <div class="tab" id="tabRegister">Đăng ký</div>
            </div>

            <?php if(!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if(!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <!-- Form Đăng nhập -->
            <div id="formLoginView">
                <form method="POST" action="">
                    @csrf
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="Nhập email của bạn">
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="Nhập mật khẩu">
                        <div style="text-align: right; margin-top: 8px;">
                            <a href="javascript:void(0)" style="font-size: 13px; color: #0b5ed7; text-decoration: none; font-weight: 500;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Quên mật khẩu?</a>
                        </div>
                    </div>

                    <button type="submit" name="login_submit" class="btn-submit">Đăng Nhập</button>
                    <div style="text-align: center; margin-top: 20px;">
                        <span style="font-size: 14px; color: #6b7280;">Bạn mới biết đến chúng tôi? </span>
                        <a href="javascript:void(0)" onclick="showRegister()" style="font-size: 14px; color: #0b5ed7; text-decoration: none; font-weight: 600;">Đăng ký</a>
                    </div>
                </form>

                <div class="divider">Hoặc đăng nhập bằng</div>
                <div class="social-login">
                    <button class="btn-social" type="button" onclick="window.location.href='{{ route('social.login', 'google') }}'">
                        <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg> Google
                    </button>
                    <button class="btn-social" type="button" onclick="window.location.href='{{ route('social.login', 'facebook') }}'">
                        <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg> Facebook
                    </button>
                </div>
            </div>
            
            <!-- Form Đăng ký -->
            <div id="formRegisterView" class="hidden">
                <form method="POST" action="">
                    @csrf
                    <div class="form-group">
                        <label for="full_name">Họ và tên</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="{{ old('full_name') }}" required placeholder="Nhập họ và tên của bạn">
                    </div>

                    <div class="form-group">
                        <label for="reg_email">Email</label>
                        <input type="email" id="reg_email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="Nhập email đăng ký">
                    </div>

                    <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                        <div style="flex: 1;">
                            <label for="reg_password" style="display: block; margin-bottom: 6px; font-size: 14px; color: #374151; font-weight: 500;">Mật khẩu</label>
                            <input type="password" id="reg_password" name="password" class="form-control" required minlength="8" placeholder="Mật khẩu">
                        </div>
                        <div style="flex: 1;">
                            <label for="password_confirmation" style="display: block; margin-bottom: 6px; font-size: 14px; color: #374151; font-weight: 500;">Xác nhận</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8" placeholder="Xác nhận">
                        </div>
                    </div>

                    <button type="submit" name="register_submit" class="btn-submit-yellow">Đăng Ký Ngay</button>
                    <div style="text-align: center; margin-top: 20px;">
                        <span style="font-size: 14px; color: #6b7280;">Đã có tài khoản? </span>
                        <a href="javascript:void(0)" onclick="showLogin()" style="font-size: 14px; color: #0b5ed7; text-decoration: none; font-weight: 600;">Đăng nhập</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        const tabLogin = document.getElementById('tabLogin');
        const tabRegister = document.getElementById('tabRegister');
        const formLoginView = document.getElementById('formLoginView');
        const formRegisterView = document.getElementById('formRegisterView');

        function showLogin() {
            tabLogin.classList.add('active');
            tabRegister.classList.remove('active');
            formLoginView.classList.remove('hidden');
            formRegisterView.classList.add('hidden');
        }

        function showRegister() {
            tabRegister.classList.add('active');
            tabLogin.classList.remove('active');
            formRegisterView.classList.remove('hidden');
            formLoginView.classList.add('hidden');
        }

        tabLogin.addEventListener('click', showLogin);
        tabRegister.addEventListener('click', showRegister);

        // Giữ tab theo biến active_tab từ PHP
        const currentActiveTab = '<?php echo $active_tab; ?>';
        if (currentActiveTab === 'register') {
            showRegister();
        } else {
            showLogin();
        }
    </script>
</body>
</html>