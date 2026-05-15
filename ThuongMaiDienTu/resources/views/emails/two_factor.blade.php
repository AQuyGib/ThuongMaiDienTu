<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực Bảo mật - DienMayPro</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f6f6f6; font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Helvetica Neue", Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f6f6f6; padding-top: 40px; padding-bottom: 40px; }
        .main { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #eaeaea; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.04); }
        .header { padding: 48px 40px 24px; text-align: center; }
        .logo { width: 48px; height: 48px; background: #000000; color: #ffffff; border-radius: 12px; display: inline-block; line-height: 48px; font-size: 20px; font-weight: 700; margin-bottom: 24px; letter-spacing: -1px; }
        .title { font-size: 24px; font-weight: 600; color: #111111; margin: 0; letter-spacing: -0.5px; }
        .content { padding: 0 40px 40px; }
        .text { font-size: 15px; line-height: 24px; color: #444444; margin-bottom: 32px; text-align: center; }
        .otp-box { background-color: #fafafa; border: 1px solid #eaeaea; border-radius: 8px; padding: 32px; text-align: center; margin-bottom: 32px; }
        .otp-label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #666666; margin-bottom: 12px; display: block; font-weight: 600; }
        .otp-code { font-size: 46px; font-weight: 700; color: #000000; letter-spacing: 16px; margin-left: 16px; font-family: "SF Mono", ui-monospace, Menlo, Monaco, Consolas, monospace; }
        .divider { border-top: 1px solid #eaeaea; margin: 32px 0; }
        .meta { font-size: 14px; line-height: 22px; color: #666666; text-align: center; }
        .meta strong { color: #111111; font-weight: 600; }
        .alert-box { background-color: #fffbfa; border: 1px solid #ffefe6; border-radius: 8px; padding: 16px; text-align: center; margin-top: 32px; }
        .alert-text { font-size: 13px; line-height: 20px; color: #d946ef; margin: 0; color: #e11d48; }
        .footer { padding: 32px 40px; background-color: #fafafa; border-top: 1px solid #eaeaea; text-align: center; }
        .footer-text { font-size: 12px; line-height: 18px; color: #999999; margin: 0; }
        .footer-link { color: #666666; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <div class="logo">DMP</div>
                <h1 class="title">Xác thực bảo mật 2 lớp</h1>
            </div>
            
            <div class="content">
                <p class="text">
                    Xin chào <strong>{{ $user->full_name }}</strong>,<br>
                    Để hoàn tất yêu cầu bảo vệ tài khoản của bạn tại hệ thống DienMayPro, vui lòng sử dụng mã xác nhận bên dưới.
                </p>
                
                <div class="otp-box">
                    <span class="otp-label">Mã bảo mật của bạn</span>
                    <div class="otp-code">{{ $otp }}</div>
                </div>
                
                <p class="meta">
                    Mã này có hiệu lực trong <strong>5 phút</strong>.<br>
                    Tuyệt đối không chia sẻ mã này với bất kỳ ai để đảm bảo an toàn.
                </p>

                <div class="alert-box">
                    <p class="alert-text">
                        Nếu bạn không thực hiện yêu cầu này, hãy truy cập <a href="{{ config('app.url') }}/security" style="color:#e11d48; text-decoration:underline;">Trung tâm Bảo mật</a> để đổi mật khẩu ngay lập tức.
                    </p>
                </div>
            </div>

            <div class="footer">
                <p class="footer-text">
                    &copy; {{ date('Y') }} DienMayPro Enterprise.<br>
                    Đây là email tự động từ hệ thống máy chủ, vui lòng không trả lời.<br><br>
                    <a href="{{ config('app.url') }}" class="footer-link">Trang chủ</a> &nbsp;&bull;&nbsp; 
                    <a href="{{ config('app.url') }}/security" class="footer-link">Bảo mật</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
