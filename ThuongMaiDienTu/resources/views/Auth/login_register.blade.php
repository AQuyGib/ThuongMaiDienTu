@php
    $error_message = $errors->first('login_error') ?: $errors->first();
    $success_message = session('success');
    $active_tab = session('active_tab', 'login');
@endphp

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập / Đăng Ký</title>
    <!-- Font system: Outfit (UI) + Space Grotesk (Headlines) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <style>
        /* --- KHÁI QUÁT CẤU HÌNH MÀU (CSS VARIABLES) - CHỦ ĐỀ CÔNG NGHỆ ĐỎ XANH --- */
        :root {
            /* Màu chủ đạo Công nghệ (Đỏ & Xanh) */
            --tech-red: #ef4444;
            --tech-blue: #3b82f6;
            --tech-dark: #0f172a;
            
            --primary: #3b82f6; /* Nút chính màu Xanh */
            --primary-hover: #2563eb;
            --secondary: #ef4444; /* Màu phụ màu Đỏ */
            
            /* Màu chữ */
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --text-light: #ffffff;
            
            /* Màu nền & Viền form */
            --bg-form: #ffffff;
            --bg-input: #f8fafc;
            --border-input: #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        /* Tự động chức năng số đếm lên */
        @property --count-num { syntax: '<integer>'; initial-value: 0; inherits: false; }

        body {
            /* Hình ảnh nền ban đầu */
            background-color: #0f172a;
            background-image: url('{{ asset('assets/img/background_login_register.avif') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            
            /* Lớp phủ mờ nhẹ */
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        /* --- CẤU TRÚC CHÍNH BỐ CỤC CHIA ĐÔI --- */
        .main-wrapper {
            width: 100%; max-width: 1100px;
            height: 680px;
            background: var(--bg-form);
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            display: flex;
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        /* --- BẢNG TRÁI: CHÀO MỪNG PREMIUM E-COMMERCE --- */
        .visual-panel {
            width: 45%;
            position: relative;
            background: linear-gradient(150deg, #c0392b 0%, #96281b 35%, #1a2a6c 100%);
            display: flex; flex-direction: column;
            justify-content: flex-end; align-items: stretch;
            padding: 0; text-align: left; color: var(--text-light);
            overflow: hidden;
            /* Layer shadow sang trong */
            box-shadow: inset -1px 0 0 rgba(255,255,255,0.08);
        }

        /* Các vòng trang trí nền ánh sáng */
        .bg-glow {
            position: absolute; border-radius: 50%; pointer-events: none;
            filter: blur(60px);
        }
        .bg-glow-1 { width: 280px; height: 280px; top: -60px; right: -60px; background: rgba(239,68,68,0.5); animation: glowFloat1 8s ease-in-out infinite; }
        .bg-glow-2 { width: 220px; height: 220px; bottom: 60px; left: -80px; background: rgba(59,130,246,0.4); animation: glowFloat2 10s ease-in-out infinite; }
        .bg-glow-3 { width: 160px; height: 160px; top: 40%; right: 30px; background: rgba(255,255,255,0.08); animation: glowFloat1 6s ease-in-out infinite reverse; }

        @keyframes glowFloat1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(20px, 15px) scale(1.1); } }
        @keyframes glowFloat2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-15px, -20px) scale(1.15); } }

        /* Pattern chấm bi nền */
        .bg-dots {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(rgba(255,255,255,0.08) 1.5px, transparent 1.5px);
            background-size: 24px 24px;
            pointer-events: none;
        }

        /* Nội dung chào mừng */
        .welcome-content {
            position: relative; z-index: 10;
            padding: 40px 42px 36px;
            display: flex; flex-direction: column; height: 100%;
            justify-content: space-between;
        }

        /* Logo / thương hiệu bên trái trên cùng */
        .vp-brand {
            font-size: 20px; font-weight: 800; letter-spacing: 0.5px;
            display: flex; align-items: center; gap: 8px;
        }
        .vp-brand-dot { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: inline-block; }
        .vp-brand-dot-2 { background: #3b82f6; }

        /* Phần tiêu đề chính giữa */
        .vp-main { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 24px 0; }
        .vp-tag {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50px; padding: 5px 14px; font-size: 12px; font-weight: 600;
            margin-bottom: 18px; width: fit-content;
            letter-spacing: 0.5px; text-transform: uppercase;
        }
        .vp-tag-dot { width: 6px; height: 6px; border-radius: 50%; background: #86efac; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.4); } }

        .vp-title {
            font-family: 'Space Grotesk', 'Outfit', sans-serif;
            font-size: 38px; font-weight: 800; line-height: 1.15; margin-bottom: 14px;
            animation: slideUp 0.8s ease 0.3s both;
        }
        .vp-title span { color: #fca5a5; }
        .vp-desc {
            font-size: 14px; font-weight: 400; color: rgba(255,255,255,0.75); line-height: 1.7;
        }

        /* Stats row */
        .vp-stats {
            display: flex; gap: 0;
            border-top: 1px solid rgba(255,255,255,0.15);
            padding-top: 24px;
        }
        .vp-stat {
            flex: 1; text-align: center; padding: 0 12px;
            border-right: 1px solid rgba(255,255,255,0.15);
        }
        .vp-stat:last-child { border-right: none; }
        .vp-stat-num { font-size: 22px; font-weight: 800; margin-bottom: 2px; }
        .vp-stat-num span { color: #fca5a5; }
        .vp-stat-label { font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.65); text-transform: uppercase; letter-spacing: 0.5px; }


        /* --- BẢNG PHẢI: KHU VỰC FORM --- */
        .form-panel {
            width: 55%;
            padding: 40px 60px;
            background: var(--bg-form);
            display: flex; flex-direction: column; justify-content: center;
        }

        /* Brand title: dùng Space Grotesk cho headings nổi bật hơn */
        .form-header { text-align: center; margin-bottom: 30px; animation: fadeSlideDown 0.6s ease both; }
        .brand-title {
            font-family: 'Space Grotesk', 'Outfit', sans-serif;
            font-size: 38px; font-weight: 800; color: var(--tech-dark); letter-spacing: -1.5px; margin-bottom: 4px;
        }
        .brand-title .highlight {
            color: transparent;
            background: linear-gradient(90deg, var(--tech-blue), var(--tech-red));
            -webkit-background-clip: text;
        }
        .brand-slogan { font-size: 14px; color: var(--text-muted); font-weight: 500; }

        /* Tabs với hiệu ứng underline trượt */
        .tabs { display: flex; gap: 30px; margin-bottom: 28px; justify-content: center; position: relative; }
        .tab { font-size: 18px; font-weight: 700; color: #cbd5e1; cursor: pointer; transition: all 0.3s; padding-bottom: 6px; border-bottom: 2px solid transparent; position: relative; }
        .tab.active { color: var(--tech-dark); border-bottom: 2px solid var(--tech-blue); }
        .tab:hover:not(.active) { color: var(--text-muted); }
        /* Micro-animation khi chuyển tab */
        .tab.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: var(--tech-blue); animation: underlineSlide 0.3s ease; }
        @keyframes underlineSlide { from { transform: scaleX(0); } to { transform: scaleX(1); } }

        .form-group { margin-bottom: 20px; position: relative; animation: fadeSlideUp 0.5s ease both; }
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 12px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        
        /* Eye toggle cho mật khẩu */
        .input-wrapper { position: relative; }
        .input-wrapper .form-control { padding-right: 46px; }
        .eye-toggle {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: #94a3b8; transition: color 0.2s; background: none; border: none; padding: 0;
        }
        .eye-toggle:hover { color: var(--tech-blue); }
        
        .form-control {
            width: 100%; padding: 14px 16px; 
            background: var(--bg-input);
            border: 1px solid var(--border-input);
            border-radius: 12px; font-size: 15px; color: var(--tech-dark); font-weight: 500;
            transition: all 0.3s ease;
        }
        .form-control::placeholder { color: #94a3b8; font-weight: 400; }
        .form-control:focus { 
            outline: none; 
            background: var(--bg-form);
            border-color: var(--tech-blue); 
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); 
        }

        .forgot-link { font-size: 13px; color: var(--text-muted); text-decoration: none; font-weight: 600; transition: color 0.3s; }
        .forgot-link:hover { color: var(--tech-blue); text-decoration: underline; }

        /* Nút chức năng chính */
        .btn-submit {
            width: 100%; padding: 15px; 
            background: linear-gradient(90deg, var(--tech-blue), #2563eb);
            color: var(--text-light); border: none; border-radius: 12px; 
            font-size: 16px; font-weight: 700; cursor: pointer; 
            margin-top: 5px; transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }
        .btn-submit:hover { 
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(59, 130, 246, 0.4);
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
        }
        .btn-submit:active { transform: translateY(0); }

        .btn-submit-red { 
            background: linear-gradient(90deg, var(--tech-red), #dc2626); 
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3); 
        }
        .btn-submit-red:hover { 
            background: linear-gradient(90deg, #dc2626, #b91c1c); 
            box-shadow: 0 12px 25px rgba(239, 68, 68, 0.4); 
        }

        .divider { 
            display: flex; align-items: center; text-align: center; 
            margin: 25px 0 20px 0; color: var(--text-muted); font-size: 13px; font-weight: 500;
        }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid var(--border-input); }
        .divider:not(:empty)::before { margin-right: 1em; }
        .divider:not(:empty)::after { margin-left: 1em; }


        /* Nút Sign in with Google */
        .btn-google {
            width: 100%; padding: 13px 20px;
            background: #ffffff;
            border: 1px solid #dadce0;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            text-decoration: none;
            font-size: 15px; font-weight: 600; color: #3c4043;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .btn-google:hover {
            background: #f8fafc;
            border-color: #c5c8cc;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        
        .alert { padding: 10px; border-radius: 10px; margin-bottom: 15px; font-size: 14px; text-align: center; font-weight: 600; }
        .alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;}
        .alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;}
        
        .hidden { display: none !important; }

        /* Form animations */
        .form-view { animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeSlideDown { from { opacity: 0; transform: translateY(-16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeSlideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

        /* Hiệu ứng wrapper cứ khi load */
        .main-wrapper { animation: wrapperAppear 0.7s cubic-bezier(0.22,1,0.36,1) both; }
        @keyframes wrapperAppear { from { opacity: 0; transform: scale(0.96) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }

        /* Btn ripple effect */
        .btn-submit { position: relative; overflow: hidden; }
        .btn-submit::after { content: ''; position: absolute; inset: 0; background: rgba(255,255,255,0.15); opacity: 0; transition: opacity 0.3s; }
        .btn-submit:active::after { opacity: 1; }

        /* Canvas particle layer */
        #particle-canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 3; pointer-events: none; }

        /* Nút quay lại trang chủ */
        .back-to-home {
            position: absolute;
            top: 25px;
            right: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 10px 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .back-to-home svg {
            transition: transform 0.3s ease;
        }
        .back-to-home:hover {
            color: var(--tech-blue);
            background: #ffffff;
            border-color: var(--tech-blue);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }
        .back-to-home:hover svg {
            transform: translateX(-4px);
        }

    </style>
</head>
<body>
    
    <div class="main-wrapper">
        <!-- VISUAL PANEL (Trái): Premium E-commerce Welcome -->
        <div class="visual-panel">
            <canvas id="particle-canvas"></canvas>
            <div class="bg-glow bg-glow-1"></div>
            <div class="bg-glow bg-glow-2"></div>
            <div class="bg-glow bg-glow-3"></div>
            <div class="bg-dots"></div>

            <div class="welcome-content">
                <!-- Logo trên cùng -->
                <div class="vp-brand">
                    <span class="vp-brand-dot"></span>
                    <span class="vp-brand-dot vp-brand-dot-2"></span>
                    DienMayPro
                </div>

                <!-- Nội dung chính -->
                <div class="vp-main">
                    <div class="vp-tag">
                        <span class="vp-tag-dot"></span>
                        Đang hoạt động
                    </div>
                    <h2 class="vp-title">Mua sắm điện máy<br><span>thông minh hơn</span></h2>
                    <p class="vp-desc">
                        Hàng nghìn sản phẩm chính hãng với giá tốt nhất.<br>
                        Giao hàng nhanh · Bảo hành chính hãng · Hoàn tiền 30 ngày.
                    </p>
                </div>

                <!-- Thống kê dưới cùng -->
                <div class="vp-stats">
                    <div class="vp-stat">
                        <div class="vp-stat-num">10K<span>+</span></div>
                        <div class="vp-stat-label">Sản phẩm</div>
                    </div>
                    <div class="vp-stat">
                        <div class="vp-stat-num">98<span>%</span></div>
                        <div class="vp-stat-label">Hài lòng</div>
                    </div>
                    <div class="vp-stat">
                        <div class="vp-stat-num">2H<span>+</span></div>
                        <div class="vp-stat-label">Giao hàng</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM PANEL (Phải) -->
        <div class="form-panel" style="position: relative;">
            
            <!-- Nút Quay lại Trang chủ -->
            <a href="{{ route('home') }}" class="back-to-home">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Trang chủ</span>
            </a>

            <!-- Đưa tên thương hiệu lên trên -->
            <div class="form-header">
                <h2 class="brand-title">DienMay<span class="highlight">Pro</span></h2>
                <p class="brand-slogan">Chào mừng đến với cửa hàng điện máy thông minh</p>
            </div>

            <div class="tabs">
                <div class="tab" id="tabLogin">Đăng nhập</div>
                <div class="tab" id="tabRegister">Đăng ký</div>
            </div>

            @if($error_message)
                <div class="alert alert-danger">{{ $error_message }}</div>
            @endif
            @if($success_message)
                <div class="alert alert-success">{{ $success_message }}</div>
            @endif

            <!-- Form Đăng nhập -->
            <div id="formLoginView" class="form-view">
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="Nhập địa chỉ email">
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="••••••••">
                            <button type="button" class="eye-toggle" onclick="togglePassword('password', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        <div style="text-align: right; margin-top: 8px;">
                            <a href="{{ route('password.request') }}" class="forgot-link">Quên mật khẩu?</a>
                        </div>
                    </div>

                    <button type="submit" name="login_submit" class="btn-submit">Đăng Nhập Ngay</button>
                </form>

                <div class="divider">hoặc</div>

                <a href="{{ route('social.login', 'google') }}" class="btn-google">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    <span>Sign in with Google</span>
                </a>
            </div>
            
            <!-- Form Đăng ký -->
            <div id="formRegisterView" class="hidden form-view">
                <form method="POST" action="{{ route('register.post') }}">
                    @csrf
                    <div class="form-group">
                        <label for="full_name">Họ và tên</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="{{ old('full_name') }}" required placeholder="Nhập họ và tên">
                    </div>

                    <div class="form-group">
                        <label for="reg_email">Email</label>
                        <input type="email" id="reg_email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="Nhập địa chỉ email">
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="reg_password">Mật khẩu</label>
                            <div class="input-wrapper">
                                <input type="password" id="reg_password" name="password" class="form-control" required minlength="8" placeholder="••••••••">
                                <button type="button" class="eye-toggle" onclick="togglePassword('reg_password', this)">
                                    <svg id="eye-reg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="password_confirmation">Xác nhận</label>
                            <div class="input-wrapper">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8" placeholder="••••••••">
                                <button type="button" class="eye-toggle" onclick="togglePassword('password_confirmation', this)">
                                    <svg id="eye-confirm" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="register_submit" class="btn-submit btn-submit-red">Đăng Ký Ngay</button>
                </form>
            </div>

        </div>
    </div>

    <script>
        /* ===== CANVAS PARTICLE SYSTEM ===== */
        const canvas = document.getElementById('particle-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];

        function resizeCanvas() {
            const panel = canvas.parentElement;
            canvas.width = panel.offsetWidth;
            canvas.height = panel.offsetHeight;
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        class Particle {
            constructor() { this.reset(); }
            reset() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.r = Math.random() * 1.8 + 0.4;
                this.vx = (Math.random() - 0.5) * 0.4;
                this.vy = (Math.random() - 0.5) * 0.4 - 0.2;
                this.alpha = Math.random() * 0.5 + 0.1;
                this.color = Math.random() > 0.5 ? '255,100,100' : '100,160,255';
            }
            update() {
                this.x += this.vx; this.y += this.vy;
                if (this.y < -5 || this.x < -5 || this.x > canvas.width + 5) this.reset();
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${this.color},${this.alpha})`;
                ctx.fill();
            }
        }

        // Khởi tạo 80 hạt
        for (let i = 0; i < 80; i++) particles.push(new Particle());

        function drawConnections() {
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx*dx + dy*dy);
                    if (dist < 70) {
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.strokeStyle = `rgba(255,255,255,${0.06 * (1 - dist/70)})`;
                        ctx.lineWidth = 0.5;
                        ctx.stroke();
                    }
                }
            }
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            drawConnections();
            particles.forEach(p => { p.update(); p.draw(); });
            requestAnimationFrame(animateParticles);
        }
        animateParticles();

        /* ===== PASSWORD TOGGLE ===== */
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            // Icon mắt mở / mắt gạch
            btn.innerHTML = isHidden
                ? `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>`
                : `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
        }

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

        const currentActiveTab = '{{ $active_tab }}';
        if (currentActiveTab === 'register') {
            showRegister();
        } else {
            showLogin();
        }
    </script>
</body>
</html>