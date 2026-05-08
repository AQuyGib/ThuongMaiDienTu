<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - DienMayPro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --tech-red: #ef4444; --tech-blue: #3b82f6; --tech-dark: #0f172a;
            --primary: #3b82f6; --primary-hover: #2563eb;
            --text-dark: #0f172a; --text-muted: #64748b; --text-light: #ffffff;
            --bg-form: #ffffff; --bg-input: #f8fafc; --border-input: #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        body {
            background-color: #0f172a;
            background-image: url('{{ asset('assets/img/background_login_register.avif') }}');
            background-size: cover; background-position: center; background-attachment: fixed;
            display: flex; justify-content: center; align-items: center; height: 100vh; overflow: hidden;
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        }

        .main-wrapper {
            width: 100%; max-width: 1100px; height: 620px;
            background: var(--bg-form); border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
            display: flex; overflow: hidden; position: relative; z-index: 10;
        }

        /* --- BẢNG TRÁI: Đồng bộ với trang Login --- */
        .visual-panel {
            width: 45%; position: relative;
            background: linear-gradient(150deg, #c0392b 0%, #96281b 35%, #1a2a6c 100%);
            display: flex; flex-direction: column;
            justify-content: flex-end; align-items: stretch;
            padding: 0; color: var(--text-light); overflow: hidden;
        }

        .bg-glow { position: absolute; border-radius: 50%; pointer-events: none; filter: blur(60px); }
        .bg-glow-1 { width: 280px; height: 280px; top: -60px; right: -60px; background: rgba(239,68,68,0.5); }
        .bg-glow-2 { width: 220px; height: 220px; bottom: 60px; left: -80px; background: rgba(59,130,246,0.4); }
        .bg-glow-3 { width: 160px; height: 160px; top: 40%; right: 30px; background: rgba(255,255,255,0.08); }

        .bg-dots {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(rgba(255,255,255,0.08) 1.5px, transparent 1.5px);
            background-size: 24px 24px; pointer-events: none;
        }

        .welcome-content {
            position: relative; z-index: 10; padding: 40px 42px 36px;
            display: flex; flex-direction: column; height: 100%; justify-content: space-between;
        }

        .vp-brand { font-size: 20px; font-weight: 800; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px; }
        .vp-brand-dot { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: inline-block; }
        .vp-brand-dot-2 { background: #3b82f6; }

        .vp-main { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 24px 0; }

        .vp-tag {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50px; padding: 5px 14px; font-size: 12px; font-weight: 600;
            margin-bottom: 18px; width: fit-content; letter-spacing: 0.5px; text-transform: uppercase;
        }
        .vp-tag-dot { width: 6px; height: 6px; border-radius: 50%; background: #fde68a; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.4); } }

        .vp-title { font-size: 36px; font-weight: 800; line-height: 1.2; margin-bottom: 14px; }
        .vp-title span { color: #fca5a5; }
        .vp-desc { font-size: 14px; font-weight: 400; color: rgba(255,255,255,0.75); line-height: 1.7; }

        /* Các bước recovery */
        .vp-steps { display: flex; flex-direction: column; gap: 12px; }
        .vp-step {
            display: flex; align-items: center; gap: 14px;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
            border-radius: 14px; padding: 12px 16px;
        }
        .vp-step-num {
            width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 800; flex-shrink: 0;
        }
        .vp-step-num.active { background: var(--tech-red); }
        .vp-step-text { font-size: 13px; font-weight: 500; color: rgba(255,255,255,0.85); }

        /* --- BẢNG PHẢI: FORM --- */
        .form-panel {
            width: 55%; padding: 50px 70px;
            background: var(--bg-form); display: flex; flex-direction: column; justify-content: center;
        }

        .form-back {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 600; color: var(--text-muted);
            text-decoration: none; margin-bottom: 30px; transition: color 0.2s;
        }
        .form-back:hover { color: var(--tech-blue); }

        .form-title { font-size: 32px; font-weight: 800; color: var(--text-dark); margin-bottom: 8px; }
        .form-desc { font-size: 15px; color: var(--text-muted); margin-bottom: 32px; line-height: 1.5; font-weight: 500; }

        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; color: var(--text-dark); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control {
            width: 100%; padding: 14px 16px;
            background: var(--bg-input); border: 1px solid var(--border-input);
            border-radius: 12px; font-size: 15px; color: var(--tech-dark); font-weight: 500;
            transition: all 0.3s ease;
        }
        .form-control::placeholder { color: #94a3b8; font-weight: 400; }
        .form-control:focus { outline: none; background: var(--bg-form); border-color: var(--tech-blue); box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }

        .btn-submit {
            width: 100%; padding: 15px;
            background: linear-gradient(90deg, var(--tech-blue), var(--primary-hover));
            color: var(--text-light); border: none; border-radius: 12px;
            font-size: 16px; font-weight: 700; cursor: pointer;
            margin-top: 5px; transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(59,130,246,0.3);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(59,130,246,0.4); }

        .alert { padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; text-align: center; font-weight: 600; }
        .alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <!-- VISUAL PANEL (Trái) -->
        <div class="visual-panel">
            <div class="bg-glow bg-glow-1"></div>
            <div class="bg-glow bg-glow-2"></div>
            <div class="bg-glow bg-glow-3"></div>
            <div class="bg-dots"></div>

            <div class="welcome-content">
                <div class="vp-brand">
                    <span class="vp-brand-dot"></span>
                    <span class="vp-brand-dot vp-brand-dot-2"></span>
                    DienMayPro
                </div>

                <div class="vp-main">
                    <div class="vp-tag">
                        <span class="vp-tag-dot"></span>
                        Khôi phục tài khoản
                    </div>
                    <h2 class="vp-title">Quên mật khẩu?<br><span>Đừng lo lắng!</span></h2>
                    <p class="vp-desc">Chỉ cần 3 bước đơn giản để lấy lại quyền truy cập tài khoản của bạn một cách an toàn.</p>
                </div>

                <div class="vp-steps">
                    <div class="vp-step">
                        <div class="vp-step-num active">1</div>
                        <div class="vp-step-text">Nhập email đăng ký tài khoản</div>
                    </div>
                    <div class="vp-step">
                        <div class="vp-step-num">2</div>
                        <div class="vp-step-text">Nhận & nhập mã OTP gửi về email</div>
                    </div>
                    <div class="vp-step">
                        <div class="vp-step-num">3</div>
                        <div class="vp-step-text">Tạo mật khẩu mới và đăng nhập</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM PANEL (Phải) -->
        <div class="form-panel">
            <a href="{{ route('login_register') }}" class="form-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Quay lại đăng nhập
            </a>

            <h2 class="form-title">Khôi phục mật khẩu</h2>
            <p class="form-desc">Nhập địa chỉ email đăng ký. Chúng tôi sẽ gửi mã OTP có hiệu lực trong <strong>5 phút</strong>.</p>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email đăng ký</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="Nhập địa chỉ email của bạn" value="{{ old('email') }}">
                </div>
                <button type="submit" class="btn-submit">Gửi mã OTP</button>
            </form>
        </div>
    </div>
</body>
</html>
