<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi phục mật khẩu - DienMayPro</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; background-color: #f4f6f9; padding: 40px 0;">
        <tr>
            <td align="center">
                <!-- KHUNG CHÍNH EMAIL -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px; background-color: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08); border: 1px solid rgba(226, 232, 240, 0.8);">
                    
                    <!-- TIÊU ĐỀ EMAIL PREMIUM -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 45px 40px; text-align: center; color: #ffffff;">
                            <!-- BIỂU TƯỢNG LOGO -->
                            <div style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 12px; display: inline-block;">
                                <span style="color: #ffffff;">DienMay</span><span style="color: #3b82f6;">Pro</span>
                            </div>
                            <!-- PHỤ ĐỀ -->
                            <h1 style="font-size: 14px; margin: 0; font-weight: 700; color: #f1f5f9; letter-spacing: 0.5px; text-transform: uppercase; opacity: 0.85; margin-bottom: 6px;">
                                Khôi phục quyền truy cập
                            </h1>
                            <div style="width: 40px; height: 3px; background-color: #3b82f6; margin: 12px auto 0; border-radius: 2px;"></div>
                        </td>
                    </tr>
                    
                    <!-- NỘI DUNG CHÍNH -->
                    <tr>
                        <td style="padding: 40px 40px 30px;">
                            <h2 style="font-size: 20px; color: #0f172a; font-weight: 700; margin-top: 0; margin-bottom: 16px; text-align: center;">
                                Xác minh yêu cầu thay đổi mật khẩu
                            </h2>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 28px; text-align: center;">
                                Chào bạn,<br>
                                Chúng tôi đã nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn. Để tiếp tục quá trình, vui lòng sử dụng mã xác minh (OTP) bảo mật dưới đây:
                            </p>
                            
                            <!-- BẢNG MÃ OTP -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 30px 20px; text-align: center; margin-bottom: 28px; box-shadow: inset 0 2px 4px rgba(15, 23, 42, 0.02);">
                                <span style="display: block; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px;">
                                    Mã xác minh của bạn
                                </span>
                                <div style="font-size: 38px; font-weight: 800; color: #0f172a; letter-spacing: 8px; font-family: 'Courier New', Courier, monospace; margin: 10px 0 15px 0;">
                                    {{ $otp }}
                                </div>
                            </div>
                            
                            <!-- NÚT LIÊN KẾT NHẬP NHANH -->
                            <div style="text-align: center; margin-bottom: 30px;">
                                <a href="{{ route('password.verify.form', ['email' => $email]) }}" style="display: inline-block; background-color: #3b82f6; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-size: 15px; font-weight: 700; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25); transition: background-color 0.2s;">
                                    Xác minh trực tiếp tại đây &rarr;
                                </a>
                            </div>
                            
                            <!-- HỘP THÔNG BÁO BẢO MẬT -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 8px; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <p style="font-size: 13px; line-height: 1.6; color: #78350f; margin: 0;">
                                            <strong style="color: #92400e; font-weight: 700;">Lưu ý bảo mật quan trọng:</strong><br>
                                            • Mã này chỉ có hiệu lực trong vòng <strong style="color: #92400e;">5 phút</strong>.<br>
                                            • Tuyệt đối không chia sẻ mã này với bất kỳ ai, kể cả nhân viên của chúng tôi.<br>
                                            • Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này để giữ an toàn cho tài khoản.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- PHẦN CHÂN EMAIL -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 35px 40px; text-align: center; border-top: 1px solid #edf2f7;">
                            <p style="font-size: 12px; color: #94a3b8; margin: 0 0 12px;">
                                Đây là email tự động từ hệ thống. Vui lòng không trả lời trực tiếp email này.
                            </p>
                            <p style="font-size: 12px; color: #64748b; margin: 0 0 16px; font-weight: 500;">
                                &copy; {{ date('Y') }} DienMayPro. Bảo lưu mọi quyền.
                            </p>
                            <div style="font-size: 12px;">
                                <a href="{{ url('/') }}" style="color: #3b82f6; text-decoration: none; font-weight: 600; margin: 0 10px;">
                                    Trang chủ
                                </a>
                                <span style="color: #cbd5e1;">|</span>
                                <a href="{{ url('/') }}" style="color: #3b82f6; text-decoration: none; font-weight: 600; margin: 0 10px;">
                                    Trung tâm hỗ trợ
                                </a>
                                <span style="color: #cbd5e1;">|</span>
                                <a href="{{ url('/') }}" style="color: #3b82f6; text-decoration: none; font-weight: 600; margin: 0 10px;">
                                    Điều khoản dịch vụ
                                </a>
                            </div>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
