<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác minh OTP - DienMayPro Security</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --tech-red: #ef4444; --tech-blue: #3b82f6; --tech-dark: #0f172a;
            --primary: #3b82f6; --primary-hover: #2563eb;
            --text-dark: #0f172a; --text-muted: #64748b; --text-light: #ffffff;
            --bg-form: rgba(255, 255, 255, 0.95); --bg-input: #f8fafc; --border-input: #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        body {
            background-color: #0f172a;
            background-image: url('{{ asset('assets/img/background_login_register.avif') }}');
            background-size: cover; background-position: center; background-attachment: fixed;
            display: flex; justify-content: center; align-items: center; min-height: 100vh; overflow-x: hidden;
            position: relative; padding: 20px;
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        }

        #particle-canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none; }

        .main-wrapper {
            width: 100%; max-width: 1050px; min-height: 620px;
            background: var(--bg-form); border-radius: 30px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.5);
            display: flex; overflow: hidden; position: relative; z-index: 10;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* --- VISUAL PANEL (Bảng trái) --- */
        .visual-panel {
            width: 42%; position: relative;
            background: linear-gradient(160deg, #1e293b 0%, #0f172a 100%);
            display: flex; flex-direction: column;
            justify-content: space-between; align-items: stretch;
            padding: 45px; color: var(--text-light); overflow: hidden;
            border-right: 1px solid rgba(255,255,255,0.05);
        }

        .bg-glow { position: absolute; border-radius: 50%; pointer-events: none; filter: blur(80px); opacity: 0.5; }
        .bg-glow-1 { width: 300px; height: 300px; top: -100px; right: -100px; background: #3b82f6; }
        .bg-glow-2 { width: 250px; height: 250px; bottom: -50px; left: -50px; background: #ef4444; }

        .welcome-content { position: relative; z-index: 10; height: 100%; display: flex; flex-direction: column; }
        .vp-brand { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px; margin-bottom: 50px; }
        .vp-brand i { color: #3b82f6; font-size: 24px; }

        .vp-main { flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .vp-tag {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 50px; padding: 6px 16px; font-size: 11px; font-weight: 700;
            margin-bottom: 24px; width: fit-content; color: #60a5fa; text-transform: uppercase; letter-spacing: 1px;
        }
        .vp-tag-dot { width: 6px; height: 6px; border-radius: 50%; background: #60a5fa; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.5); opacity: 0.5; } }

        .vp-title { font-size: 38px; font-weight: 800; line-height: 1.15; margin-bottom: 18px; letter-spacing: -1px; }
        .vp-title span { background: linear-gradient(to right, #60a5fa, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .vp-desc { font-size: 15px; font-weight: 400; color: #94a3b8; line-height: 1.6; max-width: 90%; }

        .vp-steps { display: flex; flex-direction: column; gap: 12px; margin-top: 40px; }
        .vp-step {
            display: flex; align-items: center; gap: 16px;
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px; padding: 12px 18px; transition: all 0.3s ease;
        }
        .vp-step-num {
            width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800; color: #94a3b8;
        }
        .vp-step.active { background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.3); transform: translateX(10px); }
        .vp-step.active .vp-step-num { background: #3b82f6; color: white; box-shadow: 0 0 15px rgba(59,130,246,0.5); }
        .vp-step-text { font-size: 12px; font-weight: 600; color: #94a3b8; }
        .vp-step.active .vp-step-text { color: white; }

        /* --- FORM PANEL (Bảng phải) --- */
        .form-panel {
            width: 58%; padding: 50px 70px;
            background: var(--bg-form); display: flex; flex-direction: column; justify-content: center;
            position: relative;
        }

        .form-back {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 600; color: var(--text-muted);
            text-decoration: none; margin-bottom: 30px; transition: all 0.3s;
            width: fit-content;
        }
        .form-back:hover { color: var(--tech-blue); transform: translateX(-5px); }

        .form-header { margin-bottom: 25px; }
        .form-title { font-size: 32px; font-weight: 800; color: var(--text-dark); margin-bottom: 10px; letter-spacing: -0.5px; }
        .form-desc { font-size: 14px; color: var(--text-muted); line-height: 1.6; font-weight: 500; }
        .form-desc strong { color: var(--tech-blue); }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 11px; color: var(--text-dark); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }

        .btn-submit {
            width: 100%; padding: 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: var(--text-light); border: none; border-radius: 12px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            margin-top: 10px; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px rgba(37,99,235,0.3);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(37,99,235,0.4); }
        .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; transform: none; box-shadow: none; opacity: 0.7; }

        .alert { padding: 14px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px; animation: slideIn 0.4s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
        .alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }

        /* --- OTP INPUTS --- */
        .otp-inputs { display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; }
        .otp-input {
            width: 50px; height: 60px; border: 2px solid var(--border-input);
            border-radius: 12px; text-align: center; font-size: 24px; font-weight: 800;
            background: var(--bg-input); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--tech-blue);
        }
        .otp-input:focus { border-color: var(--tech-blue); background: white; transform: translateY(-4px); box-shadow: 0 8px 20px rgba(59,130,246,0.15); }
        .otp-input.filled { border-color: #10b981; color: #10b981; }

        .resend-container { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #edf2f7; }
        .resend-text { font-size: 13px; color: var(--text-muted); font-weight: 500; }
        .btn-resend { background: none; border: none; color: var(--tech-blue); font-weight: 700; cursor: pointer; padding: 0; font-size: 13px; text-decoration: none; }
        .btn-resend:hover { text-decoration: underline; }
        .btn-resend:disabled { color: #94a3b8; cursor: not-allowed; text-decoration: none; }

        .loading-spinner {
            width: 18px; height: 18px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%;
            border-top-color: #fff; animation: spin 1s linear infinite; display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 900px) {
            .main-wrapper { flex-direction: column; max-width: 500px; min-height: auto; }
            .visual-panel { display: none; }
            .form-panel { width: 100%; padding: 40px 25px; }
            .otp-input { width: 40px; height: 50px; font-size: 20px; }
        }
    </style>
</head>
<body>
    <canvas id="particle-canvas"></canvas>

    <div class="main-wrapper">
        <!-- BẢNG TRÁI: THƯƠNG HIỆU -->
        <div class="visual-panel">
            <div class="bg-glow bg-glow-1"></div>
            <div class="bg-glow bg-glow-2"></div>
            <div class="welcome-content">
                <div class="vp-brand"><i class="fas fa-shield-halved"></i> DienMayPro</div>
                <div class="vp-main">
                    <div class="vp-tag"><span class="vp-tag-dot"></span> Security Center</div>
                    <h2 class="vp-title">Quên mật khẩu?<br><span>Chúng tôi sẽ giúp bạn.</span></h2>
                    <p class="vp-desc">Quy trình khôi phục tài khoản an toàn và bảo mật. Vui lòng nhập đúng thông tin để tiếp tục.</p>
                </div>
                <div class="vp-steps">
                    <div class="vp-step">
                        <div class="vp-step-num" style="background: #22c55e; color: white;">✓</div>
                        <div class="vp-step-text">Gửi yêu cầu qua Email</div>
                    </div>
                    <div class="vp-step active">
                        <div class="vp-step-num">2</div>
                        <div class="vp-step-text">Xác minh mã OTP</div>
                    </div>
                    <div class="vp-step">
                        <div class="vp-step-num">3</div>
                        <div class="vp-step-text">Thay đổi mật khẩu</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BẢNG PHẢI: FORM NHẬP -->
        <div class="form-panel">
            <a href="{{ route('password.request') }}" class="form-back"><i class="fas fa-arrow-left"></i> Quay lại</a>

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="form-header">
                <h2 class="form-title">Xác minh mã OTP</h2>
                <p class="form-desc">Mã xác minh OTP đã được gửi tới <strong>{{ $email }}</strong>. Vui lòng kiểm tra email và nhập mã gồm 6 chữ số dưới đây.</p>
            </div>

            <form method="POST" action="{{ route('password.verify.post') }}" id="otp-form">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                
                <div class="form-group" style="text-align: center;">
                    <label style="display: inline-block; margin-bottom: 15px;">MÃ XÁC MINH OTP</label>
                    <div class="otp-inputs">
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                    </div>
                    <input type="hidden" name="otp" id="full-otp">
                </div>

                <button type="submit" id="btn-main" class="btn-submit">
                    <span>Xác nhận mã OTP</span>
                    <i class="fas fa-shield-halved"></i>
                    <div class="loading-spinner" id="btn-spinner"></div>
                </button>
            </form>

            <div class="resend-container">
                <p class="resend-text">Không nhận được mã? 
                    <form action="{{ route('password.email') }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <button type="submit" class="btn-resend" id="resend-trigger" style="background:none; border:none; color:var(--tech-blue); font-weight:700; cursor:pointer;">Gửi lại yêu cầu</button>
                    </form>
                </p>
            </div>
        </div>
    </div>

    <script>
        /* Particles */
        const canvas = document.getElementById('particle-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        window.addEventListener('resize', resize); resize();
        class Particle {
            constructor() { this.reset(); }
            reset() { this.x = Math.random() * canvas.width; this.y = Math.random() * canvas.height; this.vx = (Math.random() - 0.5) * 0.5; this.vy = (Math.random() - 0.5) * 0.5; this.size = Math.random() * 2 + 0.5; }
            update() { this.x += this.vx; this.y += this.vy; if (this.x < 0 || this.x > canvas.width) this.vx *= -1; if (this.y < 0 || this.y > canvas.height) this.vy *= -1; }
            draw() { ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2); ctx.fillStyle = 'rgba(255, 255, 255, 0.3)'; ctx.fill(); }
        }
        for (let i = 0; i < 50; i++) particles.push(new Particle());
        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height); particles.forEach(p => { p.update(); p.draw(); });
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)'; ctx.lineWidth = 0.5;
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x; const dy = particles[i].y - particles[j].y;
                    if (Math.sqrt(dx*dx + dy*dy) < 100) { ctx.beginPath(); ctx.moveTo(particles[i].x, particles[i].y); ctx.lineTo(particles[j].x, particles[j].y); ctx.stroke(); }
                }
            }
            requestAnimationFrame(animate);
        }
        animate();

        /* OTP Segment Logic */
        const otpInputs = document.querySelectorAll('.otp-input');
        const fullOtpInput = document.getElementById('full-otp');
        const form = document.getElementById('otp-form');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value.length > 1) {
                    e.target.value = value.slice(-1);
                }
                if (e.target.value) {
                    input.classList.add('filled');
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                } else {
                    input.classList.remove('filled');
                }
                updateFullOtp();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });

        // Paste logic
        otpInputs[0].parentElement.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6).split('');
            if (pasteData.length > 0) {
                pasteData.forEach((char, i) => {
                    if (otpInputs[i]) {
                        otpInputs[i].value = char;
                        otpInputs[i].classList.add('filled');
                    }
                });
                updateFullOtp();
                if (Array.from(otpInputs).every(i => i.value)) {
                    form.submit();
                } else {
                    const nextFocus = Math.min(pasteData.length, 5);
                    otpInputs[nextFocus].focus();
                }
            }
        });

        function updateFullOtp() {
            let otp = '';
            otpInputs.forEach(input => otp += input.value);
            fullOtpInput.value = otp;
        }

        form.addEventListener('submit', (e) => {
            updateFullOtp();
            if (fullOtpInput.value.length < 6) {
                e.preventDefault();
                alert('Vui lòng nhập đủ 6 chữ số mã OTP.');
            } else {
                document.getElementById('btn-spinner').style.display = 'block';
                document.getElementById('btn-main').disabled = true;
            }
        });
    </script>
</body>
</html>
