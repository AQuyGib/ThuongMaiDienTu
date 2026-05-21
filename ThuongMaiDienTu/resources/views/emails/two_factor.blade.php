<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực bảo mật - DienMayPro</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f1f5f9; padding: 40px 0; }
        .main { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
        
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 60px 40px 40px; text-align: center; color: #ffffff; position: relative; }
        .header-icon { font-size: 40px; margin-bottom: 20px; display: block; }
        .logo-text { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 10px; display: inline-block; }
        .logo-text span { color: #3b82f6; }
        
        .content { padding: 45px 50px; text-align: center; }
        .greeting { font-size: 18px; color: #1e293b; font-weight: 700; margin-bottom: 15px; }
        .description { font-size: 15px; line-height: 1.6; color: #64748b; margin-bottom: 35px; }
        
        .otp-wrapper { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 40px 30px; margin-bottom: 35px; }
        .otp-label { font-size: 12px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 2.5px; margin-bottom: 20px; display: block; }
        .otp-code { font-size: 52px; font-weight: 900; color: #0f172a; letter-spacing: 15px; margin-left: 15px; font-family: 'SF Mono', 'Roboto Mono', Menlo, monospace; }
        
        .info-row { border-top: 1px solid #f1f5f9; padding-top: 25px; margin-top: 25px; display: flex; flex-direction: column; gap: 10px; }
        .info-item { font-size: 13px; color: #94a3b8; line-height: 1.5; }
        .info-item strong { color: #475569; }
        
        .security-alert { margin-top: 35px; padding: 20px; background-color: #fff1f2; border-radius: 16px; border: 1px solid #ffe4e6; }
        .security-alert p { font-size: 13px; color: #e11d48; margin: 0; line-height: 1.5; font-weight: 500; }
        
        .footer { padding: 40px; text-align: center; background-color: #f8fafc; border-top: 1px solid #e2e8f0; }
        .footer p { font-size: 12px; color: #94a3b8; margin: 0 0 12px; }
        .footer-links { font-size: 12px; font-weight: 600; }
        .footer-links a { color: #64748b; text-decoration: none; margin: 0 10px; }
        
        @media screen and (max-width: 600px) {
            .main { border-radius: 0; }
            .header, .content, .footer { padding: 40px 25px; }
            .otp-code { font-size: 38px; letter-spacing: 10px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <div class="logo-text">DienMay<span>Pro</span></div>
                <div style="font-size: 18px; font-weight: 600; opacity: 0.8; margin-top: 5px;">Security Verification</div>
            </div>
            
            <div class="content">
                <div class="greeting">Xin chào, {{ $user->full_name }}!</div>
                <div class="description">Để bảo mật tuyệt đối cho tài khoản của bạn, vui lòng sử dụng mã xác nhận (OTP) dưới đây để hoàn tất đăng nhập.</div>
                
                <div class="otp-wrapper">
                    <span class="otp-label">Mã bảo mật của bạn</span>
                    <div class="otp-code">{{ $otp }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-item">Thời gian hiệu lực: <strong>5 phút</strong></div>
                    <div class="info-item">Loại xác thực: <strong>Bảo mật 2 lớp (2FA)</strong></div>
                </div>

                <div class="security-alert">
                    <p>Nếu bạn không thực hiện yêu cầu này, có thể ai đó đang cố gắng truy cập tài khoản của bạn. Vui lòng bỏ qua mã này và đổi mật khẩu ngay lập tức.</p>
                </div>
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} DienMayPro Enterprise. All rights reserved.</p>
                <div class="footer-links">
                    <a href="{{ url('/') }}">Trang chủ</a>
                    <a href="{{ url('/security') }}">Cài đặt bảo mật</a>
                    <a href="mailto:support@dienmaypro.vn">Hỗ trợ</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
