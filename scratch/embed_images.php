<?php
$file = 'BaoCao_ChiTiet_DuAn.md';
if (!file_exists($file)) {
    die("File not found: $file\n");
}
$content = file_get_contents($file);

$replacements = [
    '### 4.1. Giao diện Đăng nhập / Đăng ký & Xác thực 2 lớp (2FA)' => 
    "### 4.1. Giao diện Đăng nhập / Đăng ký & Xác thực 2 lớp (2FA)\n\n![Giao diện Đăng nhập & Xác thực 2FA OTP](images/login_2fa_ui.png)\n",

    '### 4.2. Giao diện Giỏ hàng & Thanh toán QR Code (VietQR Compact API)' =>
    "### 4.2. Giao diện Giỏ hàng & Thanh toán QR Code (VietQR Compact API)\n\n![Giao diện Giỏ hàng & Thanh toán VietQR](images/cart_payment_ui.png)\n",

    '### 4.3. Giao diện Cổng thông tin khách hàng, Tra cứu bảo hành & Đặt lịch sửa chữa' =>
    "### 4.3. Giao diện Cổng thông tin khách hàng, Tra cứu bảo hành & Đặt lịch sửa chữa\n\n![Giao diện Đặt lịch sửa chữa & Live Stepper](images/repair_portal_ui.png)\n",

    '### 4.4. Giao diện Vòng quay may mắn & Đổi điểm thưởng (Loyalty)' =>
    "### 4.4. Giao diện Vòng quay may mắn & Đổi điểm thưởng (Loyalty)\n\n![Giao diện Vòng quay may mắn & Tích điểm](images/lucky_wheel_ui.png)\n",

    '### 4.5. Giao diện Trợ lý ảo AI Chatbot tư vấn RAG' =>
    "### 4.5. Giao diện Trợ lý ảo AI Chatbot tư vấn RAG\n\n![Giao diện AI Chatbot tư vấn khách hàng](images/ai_chatbot_ui.png)\n",

    '### 4.7. Giao diện Admin: Quét duyệt đơn hàng bằng AI & Nhập kho IMEI' =>
    "### 4.7. Giao diện Admin: Quét duyệt đơn hàng bằng AI & Nhập kho IMEI\n\n![Giao diện Admin Dashboard & AI Audit Order](images/admin_dashboard_ui.png)\n"
];

foreach ($replacements as $search => $replace) {
    if (strpos($content, $search) !== false && strpos($content, $replace) === false) {
        $content = str_replace($search, $replace, $content);
        echo "Embedded image under heading: $search\n";
    }
}

file_put_contents($file, $content);
echo "Completed image embedding in $file.\n";
