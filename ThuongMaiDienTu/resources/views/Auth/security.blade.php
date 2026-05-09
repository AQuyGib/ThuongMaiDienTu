<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảo mật tài khoản - DienMayPro</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --success: #10b981;
            --success-light: #d1fae5;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --radius-lg: 18px;
            --radius-xl: 26px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        h1, h2, h3, h4, .logo { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ── Navbar ── */
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 0 2rem;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .logo {
            font-size: 24px; font-weight: 800; color: #d0021b; /* Màu đỏ thương hiệu */
            text-decoration: none; display: flex; align-items: center; gap: 8px;
            letter-spacing: -0.5px;
        }
        .logo i { font-size: 22px; color: #ffc107; } /* Bolt vàng */
        .logo span { color: #000; font-weight: 900; margin-left: 2px; }

        .nav-links { display: flex; align-items: center; gap: 24px; }
        .nav-links a {
            color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 600;
            transition: all 0.2s ease; display: flex; align-items: center; gap: 8px;
        }
        .nav-links a:hover { color: var(--primary); }

        .user-profile {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 16px 6px 6px; background: var(--bg-body);
            border: 1px solid var(--border-color); border-radius: 50px;
            font-weight: 600; color: var(--text-main); font-size: 14px;
        }

        .avatar {
            width: 28px; height: 28px; background: var(--primary); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 12px;
        }

        /* ── Layout & Animations ── */
        .container { max-width: 860px; margin: 40px auto; padding: 0 24px; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-up { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }

        /* ── Page Header ── */
        .page-header { margin-bottom: 40px; }
        .page-header h1 { font-size: 32px; font-weight: 800; color: var(--text-main); margin-bottom: 12px; }
        .page-header p { color: var(--text-muted); font-size: 16px; }

        /* ── Cards ── */
        .card {
            background: var(--surface); border-radius: var(--radius-xl);
            border: 2px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-bottom: 32px; overflow: hidden; transition: all 0.3s ease;
        }
        .card:hover { border-color: var(--primary); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.08); }
        .card-body { padding: 32px; }

        /* ── 2FA Section ── */
        .mfa-hero { display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .mfa-info { display: flex; gap: 20px; align-items: center; }
        .mfa-icon {
            width: 64px; height: 64px; background: var(--primary-light); color: var(--primary);
            border-radius: 20px; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 28px; border: 2px solid #dbeafe;
        }
        .mfa-text h2 { font-size: 20px; font-weight: 800; color: var(--text-main); margin-bottom: 4px; }
        .mfa-text p { color: var(--text-muted); font-size: 14px; line-height: 1.6; }

        .toggle-container { display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .toggle-switch { position: relative; width: 76px; height: 40px; display: inline-block; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #cbd5e1; transition: .4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-radius: 40px; border: 2px solid rgba(0,0,0,0.05);
        }
        .slider:before {
            position: absolute; content: ""; height: 30px; width: 30px; left: 3px; bottom: 3px;
            background-color: white; transition: .4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-radius: 50%; box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        }
        input:checked + .slider { background-color: var(--success); }
        input:checked + .slider:before { transform: translateX(36px); }

        .status-tag {
            padding: 5px 14px; border-radius: 50px; font-size: 11px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.8px;
        }
        .tag-on { background: var(--success-light); color: #065f46; border: 1px solid #a7f3d0; }
        .tag-off { background: var(--danger-light); color: #991b1b; border: 1px solid #fecaca; }

        .status-box {
            margin-top: 28px; padding: 20px 24px; border-radius: var(--radius-lg);
            display: flex; gap: 16px; align-items: flex-start; border: 2.5px solid transparent; width: 100%;
        }
        .status-box-warning { background-color: var(--warning-light); border-color: #fcd34d; }
        .status-box-success { background-color: var(--success-light); border-color: #a7f3d0; }

        /* ── Info Section Clearer ── */
        .section-title { padding: 24px 32px 0; font-size: 18px; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 12px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
        .info-card {
            padding: 24px; border-radius: var(--radius-lg); border: 2px solid var(--border-color);
            background: #fff; transition: all 0.3s; display: flex; align-items: center; gap: 16px;
        }
        .info-card:hover { border-color: var(--primary); transform: translateY(-3px); }
        .info-card-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .icon-blue { background: #eff6ff; color: #2563eb; }
        .icon-green { background: #f0fdf4; color: #16a34a; }
        .icon-yellow { background: #fffbeb; color: #d97706; }
        
        .info-content { flex: 1; }
        .info-label { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .info-value { font-size: 15px; font-weight: 800; color: var(--text-main); word-break: break-all; }

        /* ── Steps ── */
        .steps-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px; }
        .step-card { text-align: center; }
        .step-num { 
            width: 48px; height: 48px; background: #fff; color: var(--primary); 
            border: 2px solid var(--primary); border-radius: 14px; 
            display: flex; align-items: center; justify-content: center; 
            margin: 0 auto 16px; font-size: 18px; font-weight: 800;
        }
        .step-card h3 { font-size: 16px; font-weight: 800; margin-bottom: 8px; }
        .step-card p { font-size: 13px; color: var(--text-muted); line-height: 1.6; }

        /* ── Buttons ── */
        .btn-full {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 18px; border-radius: 18px; font-size: 16px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 1px; border: 2.5px solid transparent;
            cursor: pointer; transition: all 0.3s; text-decoration: none; margin-top: 12px;
        }
        .btn-primary-bold { background: #d0021b; color: white; border-color: #ff4d4d; box-shadow: 0 8px 25px rgba(208, 2, 27, 0.3); }
        .btn-primary-bold:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(208, 2, 27, 0.4); border-color: #fff; }

        /* ── Alerts ── */
        .alert { padding: 18px 24px; border-radius: var(--radius-lg); margin-bottom: 24px; display: flex; align-items: center; gap: 14px; font-size: 15px; font-weight: 700; border: 2.5px solid transparent; }
        .alert-success { background: #f0fdf4; color: #065f46; border-color: #a7f3d0; }
        .alert-danger { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="{{ route('home') }}" class="logo">
            <i class="fa-solid fa-bolt"></i>
            DIENMAY<span>PRO</span>
        </a>
        <div class="nav-links">
            <a href="{{ route('home') }}">
                <i class="fa-solid fa-chevron-left"></i>
                Về trang chủ
            </a>
            <div class="user-profile">
                <div class="avatar">{{ substr(auth()->user()->full_name ?? 'U', 0, 1) }}</div>
                <span>{{ auth()->user()->full_name }}</span>
            </div>
        </div>
    </nav>

    <main class="container">
        
        <div class="page-header animate-up">
            <h1>🛡️ Bảo vệ tài khoản</h1>
            <p>Xác thực hai bước (2FA) giúp ngăn chặn hacker truy cập vào tài khoản của bạn ngay cả khi họ có mật khẩu.</p>
        </div>

        @if(session('success'))
        <div class="alert alert-success animate-up">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger animate-up">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <div class="card animate-up delay-1">
            <div class="card-body">
                <div class="mfa-hero">
                    <div class="mfa-info">
                        <div class="mfa-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="mfa-text">
                            <h2>Trạng thái 2FA</h2>
                            <p>Tăng cường bảo mật bằng cách yêu cầu mã xác minh mỗi khi đăng nhập.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('2fa.toggle') }}" class="toggle-container" id="mfa-form">
                        @csrf
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_2fa_enabled" onchange="this.form.submit()" {{ auth()->user()->is_2fa_enabled ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                        <span class="status-tag {{ auth()->user()->is_2fa_enabled ? 'tag-on' : 'tag-off' }}">
                            {{ auth()->user()->is_2fa_enabled ? 'Đã bật' : 'Đã tắt' }}
                        </span>
                    </form>
                </div>

                @if(!auth()->user()->is_2fa_enabled)
                <div class="status-box status-box-warning animate-up">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 24px; color: var(--warning);"></i>
                    <div>
                        <strong>Mức độ bảo mật: Thấp</strong>
                        <p>Tài khoản của bạn chỉ được bảo vệ bởi mật khẩu. Hãy bật 2FA để tránh bị đánh cắp thông tin.</p>
                    </div>
                </div>
                <button type="button" onclick="document.querySelector('input[name=is_2fa_enabled]').click()" class="btn-full btn-primary-bold">
                    Kích hoạt bảo mật 2 lớp ngay
                </button>
                @else
                <div class="status-box status-box-success animate-up">
                    <i class="fa-solid fa-shield-check" style="font-size: 24px; color: var(--success);"></i>
                    <div>
                        <strong>Mức độ bảo mật: Cao</strong>
                        <p>Tài khoản đã được bảo vệ tối ưu. Mã OTP sẽ được gửi đến email của bạn khi cần xác minh.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- ── Đăng nhập & Thiết bị ── -->
        <div class="card animate-up delay-1">
            <div class="section-title">
                <i class="fa-solid fa-desktop" style="color: var(--primary);"></i>
                Thiết bị đang đăng nhập
            </div>
            <div class="card-body">
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">
                    Dưới đây là danh sách các thiết bị đã đăng nhập vào tài khoản của bạn. Nếu thấy thiết bị lạ, hãy đăng xuất ngay lập tức.
                </p>

                <div class="session-list" style="display: flex; flex-direction: column; gap: 16px;">
                    @foreach($sessions as $session)
                    <div class="session-item" style="display: flex; align-items: center; justify-content: space-between; padding: 16px; border: 2px solid var(--border-color); border-radius: var(--radius-lg); transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 48px; height: 48px; background: var(--primary-light); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                @if($session->device == 'iPhone')
                                    <i class="fa-solid fa-mobile-screen-button"></i>
                                @elseif($session->device == 'Điện thoại Android')
                                    <i class="fa-solid fa-mobile-button"></i>
                                @else
                                    <i class="fa-solid fa-laptop"></i>
                                @endif
                            </div>
                            <div>
                                <div style="font-weight: 800; font-size: 15px; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                                    {{ $session->platform }} ({{ $session->browser }})
                                    @if($session->is_current_device)
                                        <span style="background: var(--success-light); color: #065f46; font-size: 10px; padding: 2px 8px; border-radius: 50px; text-transform: uppercase;">Thiết bị này</span>
                                    @endif
                                </div>
                                <div style="font-size: 13px; color: var(--text-muted);">
                                    IP: {{ $session->ip_address }} • Hoạt động {{ $session->last_active }}
                                </div>
                            </div>
                        </div>

                        @if(!$session->is_current_device)
                        <form action="{{ route('security.session.destroy', $session->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn đăng xuất khỏi thiết bị này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: var(--danger-light); color: var(--danger); border: 1px solid #fecaca; padding: 8px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                                Đăng xuất
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>


        <div class="card animate-up delay-2">
            <div class="section-title">
                <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i>
                Thông tin xác thực chi tiết
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-card-icon icon-blue"><i class="fa-solid fa-envelope"></i></div>
                        <div class="info-content">
                            <div class="info-label">Phương thức nhận mã</div>
                            <div class="info-value">Email cá nhân</div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon icon-green"><i class="fa-solid fa-address-card"></i></div>
                        <div class="info-content">
                            <div class="info-label">Email đăng ký</div>
                            <div class="info-value">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon icon-yellow"><i class="fa-solid fa-hourglass-half"></i></div>
                        <div class="info-content">
                            <div class="info-label">Thời gian mã hết hạn</div>
                            <div class="info-value">05 Phút</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card animate-up delay-2">
            <div class="section-title">
                <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
                Quy trình xác minh
            </div>
            <div class="card-body">
                <div class="steps-row">
                    <div class="step-card">
                        <div class="step-num">1</div>
                        <h3>Mật khẩu</h3>
                        <p>Đăng nhập bằng Email và mật khẩu cá nhân.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-num">2</div>
                        <h3>Nhận OTP</h3>
                        <p>Hệ thống gửi mã 6 số qua email ngay lập tức.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-num">3</div>
                        <h3>Vào tài khoản</h3>
                        <p>Nhập mã để mở khóa và truy cập hệ thống.</p>
                    </div>
                </div>
            </div>
        </div>

    </main>
</body>
</html>
