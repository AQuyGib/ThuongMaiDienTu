<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - DienMayPro</title>
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

        /* --- BẢNG TRÁI --- */
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
        .vp-tag-dot { width: 6px; height: 6px; border-radius: 50%; background: #86efac; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.4); } }

        .vp-title { font-size: 36px; font-weight: 800; line-height: 1.2; margin-bottom: 14px; }
        .vp-title span { color: #86efac; }
        .vp-desc { font-size: 14px; font-weight: 400; color: rgba(255,255,255,0.75); line-height: 1.7; }

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
        .vp-step-num.done { background: #22c55e; }
        .vp-step-num.active { background: #22c55e; }
        .vp-step-text { font-size: 13px; font-weight: 500; color: rgba(255,255,255,0.85); }

        /* --- BẢNG PHẢI: FORM --- */
        .form-panel {
            width: 55%; padding: 50px 70px;
            background: var(--bg-form); display: flex; flex-direction: column; justify-content: center;
        }

        .form-title { font-size: 32px; font-weight: 800; color: var(--text-dark); margin-bottom: 8px; }
        .form-desc { font-size: 15px; color: var(--text-muted); margin-bottom: 32px; line-height: 1.5; font-weight: 500; }

        .form-group { margin-bottom: 20px; position: relative; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; color: var(--text-dark); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        .input-wrapper { position: relative; }
        .input-wrapper .form-control { padding-right: 46px; }
        .eye-toggle {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: #94a3b8; transition: color 0.2s; background: none; border: none; padding: 0;
        }
        .eye-toggle:hover { color: var(--tech-blue); }

        .form-control {
            width: 100%; padding: 14px 16px;
            background: var(--bg-input); border: 1px solid var(--border-input);
            border-radius: 12px; font-size: 15px; color: var(--tech-dark); font-weight: 500;
            transition: all 0.3s ease;
        }
        .form-control::placeholder { color: #94a3b8; font-weight: 400; }
        .form-control:focus { outline: none; background: var(--bg-form); border-color: var(--tech-blue); box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }

        /* Ẩn con mắt mặc định của trình duyệt (Edge) để tránh 2 con mắt */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        /* Thanh sức mạnh mật khẩu */
        .strength-bar { display: flex; gap: 4px; margin-top: 8px; }
        .strength-seg { flex: 1; height: 4px; border-radius: 4px; background: #e2e8f0; transition: background 0.3s; }

        .btn-submit {
            width: 100%; padding: 15px;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            color: var(--text-light); border: none; border-radius: 12px;
            font-size: 16px; font-weight: 700; cursor: pointer;
            margin-top: 10px; transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(34,197,94,0.3);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(34,197,94,0.4); }

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
                        Bước cuối cùng
                    </div>
                    <h2 class="vp-title">Gần xong rồi!<br><span>Tạo mật khẩu mới</span></h2>
                    <p class="vp-desc">Hãy tạo một mật khẩu đủ mạnh để bảo vệ tài khoản của bạn. Khuyến nghị ít nhất 8 ký tự, bao gồm chữ hoa, số và ký tự đặc biệt.</p>
                </div>

                <div class="vp-steps">
                    <div class="vp-step">
                        <div class="vp-step-num done">✓</div>
                        <div class="vp-step-text">Đã xác nhận email</div>
                    </div>
                    <div class="vp-step">
                        <div class="vp-step-num done">✓</div>
                        <div class="vp-step-text">Đã xác minh OTP thành công</div>
                    </div>
                    <div class="vp-step">
                        <div class="vp-step-num active">3</div>
                        <div class="vp-step-text">Tạo mật khẩu mới và đăng nhập</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM PANEL (Phải) -->
        <div class="form-panel">
            <h2 class="form-title">Tạo mật khẩu mới</h2>
            <p class="form-desc">Vui lòng nhập mật khẩu mới để hoàn tất quá trình khôi phục tài khoản.</p>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('password.reset.post') }}">
                @csrf
                <div class="form-group">
                    <label for="password">Mật khẩu mới</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="Tối thiểu 8 ký tự" oninput="checkStrength(this.value)">
                        <button type="button" class="eye-toggle" onclick="togglePassword('password', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <div class="strength-bar">
                        <div class="strength-seg" id="seg1"></div>
                        <div class="strength-seg" id="seg2"></div>
                        <div class="strength-seg" id="seg3"></div>
                        <div class="strength-seg" id="seg4"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Xác nhận mật khẩu</label>
                    <div class="input-wrapper">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8" placeholder="Nhập lại mật khẩu">
                        <button type="button" class="eye-toggle" onclick="togglePassword('password_confirmation', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">✓ Đổi Mật Khẩu & Đăng Nhập</button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.innerHTML = isHidden
                ? `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>`
                : `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
        }

        function checkStrength(val) {
            const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            for (let i = 1; i <= 4; i++) {
                const seg = document.getElementById(`seg${i}`);
                seg.style.background = i <= score ? colors[score - 1] : '#e2e8f0';
            }
        }
    </script>
</body>
</html>
