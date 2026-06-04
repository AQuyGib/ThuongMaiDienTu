import os
import asyncio
from playwright.async_api import async_playwright
import sys

sys.stdout.reconfigure(encoding='utf-8')

# Import existing wireframes if possible
try:
    sys.path.append('d:/repogist/ThuongMaiDienTu/scratch')
    from generate_wireframes import wireframes as manual_wireframes, SHARED_CSS
except ImportError:
    manual_wireframes = {}
    SHARED_CSS = """
body {
    font-family: 'Courier New', Courier, monospace;
    background-color: #ffffff;
    color: #000000;
    margin: 0;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    box-sizing: border-box;
}
.container {
    width: 1200px;
    border: 3px double #000000;
    padding: 10px;
    background-color: #ffffff;
    box-sizing: border-box;
}
.title-bar {
    text-align: center;
    font-weight: bold;
    font-size: 20px;
    border: 2px solid #000000;
    padding: 10px;
    margin-bottom: 10px;
    text-transform: uppercase;
}
.header-bar {
    border: 1px solid #000000;
    padding: 8px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    font-size: 13px;
}
.grid {
    display: grid;
    gap: 10px;
}
.border-box {
    border: 1px solid #000000;
    padding: 10px;
    background-color: #ffffff;
}
.double-border {
    border: 3px double #000000;
    padding: 10px;
}
.dashed-border {
    border: 2px dashed #000000;
    padding: 15px;
    text-align: center;
}
.flex-row {
    display: flex;
    gap: 10px;
    align-items: center;
}
.flex-space {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.badge {
    border: 1px solid #000000;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}
.btn {
    border: 2px solid #000000;
    background-color: #ffffff;
    color: #000000;
    padding: 6px 12px;
    cursor: pointer;
    font-weight: bold;
    text-align: center;
    text-transform: uppercase;
}
.btn-disabled {
    border: 1px dashed #aaaaaa;
    color: #aaaaaa;
    background-color: #f9f9f9;
    padding: 6px 12px;
    text-align: center;
    text-transform: uppercase;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th, td {
    border: 1px solid #000000;
    padding: 8px;
    text-align: left;
    font-size: 13px;
}
th {
    background-color: #f2f2f2;
    font-weight: bold;
}
.footer-bar {
    border: 1px solid #000000;
    padding: 8px;
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    font-size: 12px;
}
.bold {
    font-weight: bold;
}
.text-center {
    text-align: center;
}
.progress-bar-container {
    border: 1px solid #000000;
    height: 20px;
    width: 100%;
    margin-top: 5px;
    position: relative;
    background-color: #ffffff;
}
.progress-bar-fill {
    background-color: #e0e0e0;
    height: 100%;
    border-right: 1px solid #000000;
}
.progress-bar-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 11px;
    font-weight: bold;
}
"""

all_wireframes = {}

# Custom helper function to wrap components in admin shell layout
def wrap_admin_layout(fid, title, content_html):
    return f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
{SHARED_CSS}
.admin-shell {{
    display: grid;
    grid-template-columns: 2.5fr 9.5fr;
    gap: 10px;
    margin-top: 10px;
}}
.sidebar-menu div {{
    padding: 8px;
    border-bottom: 1px dashed black;
    font-size: 13px;
}}
.sidebar-menu div.active {{
    font-weight: bold;
    border: 1px solid black;
    background-color: #f0f0f0;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN QUẢN TRỊ (UI LAYOUT) - {title}</div>
    <div class="header-bar">
        <div>HỆ THỐNG QUẢN TRỊ ADMIN - DIENMAYPRO (Chức năng 7.{fid})</div>
        <div>Tài khoản: admin@dienmaypro.vn | Quyền: Quản trị viên</div>
    </div>
    
    <div class="admin-shell">
        <!-- Sidebar -->
        <div class="border-box double-border sidebar-menu">
            <div class="bold text-center" style="border-bottom: 2px solid black; padding-bottom: 5px; margin-bottom: 10px;">DANH MỤC QUẢN TRỊ</div>
            <div>[Dashboard] Tổng quan</div>
            <div class="{"active" if fid in [8, 9, 10, 31, 32, 33, 34, 35] else ""}">[Kho hàng] Quản lý tồn kho</div>
            <div class="{"active" if fid in [5, 6, 7] else ""}">[Nhân sự] Quản lý & RBAC</div>
            <div class="{"active" if fid in [23, 25] else ""}">[Sửa chữa] Repair Tickets</div>
            <div class="{"active" if fid in [42] else ""}">[Sổ quỹ] Thu chi Cashbook</div>
            <div class="{"active" if fid in [29] else ""}">[Nhà cung cấp] Supplier</div>
            <div>[Đơn hàng] Quản lý hóa đơn</div>
            <div>[Cấu hình] Cài đặt hệ thống</div>
        </div>
        
        <!-- Main Content -->
        <div class="border-box">
            {content_html}
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống quản trị nội bộ DIENMAYPRO.VN</div>
        <div>Phụ lục Đặc tả chức năng - Phần mềm Thương mại Điện tử</div>
    </div>
</div>
</body>
</html>"""

# Custom helper function to wrap components in customer shell layout
def wrap_customer_layout(fid, title, content_html):
    return f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
{SHARED_CSS}
.customer-shell {{
    margin-top: 10px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN (UI LAYOUT) - {title}</div>
    <div class="header-bar">
        <div>DIENMAYPRO.VN - SIÊU THỊ ĐIỆN MÁY CHÍNH HÃNG</div>
        <div>Hotline: 1800.6699 | Tìm kiếm: [ Nhập sản phẩm cần mua... ] [Tìm]</div>
        <div>[Giỏ hàng (3)] | [Tra cứu bảo hành] | [Tài khoản]</div>
    </div>
    
    <div class="customer-shell">
        {content_html}
    </div>
    
    <div class="footer-bar">
        <div>Bản quyền thuộc về DIENMAYPRO.VN - Siêu thị điện máy tích hợp dịch vụ sửa chữa thông minh</div>
        <div>Thiết kế giao diện Wireframe - Chức năng 7.{fid}</div>
    </div>
</div>
</body>
</html>"""

# Define layouts for the 35 remaining functions
all_wireframes[1] = wrap_customer_layout(1, "ĐĂNG NHẬP / ĐĂNG KÝ ĐA KÊNH", """
<div style="max-width: 500px; margin: 40px auto;" class="border-box double-border">
    <div class="bold text-center" style="font-size: 18px; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 15px;">ĐĂNG NHẬP HỆ THỐNG</div>
    <div style="margin-bottom: 10px;">
        <label class="bold">Email hoặc Số điện thoại:</label>
        <div style="border: 1px solid black; padding: 8px; margin-top: 5px;">[ user@example.com hoặc 0901234567 ]</div>
    </div>
    <div style="margin-bottom: 15px;">
        <label class="bold">Mật khẩu:</label>
        <div style="border: 1px solid black; padding: 8px; margin-top: 5px;">[ ****************** ]</div>
    </div>
    <div class="flex-space" style="margin-bottom: 15px; font-size: 12px;">
        <label><input type="checkbox" checked> Duy trì đăng nhập</label>
        <a href="#" style="color: black; text-decoration: underline;">Quên mật khẩu?</a>
    </div>
    <div class="btn text-center" style="width: 100%; box-sizing: border-box; margin-bottom: 15px;">ĐĂNG NHẬP</div>
    <div class="text-center" style="font-size: 12px; margin-bottom: 15px; border-top: 1px dashed black; padding-top: 10px;">HOẶC ĐĂNG NHẬP QUA LIÊN KẾT</div>
    <div class="flex-row" style="justify-content: center; gap: 10px;">
        <div class="btn" style="flex: 1; font-size: 11px;">[G] Đăng nhập Google</div>
        <div class="btn" style="flex: 1; font-size: 11px;">[F] Đăng nhập Facebook</div>
    </div>
    <div class="text-center" style="margin-top: 15px; font-size: 12px;">
        Chưa có tài khoản? <a href="#" style="color: black; text-decoration: underline;">Đăng ký ngay</a>
    </div>
</div>
""")

all_wireframes[2] = wrap_customer_layout(2, "XÁC THỰC 2 LỚP BẢO MẬT (2FA OTP)", """
<div style="max-width: 500px; margin: 40px auto;" class="border-box double-border">
    <div class="bold text-center" style="font-size: 18px; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 15px;">XÁC THỰC LỚP BẢO MẬT THỨ HAI (2FA OTP)</div>
    <div class="dashed-border" style="margin-bottom: 15px; font-size: 12px;">
        [Biểu tượng khiên bảo mật]<br>
        Mã xác thực đã được gửi đến số điện thoại của bạn (*******890).<br>
        Vui lòng kiểm tra tin nhắn SMS hoặc ứng dụng Authenticator để lấy mã.
    </div>
    <div style="margin-bottom: 15px; text-align: center;">
        <label class="bold">Nhập mã xác thực 6 chữ số:</label>
        <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
            <div style="border: 2px solid black; width: 40px; height: 40px; font-size: 24px; line-height: 40px; font-weight: bold; text-align: center;">4</div>
            <div style="border: 2px solid black; width: 40px; height: 40px; font-size: 24px; line-height: 40px; font-weight: bold; text-align: center;">8</div>
            <div style="border: 2px solid black; width: 40px; height: 40px; font-size: 24px; line-height: 40px; font-weight: bold; text-align: center;">2</div>
            <div style="border: 2px solid black; width: 40px; height: 40px; font-size: 24px; line-height: 40px; font-weight: bold; text-align: center;">[ ]</div>
            <div style="border: 2px solid black; width: 40px; height: 40px; font-size: 24px; line-height: 40px; font-weight: bold; text-align: center;">[ ]</div>
            <div style="border: 2px solid black; width: 40px; height: 40px; font-size: 24px; line-height: 40px; font-weight: bold; text-align: center;">[ ]</div>
        </div>
    </div>
    <div class="btn text-center" style="width: 100%; box-sizing: border-box; margin-bottom: 10px;">XÁC MINH MÃ OTP</div>
    <div class="text-center" style="font-size: 12px;">
        Chưa nhận được mã? <a href="#" style="color: black; text-decoration: underline;">Gửi lại mã OTP (chờ 45s)</a>
    </div>
</div>
""")

all_wireframes[3] = wrap_customer_layout(3, "KHÔI PHỤC MẬT KHẨU", """
<div style="max-width: 500px; margin: 40px auto;" class="border-box double-border">
    <div class="bold text-center" style="font-size: 18px; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 15px;">KHÔI PHỤC MẬT KHẨU</div>
    <div style="font-size: 12px; margin-bottom: 15px; text-align: justify;">
        Nhập địa chỉ email liên kết với tài khoản của bạn. Hệ thống sẽ tự động tạo và gửi một đường dẫn khôi phục bảo mật.
    </div>
    <div style="margin-bottom: 15px;">
        <label class="bold">Địa chỉ Email đã đăng ký:</label>
        <div style="border: 1px solid black; padding: 8px; margin-top: 5px;">[ nguyenvanan@gmail.com ]</div>
    </div>
    <div class="btn text-center" style="width: 100%; box-sizing: border-box; margin-bottom: 10px;">GỬI LINK XÁC NHẬN ĐẶT LẠI MẬT KHẨU</div>
    <div class="text-center" style="font-size: 12px;">
        <a href="#" style="color: black; text-decoration: underline;">Quay lại trang Đăng nhập</a>
    </div>
</div>
""")

all_wireframes[4] = wrap_customer_layout(4, "LỊCH SỬ ĐĂNG NHẬP BẢO MẬT", """
<div class="border-box double-border">
    <div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">LỊCH SỬ THIẾT BỊ VÀ ĐĂNG NHẬP GẦN ĐÂY</div>
    <div style="font-size: 12px; margin-bottom: 10px;">
        Dưới đây là danh sách các thiết bị và địa chỉ IP đã đăng nhập vào tài khoản của bạn. Nếu phát hiện hoạt động bất thường, vui lòng nhấn nút "Đăng xuất khỏi tất cả thiết bị khác" ngay lập tức.
    </div>
    <table>
        <thead>
            <tr>
                <th>Thời gian đăng nhập</th>
                <th>Địa chỉ IP</th>
                <th>Thiết bị / Trình duyệt</th>
                <th>Vị trí ước tính</th>
                <th>Phương thức xác thực</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="bold">2026-06-04 23:15:02</td>
                <td>113.161.45.22</td>
                <td>Chrome v124 / Windows 11</td>
                <td>Hà Nội, Việt Nam (Mạng FPT)</td>
                <td>Mật khẩu + SMS OTP</td>
                <td><span class="badge" style="background-color: #e0e0e0;">Thiết bị hiện tại</span></td>
            </tr>
            <tr>
                <td>2026-06-04 18:22:45</td>
                <td>27.72.88.192</td>
                <td>Safari v17 / iPhone 15 Pro</td>
                <td>TP. Hồ Chí Minh, Việt Nam (Mạng Viettel)</td>
                <td>Đăng nhập nhanh Google OAuth</td>
                <td>Thành công</td>
            </tr>
            <tr>
                <td>2026-06-03 09:12:11</td>
                <td>1.53.192.110</td>
                <td>Firefox v120 / Linux Mint</td>
                <td>Đà Nẵng, Việt Nam</td>
                <td>Mật khẩu thường</td>
                <td><span style="color: red; font-weight: bold;">Sai mật khẩu (Bị khóa IP)</span></td>
            </tr>
        </tbody>
    </table>
    
    <div class="flex-space" style="margin-top: 15px;">
        <div class="btn" style="border-color: red; color: red;">ĐĂNG XUẤT KHỎI TẤT CẢ THIẾT BỊ KHÁC</div>
        <div>[Trang trước] [1] [Trang sau]</div>
    </div>
</div>
""")

all_wireframes[5] = wrap_admin_layout(5, "PHÂN QUYỀN NHÂN VIÊN (RBAC)", """
<div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">
    <div class="bold">DANH SÁCH VAI TRÒ & PHÂN QUYỀN TRÊN HỆ THỐNG (RBAC)</div>
    <div class="btn">+ THÊM VAI TRÒ MỚI</div>
</div>

<table>
    <thead>
        <tr>
            <th>Vai trò (Role)</th>
            <th>Mô tả chức năng hành chính</th>
            <th>Các quyền hạn được cấp (Permissions)</th>
            <th>Nhân viên được áp dụng</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <tr class="bold">
            <td>Super Admin</td>
            <td>Toàn quyền hệ thống</td>
            <td>[x] All Permissions (Mọi thao tác cấu hình, xóa, sửa, xem)</td>
            <td>2 người</td>
            <td>[Khóa]</td>
        </tr>
        <tr>
            <td>Technician (Kỹ thuật)</td>
            <td>Kỹ thuật viên sửa chữa điện máy</td>
            <td>
                [x] Xem phiếu sửa chữa | [x] Cập nhật Live Stepper trạng thái sửa chữa | 
                [x] Khảo sát thiết bị | [ ] Xuất hóa đơn | [ ] Xóa dữ liệu lịch sử
            </td>
            <td>8 người</td>
            <td>[Sửa quyền] | [Xóa vai trò]</td>
        </tr>
        <tr>
            <td>Cashier (Thu ngân)</td>
            <td>Thu ngân trực quầy POS siêu thị</td>
            <td>
                [x] Tạo đơn hàng tại quầy | [x] Quét mã vạch sản phẩm | 
                [x] Áp dụng thanh toán chia phần | [x] In hóa đơn nhiệt | [ ] Nhập kho
            </td>
            <td>5 người</td>
            <td>[Sửa quyền] | [Xóa vai trò]</td>
        </tr>
        <tr>
            <td>Store Keeper (Thủ kho)</td>
            <td>Quản lý xuất nhập tồn, IMEI</td>
            <td>
                [x] Lập phiếu nhập kho | [x] Quản lý số IMEI thiết bị | 
                [x] Điều chuyển kho nội bộ | [x] Kiểm kê kho hàng | [ ] Xem sổ quỹ
            </td>
            <td>3 người</td>
            <td>[Sửa quyền] | [Xóa vai trò]</td>
        </tr>
    </tbody>
</table>

<div class="dashed-border" style="margin-top: 15px;">
    <div class="bold">BIỂU MẪU CẤP QUYỀN NHANH CHO VAI TRÒ: [ Technician ]</div>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px; text-align: left;">
        <label><input type="checkbox" checked> View_Tickets (Xem phiếu)</label>
        <label><input type="checkbox" checked> Edit_Stepper (Cập nhật Stepper)</label>
        <label><input type="checkbox" checked> Diagnose_AI (AI chẩn đoán)</label>
        <label><input type="checkbox"> Export_Invoices (Xuất hóa đơn)</label>
        <label><input type="checkbox"> Delete_Records (Xóa dữ liệu)</label>
        <label><input type="checkbox"> Manage_Settings (Cài đặt hệ thống)</label>
    </div>
    <div class="btn" style="margin-top: 15px;">CẬP NHẬT PHÂN QUYỀN HỆ THỐNG</div>
</div>
""")

all_wireframes[6] = wrap_admin_layout(6, "QUẢN LÝ NHÂN VIÊN & KPI", """
<div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">
    <div class="bold">DANH SÁCH NHÂN VIÊN VÀ CHỈ TIÊU KPI THÁNG 06/2026</div>
    <div class="flex-row">
        [ Lọc theo vai trò: Tất cả ]
        <div class="btn">+ THÊM NHÂN VIÊN MỚI</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Mã NV</th>
            <th>Họ và Tên</th>
            <th>Vai trò</th>
            <th>Chỉ tiêu KPI được giao</th>
            <th>Kết quả thực tế</th>
            <th>Tỷ lệ hoàn thành</th>
            <th>Đánh giá trạng thái</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="bold">NV-001</td>
            <td>Nguyễn Văn A</td>
            <td>Kỹ thuật viên</td>
            <td>Hoàn thành sửa 50 phiếu</td>
            <td>48 phiếu sửa xong</td>
            <td class="bold">96.0%</td>
            <td><span class="badge" style="background-color: #d4edda; color: #155724;">Đạt yêu cầu</span></td>
            <td>[Giao KPI] | [Báo cáo] | [Sửa]</td>
        </tr>
        <tr>
            <td class="bold">NV-002</td>
            <td>Trần Thị B</td>
            <td>Thu ngân</td>
            <td>Doanh thu tại quầy: 100M</td>
            <td>112M VNĐ</td>
            <td class="bold">112.0%</td>
            <td><span class="badge" style="background-color: #d4edda; color: #155724; border: 2px solid green;">Xuất sắc</span></td>
            <td>[Giao KPI] | [Báo cáo] | [Sửa]</td>
        </tr>
        <tr>
            <td class="bold">NV-003</td>
            <td>Lê Văn C</td>
            <td>Kỹ thuật viên</td>
            <td>Hoàn thành sửa 40 phiếu</td>
            <td>15 phiếu sửa xong</td>
            <td class="bold" style="color: red;">37.5%</td>
            <td><span class="badge" style="background-color: #f8d7da; color: #721c24;">Cảnh báo (Thấp)</span></td>
            <td>[Giao KPI] | [Báo cáo] | [Sửa]</td>
        </tr>
    </tbody>
</table>

<div class="flex-space" style="margin-top: 15px;">
    <div class="btn">XUẤT BÁO CÁO HIỆU SUẤT KPI NHÂN SỰ</div>
    <div>[Trang trước] [1] [2] [Trang sau]</div>
</div>
""")

all_wireframes[7] = wrap_admin_layout(7, "ACTIVITY LOGS", """
<div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">
    <div class="bold">NHẬT KÝ HOẠT ĐỘNG TOÀN HỆ THỐNG (SYSTEM ACTIVITY AUDIT LOGS)</div>
    <div class="flex-row">
        [ Lọc theo loại: Tất cả ]
        [ Nhập IP hoặc Tài khoản... ]
        <div class="btn">Tìm kiếm</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Thời gian</th>
            <th>Tài khoản</th>
            <th>Hành động (Action)</th>
            <th>Bảng dữ liệu</th>
            <th>Địa chỉ IP</th>
            <th>Dữ liệu cũ (Old Value)</th>
            <th>Dữ liệu mới (New Value)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>2026-06-04 23:35:12</td>
            <td class="bold">admin@dienmay.com</td>
            <td>UPDATE_KPI</td>
            <td>employees</td>
            <td>192.168.1.15</td>
            <td style="font-size: 11px;">{"kpi_target": 40}</td>
            <td style="font-size: 11px;">{"kpi_target": 50}</td>
        </tr>
        <tr>
            <td>2026-06-04 23:31:05</td>
            <td class="bold">cashier@dienmay.com</td>
            <td>CREATE_INVOICE</td>
            <td>invoices</td>
            <td>192.168.1.5</td>
            <td style="font-size: 11px;">NULL</td>
            <td style="font-size: 11px;">{"invoice_id": "HD-0098", "total": 15990000}</td>
        </tr>
        <tr>
            <td>2026-06-04 22:12:44</td>
            <td class="bold">system_cron</td>
            <td>SYNC_INVENTORY</td>
            <td>products</td>
            <td>127.0.0.1</td>
            <td style="font-size: 11px;">{"stock": 15}</td>
            <td style="font-size: 11px;">{"stock": 12, "note": "Shopee Channel Sync"}</td>
        </tr>
        <tr>
            <td>2026-06-04 21:00:15</td>
            <td class="bold">technician@dienmay.com</td>
            <td>UPDATE_STEPPER</td>
            <td>repair_tickets</td>
            <td>113.161.45.22</td>
            <td style="font-size: 11px;">{"status": "Inspected"}</td>
            <td style="font-size: 11px;">{"status": "Repairing"}</td>
        </tr>
    </tbody>
</table>

<div class="flex-space" style="margin-top: 15px;">
    <div style="font-size: 12px;">Đang hiển thị từ log 1 đến 4 trong tổng số 8,421 bản ghi logs.</div>
    <div>[Trang trước] [1] [2] [3] ... [120] [Trang sau]</div>
</div>
""")

all_wireframes[8] = wrap_admin_layout(8, "CRUD DANH MỤC ĐA CẤP", """
<div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">QUẢN LÝ CÂU TRÚC DANH MỤC SẢN PHẨM ĐA CẤP</div>

<div style="display: grid; grid-template-columns: 4fr 8fr; gap: 15px;">
    <!-- Add form -->
    <div class="border-box double-border">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">THÊM DANH MỤC MỚI</div>
        <div style="margin-bottom: 10px;">
            <label class="bold">Tên danh mục:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 5px;">[ Tivi thông minh OLED ]</div>
        </div>
        <div style="margin-bottom: 10px;">
            <label class="bold">Danh mục cha (Parent):</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 5px;">[ Lựa chọn: --- Thiết bị nghe nhìn --- ]</div>
        </div>
        <div style="margin-bottom: 10px;">
            <label class="bold">Thứ tự ưu tiên hiển thị:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 5px;">[ 1 ]</div>
        </div>
        <div style="margin-bottom: 15px;">
            <label class="bold">Mô tả ngắn:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 5px; height: 60px;">[ Danh mục chuyên dụng cho các loại tivi OLED thế hệ mới ]</div>
        </div>
        <div class="btn text-center" style="width: 100%; box-sizing: border-box;">LƯU DANH MỤC</div>
    </div>
    
    <!-- Category tree representation -->
    <div class="border-box">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">CÂU TRÚC CÂY DANH MỤC (DRAG & DROP ORDER)</div>
        <div style="font-size: 13px; line-height: 24px; padding-left: 10px;">
            <strong>[+] 1. Thiết bị nghe nhìn</strong> <span style="font-size: 11px;">[Sửa | Xóa]</span><br>
            <span style="padding-left: 20px;">├── 1.1. Smart Tivi Sony</span> <span style="font-size: 11px;">[Sửa | Xóa]</span><br>
            <span style="padding-left: 20px;">├── 1.2. Smart Tivi Samsung</span> <span style="font-size: 11px;">[Sửa | Xóa]</span><br>
            <span style="padding-left: 20px;">└── 1.3. Loa & Amply karaoke</span> <span style="font-size: 11px;">[Sửa | Xóa]</span><br>
            
            <strong>[+] 2. Thiết bị điện lạnh</strong> <span style="font-size: 11px;">[Sửa | Xóa]</span><br>
            <span style="padding-left: 20px;">├── 2.1. Tủ lạnh side-by-side</span> <span style="font-size: 11px;">[Sửa | Xóa]</span><br>
            <span style="padding-left: 20px;">└── 2.2. Máy giặt Inverter</span> <span style="font-size: 11px;">[Sửa | Xóa]</span><br>
            
            <strong>[+] 3. Thiết bị gia dụng</strong> <span style="font-size: 11px;">[Sửa | Xóa]</span><br>
            <span style="padding-left: 20px;">├── 3.1. Máy lọc nước RO</span> <span style="font-size: 11px;">[Sửa | Xóa]</span><br>
            <span style="padding-left: 20px;">└── 3.2. Lò vi sóng & Lò nướng</span> <span style="font-size: 11px;">[Sửa | Xóa]</span>
        </div>
        
        <div class="dashed-border" style="margin-top: 15px; font-size: 12px;">
            [Gợi ý] Có thể kéo thả các danh mục để thay đổi trực tiếp cấu trúc phân cấp đa cấp (Nested Set Model).
        </div>
    </div>
</div>
""")

all_wireframes[9] = wrap_admin_layout(9, "CRUD SẢN PHẨM & BIẾN THỂ", """
<div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">THÊM VÀ THIẾT LẬP BIẾN THỂ SẢN PHẨM (PRODUCT WITH VARIANTS)</div>

<div style="display: grid; grid-template-columns: 7fr 5fr; gap: 15px;">
    <!-- Left form info -->
    <div class="border-box double-border">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">THÔNG TIN SẢN PHẨM CHÍNH</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
            <div>
                <label class="bold">Tên sản phẩm:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 5px;">[ Smart Tivi Sony Bravia OLED ]</div>
            </div>
            <div>
                <label class="bold">Mã SKU gốc:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 5px;">[ TV-SONY-BRAVIA ]</div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
            <div>
                <label class="bold">Thương hiệu:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 5px;">[ Sony ]</div>
            </div>
            <div>
                <label class="bold">Danh mục:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 5px;">[ Smart Tivi Sony ]</div>
            </div>
        </div>
        
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px; margin-top: 15px;">BẢNG CẤU HÌNH BIẾN THỂ (VARIANTS MATRIX)</div>
        <table>
            <thead>
                <tr>
                    <th>Kích thước</th>
                    <th>Màu sắc</th>
                    <th>SKU biến thể</th>
                    <th>Giá bán lẻ</th>
                    <th>Tồn kho</th>
                    <th>Kích hoạt</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>55 inch</td>
                    <td>Đen tuyền</td>
                    <td>TV-SONY-55-B</td>
                    <td>19.990.000đ</td>
                    <td>[ 5 ]</td>
                    <td>[x]</td>
                </tr>
                <tr>
                    <td>65 inch</td>
                    <td>Đen tuyền</td>
                    <td>TV-SONY-65-B</td>
                    <td>27.990.000đ</td>
                    <td>[ 2 ]</td>
                    <td>[x]</td>
                </tr>
                <tr>
                    <td>55 inch</td>
                    <td>Bạc ánh kim</td>
                    <td>TV-SONY-55-S</td>
                    <td>20.490.000đ</td>
                    <td>[ 0 ]</td>
                    <td>[x]</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Right media/images -->
    <div class="border-box">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">HÌNH ẢNH SẢN PHẨM & BIẾN THỂ</div>
        <div class="dashed-border" style="height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 15px;">
            [ Kéo thả hình ảnh chính vào đây hoặc Click để chọn tệp ]
            <span style="font-size: 11px; color: #555555; margin-top: 5px;">(Định dạng JPG, PNG tối đa 5MB)</span>
        </div>
        
        <div class="bold">ẢNH BIẾN THỂ RIÊNG BIỆT:</div>
        <div style="display: flex; gap: 10px; margin-top: 5px;">
            <div style="width: 60px; height: 60px; border: 1px dashed black; display: flex; align-items: center; justify-content: center; font-size: 10px;">Variant 55B</div>
            <div style="width: 60px; height: 60px; border: 1px dashed black; display: flex; align-items: center; justify-content: center; font-size: 10px;">Variant 65B</div>
            <div style="width: 60px; height: 60px; border: 1px dashed black; display: flex; align-items: center; justify-content: center; font-size: 10px;">[+] Thêm</div>
        </div>
        
        <div class="btn text-center" style="width: 100%; box-sizing: border-box; margin-top: 50px;">LƯU TẤT CẢ SẢN PHẨM & BIẾN THỂ</div>
    </div>
</div>
""")

all_wireframes[10] = wrap_admin_layout(10, "IMPORT / EXPORT EXCEL", """
<div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">BỘ XỬ LÝ DỮ LIỆU EXCEL TỒN KHO VÀ SẢN PHẨM</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
    <!-- Import block -->
    <div class="border-box double-border">
        <div class="bold text-center" style="font-size: 14px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">NHẬP DỮ LIỆU TỪ TỆP EXCEL (IMPORT)</div>
        <div class="dashed-border" style="margin-bottom: 15px; height: 100px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            [ Kéo thả file .xlsx, .xls vào đây hoặc Click để chọn ]
            <span style="font-size: 11px; margin-top: 5px;">File mẫu: <a href="#" style="color: black; text-decoration: underline;">download_mau_nhap_kho.xlsx</a></span>
        </div>
        
        <div class="bold" style="font-size: 12px; margin-bottom: 5px;">TIẾN TRÌNH TẢI LÊN & XỬ LÝ (MOCKUP PROGRESS):</div>
        <div class="progress-bar-container">
            <div class="progress-bar-fill" style="width: 75%;"></div>
            <div class="progress-bar-text">Đang xử lý dòng 75/100 (75%)</div>
        </div>
        
        <div class="btn text-center" style="margin-top: 20px; width: 100%; box-sizing: border-box;">BẮT ĐẦU NHẬP EXCEL</div>
    </div>
    
    <!-- Export block -->
    <div class="border-box double-border">
        <div class="bold text-center" style="font-size: 14px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">XUẤT DỮ LIỆU BÁO CÁO RA EXCEL (EXPORT)</div>
        <div style="margin-bottom: 15px;">
            <label class="bold">1. Lựa chọn bảng dữ liệu cần xuất:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 5px;">[ Lựa chọn: Danh sách sản phẩm kèm theo IMEI ]</div>
        </div>
        <div style="margin-bottom: 15px;">
            <label class="bold">2. Lọc thời gian xuất dữ liệu:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 5px;">[ Từ ngày: 2026-05-01 ] [ Đến ngày: 2026-06-04 ]</div>
        </div>
        
        <div class="btn text-center" style="margin-top: 40px; width: 100%; box-sizing: border-box;">XUẤT TỆP EXCEL (.XLSX)</div>
    </div>
</div>

<div class="border-box" style="margin-top: 15px;">
    <div class="bold" style="color: red;">NHẬT KÝ LỖI HỢP LỆ DỮ LIỆU (VALIDATION ERRORS LOGS)</div>
    <div style="font-size: 12px; margin-top: 5px; line-height: 20px;">
        [Dòng 12]: Mã SKU 'TV-SONY-99' không tồn tại trên hệ thống database.<br>
        [Dòng 45]: Số lượng nhập 'không' phải là một số nguyên dương hợp lệ.<br>
        [Dòng 78]: Mã IMEI '990088776655443' đã bị trùng lặp với sản phẩm hiện có.
    </div>
</div>
""")

all_wireframes[11] = wrap_customer_layout(11, "GIỎ HÀNG SERVER-SIDE (AJAX CART)", """
<div style="max-width: 900px; margin: 20px auto;" class="border-box double-border">
    <div class="bold" style="font-size: 18px; border-bottom: 2px solid black; padding-bottom: 8px; margin-bottom: 15px;">GIỎ HÀNG CỦA BẠN</div>
    
    <table>
        <thead>
            <tr>
                <th>Ảnh</th>
                <th>Sản phẩm</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 80px; text-align: center;">[Ảnh TV]</td>
                <td>
                    <div class="bold">Smart Tivi Sony 4K 55"</div>
                    <span style="font-size: 11px;">Màu: Đen tuyền | SKU: TV-SONY-55-B</span>
                </td>
                <td>15.990.000đ</td>
                <td>
                    <div style="display: inline-flex; border: 1px solid black;">
                        <button style="border: none; background: #e0e0e0; width: 25px; cursor: pointer;">-</button>
                        <span style="padding: 0 10px; line-height: 25px;">1</span>
                        <button style="border: none; background: #e0e0e0; width: 25px; cursor: pointer;">+</button>
                    </div>
                </td>
                <td class="bold">15.990.000đ</td>
                <td><a href="#" style="color: black; text-decoration: underline;">Xóa</a></td>
            </tr>
            <tr>
                <td style="text-align: center;">[Ảnh Loa]</td>
                <td>
                    <div class="bold">Loa Soundbar Sony 2.1 CH</div>
                    <span style="font-size: 11px;">Màu: Đen | SKU: LOA-SONY-21</span>
                </td>
                <td>3.490.000đ</td>
                <td>
                    <div style="display: inline-flex; border: 1px solid black;">
                        <button style="border: none; background: #e0e0e0; width: 25px; cursor: pointer;">-</button>
                        <span style="padding: 0 10px; line-height: 25px;">2</span>
                        <button style="border: none; background: #e0e0e0; width: 25px; cursor: pointer;">+</button>
                    </div>
                </td>
                <td class="bold">6.980.000đ</td>
                <td><a href="#" style="color: black; text-decoration: underline;">Xóa</a></td>
            </tr>
        </tbody>
    </table>
    
    <div style="margin-top: 15px; text-align: right;">
        <div style="font-size: 14px; margin-bottom: 5px;">Tạm tính: <span class="bold">22.970.000đ</span></div>
        <div style="font-size: 12px; margin-bottom: 10px; color: #555555;">Áp dụng ưu đãi thành viên (VIP Hạng Vàng): Giảm 3% nước tính (-689.100đ)</div>
        <div style="font-size: 16px; font-weight: bold; margin-bottom: 15px;">Tổng cộng: 22.280.900đ</div>
        
        <div class="flex-space" style="justify-content: flex-end; gap: 15px;">
            <div class="btn" style="background-color: #f0f0f0;">TIẾP TỤC MUA SẮM</div>
            <div class="btn" style="background-color: #ffffff; border-width: 3px;">TIẾN HÀNH THANH TOÁN</div>
        </div>
    </div>
</div>
""")

all_wireframes[15] = wrap_customer_layout(15, "THANH TOÁN QR ĐỘNG (PAYOS)", """
<div style="max-width: 800px; margin: 20px auto; display: grid; grid-template-columns: 5fr 7fr; gap: 15px;" class="border-box double-border">
    <!-- QR Code Section -->
    <div class="border-box text-center">
        <div class="bold" style="font-size: 14px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">QUÉT MÃ VIETQR ĐỂ TRẢ</div>
        
        <div class="dashed-border" style="width: 200px; height: 200px; margin: 15px auto; display: flex; align-items: center; justify-content: center; font-weight: bold; background-color: white;">
            [ ẢNH QR CODE ĐỘNG ]<br>
            PayOS Dynamic QR
        </div>
        
        <div style="font-size: 12px; margin-top: 10px;">
            Mã QR sẽ hết hạn sau:<br>
            <span class="bold" style="font-size: 18px; color: red;">09:45</span>
        </div>
        
        <div class="progress-bar-container" style="height: 10px; margin-top: 10px;">
            <div class="progress-bar-fill" style="width: 65%; background-color: black;"></div>
        </div>
    </div>
    
    <!-- Order Details Section -->
    <div class="border-box">
        <div class="bold" style="font-size: 15px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">THÔNG TIN ĐƠN HÀNG #HD-0098</div>
        <div style="font-size: 13px; line-height: 22px;">
            <strong>Mã đơn hàng:</strong> HD-009875421<br>
            <strong>Khách hàng:</strong> Nguyễn Văn An<br>
            <strong>Số điện thoại:</strong> 090****890<br>
            <strong>Nội dung chuyển khoản:</strong> <span class="bold">PAYOS0098</span><br>
            <strong>Số tiền thanh toán:</strong>
            <div style="font-size: 20px; font-weight: bold; margin-top: 5px; border: 1px dashed black; padding: 5px; display: inline-block;">
                15.990.000 đ
            </div>
        </div>
        
        <div class="dashed-border" style="margin-top: 15px; padding: 10px; background-color: #fafafa; font-size: 12px;">
            [Hệ thống tự động nhận diện thanh toán ngay khi giao dịch thành công. Quý khách vui lòng không đóng trình duyệt.]
        </div>
        
        <div class="btn text-center" style="margin-top: 15px; width: 100%; box-sizing: border-box;">TÔI ĐÃ CHUYỂN KHOẢN</div>
    </div>
</div>
""")

all_wireframes[16] = wrap_customer_layout(16, "TỰ ĐỘNG TÍNH PHÍ VẬN CHUYỂN", """
<div style="max-width: 800px; margin: 20px auto;" class="border-box double-border">
    <div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">ĐỊA CHỈ NHẬN HÀNG VÀ PHÍ VẬN CHUYỂN TỰ ĐỘNG</div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <!-- Left Side fields -->
        <div class="border-box">
            <div class="bold" style="margin-bottom: 10px;">1. Địa chỉ giao hàng:</div>
            <div style="margin-bottom: 10px;">
                <label>Tỉnh / Thành phố:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Lựa chọn: Thành phố Hà Nội ]</div>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Quận / Huyện:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Lựa chọn: Quận Cầu Giấy ]</div>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Phường / Xã:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Lựa chọn: Phường Dịch Vọng ]</div>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Số nhà, Tên đường:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Số 15, Ngõ 100, Trần Thái Tông ]</div>
            </div>
        </div>
        
        <!-- Right Side results -->
        <div class="border-box">
            <div class="bold" style="margin-bottom: 10px;">2. Phương thức vận chuyển và Phí:</div>
            
            <div style="border: 2px solid black; padding: 8px; margin-bottom: 10px;">
                <input type="radio" name="shipping" checked> <strong>Giao hàng nhanh (GHN)</strong><br>
                <span style="font-size: 12px; padding-left: 20px;">Dự kiến giao: Ngày mai (05/06)</span><br>
                <span style="font-size: 13px; font-weight: bold; padding-left: 20px;">Phí: 35.000đ (Dựa trên khoảng cách 8.2 km)</span>
            </div>
            
            <div style="border: 1px solid black; padding: 8px; margin-bottom: 15px; color: #555555;">
                <input type="radio" name="shipping"> <strong>Giao hàng Tiết kiệm (GHTK)</strong><br>
                <span style="font-size: 12px; padding-left: 20px;">Dự kiến giao: 2-3 ngày làm việc</span><br>
                <span style="font-size: 13px; font-weight: bold; padding-left: 20px;">Phí: 28.000đ</span>
            </div>
            
            <div class="border-box" style="font-size: 12px; background-color: #fafafa;">
                <div class="flex-space">
                    <div>Tổng khối lượng đơn hàng:</div>
                    <div class="bold">5.8 Kg</div>
                </div>
                <div class="flex-space" style="margin-top: 5px;">
                    <div>Kho xuất hàng:</div>
                    <div class="bold">Tổng kho Cầu Giấy (Cách 2.5km)</div>
                </div>
            </div>
            
            <div class="btn text-center" style="margin-top: 15px; width: 100%; box-sizing: border-box;">XÁC NHẬN ĐỊA CHỈ & TÍNH PHÍ</div>
        </div>
    </div>
</div>
""")

all_wireframes[17] = wrap_customer_layout(17, "ĐÁNH GIÁ & BÌNH LUẬN ĐỆ QUY", """
<div style="max-width: 850px; margin: 20px auto;" class="border-box double-border">
    <div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">ĐÁNH GIÁ CỦA KHÁCH HÀNG (THREADS COMMENTS)</div>
    
    <!-- Rating Summary -->
    <div class="flex-row border-box" style="margin-bottom: 15px; background-color: #fafafa;">
        <div class="text-center" style="padding-right: 30px; border-right: 1px dashed black;">
            <div style="font-size: 32px; font-weight: bold;">4.8 / 5</div>
            <div style="font-size: 14px; margin-top: 5px;">★★★★★</div>
            <div style="font-size: 11px; margin-top: 3px;">(128 đánh giá thực tế)</div>
        </div>
        <div style="flex: 1; padding-left: 20px; font-size: 12px; line-height: 18px;">
            5 sao: [=======================] (112)<br>
            4 sao: [===] (12)<br>
            3 sao: [=] (3)<br>
            2 sao: [ ] (1)<br>
            1 sao: [ ] (0)
        </div>
    </div>
    
    <!-- Comment List -->
    <div class="border-box">
        <!-- Comment 1 -->
        <div style="margin-bottom: 15px; border-bottom: 1px dashed #cccccc; padding-bottom: 10px;">
            <div class="flex-space">
                <span class="bold">Nguyễn Văn An <span style="font-weight: normal; font-size: 11px;">(Đã mua hàng tại siêu thị)</span></span>
                <span style="font-size: 11px;">★★★★★ | 2 giờ trước</span>
            </div>
            <div style="margin-top: 5px; font-size: 13px;">Tivi dùng rất tốt, hình ảnh sắc nét, loa nghe ấm. Nhân viên giao hàng lắp đặt rất nhiệt tình!</div>
            
            <!-- Reply 1.1 (Nested) -->
            <div style="margin-left: 40px; margin-top: 10px; padding: 8px; border-left: 2px solid black; background-color: #f9f9f9; font-size: 12px;">
                <div class="flex-space">
                    <span class="bold">Hệ thống Siêu thị Điện máy DIENMAYPRO <span style="font-weight: normal; font-size: 10px;">(Phản hồi từ Quản trị viên)</span></span>
                    <span style="font-size: 10px;">1 giờ trước</span>
                </div>
                <div style="margin-top: 3px;">Chào anh An, rất vui vì anh hài lòng với sản phẩm và dịch vụ của chúng em. Nếu cần hỗ trợ kỹ thuật gì anh cứ liên hệ lại nhé!</div>
            </div>
        </div>
    </div>
    
    <!-- Submission form -->
    <div class="border-box" style="margin-top: 15px;">
        <div class="bold">ĐĂNG ĐÁNH GIÁ VÀ CÂU HỎI MỚI</div>
        <div class="flex-row" style="margin-top: 10px; margin-bottom: 10px;">
            Chọn số sao đánh giá: [ 5 Sao ★★★★★ ]
        </div>
        <div style="margin-bottom: 10px;">
            <div style="border: 1px solid black; padding: 8px; height: 60px;">[ Nhập bình luận hoặc câu hỏi của bạn tại đây... ]</div>
        </div>
        <div class="btn" style="font-size: 12px;">ĐĂNG ĐÁNH GIÁ SẢN PHẨM</div>
    </div>
</div>
""")

all_wireframes[18] = wrap_customer_layout(18, "DANH SÁCH YÊU THÍCH (WISHLIST)", """
<div class="border-box double-border">
    <div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">DANH SÁCH SẢN PHẨM YÊU THÍCH (MY WISHLIST)</div>
    
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
        <!-- Item 1 -->
        <div class="border-box text-center">
            <div style="height: 80px; border: 1px dashed black; margin-bottom: 5px; display: flex; align-items: center; justify-content: center;">[ Ảnh Tivi Sony ]</div>
            <div class="bold" style="font-size: 12px;">Smart Tivi Sony 55"</div>
            <div class="bold" style="font-size: 13px; margin-top: 5px;">15.990.000đ</div>
            <div class="flex-row" style="margin-top: 10px; gap: 5px;">
                <div class="btn" style="flex: 1; font-size: 9px; padding: 4px 2px;">MUA NGAY</div>
                <div class="btn" style="flex: 1; font-size: 9px; padding: 4px 2px; border-color: red; color: red;">XÓA</div>
            </div>
        </div>
        
        <!-- Item 2 -->
        <div class="border-box text-center">
            <div style="height: 80px; border: 1px dashed black; margin-bottom: 5px; display: flex; align-items: center; justify-content: center;">[ Ảnh Máy giặt LG ]</div>
            <div class="bold" style="font-size: 12px;">Máy giặt LG 9Kg Inverter</div>
            <div class="bold" style="font-size: 13px; margin-top: 5px;">8.490.000đ</div>
            <div class="flex-row" style="margin-top: 10px; gap: 5px;">
                <div class="btn" style="flex: 1; font-size: 9px; padding: 4px 2px;">MUA NGAY</div>
                <div class="btn" style="flex: 1; font-size: 9px; padding: 4px 2px; border-color: red; color: red;">XÓA</div>
            </div>
        </div>
    </div>
</div>
""")

all_wireframes[19] = wrap_customer_layout(19, "SẢN PHẨM ĐÃ XEM GẦN ĐÂY", """
<div class="border-box double-border">
    <div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">SẢN PHẨM BẠN ĐÃ XEM GẦN ĐÂY (RECENTLY VIEWED ITEMS)</div>
    
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
        <!-- Item 1 -->
        <div class="border-box text-center">
            <div style="height: 80px; border: 1px dashed black; margin-bottom: 5px; display: flex; align-items: center; justify-content: center;">[ Ảnh Tivi Sony ]</div>
            <div class="bold" style="font-size: 12px;">Smart Tivi Sony 55"</div>
            <div class="bold" style="font-size: 13px; margin-top: 5px;">15.990.000đ</div>
            <div class="btn" style="margin-top: 10px; font-size: 11px; width: 100%; box-sizing: border-box;">MUA NGAY</div>
        </div>
        
        <!-- Item 2 -->
        <div class="border-box text-center">
            <div style="height: 80px; border: 1px dashed black; margin-bottom: 5px; display: flex; align-items: center; justify-content: center;">[ Ảnh Máy giặt LG ]</div>
            <div class="bold" style="font-size: 12px;">Máy giặt LG 9Kg Inverter</div>
            <div class="bold" style="font-size: 13px; margin-top: 5px;">8.490.000đ</div>
            <div class="btn" style="margin-top: 10px; font-size: 11px; width: 100%; box-sizing: border-box;">MUA NGAY</div>
        </div>
        
        <!-- Item 3 -->
        <div class="border-box text-center">
            <div style="height: 80px; border: 1px dashed black; margin-bottom: 5px; display: flex; align-items: center; justify-content: center;">[ Ảnh Tủ lạnh Samsung ]</div>
            <div class="bold" style="font-size: 12px;">Tủ lạnh Samsung 320L</div>
            <div class="bold" style="font-size: 13px; margin-top: 5px;">10.190.000đ</div>
            <div class="btn" style="margin-top: 10px; font-size: 11px; width: 100%; box-sizing: border-box;">MUA NGAY</div>
        </div>
        
        <!-- Item 4 -->
        <div class="border-box text-center" style="opacity: 0.5;">
            <div style="height: 80px; border: 1px dashed black; margin-bottom: 5px; display: flex; align-items: center; justify-content: center;">[ Ảnh Loa Soundbar ]</div>
            <div class="bold" style="font-size: 12px;">Loa Soundbar Sony 2.1</div>
            <div class="bold" style="font-size: 13px; margin-top: 5px;">3.490.000đ</div>
            <div class="btn" style="margin-top: 10px; font-size: 11px; width: 100%; box-sizing: border-box;">MUA NGAY</div>
        </div>
    </div>
</div>
""")

all_wireframes[20] = wrap_customer_layout(20, "TRA CỨU BẢO HÀNH ĐIỆN TỬ", """
<div style="max-width: 700px; margin: 30px auto;" class="border-box double-border">
    <div class="bold text-center" style="font-size: 18px; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 20px;">CỔNG TRA CỨU BẢO HÀNH ĐIỆN TỬ</div>
    
    <div class="flex-row" style="margin-bottom: 20px; justify-content: center;">
        <label class="bold">Nhập Số điện thoại hoặc IMEI sản phẩm:</label>
        <div style="border: 1px solid black; padding: 6px; width: 250px;">[ 0901234567 ]</div>
        <div class="btn">TRA CỨU NGAY</div>
    </div>
    
    <div class="border-box">
        <div class="bold" style="font-size: 14px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">KẾT QUẢ TRA CỨU THÔNG TIN BẢO HÀNH</div>
        <div style="font-size: 13px; line-height: 22px; margin-bottom: 15px;">
            <strong>Họ tên khách hàng:</strong> Nguyễn Văn An<br>
            <strong>Số điện thoại:</strong> 0901234567<br>
            <strong>Thiết bị tra cứu:</strong> Smart Tivi Sony 4K 55" (Serial: SONY55B8877)
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Gói bảo hành chính hãng</th>
                    <th>Ngày mua hàng</th>
                    <th>Ngày kích hoạt BH</th>
                    <th>Ngày hết hạn BH</th>
                    <th>Trạng thái hiện tại</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="bold">Gói bảo hành Vàng (24 tháng)</td>
                    <td>2025-06-04</td>
                    <td>2025-06-04</td>
                    <td class="bold">2027-06-04</td>
                    <td><span class="badge" style="background-color: #d4edda; color: #155724;">Đang bảo hành</span></td>
                </tr>
            </tbody>
        </table>
        
        <div class="flex-space" style="margin-top: 15px;">
            <div style="font-size: 11px;">(Thời gian còn lại: 365 ngày bảo hành chính hãng)</div>
            <div class="btn" style="font-size: 11px;">YÊU CẦU ĐẶT LỊCH SỬA CHỮA / BẢO HÀNH</div>
        </div>
    </div>
</div>
""")

all_wireframes[21] = wrap_customer_layout(21, "ĐẶT LỊCH SỬA CHỮA TRỰC TUYẾN", """
<div style="max-width: 800px; margin: 20px auto;" class="border-box double-border">
    <div class="bold text-center" style="font-size: 18px; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 20px;">ĐĂNG KÝ LỊCH HẸN BẢO HÀNH & SỬA CHỮA TRỰC TUYẾN</div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <!-- Column 1: Info device -->
        <div class="border-box">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">1. THÔNG TIN KHÁCH HÀNG & THIẾT BỊ</div>
            <div style="margin-bottom: 10px;">
                <label>Họ và tên khách hàng:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Nguyễn Văn An ]</div>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Số điện thoại liên hệ:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ 0901234567 ]</div>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Loại thiết bị gặp sự cố:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Lựa chọn: Tủ lạnh Inverter ]</div>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Mô tả chi tiết lỗi (Triệu chứng):</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px; height: 60px;">[ Tủ lạnh không làm được đá, ngăn mát vẫn hoạt động bình thường, nghe tiếng kêu re re từ block sau tủ ]</div>
            </div>
        </div>
        
        <!-- Column 2: Date slot -->
        <div class="border-box">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">2. CHỌN LỊCH HẸN VÀ PHƯƠNG THỨC</div>
            <div style="margin-bottom: 10px;">
                <label>Ngày kỹ thuật viên đến kiểm tra:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Chọn ngày: 2026-06-05 ]</div>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Khung giờ thuận tiện:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Chọn ca: Ca Sáng (08:00 - 12:00) ]</div>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Hình thức kiểm tra kỹ thuật:</label>
                <div style="margin-top: 5px;">
                    <label><input type="radio" name="check_type" checked> Kỹ thuật viên đến nhà sửa chữa</label><br>
                    <label><input type="radio" name="check_type"> Khách hàng mang thiết bị đến siêu thị</label>
                </div>
            </div>
            
            <div class="dashed-border" style="margin-top: 15px; font-size: 11px; padding: 8px;">
                Phí kiểm tra tại nhà ước tính: <span class="bold">100.000đ</span> (Miễn phí nếu đồng ý sửa chữa và thay thế linh kiện tại siêu thị).
            </div>
            
            <div class="btn text-center" style="margin-top: 15px; width: 100%; box-sizing: border-box;">ĐĂNG KÝ LỊCH HẸN SỬA CHỮA</div>
        </div>
    </div>
</div>
""")

all_wireframes[23] = wrap_admin_layout(23, "QUẢN LÝ PHIẾU SỬ CHỮA (REPAIR TICKETS)", """
<div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">
    <div class="bold">DANH SÁCH PHIẾU HẸN BẢO HÀNH & SỬA CHỮA (REPAIR TICKETS)</div>
    <div class="flex-row">
        [ Lọc trạng thái: Tất cả ]
        [ Nhập mã phiếu, tên KH... ]
        <div class="btn">Tìm kiếm</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Mã Phiếu</th>
            <th>Khách hàng</th>
            <th>Thiết bị sự cố</th>
            <th>Lỗi chẩn đoán ban đầu</th>
            <th>Kỹ thuật viên phụ trách</th>
            <th>Chi phí (VNĐ)</th>
            <th>Trạng thái sửa chữa</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="bold">PSC-001</td>
            <td>Trần Văn D</td>
            <td>Tủ lạnh Samsung Inverter</td>
            <td>Hỏng Block máy nén gas R600a</td>
            <td>Lê Văn C (KT)</td>
            <td class="bold">3.500.000đ</td>
            <td><span class="badge" style="background-color: #ffeeba; color: #856404;">Đang sửa chữa</span></td>
            <td>[Phân công] | [Xem] | [In phiếu]</td>
        </tr>
        <tr>
            <td class="bold">PSC-002</td>
            <td>Nguyễn Thị E</td>
            <td>Máy giặt LG Lồng ngang</td>
            <td>Hỏng bo mạch điều khiển công suất</td>
            <td>Nguyễn Văn A (KT)</td>
            <td class="bold">1.800.000đ</td>
            <td><span class="badge" style="background-color: #d4edda; color: #155724;">Đã hoàn thành</span></td>
            <td>[Phân công] | [Xem] | [In phiếu]</td>
        </tr>
        <tr>
            <td class="bold">PSC-003</td>
            <td>Vũ Văn F</td>
            <td>Tivi Sony 55 inch</td>
            <td>Kẻ sọc màn hình (Lỗi T-Con)</td>
            <td>Chờ phân bổ KT</td>
            <td>Chưa báo giá</td>
            <td><span class="badge" style="background-color: #f8d7da; color: #721c24;">Chờ tiếp nhận</span></td>
            <td>[Phân công] | [Xem] | [In phiếu]</td>
        </tr>
    </tbody>
</table>

<div class="flex-space" style="margin-top: 15px;">
    <div style="font-size: 12px;">Đang hiển thị từ 1 đến 3 của tổng 42 phiếu sửa chữa đang quản lý.</div>
    <div>[Trang trước] [1] [2] [3] [Trang sau]</div>
</div>
""")

all_wireframes[24] = wrap_customer_layout(24, "LIVE TRACKING STEPPER DỊCH VỤ SỬ CHỮA", """
<div style="max-width: 900px; margin: 30px auto;" class="border-box double-border">
    <div class="bold text-center" style="font-size: 16px; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 25px;">THEO DÕI HÀNH TRÌNH SỬ CHỮA THIẾT BỊ #PSC-001</div>
    
    <!-- Vertical or horizontal Stepper representation -->
    <div style="display: flex; justify-content: space-between; position: relative; margin-bottom: 30px; padding: 0 40px;">
        <!-- Step 1: Done -->
        <div style="text-align: center; width: 120px; z-index: 2;">
            <div style="width: 30px; height: 30px; border-radius: 50%; border: 2px solid black; background-color: black; color: white; line-height: 30px; margin: 0 auto; font-weight: bold;">✔</div>
            <div class="bold" style="font-size: 11px; margin-top: 8px;">Tiếp nhận yêu cầu</div>
            <div style="font-size: 10px; color: #555555; margin-top: 3px;">04/06 08:30</div>
        </div>
        
        <!-- Step 2: Done -->
        <div style="text-align: center; width: 120px; z-index: 2;">
            <div style="width: 30px; height: 30px; border-radius: 50%; border: 2px solid black; background-color: black; color: white; line-height: 30px; margin: 0 auto; font-weight: bold;">✔</div>
            <div class="bold" style="font-size: 11px; margin-top: 8px;">Khảo sát & Báo giá</div>
            <div style="font-size: 10px; color: #555555; margin-top: 3px;">04/06 10:15</div>
        </div>
        
        <!-- Step 3: Current -->
        <div style="text-align: center; width: 120px; z-index: 2;">
            <div style="width: 30px; height: 30px; border-radius: 50%; border: 3px double black; background-color: white; color: black; line-height: 30px; margin: 0 auto; font-weight: bold; animation: pulse 2s infinite;">[3]</div>
            <div class="bold" style="font-size: 11px; margin-top: 8px; text-decoration: underline;">Đang tiến hành sửa</div>
            <div style="font-size: 10px; color: #555555; margin-top: 3px;">04/06 14:00</div>
        </div>
        
        <!-- Step 4: Pending -->
        <div style="text-align: center; width: 120px; z-index: 2; opacity: 0.4;">
            <div style="width: 30px; height: 30px; border-radius: 50%; border: 1px dashed black; background-color: white; color: black; line-height: 30px; margin: 0 auto;">4</div>
            <div class="bold" style="font-size: 11px; margin-top: 8px;">Kiểm định & Test</div>
            <div style="font-size: 10px; margin-top: 3px;">Chưa thực hiện</div>
        </div>
        
        <!-- Step 5: Pending -->
        <div style="text-align: center; width: 120px; z-index: 2; opacity: 0.4;">
            <div style="width: 30px; height: 30px; border-radius: 50%; border: 1px dashed black; background-color: white; color: black; line-height: 30px; margin: 0 auto;">5</div>
            <div class="bold" style="font-size: 11px; margin-top: 8px;">Bàn giao thanh toán</div>
            <div style="font-size: 10px; margin-top: 3px;">Chưa thực hiện</div>
        </div>
        
        <!-- Background connector line -->
        <div style="position: absolute; top: 15px; left: 100px; right: 100px; height: 2px; background-color: black; z-index: 1;"></div>
    </div>
    
    <!-- Detail log info -->
    <div class="border-box">
        <div class="bold" style="font-size: 13px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">NHẬT KÝ CHI TIẾT TIẾN TRÌNH:</div>
        <div style="font-size: 12px; line-height: 20px;">
            - <strong>04/06 14:00:</strong> Kỹ thuật viên <span class="bold">Lê Văn C</span> đã bắt đầu tháo tủ lạnh và thay block máy nén mới.<br>
            - <strong>04/06 10:15:</strong> Khách hàng đồng ý phương án thay Block Samsung chính hãng với báo giá 3.500.000đ.<br>
            - <strong>04/06 08:30:</strong> Tiếp nhận yêu cầu kiểm tra tủ lạnh qua cổng Portal. Trạng thái: Chờ khảo sát.
        </div>
    </div>
</div>
""")

all_wireframes[25] = wrap_admin_layout(25, "XUẤT HÓA ĐƠN DỊCH VỤ SỬ CHỮA", """
<div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">CHI TIẾT XUẤT HÓA ĐƠN ĐIỆN TỬ DỊCH VỤ SỬ CHỮA</div>

<div style="display: grid; grid-template-columns: 8fr 4fr; gap: 15px;">
    <!-- Invoice layout left -->
    <div class="border-box double-border">
        <div class="text-center bold" style="font-size: 18px; text-transform: uppercase;">HÓA ĐƠN CHI PHÍ SỬ CHỮA & BẢO HÀNH</div>
        <div class="text-center" style="font-size: 12px; margin-bottom: 15px;">Mã số hóa đơn: HD-SC-0098 | Ngày lập: 2026-06-04</div>
        
        <div style="font-size: 13px; line-height: 20px; margin-bottom: 15px;">
            <strong>Khách hàng:</strong> Trần Văn D<br>
            <strong>Địa chỉ:</strong> Cầu Giấy, Hà Nội<br>
            <strong>Thiết bị:</strong> Tủ lạnh Samsung Inverter (Mã phiếu: PSC-001)
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Nội dung công việc / Linh kiện thay thế</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Block máy nén Samsung Inverter chính hãng</td>
                    <td>1 bộ</td>
                    <td>2.900.000đ</td>
                    <td>2.900.000đ</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Nạp gas R600a cho hệ thống làm lạnh</td>
                    <td>1 lần</td>
                    <td>300.000đ</td>
                    <td>300.000đ</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Tiền công thợ tháo lắp và kiểm tra hệ thống</td>
                    <td>-</td>
                    <td>300.000đ</td>
                    <td>300.000đ</td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 15px; text-align: right; font-size: 13px; line-height: 22px;">
            Cộng tiền hàng: 3.500.000đ<br>
            Thuế giá trị gia tăng (VAT 8%): 280.000đ<br>
            <strong>Tổng tiền thanh toán cuối cùng: 3.780.000đ</strong>
        </div>
    </div>
    
    <!-- Invoice actions right -->
    <div class="border-box">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">THAO TÁC XUẤT BẢN</div>
        
        <div style="margin-bottom: 15px;">
            <label class="bold">Phương thức thanh toán:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 5px;">[ Chuyển khoản VietQR động ]</div>
        </div>
        
        <div class="btn text-center" style="width: 100%; box-sizing: border-box; margin-bottom: 10px;">XUẤT HÓA ĐƠN PDF</div>
        <div class="btn text-center" style="width: 100%; box-sizing: border-box; margin-bottom: 10px; background-color: #f0f0f0;">IN HÓA ĐƠN NHIỆT (80MM)</div>
        <div class="btn text-center" style="width: 100%; box-sizing: border-box; margin-bottom: 15px; border-color: red; color: red;">HỦY HÓA ĐƠN NÀY</div>
        
        <div class="dashed-border" style="font-size: 11px;">
            Hóa đơn sẽ tự động được gửi qua email cho khách hàng ngay sau khi kế toán ấn nút "Xuất hóa đơn PDF".
        </div>
    </div>
</div>
""")

all_wireframes[26] = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
{SHARED_CSS}
.pos-layout {{
    display: grid;
    grid-template-columns: 7fr 5fr;
    gap: 10px;
    height: 600px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">GIAO DIỆN QUẦN THU NGÂN POS CHUYÊN DỤNG (CHỨC NĂNG 7.26)</div>
    
    <div class="pos-layout">
        <!-- POS Left: Product selection -->
        <div class="border-box">
            <div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">
                <div class="bold">DANH SÁCH HÀNG HÓA SIÊU THỊ</div>
                <div style="border: 1px solid black; padding: 4px; width: 250px;">[ Quét barcode hoặc nhập tên sản phẩm... ]</div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                <div class="border-box text-center" style="cursor: pointer; height: 110px;">
                    <div class="bold" style="font-size: 11px;">Tivi Sony 55 inch</div>
                    <div style="font-size: 12px; margin-top: 5px;">15.990.000đ</div>
                    <div style="font-size: 10px; color: #555555; margin-top: 3px;">Tồn: 5 chiếc</div>
                    <div class="btn" style="padding: 2px 5px; font-size: 10px; margin-top: 5px;">THÊM</div>
                </div>
                <div class="border-box text-center" style="cursor: pointer; height: 110px;">
                    <div class="bold" style="font-size: 11px;">Máy giặt LG 9kg</div>
                    <div style="font-size: 12px; margin-top: 5px;">8.490.000đ</div>
                    <div style="font-size: 10px; color: #555555; margin-top: 3px;">Tồn: 2 chiếc</div>
                    <div class="btn" style="padding: 2px 5px; font-size: 10px; margin-top: 5px;">THÊM</div>
                </div>
                <div class="border-box text-center" style="cursor: pointer; height: 110px;">
                    <div class="bold" style="font-size: 11px;">Tủ lạnh Samsung</div>
                    <div style="font-size: 12px; margin-top: 5px;">10.190.000đ</div>
                    <div style="font-size: 10px; color: #555555; margin-top: 3px;">Tồn: 0 chiếc</div>
                    <div class="btn-disabled" style="padding: 2px 5px; font-size: 10px; margin-top: 5px;">HẾT HÀNG</div>
                </div>
            </div>
        </div>
        
        <!-- POS Right: Cart bill -->
        <div class="border-box double-border" style="display: flex; flex-direction: column;">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">ĐƠN HÀNG ĐANG THANH TOÁN</div>
            
            <div style="flex: 1; overflow-y: auto; font-size: 12px; line-height: 22px;">
                1. Tivi Sony 55 inch x 1 | Giá: 15.990.000đ <a href="#" style="color: black;">[x]</a><br>
                2. Máy giặt LG 9kg x 1 | Giá: 8.490.000đ <a href="#" style="color: black;">[x]</a><br>
                ----------------------------------------<br>
                <strong>Tạm tính:</strong> 24.480.000 đ<br>
                <strong>Chiết khấu VIP (Hạng Kim Cương 5%):</strong> -1.224.000đ<br>
                <strong>Thuế VAT (8%):</strong> 1.860.480đ<br>
                <strong>Tổng thanh toán:</strong> 25.116.480 đ<br>
            </div>
            
            <div style="border-top: 1px dashed black; padding-top: 10px; margin-top: 10px;">
                <div style="margin-bottom: 10px;">
                    <label class="bold">Khách hàng thành viên:</label>
                    <div style="border: 1px solid black; padding: 4px; margin-top: 3px;">[ SDT: 0901234567 - Nguyễn Văn An ]</div>
                </div>
                <div class="flex-row">
                    <div class="btn" style="flex: 1; font-size: 11px; background-color: #f0f0f0;">CHIA PHẦN THANH TOÁN</div>
                    <div class="btn" style="flex: 1; font-size: 12px; border-width: 3px;">THANH TOÁN (PAY)</div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>"""

all_wireframes[27] = wrap_customer_layout(27, "SPLIT PAYMENT (THANH TOÁN CHIA PHẦN)", """
<div style="max-width: 700px; margin: 30px auto;" class="border-box double-border">
    <div class="bold text-center" style="font-size: 16px; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 20px;">BIỂU MẪU CHIA PHẦN THANH TOÁN ĐƠN HÀNG (SPLIT PAYMENT)</div>
    
    <div class="border-box" style="margin-bottom: 15px; background-color: #fafafa;">
        <div class="flex-space">
            <div>Tổng số tiền cần phải thanh toán:</div>
            <div class="bold" style="font-size: 16px;">25.116.480 VNĐ</div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div class="border-box">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">PHÂN CHIA HÌNH THỨC</div>
            
            <div style="margin-bottom: 10px;">
                <label>1. Thanh toán Tiền mặt:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ 10.116.480 VNĐ ]</div>
            </div>
            <div style="margin-bottom: 10px;">
                <label>2. Quẹt thẻ ngân hàng (POS):</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ 10.000.000 VNĐ ]</div>
            </div>
            <div style="margin-bottom: 10px;">
                <label>3. Quét mã QR chuyển khoản:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ 5.000.000 VNĐ ]</div>
            </div>
        </div>
        
        <div class="border-box" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">ĐỐI SOÁT SỐ LIỆU</div>
                <div style="font-size: 13px; line-height: 24px;">
                    Tổng số tiền đã nhập: 25.116.480 VNĐ<br>
                    Tiền mặt: 10.116.480 VNĐ<br>
                    Ngân hàng: 15.000.000 VNĐ<br>
                    -----------------------------<br>
                    Còn lại cần thu: <span class="bold" style="color: green;">0 VNĐ (Hoàn tất)</span>
                </div>
            </div>
            
            <div class="btn text-center" style="width: 100%; box-sizing: border-box;">XÁC NHẬN THANH TOÁN</div>
        </div>
    </div>
</div>
""")

all_wireframes[28] = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
{SHARED_CSS}
body {{
    background-color: #e0e0e0;
}}
.thermal-receipt {{
    width: 380px;
    border: 1px dashed #000000;
    padding: 15px;
    background-color: #ffffff;
    font-size: 12px;
    line-height: 20px;
    margin: 20px auto;
    box-sizing: border-box;
}}
</style>
</head>
<body>
<div class="thermal-receipt">
    <div class="text-center bold" style="font-size: 15px; text-transform: uppercase;">SIÊU THỊ ĐIỆN MÁY DIENMAYPRO</div>
    <div class="text-center">Địa chỉ: 144 Xuân Thủy, Cầu Giấy, Hà Nội<br>Điện thoại: 1800.6699</div>
    <div style="border-bottom: 1px dashed black; margin: 10px 0;"></div>
    
    <div class="text-center bold" style="font-size: 13px;">HÓA ĐƠN BÁN HÀNG TẠI QUẦY</div>
    <div class="text-center" style="font-size: 11px;">Số: POS-009875 | Ngày: 2026-06-04 23:45</div>
    <div style="border-bottom: 1px dashed black; margin: 10px 0;"></div>
    
    <div style="font-size: 11px;">
        Thu ngân: Trần Thị B<br>
        Khách hàng: Nguyễn Văn An (VIP Kim Cương)
    </div>
    <div style="border-bottom: 1px dashed black; margin: 10px 0;"></div>
    
    <!-- Receipt Items -->
    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
        <thead>
            <tr style="border-bottom: 1px dashed black;">
                <th style="border: none; text-align: left; padding: 2px;">Tên hàng</th>
                <th style="border: none; text-align: right; padding: 2px;">SL</th>
                <th style="border: none; text-align: right; padding: 2px;">T.Tiền</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: none; padding: 2px;">Tivi Sony 55 inch</td>
                <td style="border: none; text-align: right; padding: 2px;">1</td>
                <td style="border: none; text-align: right; padding: 2px;">15,990,000</td>
            </tr>
            <tr>
                <td style="border: none; padding: 2px;">Máy giặt LG 9kg</td>
                <td style="border: none; text-align: right; padding: 2px;">1</td>
                <td style="border: none; text-align: right; padding: 2px;">8,490,000</td>
            </tr>
        </tbody>
    </table>
    <div style="border-bottom: 1px dashed black; margin: 10px 0;"></div>
    
    <!-- Total payment block -->
    <div style="font-size: 11px; line-height: 18px;">
        <div class="flex-space">
            <div>Tổng tiền hàng:</div>
            <div>24,480,000 đ</div>
        </div>
        <div class="flex-space">
            <div>VIP Discount (5%):</div>
            <div>-1,224,000 đ</div>
        </div>
        <div class="flex-space">
            <div>Thuế VAT (8%):</div>
            <div>1,860,480 đ</div>
        </div>
        <div class="flex-space" style="font-weight: bold; font-size: 12px; margin-top: 5px;">
            <div>TỔNG CỘNG:</div>
            <div>25,116,480 đ</div>
        </div>
    </div>
    
    <div style="border-bottom: 1px dashed black; margin: 10px 0;"></div>
    <div style="font-size: 11px; line-height: 18px;">
        <div class="flex-space">
            <div>Khách đưa (Tiền mặt):</div>
            <div>10,116,480 đ</div>
        </div>
        <div class="flex-space">
            <div>Khách đưa (Thẻ):</div>
            <div>15,000,000 đ</div>
        </div>
        <div class="flex-space">
            <div>Tiền thối lại:</div>
            <div>0 đ</div>
        </div>
    </div>
    
    <div style="border-bottom: 1px dashed black; margin: 10px 0;"></div>
    <div class="text-center bold" style="font-size: 11px; margin-top: 10px;">CẢM ƠN QUÝ KHÁCH HÀNG & HẸN GẶP LẠI!<br>Powered by DIENMAYPRO.VN</div>
</div>
</body>
</html>"""

all_wireframes[29] = wrap_admin_layout(29, "QUẢN LÝ NHÀ CUNG CẤP", """
<div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">
    <div class="bold">DANH SÁCH NHÀ CUNG CẤP ĐỐI TÁC HỆ THỐNG (SUPPLIERS)</div>
    <div class="flex-row">
        [ Nhập tên, số điện thoại nhà cung cấp... ]
        <div class="btn">+ THÊM NHÀ CUNG CẤP</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Mã NCC</th>
            <th>Tên đối tác / Thương hiệu</th>
            <th>Người đại diện</th>
            <th>Điện thoại</th>
            <th>Địa chỉ trụ sở chính</th>
            <th>Nhóm mặt hàng</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="bold">NCC-001</td>
            <td>Tổng kho Điện máy Miền Bắc</td>
            <td>Phạm Văn Long</td>
            <td>024.339988</td>
            <td>KCN Từ Liêm, Từ Liêm, Hà Nội</td>
            <td>Tivi, Dàn âm thanh, Loa kéo</td>
            <td>[Sửa] | [Liên hệ] | [Lịch sử nhập]</td>
        </tr>
        <tr>
            <td class="bold">NCC-002</td>
            <td>Công ty TNHH Phân phối Toshiba VN</td>
            <td>Trần Minh Tâm</td>
            <td>028.776655</td>
            <td>Quận 1, Thành phố Hồ Chí Minh</td>
            <td>Tủ lạnh, Máy giặt, Lò vi sóng</td>
            <td>[Sửa] | [Liên hệ] | [Lịch sử nhập]</td>
        </tr>
        <tr>
            <td class="bold">NCC-003</td>
            <td>Hoàng Gia Điện Lạnh Parts</td>
            <td>Lê Thị Hồng</td>
            <td>0912.445566</td>
            <td>Thanh Xuân, Hà Nội</td>
            <td>Linh kiện thay thế, Gas R600a</td>
            <td>[Sửa] | [Liên hệ] | [Lịch sử nhập]</td>
        </tr>
    </tbody>
</table>

<div class="flex-space" style="margin-top: 15px;">
    <div>Đang hiển thị 3 trên tổng 12 nhà cung cấp.</div>
    <div>[Trang trước] [1] [Trang sau]</div>
</div>
""")

all_wireframes[30] = wrap_admin_layout(30, "PHIẾU NHẬP KHO VÀ QUẢN LÝ IMEI", """
<div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">TẠO PHIẾU NHẬP KHO HÀNG HÓA VÀ NHẬP IMEI CHI TIẾT</div>

<div style="display: grid; grid-template-columns: 5fr 7fr; gap: 15px;">
    <!-- Left form elements -->
    <div class="border-box double-border">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">THÔNG TIN PHIẾU NHẬP</div>
        <div style="margin-bottom: 10px;">
            <label class="bold">1. Lựa chọn Nhà cung cấp:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Lựa chọn: Tổng kho Điện máy Miền Bắc ]</div>
        </div>
        <div style="margin-bottom: 10px;">
            <label class="bold">2. Lựa chọn Sản phẩm nhập kho:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Lựa chọn: Smart Tivi Sony 4K 55" ]</div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
            <div>
                <label class="bold">Số lượng nhập:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ 3 ]</div>
            </div>
            <div>
                <label class="bold">Đơn giá nhập (VNĐ):</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ 12.500.000 ]</div>
            </div>
        </div>
        
        <div class="btn text-center" style="margin-top: 30px; width: 100%; box-sizing: border-box;">THÊM VÀO PHIẾU NHẬP</div>
    </div>
    
    <!-- Right IMEI list and confirmation -->
    <div class="border-box">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">DANH SÁCH MÃ IMEI/SERIAL CỦA THIẾT BỊ</div>
        
        <div style="margin-bottom: 15px;">
            <label class="bold">Nhập danh sách mã IMEI (Mỗi mã trên 1 dòng):</label>
            <div style="border: 1px solid black; padding: 8px; margin-top: 5px; font-family: monospace; height: 100px;">
                SONY55B8877112<br>
                SONY55B8877113<br>
                SONY55B8877114
            </div>
            <span style="font-size: 11px; color: #555555;">(Số lượng dòng nhập vào phải khớp đúng với Số lượng nhập ở cột bên trái)</span>
        </div>
        
        <div class="border-box" style="font-size: 12px; background-color: #fafafa; margin-bottom: 15px;">
            <strong>Tóm tắt phiếu nhập:</strong><br>
            - Smart Tivi Sony 55" x 3 cái | Giá: 12.500.000đ = 37.500.000đ<br>
            --------------------------------------------------------<br>
            <strong>Tổng tiền nhập hàng: 37.500.000 VNĐ</strong>
        </div>
        
        <div class="btn text-center" style="width: 100%; box-sizing: border-box;">HOÀN TẤT VÀ LƯU PHIẾU NHẬP KHO</div>
    </div>
</div>
""")

all_wireframes[31] = wrap_admin_layout(31, "ĐỒNG BỘ TỒN KHO ĐA KÊNH", """
<div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">
    <div class="bold">TRẠNG THÁI KẾT NỐI VÀ ĐỒNG BỘ KHO ĐA KÊNH (MULTICHANNEL SYNC LOGS)</div>
    <div class="btn">ĐỒNG BỘ TẤT CẢ CÁC KÊNH</div>
</div>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">
    <div class="border-box text-center double-border">
        <div class="bold">1. WEBSITE CHÍNH THỨC</div>
        <div style="font-size: 12px; margin-top: 5px; color: green;">Trạng thái: Đang kết nối</div>
        <div style="font-size: 11px; margin-top: 3px;">Tự động đồng bộ: Tức thì (Realtime)</div>
        <div style="font-size: 10px; color: #555555; margin-top: 5px;">Đồng bộ cuối: 1 phút trước</div>
        <div class="btn" style="font-size: 10px; margin-top: 10px;">ĐỒNG BỘ LẠI</div>
    </div>
    
    <div class="border-box text-center double-border">
        <div class="bold">2. GIAN HÀNG SHOPEE MALL</div>
        <div style="font-size: 12px; margin-top: 5px; color: green;">Trạng thái: Đang kết nối</div>
        <div style="font-size: 11px; margin-top: 3px;">API Sync: Mỗi 15 phút</div>
        <div style="font-size: 10px; color: #555555; margin-top: 5px;">Đồng bộ cuối: 12 phút trước</div>
        <div class="btn" style="font-size: 10px; margin-top: 10px;">ĐỒNG BỘ LẠI</div>
    </div>
    
    <div class="border-box text-center double-border">
        <div class="bold">3. GIAN HÀNG LAZADA MALL</div>
        <div style="font-size: 12px; margin-top: 5px; color: red;">Trạng thái: Lỗi Token API</div>
        <div style="font-size: 11px; margin-top: 3px;">API Sync: Bị ngắt quãng</div>
        <div style="font-size: 10px; color: #555555; margin-top: 5px;">Thất bại: 2 giờ trước</div>
        <div class="btn" style="font-size: 10px; margin-top: 10px; border-color: red; color: red;">KẾT NỐI LẠI</div>
    </div>
</div>

<div class="border-box">
    <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">NHẬT KÝ ĐỒNG BỘ KHÔNG THÀNH CÔNG GẦN ĐÂY:</div>
    <table>
        <thead>
            <tr>
                <th>Mã SKU</th>
                <th>Sản phẩm</th>
                <th>Kênh gặp lỗi</th>
                <th>Nguyên nhân</th>
                <th>Khắc phục đề xuất</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="bold">TV-SONY-55-B</td>
                <td>Smart Tivi Sony 4K 55"</td>
                <td>Shopee Mall</td>
                <td>Lỗi chênh lệch giá trên sàn (Giá sàn > Giá web)</td>
                <td>Cập nhật lại giá sàn phù hợp</td>
            </tr>
        </tbody>
    </table>
</div>
""")

all_wireframes[32] = wrap_admin_layout(32, "CẢNH BÁO TỒN KHO AN TOÀN", """
<div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">
    <div class="bold">DANH SÁCH SẢN PHẨM DƯỚI NGƯỠNG AN TOÀN (SAFE STOCK ALERT)</div>
    <div class="btn" style="background-color: #f0f0f0;">+ TẠO PHIẾU YÊU CẦU NHẬP HÀNG LOẠT</div>
</div>

<table>
    <thead>
        <tr>
            <th>Mã SKU</th>
            <th>Tên sản phẩm</th>
            <th>Tồn kho thực tế</th>
            <th>Ngưỡng an toàn</th>
            <th>Mức độ cảnh báo</th>
            <th>Nhà cung cấp đề xuất</th>
            <th>Thao tác khẩn cấp</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="bold">TL-SAM-320</td>
            <td>Tủ lạnh Samsung 320L</td>
            <td class="bold" style="color: red;">1 chiếc</td>
            <td>5 chiếc</td>
            <td><span class="badge" style="background-color: #f8d7da; color: #721c24; border-color: red;">Nguy kịch</span></td>
            <td>Toshiba Việt Nam</td>
            <td><div class="btn" style="font-size: 11px; padding: 3px 6px;">Nhập hàng</div></td>
        </tr>
        <tr>
            <td class="bold">MG-LG-9KG</td>
            <td>Máy giặt LG lồng ngang 9kg</td>
            <td class="bold" style="color: orange;">2 chiếc</td>
            <td>3 chiếc</td>
            <td><span class="badge" style="background-color: #ffeeba; color: #856404;">Cảnh báo</span></td>
            <td>Tổng kho Miền Bắc</td>
            <td><div class="btn" style="font-size: 11px; padding: 3px 6px;">Nhập hàng</div></td>
        </tr>
        <tr>
            <td class="bold">TV-SON-55</td>
            <td>Smart Tivi Sony 55 inch</td>
            <td class="bold" style="color: red;">0 chiếc</td>
            <td>4 chiếc</td>
            <td><span class="badge" style="background-color: #f8d7da; color: #721c24; border-color: red;">Hết hàng</span></td>
            <td>Tổng kho Miền Bắc</td>
            <td><div class="btn" style="font-size: 11px; padding: 3px 6px; border-color: red; color: red;">Tạo đơn gấp</div></td>
        </tr>
    </tbody>
</table>

<div class="dashed-border" style="margin-top: 15px; font-size: 11px;">
    <strong>Ghi chú quy trình:</strong> Hệ thống tự động gửi email cảnh báo đến Thủ kho và Bộ phận Mua hàng (Procurement) lúc 08:00 sáng hàng ngày nếu phát hiện bất kỳ sản phẩm nào có số lượng tồn kho khả dụng thấp hơn ngưỡng cảnh báo thiết lập.
</div>
""")

all_wireframes[33] = wrap_admin_layout(33, "ĐIỀU CHUYỂN KHO NỘI BỘ", """
<div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">LẬP PHIẾU ĐIỀU CHUYỂN KHO NỘI BỘ GIỮA CÁC CHI NHÁNH</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
    <!-- Form setup -->
    <div class="border-box double-border">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">THIẾT LẬP TUYẾN ĐIỀU CHUYỂN</div>
        
        <div style="margin-bottom: 10px;">
            <label class="bold">Kho xuất hàng (Source Warehouse):</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Lựa chọn: Tổng kho Cầu Giấy ]</div>
        </div>
        <div style="margin-bottom: 10px;">
            <label class="bold">Kho nhận hàng (Destination Warehouse):</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Lựa chọn: Chi nhánh Đống Đa ]</div>
        </div>
        <div style="margin-bottom: 10px;">
            <label class="bold">Sản phẩm điều chuyển:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Lựa chọn: Tủ lạnh Samsung 320L ]</div>
        </div>
        <div style="margin-bottom: 15px;">
            <label class="bold">Số lượng chuyển:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ 2 ]</div>
        </div>
        
        <div class="btn text-center" style="width: 100%; box-sizing: border-box;">THÊM VÀO DANH SÁCH ĐIỀU CHUYỂN</div>
    </div>
    
    <!-- Summary and submit -->
    <div class="border-box">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">DANH SÁCH ĐIỀU CHUYỂN TẠM THỜI</div>
        
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Lý do điều chuyển</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tủ lạnh Samsung 320L</td>
                    <td>2 chiếc</td>
                    <td>Chi nhánh Đống Đa hết hàng trưng bày</td>
                    <td>[Xóa]</td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 60px;">
            <label class="bold">Nhân viên chịu trách nhiệm vận chuyển:</label>
            <div style="border: 1px solid black; padding: 6px; margin-top: 3px; margin-bottom: 15px;">[ Nguyễn Văn X (Tài xế công ty) ]</div>
            
            <div class="btn text-center" style="width: 100%; box-sizing: border-box;">PHÊ DUYỆT & XUẤT PHIẾU ĐIỀU CHUYỂN KHO</div>
        </div>
    </div>
</div>
""")

all_wireframes[34] = wrap_admin_layout(34, "KIỂM KÊ VÀ CÂN BẰNG KHO", """
<div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">
    <div class="bold">PHIẾU KIỂM KÊ KHO HÀNG THỰC TẾ (INVENTORY AUDIT & BALANCE SHEET)</div>
    <div style="font-size: 12px;">Mã phiếu kiểm: PK-0098 | Ngày kiểm: 2026-06-04</div>
</div>

<table>
    <thead>
        <tr>
            <th>Mã SKU</th>
            <th>Tên sản phẩm</th>
            <th>Tồn hệ thống (Book Stock)</th>
            <th>Kiểm đếm thực tế (Physical Count)</th>
            <th>Chênh lệch (Variance)</th>
            <th>Lý do chênh lệch</th>
            <th>Hành động cân bằng</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="bold">TL-SAM-320</td>
            <td>Tủ lạnh Samsung 320L</td>
            <td>5 chiếc</td>
            <td>5 chiếc</td>
            <td class="bold">0</td>
            <td>Khớp tuyệt đối</td>
            <td>Không thao tác</td>
        </tr>
        <tr>
            <td class="bold">MG-LG-9KG</td>
            <td>Máy giặt LG lồng ngang 9kg</td>
            <td>3 chiếc</td>
            <td>2 chiếc</td>
            <td class="bold" style="color: red;">-1 chiếc</td>
            <td>Hao hụt chưa rõ nguyên nhân</td>
            <td>[x] Tạo phiếu xuất cân bằng âm</td>
        </tr>
        <tr>
            <td class="bold">TV-SON-55</td>
            <td>Smart Tivi Sony 55 inch</td>
            <td>1 chiếc</td>
            <td>2 chiếc</td>
            <td class="bold" style="color: green;">+1 chiếc</td>
            <td>Nhập kho quên quét mã vạch</td>
            <td>[x] Tạo phiếu nhập cân bằng dương</td>
        </tr>
    </tbody>
</table>

<div class="dashed-border" style="margin-top: 15px; text-align: left;">
    <div class="bold">LƯU Ý CÂN BẰNG KHO (AUTO ADJUSTMENT):</div>
    <div style="font-size: 11px; margin-top: 5px; line-height: 18px;">
        Khi ấn nút "Cân bằng kho và Khóa số liệu", hệ thống sẽ tự động tạo các phiếu xuất/nhập phụ để cân bằng tồn kho thực tế khớp với tồn hệ thống, đồng thời ghi nhận lịch sử và người thực hiện vào cột nhật ký.
    </div>
</div>

<div class="flex-space" style="margin-top: 15px;">
    <div class="btn" style="border-color: red; color: red;">HỦY PHIẾU KIỂM KÊ</div>
    <div class="btn" style="border-width: 3px;">XÁC NHẬN CÂN BẰNG KHO & KHÓA SỐ LIỆU</div>
</div>
""")

all_wireframes[35] = wrap_admin_layout(35, "LỊCH SỬ BIẾN ĐỘNG KHO (INVENTORY LOGS)", """
<div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">
    <div class="bold">NHẬT KÝ THAY ĐỔI VÀ BIẾN ĐỘNG KHO (INVENTORY TRANSACTION LOGS)</div>
    <div class="flex-row">
        [ Lọc theo loại biến động: Tất cả ]
        [ Nhập SKU sản phẩm... ]
        <div class="btn">Tìm kiếm</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Thời gian</th>
            <th>Mã SKU</th>
            <th>Tên sản phẩm</th>
            <th>Loại biến động</th>
            <th>Số lượng thay đổi</th>
            <th>Số lượng sau biến động</th>
            <th>Mã chứng từ liên kết</th>
            <th>Nhân viên thực hiện</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>2026-06-04 23:10:45</td>
            <td class="bold">TL-SAM-320</td>
            <td>Tủ lạnh Samsung 320L</td>
            <td>Nhập hàng từ nhà cung cấp</td>
            <td class="bold" style="color: green;">+10</td>
            <td class="bold">12 chiếc</td>
            <td>PN-00985</td>
            <td>Trần Minh Tâm (Thủ kho)</td>
        </tr>
        <tr>
            <td>2026-06-04 22:45:12</td>
            <td class="bold">MG-LG-9KG</td>
            <td>Máy giặt LG lồng ngang 9kg</td>
            <td>Xuất hàng bán (Quầy POS)</td>
            <td class="bold" style="color: red;">-1</td>
            <td class="bold">1 chiếc</td>
            <td>HD-POS-0098</td>
            <td>Trần Thị B (Thu ngân)</td>
        </tr>
        <tr>
            <td>2026-06-04 21:00:00</td>
            <td class="bold">TV-SON-55</td>
            <td>Smart Tivi Sony 55 inch</td>
            <td>Cân bằng kho (Sau kiểm kê)</td>
            <td class="bold" style="color: red;">-1</td>
            <td class="bold">0 chiếc</td>
            <td>PK-0098</td>
            <td>Admin (Cân bằng)</td>
        </tr>
        <tr>
            <td>2026-06-03 15:30:22</td>
            <td class="bold">TV-SON-55</td>
            <td>Smart Tivi Sony 55 inch</td>
            <td>Điều chuyển đi chi nhánh khác</td>
            <td class="bold" style="color: red;">-2</td>
            <td class="bold">1 chiếc</td>
            <td>PDC-0012</td>
            <td>Trần Minh Tâm (Thủ kho)</td>
        </tr>
    </tbody>
</table>

<div class="flex-space" style="margin-top: 15px;">
    <div>Hiển thị 1 - 4 trên tổng số 1,250 giao dịch kho.</div>
    <div>[Trang trước] [1] [2] [3] ... [Trang sau]</div>
</div>
""")

all_wireframes[42] = wrap_admin_layout(42, "SỔ QUỸ THU CHI (CASHBOOK)", """
<div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">BÁO CÁO DÒNG TIỀN VÀ SỔ QUỸ THU CHI (CASHBOOK LEDGER)</div>

<!-- Balance dashboard cards -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">
    <div class="border-box text-center double-border">
        <div class="bold">TỔNG QUỸ TIỀN MẶT</div>
        <div style="font-size: 20px; font-weight: bold; margin-top: 5px;">45.230.000đ</div>
        <div style="font-size: 11px; margin-top: 3px; color: #555555;">Két sắt tại quầy thu ngân</div>
    </div>
    
    <div class="border-box text-center double-border">
        <div class="bold">TỔNG QUỸ TIỀN GỬI (NGÂN HÀNG)</div>
        <div style="font-size: 20px; font-weight: bold; margin-top: 5px;">1.254.900.000đ</div>
        <div style="font-size: 11px; margin-top: 3px; color: #555555;">Tài khoản Vietcombank / PayOS</div>
    </div>
    
    <div class="border-box text-center double-border">
        <div class="bold">TỔNG THU CHI TRONG THÁNG</div>
        <div style="font-size: 14px; font-weight: bold; margin-top: 5px; color: green;">Thu: +240M</div>
        <div style="font-size: 14px; font-weight: bold; color: red;">Chi: -115M</div>
    </div>
</div>

<div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px; margin-top: 20px;">
    <div class="bold">LỊCH SỬ GIAO DỊCH DÒNG TIỀN</div>
    <div class="flex-row">
        <div class="btn" style="background-color: #d4edda; color: #155724;">+ THU QUỸ (INCOME)</div>
        <div class="btn" style="background-color: #f8d7da; color: #721c24;">- CHI QUỸ (EXPENSE)</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Mã GD</th>
            <th>Thời gian</th>
            <th>Loại giao dịch</th>
            <th>Hạng mục</th>
            <th>Số tiền (VNĐ)</th>
            <th>Tài khoản đối ứng</th>
            <th>Nhân viên lập</th>
            <th>Ghi chú diễn giải</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="bold">GD-0098</td>
            <td>2026-06-04 23:45</td>
            <td style="color: green; font-weight: bold;">Thu tiền</td>
            <td>Bán hàng tại quầy</td>
            <td class="bold">+25.116.480đ</td>
            <td>Tiền mặt + Thẻ</td>
            <td>Trần Thị B</td>
            <td>Hóa đơn bán hàng quầy POS #HD-0098</td>
        </tr>
        <tr>
            <td class="bold">GD-0097</td>
            <td>2026-06-04 15:30</td>
            <td style="color: red; font-weight: bold;">Chi tiền</td>
            <td>Nhập hàng hóa</td>
            <td class="bold">-37.500.000đ</td>
            <td>Vietcombank</td>
            <td>Trần Minh Tâm</td>
            <td>Thanh toán phiếu nhập kho #PN-00985</td>
        </tr>
        <tr>
            <td class="bold">GD-0096</td>
            <td>2026-06-03 17:00</td>
            <td style="color: red; font-weight: bold;">Chi tiền</td>
            <td>Chi phí vận hành</td>
            <td class="bold">-2.500.000đ</td>
            <td>Tiền mặt</td>
            <td>Admin</td>
            <td>Chi tiền điện tháng 05 chi nhánh Cầu Giấy</td>
        </tr>
    </tbody>
</table>

<div class="flex-space" style="margin-top: 15px;">
    <div class="btn">XUẤT FILE SỔ SÁCH BÁO CÁO THUẾ</div>
    <div>[Trang trước] [1] [2] [Trang sau]</div>
</div>
""")

all_wireframes[43] = wrap_customer_layout(43, "VIDEO REVIEW VÀ ĐĂNG TẢI TRUYỀN THÔNG", """
<div style="max-width: 950px; margin: 20px auto; display: grid; grid-template-columns: 8fr 4fr; gap: 15px;">
    <!-- Left: Video Player representation -->
    <div class="border-box double-border">
        <div class="bold" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">VIDEO TRẢI NGHIỆM THỰC TẾ: TIVI SONY BRAVIA XR OLED</div>
        
        <div class="dashed-border" style="height: 380px; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #222222; color: #ffffff; position: relative;">
            <div style="font-size: 64px; cursor: pointer;">▶</div>
            <div style="margin-top: 15px; font-size: 14px;">[ Video Reviewer Đánh giá độ sáng, tương phản màn hình Bravia XR ]</div>
            
            <!-- Video Control Bar mockup -->
            <div style="position: absolute; bottom: 0; left: 0; right: 0; background-color: rgba(0,0,0,0.8); padding: 8px; display: flex; justify-content: space-between; font-size: 11px;">
                <div>▶ Play | 🔊 80% | 02:45 / 10:12</div>
                <div style="width: 50%; height: 5px; background: #555555; margin-top: 6px; position: relative;">
                    <div style="width: 27%; height: 100%; background: white;"></div>
                </div>
                <div>[CC] Phụ đề | HD 1080p | ⛶ Fullscreen</div>
            </div>
        </div>
        
        <!-- Video description and rating -->
        <div style="margin-top: 15px; font-size: 13px; line-height: 20px;">
            <strong>Mô tả chi tiết:</strong> Đánh giá chi tiết mẫu tivi Sony Bravia OLED thế hệ mới. Trải nghiệm trực quan góc nhìn, khả năng xử lý hình ảnh chuyển động nhanh bằng chip XR Cognitive.<br>
            <strong>Đánh giá video:</strong> 4.9/5 Sao (★★★★★) - 1,245 lượt thích.
        </div>
    </div>
    
    <!-- Right: Related technology articles -->
    <div class="border-box">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">BÀI VIẾT CÔNG NGHỆ LIÊN QUAN</div>
        
        <div class="border-box" style="margin-bottom: 10px; font-size: 12px; cursor: pointer;">
            <div class="bold">So sánh Tivi OLED và QLED: Nên chọn công nghệ nào?</div>
            <div style="margin-top: 5px; color: #555555;">Đăng ngày: 01/06/2026 | Lượt xem: 8.4K</div>
        </div>
        
        <div class="border-box" style="margin-bottom: 10px; font-size: 12px; cursor: pointer;">
            <div class="bold">Hướng dẫn tối ưu cài đặt hình ảnh khi xem bóng đá</div>
            <div style="margin-top: 5px; color: #555555;">Đăng ngày: 28/05/2026 | Lượt xem: 3.2K</div>
        </div>
        
        <div class="border-box" style="margin-bottom: 15px; font-size: 12px; cursor: pointer;">
            <div class="bold">Top 5 Smart Tivi đáng mua nhất dịp hè 2026</div>
            <div style="margin-top: 5px; color: #555555;">Đăng ngày: 20/05/2026 | Lượt xem: 12K</div>
        </div>
        
        <div class="btn text-center" style="width: 100%; box-sizing: border-box; font-size: 12px;">ĐĂNG BÌNH LUẬN TRÊN VIDEO</div>
    </div>
</div>
""")

all_wireframes[44] = wrap_customer_layout(44, "CHẾ ĐỘ ĐA NGÔN NGỮ (LOCALIZATION)", """
<div style="max-width: 650px; margin: 45px auto;" class="border-box double-border">
    <div class="bold text-center" style="font-size: 16px; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 20px;">CÀI ĐẶT NGÔN NGỮ VÀ TIỀN TỆ HỆ THỐNG</div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
        <div class="border-box">
            <label class="bold">1. Ngôn ngữ hiển thị (Language):</label>
            <div style="margin-top: 8px;">
                <label><input type="radio" name="lang" checked> Tiếng Việt (Vietnamese)</label><br>
                <label><input type="radio" name="lang"> English (Tiếng Anh)</label><br>
                <label><input type="radio" name="lang"> 日本語 (Tiếng Nhật)</label><br>
                <label><input type="radio" name="lang"> 中文 (Tiếng Trung)</label>
            </div>
        </div>
        
        <div class="border-box">
            <label class="bold">2. Đơn vị tiền tệ (Currency):</label>
            <div style="margin-top: 8px;">
                <label><input type="radio" name="curr" checked> VNĐ (Việt Nam Đồng - ₫)</label><br>
                <label><input type="radio" name="curr"> USD (Đô la Mỹ - $)</label><br>
                <label><input type="radio" name="curr"> JPY (Yên Nhật - ¥)</label><br>
                <label><input type="radio" name="curr"> EUR (Euro - €)</label>
            </div>
        </div>
    </div>
    
    <!-- Translation Preview area -->
    <div class="border-box" style="background-color: #fafafa; font-size: 12px; line-height: 20px;">
        <div class="bold" style="border-bottom: 1px dashed black; padding-bottom: 3px; margin-bottom: 8px;">BẢN XEM TRƯỚC BẢN DỊCH (TRANSLATION PREVIEW)</div>
        - <strong>Tiêu đề giỏ hàng:</strong> Giỏ hàng của bạn / Your Shopping Cart<br>
        - <strong>Giá trị thanh toán:</strong> 15.990.000đ / $639.60<br>
        - <strong>Mút hành động:</strong> THANH TOÁN NGAY / CHECKOUT NOW
    </div>
    
    <div class="flex-space" style="margin-top: 20px;">
        <div class="btn" style="font-size: 12px;">KHÔI PHỤC CÀI ĐẶT MẶC ĐỊNH</div>
        <div class="btn" style="font-size: 12px; border-width: 3px;">ÁP DỤNG THAY ĐỔI</div>
    </div>
</div>
""")

all_wireframes[45] = wrap_customer_layout(45, "ĐỔI TRẢ VÀ HOÀN TIỀN (RETURNS & REFUNDS)", """
<div style="max-width: 800px; margin: 20px auto;" class="border-box double-border">
    <div class="bold text-center" style="font-size: 18px; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 20px;">YÊU CẦU ĐỔI TRẢ HÀNG VÀ HOÀN TIỀN DỊCH VỤ</div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <!-- Left details return form -->
        <div class="border-box">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">1. THÔNG TIN PHIẾU ĐỔI TRẢ</div>
            <div style="margin-bottom: 10px;">
                <label class="bold">Chọn Đơn hàng đổi trả:</label>
                <div style="border: 1px solid black; padding: 6px; margin-top: 3px;">[ Đơn hàng #HD-0098 - Mua ngày 04/06 ]</div>
            </div>
            
            <div style="margin-bottom: 10px;">
                <label class="bold">Lý do yêu cầu đổi trả:</label>
                <div style="margin-top: 5px; font-size: 12px;">
                    <label><input type="radio" name="reason" checked> Sản phẩm lỗi kỹ thuật do nhà sản xuất</label><br>
                    <label><input type="radio" name="reason"> Giao sai mẫu mã, kích thước, màu sắc</label><br>
                    <label><input type="radio" name="reason"> Sản phẩm bị vỡ hỏng trong quá trình vận chuyển</label>
                </div>
            </div>
            
            <div style="margin-bottom: 10px;">
                <label class="bold">Phương thức mong muốn:</label>
                <div style="margin-top: 5px; font-size: 12px;">
                    <label><input type="radio" name="method" checked> Đổi sản phẩm cùng loại mới tinh</label><br>
                    <label><input type="radio" name="method"> Hoàn tiền 100% về tài khoản ngân hàng</label>
                </div>
            </div>
        </div>
        
        <!-- Right media upload and bank info -->
        <div class="border-box">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 10px;">2. MINH CHỨNG VÀ HOÀN TIỀN</div>
            
            <div style="margin-bottom: 15px;">
                <label class="bold">Tải lên hình ảnh / video lỗi sản phẩm:</label>
                <div class="dashed-border" style="height: 80px; display: flex; align-items: center; justify-content: center; font-size: 11px; margin-top: 5px;">
                    [ Chọn ảnh chụp tem bảo hành, vết nứt, lỗi hiển thị ]
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label class="bold">Thông tin tài khoản nhận tiền hoàn (nếu chọn hoàn tiền):</label>
                <div style="margin-top: 5px;">
                    <div style="border: 1px solid black; padding: 6px; margin-bottom: 5px;">[ Tên ngân hàng: Vietcombank ]</div>
                    <div style="border: 1px solid black; padding: 6px; margin-bottom: 5px;">[ Số tài khoản: 0011009988776 ]</div>
                    <div style="border: 1px solid black; padding: 6px;">[ Họ và tên chủ thẻ: NGUYEN VAN AN ]</div>
                </div>
            </div>
            
            <div class="btn text-center" style="width: 100%; box-sizing: border-box;">GỬI YÊU CẦU ĐỔI TRẢ</div>
        </div>
    </div>
</div>
""")

# Merge manual wireframes into all_wireframes
for k, v in manual_wireframes.items():
    all_wireframes[k] = v

async def main():
    print("Starting Playwright layout generator for all 51 functions...")
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        # Set device_scale_factor=2 for high quality DPI crispness
        context = await browser.new_context(device_scale_factor=2)
        page = await context.new_page()
        
        output_dirs = [
            r"d:\repogist\ThuongMaiDienTu\images",
            r"d:\HOC\Hoc4\pywword\images"
        ]
        
        for d in output_dirs:
            os.makedirs(d, exist_ok=True)
            
        temp_html_path = "temp_all_wireframe.html"
        
        for fid in range(1, 52):
            if fid not in all_wireframes:
                print(f"Warning: Function 7.{fid} has no wireframe spec. Skipping.")
                continue
                
            html = all_wireframes[fid]
            print(f"Generating wireframe layout for function 7.{fid}...")
            with open(temp_html_path, "w", encoding="utf-8") as f:
                f.write(html)
                
            abs_url = "file:///" + os.path.abspath(temp_html_path).replace("\\", "/")
            await page.goto(abs_url)
            await page.wait_for_timeout(400)
            
            container = page.locator(".container")
            
            # Check if thermal print or smaller container to snap
            thermal_receipt = page.locator(".thermal-receipt")
            target_locator = thermal_receipt if fid == 28 else container
            
            for out_dir in output_dirs:
                dest_path = os.path.join(out_dir, f"ui_layout_{fid}.png")
                await target_locator.screenshot(path=dest_path)
                
        await browser.close()
        if os.path.exists(temp_html_path):
            os.remove(temp_html_path)
            
    print("All 51 functions wireframe layouts rendered successfully in High-DPI!")

if __name__ == "__main__":
    asyncio.run(main())
