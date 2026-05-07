<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP Khôi Phục Mật Khẩu</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h2 style="color: #333333; text-align: center;">Yêu cầu khôi phục mật khẩu</h2>
        <p style="color: #555555; font-size: 16px;">Chào bạn,</p>
        <p style="color: #555555; font-size: 16px;">Bạn vừa yêu cầu khôi phục mật khẩu cho tài khoản của mình. Dưới đây là mã xác minh (OTP) của bạn. Mã này có hiệu lực trong <strong>5 phút</strong>.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <span style="display: inline-block; padding: 15px 30px; background-color: #0b5ed7; color: #ffffff; font-size: 24px; font-weight: bold; border-radius: 6px; letter-spacing: 4px;">
                {{ $otp }}
            </span>
        </div>

        <p style="color: #555555; font-size: 16px;">Nếu bạn không yêu cầu thay đổi mật khẩu, vui lòng bỏ qua email này hoặc liên hệ với bộ phận hỗ trợ nếu bạn có bất kỳ nghi ngờ nào về bảo mật.</p>
        <hr style="border: none; border-top: 1px solid #eeeeee; margin: 30px 0;">
        <p style="color: #888888; font-size: 12px; text-align: center;">Trân trọng,<br>Đội ngũ hỗ trợ</p>
    </div>
</body>
</html>
