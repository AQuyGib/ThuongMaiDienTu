<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác thực đăng nhập - DienMayPro</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; }
        .wrapper { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #1e3a8a, #1a2a6c); padding: 36px 40px; text-align: center; }
        .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; }
        .header p { margin: 6px 0 0; color: rgba(255,255,255,0.7); font-size: 13px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; color: #1e293b; margin-bottom: 16px; }
        .desc { font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 28px; }
        .otp-box { text-align: center; background: #f8fafc; border: 2px dashed #bfdbfe; border-radius: 16px; padding: 28px 20px; margin-bottom: 24px; }
        .otp-code { font-size: 42px; font-weight: 900; letter-spacing: 12px; color: #1e3a8a; font-family: 'Courier New', monospace; }
        .otp-label { font-size: 12px; color: #94a3b8; margin-top: 6px; text-transform: uppercase; letter-spacing: 1px; }
        .expiry { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 10px 16px; font-size: 13px; color: #dc2626; font-weight: 600; text-align: center; margin-bottom: 24px; }
        .security-note { font-size: 13px; color: #94a3b8; line-height: 1.6; }
        .footer { background: #f8fafc; padding: 20px 40px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>🛡️ DienMayPro - Xác thực đăng nhập</h1>
        <p>Two-Factor Authentication (2FA)</p>
    </div>
    <div class="body">
        <p class="greeting">Xin chào <strong>{{ $user->full_name }}</strong>,</p>
        <p class="desc">Chúng tôi nhận thấy có yêu cầu đăng nhập vào tài khoản của bạn. Vui lòng sử dụng mã OTP dưới đây để hoàn tất đăng nhập:</p>

        <div class="otp-box">
            <div class="otp-code">{{ $otp }}</div>
            <div class="otp-label">Mã xác thực một lần (OTP)</div>
        </div>

        <div class="expiry">⏱️ Mã có hiệu lực trong <strong>5 phút</strong> kể từ lúc nhận email này.</div>

        <p class="security-note">
            🔒 <strong>Bảo mật:</strong> Nếu bạn không thực hiện đăng nhập này, vui lòng bỏ qua email và đổi mật khẩu ngay lập tức để bảo vệ tài khoản.
        </p>
    </div>
    <div class="footer">
        © {{ date('Y') }} DienMayPro &bull; Email tự động, vui lòng không phản hồi.
    </div>
</div>
</body>
</html>
