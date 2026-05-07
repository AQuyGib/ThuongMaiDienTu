<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực hai bước - DienMayPro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
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
            width: 100%; max-width: 1100px; height: 640px;
            background: var(--bg-form); border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
            display: flex; overflow: hidden;
            animation: wrapperAppear 0.7s cubic-bezier(0.22,1,0.36,1) both;
        }
        @keyframes wrapperAppear { from { opacity:0; transform:scale(0.96) translateY(20px); } to { opacity:1; transform:scale(1) translateY(0); } }

        /* Visual Panel */
        .visual-panel {
            width: 45%; position: relative;
            background: linear-gradient(150deg, #1e3a8a 0%, #1a2a6c 45%, #96281b 100%);
            display: flex; flex-direction: column;
            justify-content: flex-end; align-items: stretch;
            color: var(--text-light); overflow: hidden;
        }
        canvas { position: absolute; top:0; left:0; width:100%; height:100%; z-index:3; pointer-events:none; }

        .bg-glow { position:absolute; border-radius:50%; pointer-events:none; filter:blur(60px); }
        .bg-glow-1 { width:260px; height:260px; top:-50px; left:-50px; background:rgba(59,130,246,0.5); animation:glowFloat1 9s ease-in-out infinite; }
        .bg-glow-2 { width:200px; height:200px; bottom:40px; right:-60px; background:rgba(239,68,68,0.4); animation:glowFloat2 11s ease-in-out infinite; }
        .bg-dots { position:absolute; top:0; left:0; width:100%; height:100%; background-image:radial-gradient(rgba(255,255,255,0.07) 1.5px, transparent 1.5px); background-size:24px 24px; pointer-events:none; }

        @keyframes glowFloat1 { 0%,100% { transform:translate(0,0); } 50% { transform:translate(15px,20px); } }
        @keyframes glowFloat2 { 0%,100% { transform:translate(0,0); } 50% { transform:translate(-20px,-15px); } }

        .welcome-content { position:relative; z-index:10; padding:40px 42px 36px; display:flex; flex-direction:column; height:100%; justify-content:space-between; }

        .vp-brand { font-size:20px; font-weight:800; display:flex; align-items:center; gap:8px; }
        .vp-brand-dot { width:8px; height:8px; border-radius:50%; background:#ef4444; display:inline-block; }
        .vp-brand-dot-2 { background:#3b82f6; }

        .vp-main { flex:1; display:flex; flex-direction:column; justify-content:center; padding:24px 0; }
        .vp-tag { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.2); border-radius:50px; padding:5px 14px; font-size:12px; font-weight:600; margin-bottom:18px; width:fit-content; letter-spacing:0.5px; text-transform:uppercase; }
        .vp-tag-dot { width:6px; height:6px; border-radius:50%; background:#fde68a; animation:pulse 1.5s infinite; }
        @keyframes pulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.5; transform:scale(1.4); } }

        .vp-title { font-family:'Space Grotesk','Outfit',sans-serif; font-size:36px; font-weight:800; line-height:1.15; margin-bottom:14px; animation:slideUp 0.8s ease 0.3s both; }
        .vp-title span { color:#93c5fd; }
        .vp-desc { font-size:14px; font-weight:400; color:rgba(255,255,255,0.75); line-height:1.7; }
        @keyframes slideUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }

        /* Security info cards */
        .security-cards { display:flex; flex-direction:column; gap:10px; }
        .sec-card { display:flex; align-items:center; gap:12px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:14px; padding:12px 16px; }
        .sec-icon { width:36px; height:36px; border-radius:10px; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:18px; }
        .sec-text-title { font-size:13px; font-weight:700; margin-bottom:2px; }
        .sec-text-desc { font-size:11px; color:rgba(255,255,255,0.65); }

        /* Form Panel */
        .form-panel { width:55%; padding:50px 65px; background:var(--bg-form); display:flex; flex-direction:column; justify-content:center; }

        .shield-icon { width:60px; height:60px; background:linear-gradient(135deg,#dbeafe,#bfdbfe); border-radius:18px; display:flex; align-items:center; justify-content:center; margin-bottom:20px; animation:fadeSlideDown 0.6s ease both; }
        @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-16px)} to{opacity:1;transform:translateY(0)} }

        .form-title { font-family:'Space Grotesk','Outfit',sans-serif; font-size:28px; font-weight:800; color:var(--text-dark); margin-bottom:6px; }
        .form-desc { font-size:14px; color:var(--text-muted); margin-bottom:28px; line-height:1.6; }
        .form-desc strong { color:var(--text-dark); }

        /* OTP 6 ô riêng biệt */
        .otp-row { display:flex; gap:10px; margin-bottom:24px; }
        .otp-input {
            flex:1; height:60px; text-align:center; font-size:24px; font-weight:800;
            background:var(--bg-input); border:2px solid var(--border-input); border-radius:14px;
            color:var(--tech-dark); transition:all 0.2s; outline:none; caret-color:var(--tech-blue);
        }
        .otp-input:focus { border-color:var(--tech-blue); background:#fff; box-shadow:0 0 0 4px rgba(59,130,246,0.1); transform:scale(1.05); }
        .otp-input.filled { border-color:#22c55e; background:#f0fdf4; }

        /* Hidden real input */
        #otp-hidden { position:absolute; opacity:0; pointer-events:none; }

        .btn-verify {
            width:100%; padding:15px;
            background:linear-gradient(90deg,var(--tech-blue),#2563eb);
            color:#fff; border:none; border-radius:12px;
            font-size:16px; font-weight:700; cursor:pointer;
            transition:all 0.3s; box-shadow:0 8px 20px rgba(59,130,246,0.3);
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .btn-verify:hover { transform:translateY(-2px); box-shadow:0 12px 25px rgba(59,130,246,0.4); }
        .btn-verify:disabled { opacity:0.6; cursor:not-allowed; transform:none; }

        .resend-row { display:flex; align-items:center; justify-content:space-between; margin-top:18px; font-size:13px; }
        .resend-btn { background:none; border:none; color:var(--tech-blue); font-weight:700; cursor:pointer; font-size:13px; transition:opacity 0.2s; font-family:inherit; padding:0; }
        .resend-btn:hover { opacity:0.7; }
        .resend-btn:disabled { color:var(--text-muted); cursor:not-allowed; }

        .timer { color:var(--text-muted); font-weight:600; }
        .timer.urgent { color:#ef4444; }

        .alert { padding:12px; border-radius:12px; margin-bottom:18px; font-size:14px; text-align:center; font-weight:600; }
        .alert-danger { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
        .alert-success { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
    </style>
</head>
<body>
<div class="main-wrapper">

    <!-- VISUAL PANEL -->
    <div class="visual-panel">
        <canvas id="particle-canvas"></canvas>
        <div class="bg-glow bg-glow-1"></div>
        <div class="bg-glow bg-glow-2"></div>
        <div class="bg-dots"></div>

        <div class="welcome-content">
            <div class="vp-brand">
                <span class="vp-brand-dot"></span>
                <span class="vp-brand-dot vp-brand-dot-2"></span>
                DienMayPro
            </div>

            <div class="vp-main">
                <div class="vp-tag"><span class="vp-tag-dot"></span>Bảo mật nâng cao</div>
                <h2 class="vp-title">Xác thực<br><span>hai bước (2FA)</span></h2>
                <p class="vp-desc">Một lớp bảo vệ bổ sung giúp ngăn chặn truy cập trái phép ngay cả khi mật khẩu bị lộ.</p>
            </div>

            <div class="security-cards">
                <div class="sec-card">
                    <div class="sec-icon">🔑</div>
                    <div>
                        <div class="sec-text-title">OTP qua Email</div>
                        <div class="sec-text-desc">Mã 6 số gửi đến hộp thư, hiệu lực 5 phút</div>
                    </div>
                </div>
                <div class="sec-card">
                    <div class="sec-icon">🛡️</div>
                    <div>
                        <div class="sec-text-title">Chống đánh cắp tài khoản</div>
                        <div class="sec-text-desc">Mỗi lần đăng nhập yêu cầu xác minh riêng biệt</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM PANEL -->
    <div class="form-panel">
        <div class="shield-icon">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <polyline points="9 12 11 14 15 10"/>
            </svg>
        </div>

        <h2 class="form-title">Nhập mã xác thực</h2>
        <p class="form-desc">
            Mã OTP gồm <strong>6 chữ số</strong> đã được gửi đến<br>
            <strong>{{ $user->email ?? '' }}</strong>
        </p>

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('2fa.verify') }}" id="otp-form">
            @csrf
            <input type="hidden" name="otp" id="otp-hidden">

            <!-- 6 ô OTP riêng biệt -->
            <div class="otp-row">
                @for($i = 0; $i < 6; $i++)
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                @endfor
            </div>

            <button type="submit" class="btn-verify" id="btn-verify" disabled>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Xác minh & Đăng nhập
            </button>
        </form>

        <div class="resend-row">
            <span style="color:var(--text-muted)">Không nhận được mã?</span>
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="timer" id="timer">Gửi lại sau <strong id="countdown">60</strong>s</span>
                <form method="POST" action="{{ route('2fa.send') }}" style="display:inline;">
                    @csrf
                    <button class="resend-btn" id="resend-btn" disabled>Gửi lại</button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
/* === OTP 6 ô tự động === */
const boxes = document.querySelectorAll('.otp-input');
const hidden = document.getElementById('otp-hidden');
const submitBtn = document.getElementById('btn-verify');

boxes.forEach((box, i) => {
    box.addEventListener('input', () => {
        box.value = box.value.replace(/\D/g, '');
        if (box.value) {
            box.classList.add('filled');
            if (i < 5) boxes[i + 1].focus();
        } else {
            box.classList.remove('filled');
        }
        syncHidden();
    });

    box.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !box.value && i > 0) {
            boxes[i - 1].focus();
            boxes[i - 1].value = '';
            boxes[i - 1].classList.remove('filled');
            syncHidden();
        }
    });

    // Paste hỗ trợ
    box.addEventListener('paste', e => {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
        paste.split('').forEach((ch, idx) => {
            if (boxes[idx]) { boxes[idx].value = ch; boxes[idx].classList.add('filled'); }
        });
        syncHidden();
        if (boxes[5]) boxes[5].focus();
    });
});

function syncHidden() {
    const val = Array.from(boxes).map(b => b.value).join('');
    hidden.value = val;
    submitBtn.disabled = val.length < 6;
}

/* === Đếm ngược gửi lại === */
let seconds = 60;
const countdownEl = document.getElementById('countdown');
const timerEl = document.getElementById('timer');
const resendBtn = document.getElementById('resend-btn');

const timer = setInterval(() => {
    seconds--;
    countdownEl.textContent = seconds;
    if (seconds <= 10) timerEl.classList.add('urgent');
    if (seconds <= 0) {
        clearInterval(timer);
        timerEl.style.display = 'none';
        resendBtn.disabled = false;
    }
}, 1000);

/* === Canvas Particles === */
const canvas = document.getElementById('particle-canvas');
const ctx = canvas.getContext('2d');
function resizeCanvas() { canvas.width = canvas.parentElement.offsetWidth; canvas.height = canvas.parentElement.offsetHeight; }
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

class Particle {
    constructor() { this.reset(); }
    reset() {
        this.x = Math.random() * canvas.width; this.y = Math.random() * canvas.height;
        this.r = Math.random() * 1.6 + 0.4;
        this.vx = (Math.random() - 0.5) * 0.4; this.vy = (Math.random() - 0.5) * 0.4 - 0.15;
        this.alpha = Math.random() * 0.4 + 0.1;
        this.color = Math.random() > 0.5 ? '100,160,255' : '255,220,100';
    }
    update() { this.x += this.vx; this.y += this.vy; if (this.y < -5 || this.x < -5 || this.x > canvas.width + 5) this.reset(); }
    draw() { ctx.beginPath(); ctx.arc(this.x, this.y, this.r, 0, Math.PI*2); ctx.fillStyle=`rgba(${this.color},${this.alpha})`; ctx.fill(); }
}
const particles = Array.from({length: 70}, () => new Particle());
function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach(p => { p.update(); p.draw(); });
    requestAnimationFrame(animate);
}
animate();
</script>
</body>
</html>
