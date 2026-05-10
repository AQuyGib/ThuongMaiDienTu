<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung tâm Bảo mật - DienMayPro</title>
    
    <!-- Optimized Vietnamese Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@100;300;400;500;600;700;800;900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --brand-primary: #6366f1;
            --brand-secondary: #a855f7;
            --brand-dark: #0f172a;
            --brand-success: #10b981;
            --brand-danger: #ef4444;
            --brand-warning: #f59e0b;
            --glass-bg: rgba(255, 255, 255, 0.78);
            --glass-border: rgba(255, 255, 255, 0.5);
            --font-main: 'Be Vietnam Pro', 'Inter', sans-serif;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-font-smoothing: antialiased; }

        body {
            font-family: var(--font-main);
            background: radial-gradient(circle at top left, #f8fafc 0%, #e2e8f0 100%);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            line-height: 1.6;
        }

        .blob {
            position: fixed;
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            filter: blur(140px);
            opacity: 0.12;
            border-radius: 50%;
            z-index: -1;
            animation: move 25s infinite alternate;
        }
        .blob-1 { top: -150px; left: -150px; }
        .blob-2 { bottom: -150px; right: -150px; background: linear-gradient(135deg, #ec4899, #8b5cf6); animation-delay: -7s; }

        @keyframes move {
            from { transform: translate(0, 0) rotate(0deg); }
            to { transform: translate(120px, 80px) rotate(15deg); }
        }

        /* ── Navbar ── */
        .navbar {
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            background: rgba(255, 255, 255, 0.85);
            border-bottom: 1px solid var(--glass-border);
            padding: 0 4rem;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }

        .logo { font-weight: 900; color: var(--brand-dark); font-size: 26px; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .logo i { color: #f59e0b; }
        .logo span { color: var(--brand-primary); }

        /* ── Layout ── */
        .container { max-width: 1200px; margin: 50px auto; padding: 0 24px; display: grid; grid-template-columns: 1fr 340px; gap: 40px; }

        @keyframes reveal {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .reveal { animation: reveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        /* ── Glass Cards ── */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            padding: 35px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 20px; font-weight: 800; margin-bottom: 25px;
            display: flex; align-items: center; gap: 12px; color: var(--brand-dark);
        }

        /* ── Reason Badges ── */
        .reason-box {
            background: white; border-radius: 20px; padding: 25px;
            border: 1px solid var(--glass-border); margin-top: 20px;
        }
        .reason-header { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; font-weight: 800; color: var(--brand-primary); }
        .reason-text { font-size: 14px; color: var(--text-muted); line-height: 1.7; }

        /* ── 2FA Toggle ── */
        .mfa-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .mfa-brand { display: flex; align-items: center; gap: 20px; }
        .mfa-icon-box {
            width: 60px; height: 60px; background: white; border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: var(--brand-primary); border: 2px solid var(--brand-primary);
        }

        .custom-toggle { position: relative; width: 84px; height: 44px; cursor: pointer; }
        .custom-toggle input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; inset: 0; background: #e2e8f0; border-radius: 100px;
            transition: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .slider:before {
            content: ''; position: absolute; height: 34px; width: 34px; left: 5px; bottom: 5px;
            background: white; border-radius: 50%; transition: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        input:checked + .slider { background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); }
        input:checked + .slider:before { transform: translateX(40px); }

        /* ── Score Breakdown ── */
        .score-list { display: flex; flex-direction: column; gap: 15px; }
        .score-item { padding: 20px; background: rgba(255,255,255,0.4); border-radius: 20px; border: 1px solid var(--glass-border); }
        .score-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .score-label { font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .score-val { font-size: 11px; font-weight: 800; text-transform: uppercase; padding: 4px 10px; border-radius: 6px; }
        .val-pass { background: #d1fae5; color: #065f46; }
        .val-fail { background: #fee2e2; color: #991b1b; }
        .val-warning { background: #fff7ed; color: #9a3412; }
        .score-desc { font-size: 13px; color: var(--text-muted); font-weight: 500; }

        /* ── Sidebar ── */
        .score-box { background: var(--brand-dark); color: white; padding: 30px; border-radius: 32px; text-align: center; }
        .score-display { width: 100px; height: 100px; border-radius: 50%; border: 6px solid {{ $tierColor }}; display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .score-num { font-size: 32px; font-weight: 900; }
        .score-max { font-size: 12px; opacity: 0.5; }

        /* ── Toast ── */
        #toast { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px); background: var(--brand-dark); color: white; padding: 15px 30px; border-radius: 100px; display: flex; align-items: center; gap: 12px; font-weight: 700; opacity: 0; transition: all 0.4s; z-index: 1000; }
        #toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }

        @media (max-width: 1000px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <nav class="navbar">
        <a href="{{ route('home') }}" class="logo"><i class="fa-solid fa-bolt"></i> DIENMAY<span>PRO</span></a>
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="{{ route('home') }}" style="text-decoration: none; color: var(--text-muted); font-weight: 700; font-size: 14px;">Về trang chủ</a>
            <div style="padding: 5px 15px; background: white; border-radius: 50px; font-weight: 700; font-size: 13px; border: 1px solid var(--glass-border);">{{ auth()->user()->full_name }}</div>
        </div>
    </nav>

    <main class="container">
        
        <div class="main-content">
            <div class="reveal" style="margin-bottom: 40px;">
                <h1 style="font-size: 46px; font-weight: 900; letter-spacing: -2px; margin-bottom: 10px; background: linear-gradient(to right, #0f172a, #6366f1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Trung tâm Bảo mật</h1>
                <p style="color: var(--text-muted); font-size: 18px; max-width: 650px;">Tìm hiểu lý do và cách thức chúng tôi bảo vệ tài sản số của bạn tại DienMayPro.</p>
            </div>

            {{-- 2FA CARD --}}
            <div class="glass-card reveal" style="animation-delay: 0.1s;">
                <div class="mfa-header">
                    <div class="mfa-brand">
                        <div class="mfa-icon-box"><i class="fa-solid fa-shield-halved"></i></div>
                        <div>
                            <h2 style="font-size: 24px; font-weight: 800;">Xác thực hai yếu tố (2FA)</h2>
                            <p id="mfa-desc" style="font-size: 15px; color: var(--text-muted);">{{ auth()->user()->is_2fa_enabled ? 'Trạng thái: Đang hoạt động' : 'Trạng thái: Đang tắt' }}</p>
                        </div>
                    </div>
                    <label class="custom-toggle">
                        <input type="checkbox" id="mfa-toggle" {{ auth()->user()->is_2fa_enabled ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="reason-box">
                    <div class="reason-header"><i class="fa-solid fa-lightbulb"></i> Tại sao bạn nên bật 2FA?</div>
                    <p class="reason-text">
                        Mật khẩu chỉ là một lớp bảo vệ mỏng manh. Hacker có thể đánh cắp nó qua Phishing hoặc các vụ rò rỉ dữ liệu lớn. 2FA tạo ra một lớp chắn thứ hai: <strong>Ngay cả khi biết mật khẩu, hacker vẫn không thể truy cập tài khoản</strong> nếu không có mã OTP được gửi riêng đến email của bạn. Đây là tiêu chuẩn vàng để bảo vệ tiền và thông tin cá nhân.
                    </p>
                </div>
            </div>

            {{-- RISK ANALYSIS --}}
            <div class="glass-card reveal" style="animation-delay: 0.2s;">
                <h3 class="section-title"><i class="fa-solid fa-chart-pie"></i> Phân tích rủi ro & Lý do</h3>
                <div class="score-list">
                    {{-- 2FA Detail --}}
                    <div class="score-item">
                        <div class="score-top">
                            <div class="score-label"><i class="fa-solid {{ $details['2fa']['status'] === 'pass' ? 'fa-circle-check text-green-500' : 'fa-circle-xmark text-rose-500' }}"></i> Xác thực 2 lớp</div>
                            <span class="score-val val-{{ $details['2fa']['status'] }}">{{ $details['2fa']['status'] === 'pass' ? 'An toàn' : 'Nguy hiểm' }}</span>
                        </div>
                        <p class="score-desc"><strong>Lý do:</strong> Tránh rủi ro bị đăng nhập trái phép khi mật khẩu bị lộ. Đây là chốt chặn cuối cùng bảo vệ tài khoản.</p>
                    </div>

                    {{-- Phone Detail --}}
                    <div class="score-item">
                        <div class="score-top">
                            <div class="score-label"><i class="fa-solid {{ $details['phone']['status'] === 'pass' ? 'fa-circle-check text-green-500' : 'fa-circle-xmark text-rose-500' }}"></i> Số điện thoại liên kết</div>
                            <span class="score-val val-{{ $details['phone']['status'] }}">{{ $details['phone']['status'] === 'pass' ? 'An toàn' : 'Cần xử lý' }}</span>
                        </div>
                        <p class="score-desc"><strong>Lý do:</strong> Là "chìa khóa dự phòng" để khôi phục tài khoản nếu bạn mất quyền truy cập Email hoặc bị hack mật khẩu.</p>
                    </div>

                    {{-- Password Detail --}}
                    <div class="score-item">
                        <div class="score-top">
                            <div class="score-label"><i class="fa-solid {{ $details['password']['status'] === 'pass' ? 'fa-circle-check text-green-500' : 'fa-circle-exclamation text-amber-500' }}"></i> Độ tươi mới mật khẩu</div>
                            <span class="score-val val-{{ $details['password']['status'] }}">{{ $details['password']['status'] === 'pass' ? 'Tốt' : 'Cảnh báo' }}</span>
                        </div>
                        <p class="score-desc"><strong>Lý do:</strong> Đổi mật khẩu định kỳ giúp vô hiệu hóa các thông tin đăng nhập cũ có thể đã bị rò rỉ trong quá khứ mà bạn không biết.</p>
                    </div>
                </div>
            </div>

            {{-- SESSION HISTORY --}}
            <div class="glass-card reveal" style="animation-delay: 0.3s;">
                <h3 class="section-title"><i class="fa-solid fa-history"></i> Kiểm soát phiên đăng nhập</h3>
                <div style="background: #eff6ff; padding: 15px; border-radius: 16px; margin-bottom: 20px; font-size: 13px; color: #1e40af; border: 1px solid #bfdbfe;">
                    <strong>Tại sao cần kiểm tra?</strong> Hãy đối soát IP và thiết bị dưới đây. Nếu thấy thiết bị ở tỉnh thành lạ hoặc trình duyệt bạn không dùng, hãy đổi mật khẩu ngay để ngắt kết nối của kẻ xâm nhập.
                </div>
                @foreach($sessions as $session)
                    <div class="session-item">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div class="device-icon"><i class="fa-solid {{ str_contains($session->user_agent, 'Mobile') ? 'fa-mobile-screen' : 'fa-desktop' }}"></i></div>
                            <div>
                                <p style="font-size: 15px; font-weight: 800;">{{ $session->ip_address }} @if($session->id === session()->getId()) <span style="background:#dcfce7; color:#166534; padding:2px 8px; border-radius:4px; font-size:10px; margin-left:5px; font-weight: 900;">HIỆN TẠI</span> @endif</p>
                                <span style="font-size: 12px; color: var(--text-muted);">{{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SIDEBAR --}}
        <div class="side-stack reveal" style="animation-delay: 0.4s;">
            <div class="score-box">
                <div class="score-display"><span class="score-num">{{ $score }}</span><span class="score-max">/100</span></div>
                <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 5px;">Chỉ số An toàn</h4>
                <p style="font-size: 14px; font-weight: 700; color: {{ $tierColor }}; margin-bottom: 20px;">Mức độ: {{ $securityTier }}</p>
                <div style="text-align: left; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 20px; font-size: 13px;">
                    <p style="font-weight: 800; margin-bottom: 8px;">Lời khuyên từ chuyên gia:</p>
                    @if($score < 100) <p style="opacity: 0.8;">• Bạn cần đạt 100 điểm để được bảo vệ tuyệt đối chống lại các cuộc tấn công tinh vi nhất.</p> @else <p style="opacity: 0.8;">• Tuyệt vời! Tài khoản của bạn đang ở trạng thái bảo mật cao nhất hệ thống.</p> @endif
                </div>
            </div>

            <div class="glass-card">
                <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 15px;">Liên kết hiện tại</h4>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div>
                        <p style="font-size: 11px; font-weight: 800; color: var(--text-muted);">EMAIL KHÔI PHỤC</p>
                        <p style="font-size: 14px; font-weight: 700;">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p style="font-size: 11px; font-weight: 800; color: var(--text-muted);">SỐ ĐIỆN THOẠI</p>
                        <p style="font-size: 14px; font-weight: 700;">{{ $user->phone_number ?? 'Chưa cập nhật' }}</p>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <div id="toast"><i class="fa-solid fa-circle-check"></i> <span id="toast-msg"></span></div>

    <script>
        const mfaToggle = document.getElementById('mfa-toggle');
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-msg');

        mfaToggle.addEventListener('change', async function() {
            const isEnabled = this.checked;
            this.disabled = true;
            try {
                const res = await fetch("{{ route('2fa.toggle') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ is_2fa_enabled: isEnabled })
                });
                const data = await res.json();
                if(data.success) {
                    toastMsg.innerText = data.message;
                    toast.className = 'show';
                    setTimeout(() => window.location.reload(), 1500);
                }
            } catch (e) { this.checked = !isEnabled; } finally { this.disabled = false; }
        });
    </script>
</body>
</html>
