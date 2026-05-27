<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi phục mật khẩu - DienMayPro</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding: 40px 0; }
        .main { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #edf2f7; }
        .header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 50px 40px; text-align: center; color: #ffffff; }
        .logo-text { font-size: 28px; font-weight: 800; letter-spacing: -1px; margin-bottom: 15px; display: inline-block; }
        .logo-text span { color: #3b82f6; }
        .header h1 { font-size: 24px; margin: 0; font-weight: 700; opacity: 0.9; }
        
        .content { padding: 40px; text-align: center; }
        .content h2 { font-size: 20px; color: #1e293b; margin-top: 0; margin-bottom: 20px; }
        .content p { font-size: 16px; line-height: 1.6; color: #475569; margin-bottom: 30px; }
        
        .otp-container { background-color: #f1f5f9; border-radius: 16px; padding: 35px; margin-bottom: 30px; border: 2px dashed #cbd5e1; }
        .otp-label { display: block; font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; }
        .otp-code { font-size: 42px; font-weight: 800; color: #1e293b; letter-spacing: 12px; font-family: 'Courier New', Courier, monospace; }
        
        .security-note { font-size: 14px; color: #64748b; margin-bottom: 30px; background: #fffbeb; padding: 15px; border-radius: 10px; border-left: 4px solid #f59e0b; text-align: left; }
        .security-note strong { color: #92400e; }
        
        .footer { background-color: #f8fafc; padding: 40px; text-align: center; border-top: 1px solid #edf2f7; }
        .footer p { font-size: 13px; color: #94a3b8; margin: 0 0 10px; }
        .footer a { color: #3b82f6; text-decoration: none; font-weight: 600; margin: 0 10px; }
        
        @media screen and (max-width: 600px) {
            .main { border-radius: 0; }
            .header, .content, .footer { padding: 30px 20px; }
            .otp-code { font-size: 32px; letter-spacing: 8px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <div class="logo-text">DienMay<span>Pro</span></div>
                <h1>Khôi phục quyền truy cập</h1>
            </div>
            
            <div class="content">
                <h2>Xác minh yêu cầu thay đổi mật khẩu</h2>
                <p>Chào bạn,<br>Chúng tôi đã nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn. Để tiếp tục, vui lòng sử dụng mã xác minh (OTP) dưới đây:</p>
                
                <div class="otp-container">
                    <span class="otp-label">Mã xác minh của bạn</span>
                    <div class="otp-code">{{ $otp }}</div>
                </div>
                
                <div class="security-note">
                    <strong>Lưu ý bảo mật:</strong> Mã này chỉ có hiệu lực trong <strong>5 phút</strong>. Tuyệt đối không chia sẻ mã này với bất kỳ ai, kể cả nhân viên của chúng tôi. Nếu bạn không yêu cầu khôi phục mật khẩu, vui lòng bỏ qua email này.
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; {{ date('Y') }} DienMayPro. All rights reserved.</p>
                <div style="margin-top: 15px;">
                    <a href="{{ url('/') }}">Trang chủ</a>
                    <a href="{{ url('/security') }}">Trung tâm bảo mật</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
