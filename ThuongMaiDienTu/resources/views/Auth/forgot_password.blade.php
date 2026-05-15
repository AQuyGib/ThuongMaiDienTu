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
            --bg-form: rgba(255, 255, 255, 0.95); --bg-input: #f8fafc; --border-input: #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        body {
            background-color: #0f172a;
            background-image: url('{{ asset('assets/img/background_login_register.avif') }}');
            background-size: cover; background-position: center; background-attachment: fixed;
            display: flex; justify-content: center; align-items: center; min-height: 100vh; overflow-x: hidden;
            position: relative; padding: 20px;
        }

        #particle-canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none; }

        .main-wrapper {
            width: 100%; max-width: 1050px; min-height: 620px;
            background: var(--bg-form); border-radius: 30px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.5);
            display: flex; overflow: hidden; position: relative; z-index: 10;
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* --- VISUAL PANEL --- */
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
        .vp-step.active { background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.3); transform: translateX(10px); }
        .vp-step-num {
            width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800; color: #94a3b8;
        }
        .vp-step.active .vp-step-num { background: #3b82f6; color: white; box-shadow: 0 0 15px rgba(59,130,246,0.5); }
        .vp-step-text { font-size: 12px; font-weight: 600; color: #94a3b8; }
        .vp-step.active .vp-step-text { color: white; }

        /* --- FORM PANEL --- */
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
        
        .input-icon-wrapper { position: relative; }
        .input-icon-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 16px; }
        .form-control {
            width: 100%; padding: 14px 16px 14px 44px;
            background: var(--bg-input); border: 1.5px solid var(--border-input);
            border-radius: 12px; font-size: 14px; color: var(--tech-dark); font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .form-control:focus { outline: none; background: white; border-color: var(--tech-blue); box-shadow: 0 0 0 5px rgba(59,130,246,0.1); }
        .form-control:read-only { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; }

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

        /* --- NÂNG CAO OTP --- */
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

        .countdown { color: #f97316; font-weight: 700; margin-left: 5px; }

        .eye-toggle {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: #94a3b8; background: none; border: none; padding: 0;
            display: flex; align-items: center; justify-content: center;
        }

        .strength-bar { display: flex; gap: 4px; margin-top: 8px; }
        .strength-seg { flex: 1; height: 4px; border-radius: 4px; background: #e2e8f0; transition: background 0.3s; }

        .hidden { display: none !important; }
        .fade-in { animation: fadeIn 0.5s ease forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .loading-spinner {
            width: 18px; height: 18px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%;
            border-top-color: #fff; animation: spin 1s linear infinite; display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Hide browser's default password reveal button (Edge/IE) */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        @media (max-width: 900px) {
            .main-wrapper { flex-direction: column; max-width: 500px; min-height: auto; }
            .visual-panel { display: none; }
            .form-panel { width: 100%; padding: 40px 25px; }
            .otp-input { width: 40px; height: 50px; font-size: 20px; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <canvas id="particle-canvas"></canvas>

    <div class="main-wrapper">
        <!-- VISUAL PANEL -->
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
                    <div id="step-node-1" class="vp-step active">
                        <div class="vp-step-num">1</div>
                        <div class="vp-step-text">Gửi yêu cầu qua Email</div>
                    </div>
                    <div id="step-node-2" class="vp-step">
                        <div class="vp-step-num">2</div>
                        <div class="vp-step-text">Xác minh mã OTP</div>
                    </div>
                    <div id="step-node-3" class="vp-step">
                        <div class="vp-step-num">3</div>
                        <div class="vp-step-text">Thay đổi mật khẩu</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM PANEL -->
        <div class="form-panel">
            <a href="{{ route('login_register') }}" class="form-back"><i class="fas fa-arrow-left"></i> Quay lại đăng nhập</a>

            <div id="alert-container"></div>

            <div class="form-header">
                <h2 class="form-title" id="page-title">Khôi phục quyền truy cập</h2>
                <p class="form-desc" id="page-desc">Nhập email đăng ký của bạn. Chúng tôi sẽ gửi một mã OTP có hiệu lực trong <strong>5 phút</strong>.</p>
            </div>

            <form id="unified-form" method="POST">
                @csrf
                
                <!-- STEP 1: EMAIL -->
                <div class="form-group" id="group-step-1">
                    <label for="email">Địa chỉ Email</label>
                    <div class="input-icon-wrapper">
                        <i class="far fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" required placeholder="example@gmail.com">
                    </div>
                </div>

                <!-- STEP 2: OTP -->
                <div id="group-step-2" class="hidden">
                    <div class="form-group" style="text-align: center;">
                        <label style="display: inline-block; margin-bottom: 15px;">MÃ XÁC MINH OTP</label>
                        <div class="otp-inputs">
                            <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        </div>
                        <input type="hidden" name="otp" id="full-otp">
                    </div>
                </div>

                <!-- STEP 3: PASSWORD -->
                <div id="group-step-3" class="hidden">
                    <div class="form-group">
                        <label for="password">Mật khẩu mới</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-control" minlength="8" placeholder="Tối thiểu 8 ký tự" oninput="checkStrength(this.value)">
                            <button type="button" class="eye-toggle" onclick="togglePassword('password', this)"><i class="fas fa-eye"></i></button>
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
                        <div class="input-icon-wrapper">
                            <i class="fas fa-check-double"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" placeholder="Nhập lại mật khẩu">
                            <button type="button" class="eye-toggle" onclick="togglePassword('password_confirmation', this)"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                </div>

                <button type="submit" id="btn-main" class="btn-submit">
                    <span id="btn-text">Gửi mã OTP xác nhận</span>
                    <i class="fas fa-paper-plane" id="btn-icon"></i>
                    <div class="loading-spinner" id="btn-spinner"></div>
                </button>
            </form>

            <div id="resend-wrapper" class="resend-container hidden">
                <p class="resend-text">Không nhận được mã? 
                    <button type="button" class="btn-resend" id="resend-trigger">Gửi lại yêu cầu <span id="resend-timer" class="countdown"></span></button>
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

        /* Logic */
        const form = document.getElementById('unified-form');
        const emailInput = document.getElementById('email');
        const btnMain = document.getElementById('btn-main');
        const btnText = document.getElementById('btn-text');
        const btnIcon = document.getElementById('btn-icon');
        const btnSpinner = document.getElementById('btn-spinner');
        const alertContainer = document.getElementById('alert-container');
        const pageTitle = document.getElementById('page-title');
        const pageDesc = document.getElementById('page-desc');
        const otpInputs = document.querySelectorAll('.otp-input');
        const fullOtpInput = document.getElementById('full-otp');
        const resendBtn = document.getElementById('resend-trigger');
        const resendTimer = document.getElementById('resend-timer');

        let currentStep = 1;
        let countdown = 0;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (currentStep === 1) await step1SendEmail();
            else if (currentStep === 2) await step2VerifyOtp();
            else if (currentStep === 3) await step3ResetPassword();
        });

        async function step1SendEmail() {
            setLoading(true);
            try {
                const formData = new FormData();
                formData.append('email', emailInput.value);
                formData.append('_token', '{{ csrf_token() }}');

                const res = await fetch("{{ route('password.email') }}", { 
                    method: 'POST', 
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, 
                    body: formData 
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    showAlert(data.message, 'success');
                    toStep2();
                    startResendTimer(60);
                } else { 
                    showAlert(data.errors ? Object.values(data.errors)[0][0] : data.message, 'danger'); 
                }
            } catch (e) { showAlert('Lỗi kết nối máy chủ.', 'danger'); } finally { setLoading(false); }
        }

        async function step2VerifyOtp() {
            if (fullOtpInput.value.length < 6) { showAlert('Vui lòng nhập đủ 6 chữ số mã OTP.', 'danger'); return; }
            setLoading(true);
            try {
                const formData = new FormData();
                formData.append('email', emailInput.value);
                formData.append('otp', fullOtpInput.value);
                formData.append('_token', '{{ csrf_token() }}');

                const res = await fetch("{{ route('password.verify.post') }}", { 
                    method: 'POST', 
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, 
                    body: formData 
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    showAlert(data.message, 'success');
                    toStep3();
                } else { 
                    showAlert(data.message || 'OTP không hợp lệ.', 'danger'); 
                    otpInputs.forEach(i => { i.value = ''; i.classList.remove('filled'); });
                    otpInputs[0].focus();
                }
            } catch (e) { showAlert('Lỗi xác minh máy chủ.', 'danger'); } finally { setLoading(false); }
        }

        async function step3ResetPassword() {
            setLoading(true);
            try {
                const formData = new FormData();
                formData.append('email', emailInput.value);
                formData.append('password', document.getElementById('password').value);
                formData.append('password_confirmation', document.getElementById('password_confirmation').value);
                formData.append('_token', '{{ csrf_token() }}');

                const res = await fetch("{{ route('password.reset.post') }}", { 
                    method: 'POST', 
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, 
                    body: formData 
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    showAlert(data.message + ' Đang chuyển hướng...', 'success');
                    setTimeout(() => window.location.href = data.redirect, 1500);
                } else { 
                    showAlert(data.errors ? Object.values(data.errors)[0][0] : data.message, 'danger'); 
                }
            } catch (e) { showAlert('Lỗi cập nhật máy chủ.', 'danger'); } finally { setLoading(false); }
        }

        function toStep2() {
            currentStep = 2;
            emailInput.readOnly = true;
            document.getElementById('group-step-1').classList.add('hidden');
            document.getElementById('group-step-2').classList.remove('hidden');
            document.getElementById('group-step-2').classList.add('fade-in');
            document.getElementById('resend-wrapper').classList.remove('hidden');
            pageTitle.innerText = "Xác minh mã OTP";
            pageDesc.innerHTML = `Mã xác minh đã được gửi tới <strong>${emailInput.value}</strong>.`;
            btnText.innerText = "Xác nhận mã OTP";
            btnIcon.className = "fas fa-shield-check";
            setActiveNode(2);
            setTimeout(() => otpInputs[0].focus(), 500);
        }

        function toStep3() {
            currentStep = 3;
            document.getElementById('group-step-2').classList.add('hidden');
            document.getElementById('resend-wrapper').classList.add('hidden');
            document.getElementById('group-step-3').classList.remove('hidden');
            document.getElementById('group-step-3').classList.add('fade-in');
            pageTitle.innerText = "Thiết lập mật khẩu mới";
            pageDesc.innerText = "Vui lòng tạo một mật khẩu mới an toàn cho tài khoản của bạn.";
            btnText.innerText = "Đổi mật khẩu & Hoàn tất";
            btnIcon.className = "fas fa-check-circle";
            setActiveNode(3);
        }

        function setActiveNode(step) {
            document.querySelectorAll('.vp-step').forEach(node => node.classList.remove('active'));
            document.getElementById(`step-node-${step}`).classList.add('active');
        }

        function setLoading(isLoading) {
            btnMain.disabled = isLoading;
            btnSpinner.style.display = isLoading ? 'block' : 'none';
            btnIcon.style.display = isLoading ? 'none' : 'block';
            btnText.style.opacity = isLoading ? '0.5' : '1';
        }

        function showAlert(msg, type) { alertContainer.innerHTML = `<div class="alert alert-${type}"><i class="fas fa-${type==='success'?'circle-check':'circle-exclamation'}"></i><span>${msg}</span></div>`; }

        function startResendTimer(seconds) {
            countdown = seconds;
            resendBtn.disabled = true;
            resendTimer.innerText = `(${countdown}s)`;
            const timer = setInterval(() => {
                countdown--;
                resendTimer.innerText = `(${countdown}s)`;
                if (countdown <= 0) {
                    clearInterval(timer);
                    resendBtn.disabled = false;
                    resendTimer.innerText = '';
                }
            }, 1000);
        }

        /* OTP Segment Logic - Fixed Repetition Bug */
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                
                // If more than 1 char, keep only the last one
                if (value.length > 1) {
                    e.target.value = value.slice(-1);
                }

                if (e.target.value) {
                    input.classList.add('filled');
                    // Focus next input
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                } else {
                    input.classList.remove('filled');
                }
                
                updateFullOtp();
                
                // Auto-submit if all filled
                if (Array.from(otpInputs).every(i => i.value.length === 1)) {
                    step2VerifyOtp();
                }
            });

            input.addEventListener('keydown', (e) => {
                // On backspace, if empty, focus previous
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
                // Prevent entering non-numeric if needed (already has pattern, but extra safety)
                if (e.key !== 'Backspace' && e.key !== 'Tab' && !/^\d$/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                    // e.preventDefault(); // Let input event handle it for better compatibility
                }
            });

            // Focus management: clear on focus to avoid confusion if user wants to re-type
            input.addEventListener('focus', () => {
                // Optional: input.select(); // Or just let it be
            });
        });

        // Refined Paste Logic
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
                    step2VerifyOtp();
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

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.querySelector('i').className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
        }

        function checkStrength(val) {
            const colors = ['#ef4444','#f97316','#eab308','#22c55e']; let score = 0;
            if (val.length >= 8) score++; if (/[A-Z]/.test(val)) score++; if (/[0-9]/.test(val)) score++; if (/[^A-Za-z0-9]/.test(val)) score++;
            for (let i = 1; i <= 4; i++) { const seg = document.getElementById(`seg${i}`); if (seg) seg.style.background = i <= score ? colors[score - 1] : '#e2e8f0'; }
        }

        resendBtn.addEventListener('click', step1SendEmail);
    </script>
</body>
</html>
