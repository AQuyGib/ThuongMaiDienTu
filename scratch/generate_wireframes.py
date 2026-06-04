import os
import asyncio
from playwright.async_api import async_playwright
import sys

sys.stdout.reconfigure(encoding='utf-8')

# HTML templates for the 16 wireframes
wireframes = {}

# CSS shared styles for wireframes
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

# Wireframe HTML generators
wireframes[12] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-3-9 {{
    display: grid;
    grid-template-columns: 3fr 9fr;
    gap: 10px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN (UI LAYOUT) - BỘ LỌC SẢN PHẨM NÂNG CAO (FACETED FILTER)</div>
    <div class="header-bar">
        <div>Logo: DIENMAY PRO</div>
        <div>Thanh tìm kiếm: Bạn muốn mua gì hôm nay?</div>
        <div>Giỏ hàng (3) | Tra cứu | Đăng nhập</div>
    </div>
    
    <div class="layout-3-9">
        <div class="border-box double-border">
            <div class="bold text-center" style="border-bottom: 1px solid black; padding-bottom: 5px;">BỘ LỌC TÌM KIẾM</div>
            <div style="margin-top: 15px;">
                <div class="bold">1. Danh mục:</div>
                <div style="padding-left: 10px; margin-top: 5px;">
                    [x] Tivi & Loa âm thanh<br>
                    [ ] Tủ lạnh & Máy giặt<br>
                    [ ] Điều hòa không khí
                </div>
            </div>
            <div style="margin-top: 15px;">
                <div class="bold">2. Thương hiệu:</div>
                <div style="padding-left: 10px; margin-top: 5px;">
                    [x] Sony<br>
                    [x] Samsung<br>
                    [ ] LG
                </div>
            </div>
            <div style="margin-top: 15px;">
                <div class="bold">3. Khoảng giá (VNĐ):</div>
                <div style="margin-top: 5px;">
                    Từ: [ 10.000.000 ] Đến: [ 30.000.000 ]
                </div>
            </div>
            <div style="margin-top: 15px;">
                <div class="bold">4. Ưu đãi thành viên (VIP):</div>
                <div style="padding-left: 10px; margin-top: 5px;">
                    [x] Có giảm giá VIP<br>
                    [ ] Đổi điểm tích lũy
                </div>
            </div>
            <div style="margin-top: 15px;">
                <div class="bold">5. Đánh giá chất lượng:</div>
                <div style="padding-left: 10px; margin-top: 5px;">
                    [x] 4 sao trở lên (★★★★☆)<br>
                    [ ] 5 sao tuyệt đối (★★★★★)
                </div>
            </div>
            <div class="btn" style="margin-top: 20px; width: 100%; box-sizing: border-box;">ÁP DỤNG BỘ LỌC</div>
        </div>
        
        <div class="border-box">
            <div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 5px;">
                <div class="bold">KẾT QUẢ TÌM KIẾM (Tìm thấy 128 sản phẩm phù hợp)</div>
                <div>Sắp xếp theo: [ Giá từ thấp đến cao ]</div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 15px;">
                <!-- Product Card 1 -->
                <div class="border-box double-border text-center">
                    <div style="height: 100px; border: 1px dashed black; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">[ Ảnh Tivi Sony ]</div>
                    <div class="badge">AI gợi ý nổi bật</div>
                    <div class="bold" style="margin-top: 5px; font-size: 13px;">Smart Tivi Sony 4K 55"</div>
                    <div style="text-decoration: line-through; font-size: 11px;">18.500.000đ</div>
                    <div class="bold" style="font-size: 14px;">15.990.000đ</div>
                    <div style="font-size: 12px; margin-top: 5px;">Giảm thêm 3% (Hạng Vàng)</div>
                    <div class="btn" style="margin-top: 10px; font-size: 11px; width: 100%; box-sizing: border-box;">XEM CHI TIẾT</div>
                </div>
                
                <!-- Product Card 2 -->
                <div class="border-box double-border text-center">
                    <div style="height: 100px; border: 1px dashed black; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">[ Ảnh Tivi Samsung ]</div>
                    <div class="badge">VIP Độc quyền</div>
                    <div class="bold" style="margin-top: 5px; font-size: 13px;">Tivi QLED Samsung 50"</div>
                    <div style="text-decoration: line-through; font-size: 11px;">16.000.000đ</div>
                    <div class="bold" style="font-size: 14px;">13.490.000đ</div>
                    <div style="font-size: 12px; margin-top: 5px;">Giảm thêm 5% (Hạng Kim Cương)</div>
                    <div class="btn" style="margin-top: 10px; font-size: 11px; width: 100%; box-sizing: border-box;">XEM CHI TIẾT</div>
                </div>
                
                <!-- Product Card 3 -->
                <div class="border-box double-border text-center">
                    <div style="height: 100px; border: 1px dashed black; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">[ Ảnh Loa Soundbar Sony ]</div>
                    <div class="badge">Giá sốc</div>
                    <div class="bold" style="margin-top: 5px; font-size: 13px;">Dàn âm thanh Sony 5.1</div>
                    <div style="text-decoration: line-through; font-size: 11px;">9.500.000đ</div>
                    <div class="bold" style="font-size: 14px;">8.290.000đ</div>
                    <div style="font-size: 12px; margin-top: 5px;">Tích 500 điểm Loyalty</div>
                    <div class="btn" style="margin-top: 10px; font-size: 11px; width: 100%; box-sizing: border-box;">XEM CHI TIẾT</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[13] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN (UI LAYOUT) - SO SÁNH SẢN PHẨM</div>
    <div class="header-bar">
        <div>Logo: DIENMAY PRO</div>
        <div>Trang so sánh sản phẩm</div>
        <div>Quay lại trang chủ</div>
    </div>
    
    <div class="border-box double-border">
        <div class="bold" style="margin-bottom: 15px; border-bottom: 1px solid black; padding-bottom: 5px;">BẢNG SO SÁNH THÔNG SỐ CHI TIẾT</div>
        
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 25%;">Thông số kỹ thuật</th>
                    <th style="width: 25%; text-align: center;">Sản phẩm 1 (Gốc)<br><span class="bold">Smart TV Sony 55" 4K</span></th>
                    <th style="width: 25%; text-align: center;">Sản phẩm 2<br><span class="bold">Smart TV Samsung QLED 55"</span></th>
                    <th style="width: 25%; text-align: center;">Sản phẩm 3<br><span class="bold">Smart TV LG OLED 55"</span></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="bold">Hình ảnh minh họa</td>
                    <td style="text-align: center;"><div style="height: 80px; border: 1px dashed black; margin: auto; display: flex; align-items: center; justify-content: center;">[ Ảnh Tivi Sony ]</div></td>
                    <td style="text-align: center;"><div style="height: 80px; border: 1px dashed black; margin: auto; display: flex; align-items: center; justify-content: center;">[ Ảnh Tivi Samsung ]</div></td>
                    <td style="text-align: center;"><div style="height: 80px; border: 1px dashed black; margin: auto; display: flex; align-items: center; justify-content: center;">[ Ảnh Tivi LG ]</div></td>
                </tr>
                <tr>
                    <td class="bold">Giá bán hiện tại</td>
                    <td style="text-align: center;" class="bold">15.990.000đ</td>
                    <td style="text-align: center;" class="bold">16.490.000đ</td>
                    <td style="text-align: center;" class="bold">22.990.000đ</td>
                </tr>
                <tr>
                    <td class="bold">Công nghệ màn hình</td>
                    <td>LED Backlight (Sony Triluminos)</td>
                    <td>QLED (Màu sắc rực rỡ)</td>
                    <td>OLED (Điểm ảnh tự phát sáng - Đen tuyệt đối)</td>
                </tr>
                <tr>
                    <td class="bold">Độ phân giải</td>
                    <td>Ultra HD 4K (3840 x 2160)</td>
                    <td>Ultra HD 4K (3840 x 2160)</td>
                    <td>Ultra HD 4K (3840 x 2160)</td>
                </tr>
                <tr>
                    <td class="bold">Hệ điều hành</td>
                    <td>Google TV (Giao diện mượt mà)</td>
                    <td>Tizen OS (Độc quyền Samsung)</td>
                    <td>webOS (Giao diện thẻ trực quan)</td>
                </tr>
                <tr>
                    <td class="bold">Thời hạn bảo hành</td>
                    <td class="bold" style="background-color: #f2f2f2;">2 năm chính hãng</td>
                    <td>2 năm chính hãng</td>
                    <td>2 năm chính hãng</td>
                </tr>
                <tr>
                    <td class="bold">Hành động</td>
                    <td style="text-align: center;"><div class="btn" style="font-size: 11px;">MUA NGAY</div></td>
                    <td style="text-align: center;"><div class="btn" style="font-size: 11px;">MUA NGAY</div></td>
                    <td style="text-align: center;"><div class="btn" style="font-size: 11px;">MUA NGAY</div></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[14] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">GIAO DIỆN TRANG ĐIỆN MÁY PRO</div>
    <div class="header-bar">
        <div>Logo: DIENMAY PRO</div>
        <div style="border: 1px solid black; padding: 2px 10px;">Danh mục</div>
        <div style="border: 1px solid black; padding: 2px 10px;">Vị trí: TP. Hồ Chí Minh</div>
        <div style="border: 1px solid black; padding: 2px 10px; width: 350px;">Thanh tìm kiếm: Bạn muốn mua gì hôm nay? 🔍</div>
        <div>Icons: Tra cứu | Thông báo | Giỏ hàng</div>
    </div>
    
    <div class="border-box double-border" style="margin-top: 10px;">
        <div class="bold text-center" style="border-bottom: 1px solid black; padding-bottom: 5px;">KHUNG: MUA KÈM COMBO TIẾT KIỆM</div>
        
        <div style="margin-top: 15px;">
            <div class="bold">[AI Gợi Ý Tối Ưu] Mua kèm combo tiết kiệm</div>
            <div style="font-style: italic; font-size: 12px; margin-top: 2px;">AI đã phân tích hành vi và phân hạng thành viên của bạn để đề xuất...</div>
        </div>
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 20px;">
            
            <!-- Item 1 -->
            <div class="border-box double-border text-center" style="width: 20%; height: 160px; display: flex; flex-direction: column; justify-content: center;">
                <div class="bold" style="font-size: 14px;">Sản phẩm hiện tại</div>
                <div style="margin-top: 10px; font-size: 14px;">28.000.000đ</div>
            </div>
            
            <div class="bold" style="font-size: 24px;">+</div>
            
            <!-- Item 2 -->
            <div class="border-box double-border text-center" style="width: 20%; height: 160px; display: flex; flex-direction: column; justify-content: center;">
                <div class="bold" style="font-size: 13px;">Tai nghe Gaming JBL</div>
                <div style="margin-top: 10px; font-size: 14px;">6.882.000đ</div>
                <div style="font-size: 12px; margin-top: 5px;">(-7%)</div>
            </div>
            
            <div class="bold" style="font-size: 24px;">+</div>
            
            <!-- Item 3 -->
            <div class="border-box double-border text-center" style="width: 20%; height: 160px; display: flex; flex-direction: column; justify-content: center;">
                <div class="bold" style="font-size: 13px;">Loa Soundbar Sony</div>
                <div style="margin-top: 10px; font-size: 14px;">8.730.000đ</div>
                <div style="font-size: 12px; margin-top: 5px;">(-70.000đ)</div>
            </div>
            
            <div class="bold" style="font-size: 24px;">=</div>
            
            <!-- Calculations & AI Panel -->
            <div class="border-box double-border" style="width: 30%; height: 230px; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between;">
                <div class="bold text-center" style="border-bottom: 1px solid black; padding-bottom: 5px; font-size: 12px;">Khung Tính Toán & Gợi Ý AI</div>
                
                <div style="font-size: 13px; margin-top: 5px;">
                    <div>Tổng cộng 3 sản phẩm:</div>
                    <div class="bold" style="font-size: 16px; margin-top: 2px;">43.612.000đ</div>
                    <div style="margin-top: 5px;">Tiết kiệm thêm: <span class="bold">588.000đ</span></div>
                </div>
                
                <div class="btn" style="font-size: 12px; width: 100%; box-sizing: border-box; margin-top: 5px;">THÊM COMBO VÀO GIỎ</div>
                
                <div style="border-top: 1px dashed black; padding-top: 5px; margin-top: 5px; font-size: 10px; line-height: 1.2;">
                    <span class="bold">Nhận định tối ưu từ AI:</span><br>
                    • Tai nghe Gaming JBL... Nâng tầm trải nghiệm chiến game đỉnh cao với âm thanh sống động...<br>
                    • Loa Soundbar Sony... Tận hưởng không gian giải trí trọn vẹn hơn...
                </div>
            </div>
            
        </div>
    </div>
    
    <div class="flex-space" style="border: 1px solid black; padding: 8px; margin-top: 10px; font-size: 13px;">
        <div class="bold">Màn hình Gaming OLED Samsung 24 inch 7816</div>
        <div><span class="bold">28.000.000đ</span> (Gốc: 40.000.000đ)</div>
        <div class="flex-row">
            <div class="btn" style="padding: 3px 10px; font-size: 11px;">Trả góp 0%</div>
            <div class="btn" style="padding: 3px 10px; font-size: 11px; background-color: #000000; color: #ffffff;">MUA NGAY</div>
            <div class="btn" style="padding: 3px 10px; font-size: 11px;">Giỏ hàng</div>
        </div>
    </div>
</div>
</body>
</html>"""

wireframes[22] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-2col {{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC ĐẶT LỊCH SỬA CHỮA - AI CHẨN ĐOÁN LỖI (GEMINI VISION)</div>
    <div class="header-bar">
        <div>Sửa chữa máy móc chuyên nghiệp DIENMAY PRO</div>
        <div>Hỗ trợ khách hàng 24/7</div>
    </div>
    
    <div class="layout-2col">
        <div class="border-box double-border">
            <div class="bold text-center" style="border-bottom: 1px solid black; padding-bottom: 5px;">THÔNG TIN ĐĂNG KÝ SỬA CHỮA</div>
            
            <div style="margin-top: 15px;">
                <label class="bold">Họ và tên khách hàng (*):</label><br>
                <input type="text" value="Nguyễn Văn A" style="width: 95%; margin-top: 5px; border: 1px solid black; padding: 5px;" disabled>
            </div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Số điện thoại (*):</label><br>
                <input type="text" value="0987654321" style="width: 95%; margin-top: 5px; border: 1px solid black; padding: 5px;" disabled>
            </div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Loại thiết bị cần sửa (*):</label><br>
                <select style="width: 98%; margin-top: 5px; border: 1px solid black; padding: 5px;" disabled>
                    <option>Tivi & Màn hình hiển thị</option>
                </select>
            </div>
            
            <div style="margin-top: 15px;">
                <label class="bold">Tải ảnh chụp tình trạng thiết bị lỗi (*):</label>
                <div class="dashed-border" style="margin-top: 5px;">
                    <div>[ Thumbnail_Loi_Tivi.jpg ]</div>
                    <div style="font-size: 11px; margin-top: 5px; text-decoration: underline;">Chọn ảnh chụp thiết bị hỏng khác</div>
                </div>
            </div>
            
            <div class="btn" style="margin-top: 15px; width: 100%; box-sizing: border-box; background-color: #000000; color: #ffffff;">BẮT ĐẦU CHẨN ĐOÁN BẰNG AI VISION</div>
        </div>
        
        <div class="border-box double-border" style="background-color: #fafafa;">
            <div class="bold text-center" style="border-bottom: 1px solid black; padding-bottom: 5px;">KHUNG KẾT QUẢ PHÂN TÍCH (AI DIAGNOSIS PANEL)</div>
            
            <div style="margin-top: 15px;">
                <div class="bold">1. Chẩn đoán xác suất lỗi thiết bị:</div>
                <table style="width: 100%; margin-top: 5px;">
                    <thead>
                        <tr>
                            <th>Nguyên nhân dự đoán</th>
                            <th>Độ tin cậy (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #e0e0e0;">
                            <td class="bold">Cháy IC nguồn / Hỏng bo mạch nguồn</td>
                            <td class="bold" style="text-align: center;">88%</td>
                        </tr>
                        <tr>
                            <td>Sọc tấm nền LCD (Panel)</td>
                            <td style="text-align: center;">10%</td>
                        </tr>
                        <tr>
                            <td>Hỏng vi xử lý trung tâm</td>
                            <td style="text-align: center;">2%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 15px;">
                <div class="bold">2. Ước tính linh kiện & Khoảng giá đề xuất:</div>
                <div style="border: 1px solid black; padding: 10px; margin-top: 5px; background-color: #ffffff;">
                    <div>• Linh kiện thay thế: <span class="bold">IC nguồn chính hãng Sony</span></div>
                    <div style="margin-top: 5px;">• Chi phí ước tính: <span class="bold" style="font-size: 16px;">500.000đ - 700.000đ</span></div>
                </div>
            </div>
            
            <div style="margin-top: 15px; border: 2px solid #000000; padding: 10px; background-color: #ffffff;">
                <div class="bold" style="color: #ff0000; text-align: center;">⚠️ CẢNH BÁO AN TOÀN TỪ AI VISION:</div>
                <div style="font-weight: bold; text-align: center; margin-top: 5px; font-size: 12px; line-height: 1.3;">
                    "Cảnh báo: Rút phích cắm điện ngay lập tức để tránh cháy nổ hoặc hư hại nặng thêm cho tấm nền màn hình!"
                </div>
            </div>
            
            <div class="btn" style="margin-top: 15px; width: 100%; box-sizing: border-box;">XÁC NHẬN ĐẶT LỊCH SỬA CHỮA</div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[36] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-3-9 {{
    display: grid;
    grid-template-columns: 3fr 9fr;
    gap: 10px;
}}
.vip-card {{
    border: 3px double #000000;
    padding: 20px;
    background-color: #f5f5f5;
    position: relative;
    overflow: hidden;
    margin-top: 15px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC TRANG CÁ NHÂN - THẺ THÀNH VIÊN ĐIỆN TỬ (LOYALTY VIP CARD)</div>
    <div class="header-bar">
        <div>Tài khoản khách hàng: Nguyễn Văn A</div>
        <div>Hạng thành viên hiện tại: VÀNG</div>
    </div>
    
    <div class="layout-3-9">
        <div class="border-box">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px;">DANH MỤC CÁ NHÂN</div>
            <div style="margin-top: 10px; line-height: 2;">
                • Thông tin cá nhân<br>
                • Đơn hàng của tôi<br>
                • Danh sách yêu thích (Wishlist)<br>
                <span class="bold">• Khách hàng Loyalty (Tích điểm)</span><br>
                • Lịch sử sửa chữa thiết bị
            </div>
        </div>
        
        <div class="border-box double-border">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px;">HẠNG THÀNH VIÊN & ĐIỂM TÍCH LŨY</div>
            
            <div class="vip-card">
                <div class="flex-space">
                    <div class="bold" style="font-size: 18px;">HỘI VIÊN VÀNG (GOLD TIER CARD)</div>
                    <div class="badge">DIENMAY PRO VIP</div>
                </div>
                <div style="margin-top: 30px;">
                    <div style="font-size: 13px;">Mã số thẻ thành viên: <span class="bold">VIP-888899</span></div>
                    <div style="font-size: 13px; margin-top: 5px;">Chủ thẻ: <span class="bold">Nguyễn Văn A</span></div>
                </div>
                <div class="flex-space" style="margin-top: 20px; border-top: 1px dashed black; padding-top: 10px;">
                    <div>ĐIỂM TÍCH LŨY HIỆN CÓ:</div>
                    <div class="bold" style="font-size: 20px;">1.250 Điểm</div>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <div class="bold">Thanh tiến trình nâng hạng tiếp theo:</div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: 62.5%;"></div>
                    <div class="progress-bar-text">62.5% - Đạt 1.250 / 2.000 điểm</div>
                </div>
                <div style="font-size: 12px; margin-top: 5px; text-align: right;" class="bold">
                    Cần thêm 750 điểm để nâng lên hạng thẻ KIM CƯƠNG
                </div>
            </div>
            
            <div style="margin-top: 25px;">
                <div class="bold">LỊCH SỬ BIẾN ĐỘNG ĐIỂM THƯỞNG:</div>
                <table style="width: 100%; margin-top: 5px;">
                    <thead>
                        <tr>
                            <th>Ngày thực hiện</th>
                            <th>Nội dung giao dịch</th>
                            <th style="text-align: center;">Điểm thay đổi</th>
                            <th style="text-align: center;">Số dư điểm</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>04/06/2026</td>
                            <td>Tích điểm mua đơn hàng #DH-99120</td>
                            <td class="bold" style="text-align: center; color: #000;">+500</td>
                            <td style="text-align: center;">1.250</td>
                        </tr>
                        <tr>
                            <td>03/06/2026</td>
                            <td>Quay vòng quay may mắn - Lucky Wheel</td>
                            <td class="bold" style="text-align: center; color: #000;">-100</td>
                            <td style="text-align: center;">750</td>
                        </tr>
                        <tr>
                            <td>01/06/2026</td>
                            <td>Đổi quà tặng Voucher 50.000đ</td>
                            <td class="bold" style="text-align: center; color: #000;">-500</td>
                            <td style="text-align: center;">850</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[37] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.voucher-grid {{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 15px;
}}
.voucher-card {{
    border: 3px double #000000;
    padding: 15px;
    background-color: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 200px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN (UI LAYOUT) - CỬA HÀNG ĐỔI THƯỞNG VOUCHER</div>
    <div class="header-bar">
        <div class="bold">Số điểm tích lũy hiện có của bạn: 1.250 Điểm</div>
        <div>Hạng thành viên: Hội viên Vàng</div>
    </div>
    
    <div class="border-box double-border">
        <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px;">DANH SÁCH VOUCHER QUÀ TẶNG CÓ SẴN</div>
        
        <div class="voucher-grid">
            
            <!-- Voucher 1 -->
            <div class="voucher-card">
                <div>
                    <div class="badge">Voucher 50.000đ</div>
                    <div class="bold" style="margin-top: 10px; font-size: 13px;">Giảm giá trực tiếp 50k</div>
                    <div style="font-size: 11px; margin-top: 5px;">• Áp dụng đơn từ: 200.000đ</div>
                    <div style="font-size: 11px;">• Hạn dùng: 30 ngày từ khi đổi</div>
                </div>
                <div>
                    <div style="font-size: 12px; margin-bottom: 5px;" class="bold">Điểm yêu cầu: 500 điểm</div>
                    <div style="font-size: 11px; margin-bottom: 5px; font-style: italic;">Còn lại trong kho: 45 chiếc</div>
                    <div class="btn" style="width: 100%; box-sizing: border-box; background-color: #000000; color: #ffffff; font-size: 11px;">ĐỔI NGAY</div>
                </div>
            </div>
            
            <!-- Voucher 2 -->
            <div class="voucher-card">
                <div>
                    <div class="badge">Voucher 100.000đ</div>
                    <div class="bold" style="margin-top: 10px; font-size: 13px;">Giảm giá trực tiếp 100k</div>
                    <div style="font-size: 11px; margin-top: 5px;">• Áp dụng đơn từ: 500.000đ</div>
                    <div style="font-size: 11px;">• Hạn dùng: 30 ngày từ khi đổi</div>
                </div>
                <div>
                    <div style="font-size: 12px; margin-bottom: 5px;" class="bold">Điểm yêu cầu: 900 điểm</div>
                    <div style="font-size: 11px; margin-bottom: 5px; font-style: italic;">Còn lại trong kho: 12 chiếc</div>
                    <div class="btn" style="width: 100%; box-sizing: border-box; background-color: #000000; color: #ffffff; font-size: 11px;">ĐỔI NGAY</div>
                </div>
            </div>
            
            <!-- Voucher 3 -->
            <div class="voucher-card" style="border: 2px dashed #aaaaaa; background-color: #fcfcfc;">
                <div>
                    <div class="badge" style="border: 1px dashed #aaaaaa; color: #aaaaaa;">Voucher 200.000đ</div>
                    <div class="bold" style="margin-top: 10px; font-size: 13px; color: #aaaaaa;">Giảm giá trực tiếp 200k</div>
                    <div style="font-size: 11px; margin-top: 5px; color: #aaaaaa;">• Áp dụng đơn từ: 1.000.000đ</div>
                    <div style="font-size: 11px; color: #aaaaaa;">• Hạn dùng: 30 ngày từ khi đổi</div>
                </div>
                <div>
                    <div style="font-size: 12px; margin-bottom: 5px; color: #aaaaaa;" class="bold">Điểm yêu cầu: 1.800 điểm</div>
                    <div style="font-size: 11px; margin-bottom: 5px; font-style: italic; color: #aaaaaa;">Còn lại trong kho: 5 chiếc</div>
                    <div class="btn-disabled" style="font-size: 11px;">CHƯA ĐỦ ĐIỂM</div>
                </div>
            </div>
            
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[38] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-wheel {{
    display: grid;
    grid-template-columns: 7fr 5fr;
    gap: 15px;
}}
.wheel-outer {{
    width: 320px;
    height: 320px;
    border: 10px double #000000;
    border-radius: 50%;
    margin: 15px auto;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #ffffff;
}}
.wheel-sector-line {{
    position: absolute;
    width: 2px;
    height: 150px;
    background-color: #000000;
    top: 10px;
    left: 159px;
    transform-origin: bottom center;
}}
.wheel-text {{
    position: absolute;
    font-size: 9px;
    font-weight: bold;
    transform-origin: center;
}}
.wheel-btn {{
    position: absolute;
    width: 60px;
    height: 60px;
    border: 3px solid #000000;
    border-radius: 50%;
    background-color: #ffffff;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    z-index: 10;
}}
.wheel-pointer {{
    position: absolute;
    top: -5px;
    width: 0;
    height: 0;
    border-left: 15px solid transparent;
    border-right: 15px solid transparent;
    border-bottom: 25px solid #000000;
    z-index: 15;
}}
.winner-ticker {{
    border: 1px solid #000000;
    padding: 10px;
    height: 180px;
    background-color: #fafafa;
    overflow: hidden;
    line-height: 1.6;
    font-size: 12px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN (UI LAYOUT) - VÒNG QUAY MAY MẮN (LUCKY WHEEL)</div>
    <div class="header-bar">
        <div>Điểm tích lũy: 1.250 điểm</div>
        <div>Hạng tài khoản: Vàng</div>
        <div>Vòng quay Standard (Mở khóa)</div>
    </div>
    
    <div class="layout-wheel">
        <div class="border-box double-border text-center">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px;">KHU VỰC VÒNG QUAY MAY MẮN</div>
            
            <div style="position: relative; width: 330px; margin: auto;">
                <div class="wheel-pointer"></div>
                <div class="wheel-outer">
                    <!-- Center Button -->
                    <div class="wheel-btn">QUAY</div>
                    
                    <!-- Lines and text representatives for visual rendering -->
                    <div class="wheel-sector-line" style="transform: rotate(0deg);"></div>
                    <div class="wheel-sector-line" style="transform: rotate(60deg);"></div>
                    <div class="wheel-sector-line" style="transform: rotate(120deg);"></div>
                    <div class="wheel-sector-line" style="transform: rotate(180deg);"></div>
                    <div class="wheel-sector-line" style="transform: rotate(240deg);"></div>
                    <div class="wheel-sector-line" style="transform: rotate(300deg);"></div>
                    
                    <div class="wheel-text" style="top: 40px; left: 130px; transform: rotate(0deg);">Voucher 10k</div>
                    <div class="wheel-text" style="top: 80px; right: 40px; transform: rotate(60deg);">AirPods Pro</div>
                    <div class="wheel-text" style="bottom: 80px; right: 40px; transform: rotate(120deg);">Chúc may mắn</div>
                    <div class="wheel-text" style="bottom: 40px; left: 130px; transform: rotate(180deg);">Voucher 50k</div>
                    <div class="wheel-text" style="bottom: 80px; left: 40px; transform: rotate(240deg);">100 Điểm</div>
                    <div class="wheel-text" style="top: 80px; left: 40px; transform: rotate(300deg);">Voucher 100k</div>
                </div>
            </div>
            
            <div style="margin-top: 15px;">
                <div class="bold" style="font-size: 14px;">Lượt quay miễn phí hôm nay: 1 lượt</div>
                <div style="display: flex; gap: 10px; justify-content: center; margin-top: 10px;">
                    <div class="btn">QUAY MIỄN PHÍ</div>
                    <div class="btn">QUAY BẰNG 100 ĐIỂM THƯỞNG</div>
                </div>
            </div>
        </div>
        
        <div class="border-box double-border">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">BẢNG VÀNG VINH DANH TRÚNG THƯỞNG</div>
            
            <div class="winner-ticker">
                <div class="bold">[12:45:10] Nguyễn Văn A đã trúng <span class="bold">Voucher 50.000đ</span></div>
                <div>[12:44:03] Trần Thị B đã trúng <span class="bold">100 Điểm thưởng</span></div>
                <div>[12:42:15] Lê Văn C đã trúng <span class="bold">Voucher 10.000đ</span></div>
                <div>[12:39:55] Hoàng Thị D đã trúng <span class="bold">Chúc may mắn lần sau</span></div>
                <div>[12:35:12] Nguyễn Văn E đã trúng <span class="bold">Voucher 100.000đ</span></div>
                <div style="border-top: 1px dashed black; padding-top: 10px; margin-top: 10px; font-style: italic; font-size: 11px;">
                    * Danh sách được cập nhật thời gian thực trên hệ thống
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[39] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-form {{
    display: grid;
    grid-template-columns: 7fr 5fr;
    gap: 15px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN - QUẢN LÝ CHIẾN DỊCH THÔNG BÁO (NOTIFICATION CAMPAIGNS)</div>
    <div class="header-bar">
        <div>Trang quản trị hệ thống Admin</div>
        <div>Thông báo đẩy đa kênh (Web Push / Loyalty System Notification)</div>
    </div>
    
    <div class="layout-form">
        <div class="border-box double-border">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">DANH SÁCH CHIẾN DỊCH THÔNG BÁO</div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th>Tiêu đề</th>
                        <th>Đối tượng nhận</th>
                        <th style="text-align: center;">Trạng thái</th>
                        <th style="text-align: center;">Số lượng gửi</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>01</td>
                        <td class="bold">Khuyến mãi Flash Sale Giờ Vàng 06/06</td>
                        <td>Tất cả khách hàng</td>
                        <td style="text-align: center;"><span class="badge">Hoàn tất</span></td>
                        <td style="text-align: center;" class="bold">1.250</td>
                        <td>04/06/2026</td>
                    </tr>
                    <tr>
                        <td>02</td>
                        <td class="bold">Ưu đãi độc quyền nâng hạng Kim Cương</td>
                        <td>Hạng Vàng</td>
                        <td style="text-align: center;"><span class="badge">Đang gửi</span></td>
                        <td style="text-align: center;" class="bold">450</td>
                        <td>04/06/2026</td>
                    </tr>
                    <tr>
                        <td>03</td>
                        <td class="bold">Tặng voucher vòng quay may mắn tri ân</td>
                        <td>Tất cả khách hàng</td>
                        <td style="text-align: center;"><span class="badge">Đang chờ</span></td>
                        <td style="text-align: center;" class="bold">0</td>
                        <td>03/06/2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="border-box double-border">
            <div class="bold text-center" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">TẠO CHIẾN DỊCH MỚI</div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Tiêu đề thông báo đẩy (*):</label><br>
                <input type="text" value="Flash Sale Giờ Vàng Cuối Tuần!" style="width: 95%; margin-top: 5px; border: 1px solid black; padding: 5px;">
                <div style="font-size: 10px; margin-top: 2px; text-align: right;">Độ dài: 32 / Tối đa 100 ký tự</div>
            </div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Nội dung chi tiết thông báo (*):</label><br>
                <textarea style="width: 95%; height: 80px; margin-top: 5px; border: 1px solid black; padding: 5px; font-family: monospace;">Săn ngay tivi Sony giảm tới 50% chỉ duy nhất khung giờ vàng 9h-11h sáng mai. Độc quyền thành viên Loyalty.</textarea>
                <div style="font-size: 10px; margin-top: 2px; text-align: right;">Độ dài: 108 / Tối đa 250 ký tự</div>
            </div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Nhóm người nhận (*):</label><br>
                <select style="width: 98%; margin-top: 5px; border: 1px solid black; padding: 5px;">
                    <option>Tất cả khách hàng</option>
                    <option>Hạng Đồng</option>
                    <option>Hạng Bạc</option>
                    <option>Hạng Vàng</option>
                    <option>Hạng Kim Cương</option>
                </select>
            </div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Đường dẫn liên kết (Action URL):</label><br>
                <input type="text" value="/flash-sales" style="width: 95%; margin-top: 5px; border: 1px solid black; padding: 5px;">
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <div class="btn" style="flex: 1; background-color: #000000; color: #ffffff;">KÍCH HOẠT CHIẾN DỊCH</div>
                <div class="btn" style="flex: 1;">HỦY BỎ</div>
            </div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[40] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-ugc {{
    display: grid;
    grid-template-columns: 4fr 8fr;
    gap: 15px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN (UI LAYOUT) - KIỂM DUYỆT BÀI VIẾT UGC BẰNG AI</div>
    <div class="header-bar">
        <div>Hệ thống kiểm duyệt bài viết tự động</div>
        <div>Mô hình AI Gemini 1.5 Flash Content Moderation</div>
    </div>
    
    <div class="layout-ugc">
        <div class="border-box double-border">
            <div class="bold text-center" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">SOẠN THẢO BÀI VIẾT (USER)</div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Tiêu đề bài viết:</label><br>
                <input type="text" value="Đánh giá chi tiết Tivi Sony 55 inch sau 6 tháng sử dụng" style="width: 95%; margin-top: 5px; border: 1px solid black; padding: 5px;" disabled>
            </div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Danh mục liên quan:</label><br>
                <select style="width: 98%; margin-top: 5px; border: 1px solid black; padding: 5px;" disabled>
                    <option>Đánh giá sản phẩm</option>
                </select>
            </div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Nội dung bài chia sẻ:</label><br>
                <textarea style="width: 95%; height: 120px; margin-top: 5px; border: 1px solid black; padding: 5px; font-family: monospace;" disabled>Tivi hiển thị màu sắc rất đẹp, thiết kế mỏng. Điểm trừ duy nhất là loa tích hợp nghe hơi nhỏ, mua thêm loa soundbar kèm sẽ tối ưu hơn. Dịch vụ bảo hành của DIENMAY PRO nhiệt tình.</textarea>
            </div>
            
            <div class="btn" style="margin-top: 15px; width: 100%; box-sizing: border-box;" disabled>ĐĂNG BÀI VIẾT LÊN BLOG</div>
        </div>
        
        <div class="border-box double-border">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">DANH SÁCH BÀI VIẾT CHỜ DUYỆT (ADMIN SIDE)</div>
            
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th>Tiêu đề bài viết</th>
                        <th>Tác giả</th>
                        <th style="text-align: center;">Điểm AI</th>
                        <th style="text-align: center;">Quyết định AI</th>
                        <th style="text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background-color: #fafafa;">
                        <td>01</td>
                        <td class="bold">Đánh giá chi tiết Tivi Sony 55 inch...</td>
                        <td>Nguyễn Văn A</td>
                        <td style="text-align: center;" class="bold">92 / 100</td>
                        <td style="text-align: center;"><span class="badge" style="background-color: #ffffff; border: 2px solid black;">AI APPROVED</span></td>
                        <td style="text-align: center;"><div class="btn" style="padding: 2px 6px; font-size: 11px;">Duyệt nhanh</div></td>
                    </tr>
                    <tr>
                        <td>02</td>
                        <td class="bold" style="text-decoration: line-through;">Nhận cá độ bóng đá giá rẻ uy tín...</td>
                        <td>SpamBot99</td>
                        <td style="text-align: center; color: red;" class="bold">12 / 100</td>
                        <td style="text-align: center;"><span class="badge" style="background-color: #ffffff; border: 1px dashed red; color: red;">AI REJECTED</span></td>
                        <td style="text-align: center;"><div class="btn" style="padding: 2px 6px; font-size: 11px; color: red; border-color: red;">Xóa bài</div></td>
                    </tr>
                    <tr>
                        <td>03</td>
                        <td class="bold">Mẹo tiết kiệm điện khi dùng điều hòa...</td>
                        <td>Trần Thị B</td>
                        <td style="text-align: center; color: orange;" class="bold">68 / 100</td>
                        <td style="text-align: center;"><span class="badge" style="background-color: #ffffff; border: 1px solid orange; color: orange;">AI MANUAL</span></td>
                        <td style="text-align: center;"><div class="btn" style="padding: 2px 6px; font-size: 11px;">Xem & duyệt</div></td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 15px; font-size: 12px; line-height: 1.5; border: 1px dashed black; padding: 10px;">
                <span class="bold">Quy luật kiểm duyệt tự động bằng AI (Business Rules):</span><br>
                • Điểm AI >= 80: Đánh dấu duyệt tự động (AI Approved).<br>
                • Điểm AI < 40 hoặc chứa nội dung rác, cá độ, bạo lực: Đánh dấu từ chối tự động (AI Rejected).<br>
                • Điểm AI từ 40 đến 79 hoặc có từ khóa nghi ngờ: Chuyển duyệt thủ công (AI Manual).
            </div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[41] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-orders {{
    display: grid;
    grid-template-columns: 8fr 4fr;
    gap: 15px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN - TRANG CHI TIẾT ĐƠN HÀNG & DUYỆT ĐƠN HÀNG TỰ ĐỘNG BẰNG AI</div>
    <div class="header-bar">
        <div>Trang quản trị Admin</div>
        <div>Hệ thống phòng chống gian lận & Phân tích rủi ro AI (AI Fraud Detection)</div>
    </div>
    
    <div class="layout-orders">
        <div class="border-box double-border">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">CHI TIẾT ĐƠN HÀNG (#DH-99120)</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px;">
                <div>
                    <span class="bold">Thông tin khách hàng:</span><br>
                    • Họ tên: Nguyễn Văn A<br>
                    • Điện thoại: 0905123456<br>
                    • Email: nguyenvanasale@gmail.com
                </div>
                <div>
                    <span class="bold">Địa chỉ giao hàng:</span><br>
                    • Số nhà: Gầm cầu chữ Y, Phường 1<br>
                    • Quận/Huyện: Quận 8<br>
                    • Tỉnh/Thành phố: TP. Hồ Chí Minh
                </div>
            </div>
            
            <table style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="text-align: center;">Số lượng</th>
                        <th style="text-align: right;">Đơn giá</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="bold">Màn hình Gaming OLED Samsung 24 inch</td>
                        <td style="text-align: center;">01</td>
                        <td style="text-align: right;">28.000.000đ</td>
                        <td style="text-align: right;">28.000.000đ</td>
                    </tr>
                    <tr>
                        <td class="bold">Tai nghe Gaming JBL Quantum</td>
                        <td style="text-align: center;">01</td>
                        <td style="text-align: right;">6.882.000đ</td>
                        <td style="text-align: right;">6.882.000đ</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="bold" style="text-align: right;">TỔNG TIỀN ĐƠN HÀNG:</td>
                        <td class="bold" style="text-align: right; font-size: 15px;">34.882.000đ</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="border-box double-border" style="background-color: #fafafa;">
            <div class="bold text-center" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">ĐÁNH GIÁ RỦI RO AI (AI FRAUD PANEL)</div>
            
            <div class="text-center" style="border: 2px solid #000; padding: 15px; background-color: #ffffff;">
                <div class="bold" style="font-size: 13px;">ĐIỂM SỐ RỦI RO (RISK SCORE)</div>
                <div class="bold" style="font-size: 32px; margin-top: 5px;">78%</div>
                <div class="badge" style="border-color: red; color: red; margin-top: 5px; display: inline-block;">RỦI RO CAO (HIGH RISK)</div>
            </div>
            
            <div style="margin-top: 15px;">
                <div class="bold">Lý do phân tích của AI:</div>
                <div style="font-size: 11px; margin-top: 5px; line-height: 1.4;">
                    • Địa chỉ giao hàng "Gầm cầu chữ Y" không khớp trên bản đồ định vị.<br>
                    • Số điện thoại có lịch sử bom hàng 3 lần trên hệ thống trong 30 ngày qua.<br>
                    • Đơn hàng giá trị cao (>30 triệu) chọn hình thức COD (Thanh toán khi nhận hàng).
                </div>
            </div>
            
            <div style="margin-top: 15px; border-top: 1px dashed black; padding-top: 10px;">
                <div class="bold">Đề xuất hành động:</div>
                <div class="bold" style="margin-top: 5px; font-size: 13px;">TỪ CHỐI ĐƠN HÀNG HOẶC TẠM GIỮ GỌI ĐIỆN</div>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 20px;">
                <div class="btn" style="background-color: #000000; color: #ffffff;">TẠM GIỮ VÀ GỌI ĐỐI SOÁT</div>
                <div class="btn" style="color: red; border-color: red;">HỦY ĐƠN HÀNG HÀNG LOẠT</div>
            </div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[46] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-home {{
    position: relative;
    height: 520px;
    background-color: #f2f2f2;
    border: 1px solid #000;
}}
.chat-window {{
    position: absolute;
    bottom: 20px;
    right: 20px;
    width: 350px;
    height: 420px;
    border: 3px double #000000;
    background-color: #ffffff;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
}}
.chat-header {{
    border-bottom: 2px solid #000;
    padding: 8px;
    background-color: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    font-size: 13px;
}}
.chat-messages {{
    flex: 1;
    padding: 10px;
    overflow-y: auto;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}}
.msg-user {{
    align-self: flex-end;
    border: 1px solid #000000;
    padding: 8px;
    background-color: #f2f2f2;
    max-width: 80%;
}}
.msg-bot {{
    align-self: flex-start;
    border: 1px solid #000000;
    padding: 8px;
    background-color: #ffffff;
    max-width: 80%;
}}
.product-card-in-chat {{
    border: 1px solid #000;
    padding: 5px;
    margin-top: 5px;
    display: flex;
    gap: 5px;
    align-items: center;
    background-color: #fafafa;
}}
.chat-input-bar {{
    border-top: 1px solid #000;
    padding: 5px;
    display: flex;
    gap: 5px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN (UI LAYOUT) - AI CHATBOT HỖ TRỢ KHÁCH HÀNG (GEMINI RAG)</div>
    <div class="header-bar">
        <div>Logo: DIENMAY PRO</div>
        <div>Thanh tìm kiếm sản phẩm...</div>
        <div>Giỏ hàng | Liên hệ</div>
    </div>
    
    <div class="layout-home">
        <div style="padding: 20px; font-style: italic;">
            [ Khung nội dung Trang chủ: Banner khuyến mãi, Danh mục sản phẩm nổi bật, Khung Flash Sale... ]
        </div>
        
        <!-- Chatbox Active Window Mockup -->
        <div class="chat-window">
            <div class="chat-header">
                <div>🤖 Trợ lý ảo DIENMAY PRO</div>
                <div style="font-size: 10px;">🟢 Online</div>
            </div>
            
            <div class="chat-messages">
                <div class="msg-bot">Chào bạn, tôi là trợ lý AI thông minh của DIENMAY PRO. Tôi có thể giúp gì cho bạn?</div>
                <div class="msg-user">Tư vấn tivi Sony giúp tôi</div>
                <div class="msg-bot">
                    Dưới đây là sản phẩm phù hợp với nhu cầu của bạn:<br>
                    <div class="product-card-in-chat">
                        <div style="width: 40px; height: 40px; border: 1px dashed black; display: flex; align-items: center; justify-content: center; font-size: 9px;">[Img]</div>
                        <div>
                            <div class="bold" style="font-size: 10px;">Smart TV Sony 4K 55"</div>
                            <div class="bold" style="font-size: 10px;">15.990.000đ</div>
                        </div>
                    </div>
                    <div style="margin-top: 5px;">Sản phẩm được bảo hành 2 năm và tặng kèm 500 điểm thưởng Loyalty.</div>
                </div>
            </div>
            
            <div class="chat-input-bar">
                <input type="text" value="Tư vấn tivi Sony giúp tôi" style="flex: 1; border: 1px solid black; padding: 4px; font-size: 11px;">
                <div class="btn" style="padding: 2px 8px; font-size: 11px;">GỬI</div>
            </div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[47] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.fs-grid {{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 15px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN (UI LAYOUT) - FLASH SALE GIỜ VÀNG & CHỐNG BÁN VƯỢT KHO</div>
    <div class="header-bar">
        <div>Logo: DIENMAY PRO</div>
        <div>Kênh mua sắm trực tuyến hàng đầu</div>
        <div>Giỏ hàng (0)</div>
    </div>
    
    <div class="border-box double-border" style="background-color: #fafafa;">
        <div class="flex-space" style="border-bottom: 2px solid #000; padding-bottom: 10px;">
            <div class="bold" style="font-size: 18px; display: flex; align-items: center; gap: 10px;">
                ⚡ FLASH SALE GIỜ VÀNG ĐANG DIỄN RA
            </div>
            <div class="bold" style="font-size: 14px; border: 1px solid #000; padding: 4px 10px;">
                KẾT THÚC SAU: 02 : 45 : 12
            </div>
        </div>
        
        <div class="fs-grid">
            
            <!-- Item 1 -->
            <div class="border-box double-border text-center" style="background-color: #ffffff;">
                <div style="position: relative;">
                    <div class="badge" style="position: absolute; top: 5px; left: 5px; background: white;">GIẢM 25%</div>
                    <div style="height: 120px; border: 1px dashed black; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">[ Ảnh máy giặt LG ]</div>
                </div>
                <div class="bold" style="font-size: 13px;">Máy giặt LG Inverter 9kg</div>
                <div style="text-decoration: line-through; font-size: 11px; margin-top: 5px;">9.320.000đ</div>
                <div class="bold" style="font-size: 15px;">6.990.000đ</div>
                
                <div style="margin-top: 10px;">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: 80%;"></div>
                        <div class="progress-bar-text">Đã bán 80% - Chỉ còn 4 sản phẩm</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 5px; margin-top: 10px;">
                    <div class="btn" style="flex: 1; font-size: 11px; background-color: #000; color: #fff;">MUA NGAY</div>
                    <div class="btn" style="flex: 1; font-size: 11px;">+ GIỎ HÀNG</div>
                </div>
            </div>
            
            <!-- Item 2 -->
            <div class="border-box double-border text-center" style="background-color: #ffffff;">
                <div style="position: relative;">
                    <div class="badge" style="position: absolute; top: 5px; left: 5px; background: white;">GIẢM 15%</div>
                    <div style="height: 120px; border: 1px dashed black; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">[ Ảnh tủ lạnh Samsung ]</div>
                </div>
                <div class="bold" style="font-size: 13px;">Tủ lạnh Samsung Inverter 320L</div>
                <div style="text-decoration: line-through; font-size: 11px; margin-top: 5px;">14.500.000đ</div>
                <div class="bold" style="font-size: 15px;">12.325.000đ</div>
                
                <div style="margin-top: 10px;">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: 45%;"></div>
                        <div class="progress-bar-text">Đã bán 45% - Còn 11 sản phẩm</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 5px; margin-top: 10px;">
                    <div class="btn" style="flex: 1; font-size: 11px; background-color: #000; color: #fff;">MUA NGAY</div>
                    <div class="btn" style="flex: 1; font-size: 11px;">+ GIỎ HÀNG</div>
                </div>
            </div>
            
            <!-- Item 3 -->
            <div class="border-box double-border text-center" style="background-color: #ffffff;">
                <div style="position: relative;">
                    <div class="badge" style="position: absolute; top: 5px; left: 5px; background: white;">GIẢM 30%</div>
                    <div style="height: 120px; border: 1px dashed black; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">[ Ảnh nồi cơm điện ]</div>
                </div>
                <div class="bold" style="font-size: 13px;">Nồi cơm điện cao tần Toshiba</div>
                <div style="text-decoration: line-through; font-size: 11px; margin-top: 5px;">3.200.000đ</div>
                <div class="bold" style="font-size: 15px;">2.240.000đ</div>
                
                <div style="margin-top: 10px;">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: 95%;"></div>
                        <div class="progress-bar-text">Đã bán 95% - CHÁY HÀNG</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 5px; margin-top: 10px;">
                    <div class="btn-disabled" style="flex: 1; font-size: 11px; width: 100%;">HẾT HÀNG</div>
                </div>
            </div>
            
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[48] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-crm {{
    position: relative;
    height: 480px;
}}
.sweetalert-popup {{
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 420px;
    border: 3px double #000000;
    background-color: #ffffff;
    padding: 20px;
    box-sizing: border-box;
    z-index: 100;
    box-shadow: 5px 5px 0px #000;
}}
.overlay {{
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.7);
    z-index: 50;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN - QUẢN LÝ KHÁCH HÀNG (CRM) & HỆ THỐNG XỬ PHẠT (BANNING)</div>
    <div class="header-bar">
        <div>Trang quản trị Admin CRM</div>
        <div>Tìm kiếm khách hàng | Quản lý tài khoản VIP</div>
    </div>
    
    <div class="layout-crm border-box">
        <div class="overlay"></div>
        
        <div class="flex-space" style="margin-bottom: 10px;">
            <div class="bold">DANH SÁCH KHÁCH HÀNG TOÀN HỆ THỐNG</div>
            <div>Tìm kiếm: [ Nguyễn Văn A ]</div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Họ và Tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Hạng VIP</th>
                    <th style="text-align: center;">Điểm số</th>
                    <th style="text-align: center;">Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>[Img]</td>
                    <td class="bold">Nguyễn Văn A</td>
                    <td>nguyenvanasale@gmail.com</td>
                    <td>0905123456</td>
                    <td>Hạng Vàng</td>
                    <td style="text-align: center;">1.250</td>
                    <td style="text-align: center;"><span class="badge">Active</span></td>
                    <td><div class="btn" style="font-size: 10px; padding: 2px 6px;">Xử phạt</div></td>
                </tr>
                <tr>
                    <td>[Img]</td>
                    <td class="bold">Trần Văn Spam</td>
                    <td>spambot99@gmail.com</td>
                    <td>0912345678</td>
                    <td>Hạng Đồng</td>
                    <td style="text-align: center;">120</td>
                    <td style="text-align: center; color: red;"><span class="badge" style="border-color: red;">Banned</span></td>
                    <td><div class="btn" style="font-size: 10px; padding: 2px 6px;">Mở khóa</div></td>
                </tr>
            </tbody>
        </table>
        
        <!-- SweetAlert2 Banning Modal Popup Mockup -->
        <div class="sweetalert-popup">
            <div class="bold text-center" style="font-size: 16px; border-bottom: 1px solid black; padding-bottom: 5px;">⚠️ XỬ PHẠT KHÁCH HÀNG VI PHẠM</div>
            
            <div style="margin-top: 15px; font-size: 12px; line-height: 1.4;">
                Hệ thống ghi nhận tài khoản <span class="bold">Nguyễn Văn A</span> đăng tải bình luận chứa từ khóa nhạy cảm. Chọn hình thức xử phạt:
            </div>
            
            <div style="margin-top: 15px; font-size: 12px;">
                <label><input type="radio" name="ban_type" checked> Chỉ xóa bình luận vi phạm</label><br>
                <label><input type="radio" name="ban_type"> Xóa bình luận + Khóa tài khoản 1 ngày</label><br>
                <label><input type="radio" name="ban_type"> Xóa bình luận + Khóa tài khoản 3 ngày</label><br>
                <label><input type="radio" name="ban_type"> Khóa tài khoản vĩnh viễn (Banned Permanent)</label>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <div class="btn" style="flex: 1; background-color: #000; color: #fff; font-size: 11px;">ĐỒNG Ý XỬ PHẠT</div>
                <div class="btn" style="flex: 1; font-size: 11px;">HỦY BỎ</div>
            </div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[49] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-articles {{
    display: grid;
    grid-template-columns: 8fr 4fr;
    gap: 15px;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN - QUẢN LÝ BÀI VIẾT BLOG CÔNG NGHỆ (CRUD ARTICLES)</div>
    <div class="header-bar">
        <div>Trang quản trị bài viết Admin</div>
        <div>Bài viết chia sẻ kiến thức công nghệ và đánh giá thiết bị (UGC & Admin Blog)</div>
    </div>
    
    <div class="layout-articles">
        <div class="border-box double-border">
            <div class="flex-space" style="border-bottom: 1px solid black; padding-bottom: 10px; margin-bottom: 15px;">
                <div class="bold">DANH SÁCH BÀI VIẾT UGC CHỜ DUYỆT HÀNG LOẠT</div>
                <div class="btn" style="background-color: #000; color: #fff; font-size: 12px;">⚡ DUYỆT HÀNG LOẠT BÀI VIẾT (BULK APPROVE)</div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">[x]</th>
                        <th>Tiêu đề bài viết</th>
                        <th>Tác giả</th>
                        <th style="text-align: center;">Trạng thái</th>
                        <th style="text-align: center;">Điểm AI</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background-color: #fafafa;">
                        <td style="text-align: center;">[x]</td>
                        <td class="bold">Mẹo tiết kiệm điện tối ưu cho điều hòa Inverter</td>
                        <td>Nguyễn Văn A</td>
                        <td style="text-align: center;"><span class="badge">Pending</span></td>
                        <td style="text-align: center;" class="bold">88 / 100</td>
                        <td>04/06/2026</td>
                    </tr>
                    <tr style="background-color: #fafafa;">
                        <td style="text-align: center;">[x]</td>
                        <td class="bold">Đánh giá thực tế máy hút bụi cầm tay Dyson</td>
                        <td>Lê Thị B</td>
                        <td style="text-align: center;"><span class="badge">Pending</span></td>
                        <td style="text-align: center;" class="bold">92 / 100</td>
                        <td>04/06/2026</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">[ ]</td>
                        <td class="bold" style="text-decoration: line-through;">Nhận cá độ Euro 2026 tỉ lệ ăn cực cao...</td>
                        <td>SpamBot99</td>
                        <td style="text-align: center; color: red;"><span class="badge" style="border-color: red;">Pending</span></td>
                        <td style="text-align: center; color: red;" class="bold">15 / 100</td>
                        <td>03/06/2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="border-box double-border">
            <div class="bold text-center" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">THÔNG TIN DUYỆT HÀNG LOẠT</div>
            
            <div style="font-size: 12px; line-height: 1.5;">
                <span class="bold">Quy tắc lọc bài viết:</span><br>
                • Hệ thống tự động chọn (tích checkbox) các bài viết ở trạng thái <span class="bold">Pending</span> có điểm kiểm duyệt AI chất lượng >= 80.<br>
                • Click nút "Bulk Approve" để xuất bản đồng thời và cộng điểm tích lũy thưởng cho khách hàng.
            </div>
            
            <div style="border-top: 1px dashed black; margin-top: 15px; padding-top: 15px; font-size: 11px;">
                <span class="bold">Thông tin thống kê đợt duyệt:</span><br>
                - Số bài viết phù hợp: 2 bài viết.<br>
                - Điểm tích lũy cộng dự kiến: +100 điểm thưởng.
            </div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[50] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
.layout-customizer {{
    display: grid;
    grid-template-columns: 5fr 7fr;
    gap: 15px;
}}
.preview-iframe {{
    border: 2px dashed red;
    padding: 10px;
    height: 380px;
    background-color: #ffffff;
    position: relative;
}}
.overlay-highlight {{
    position: absolute;
    top: 5px;
    left: 5px;
    right: 5px;
    height: 70px;
    border: 2px dashed red;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(255, 0, 0, 0.05);
    font-size: 11px;
    font-weight: bold;
    color: red;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC GIAO DIỆN - TÙY BIẾN GIAO DIỆN HEADER/FOOTER (THEME CUSTOMIZER)</div>
    <div class="header-bar">
        <div>Trang quản trị giao diện Admin Customizer</div>
        <div>Cơ chế Live Preview tương tác thời gian thực qua Iframe</div>
    </div>
    
    <div class="layout-customizer">
        <div class="border-box double-border">
            <div class="bold text-center" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">BẢNG ĐIỀU CHỈNH GIAO DIỆN</div>
            
            <div style="border: 1px solid #000; padding: 2px 10px; display: inline-block;" class="bold">Tab: Đầu trang (Header)</div>
            <div style="border: 1px dashed #aaa; padding: 2px 10px; display: inline-block; color: #aaa;">Tab: Chân trang (Footer)</div>
            
            <div style="margin-top: 15px;">
                <label class="bold">Màu nền Header (Background Color):</label><br>
                <input type="text" value="#0046ab" style="width: 95%; margin-top: 5px; border: 1px solid black; padding: 5px;">
            </div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Số hotline liên hệ hiển thị:</label><br>
                <input type="text" value="1900 6688" style="width: 95%; margin-top: 5px; border: 1px solid black; padding: 5px;">
            </div>
            
            <div style="margin-top: 10px;">
                <label class="bold">Đường dẫn tệp Logo ảnh đại diện:</label><br>
                <input type="text" value="/uploads/images/logo.png" style="width: 95%; margin-top: 5px; border: 1px solid black; padding: 5px;">
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <div class="btn" style="flex: 1; background-color: #000; color: #fff;">LƯU THAY ĐỔI</div>
                <div class="btn" style="flex: 1;">KHÔI PHỤC</div>
            </div>
        </div>
        
        <div class="border-box double-border">
            <div class="bold" style="border-bottom: 1px solid black; padding-bottom: 5px; margin-bottom: 15px;">XEM TRƯỚC TRỰC TIẾP (LIVE PREVIEW)</div>
            
            <div class="preview-iframe">
                <div class="overlay-highlight">Highlight Overlay: Đang chỉnh sửa Header</div>
                
                <div style="margin-top: 80px; padding: 20px; border: 1px solid #ccc; font-size: 12px; line-height: 1.6; text-align: center;">
                    [ Giao diện mô phỏng Trang chủ hiển thị ở chế độ Iframe ]<br>
                    • Nội dung tự động cập nhật ngay khi thay đổi giá trị màu sắc hay chữ ở bảng điều khiển bên trái mà không cần tải lại trang.
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

wireframes[51] = f"""<!DOCTYPE html>
<html>
<head>
<style>
{SHARED_CSS}
body {{
    background-color: #000000;
    color: #ffffff;
    font-family: 'Courier New', Courier, monospace;
}}
.container {{
    border: 3px double #ffffff;
    background-color: #000000;
    color: #ffffff;
}}
.title-bar {{
    border: 2px solid #ffffff;
    background-color: #000000;
    color: #ffffff;
}}
.header-bar {{
    border: 1px solid #ffffff;
    color: #ffffff;
}}
.border-box {{
    border: 1px solid #ffffff;
    background-color: #000000;
    color: #ffffff;
}}
.double-border {{
    border: 3px double #ffffff;
}}
.footer-bar {{
    border: 1px solid #ffffff;
    color: #ffffff;
}}
.cli-container {{
    padding: 20px;
    font-size: 14px;
    line-height: 1.6;
}}
</style>
</head>
<body>
<div class="container">
    <div class="title-bar">BỐ CỤC CLI - SMART SETUP WIZARD & SYSTEM ORCHESTRATOR</div>
    <div class="header-bar">
        <div>Command Line Tool: start.bat</div>
        <div>Hệ điều hành tương thích: Windows PowerShell / CMD</div>
    </div>
    
    <div class="border-box double-border cli-container">
        <div>========================================================================</div>
        <div class="bold">    HỆ THỐNG CÀI ĐẶT TỰ ĐỘNG THÔNG MINH - DIENMAYPRO SMART SETUP WIZARD v8.0</div>
        <div>========================================================================</div>
        <div style="margin-top: 15px;">[+] Đang chạy kiểm tra chẩn đoán hệ thống...</div>
        <div>[OK] PHP CLI v8.2.0 phát hiện thành công.</div>
        <div>[OK] Composer dependencies phát hiện đầy đủ.</div>
        <div>[OK] Node.js & NPM node_modules phát hiện đầy đủ.</div>
        
        <div style="margin-top: 15px; border-top: 1px dashed #ffffff; padding-top: 10px;">
            Vui lòng nhập lựa chọn chức năng thực thi (1-6):
        </div>
        <div style="margin-top: 10px; padding-left: 20px;">
            <span class="bold">[1] Check System Prerequisites</span> (Kiểm tra phần cứng & phần mềm nền)<br>
            <span class="bold">[2] Initialize Database Configuration</span> (Tạo file môi trường .env tự động)<br>
            <span class="bold">[3] Run Database Migrations & Seeders</span> (Chạy khởi tạo bảng & dữ liệu mẫu)<br>
            <span class="bold">[4] Clear Cache & Optimize Application</span> (Làm sạch cache Laravel/Vite)<br>
            <span class="bold">[5] Fast Rebuild & Run Dev Server</span> (Khởi động máy chủ thử nghiệm nhanh)<br>
            <span class="bold">[6] System Repair Assistant & Troubleshooting</span> (Trình tự chữa lỗi tự động)
        </div>
        
        <div style="margin-top: 15px; border-top: 1px dashed #ffffff; padding-top: 10px;">
            <span class="bold">Nhập lựa chọn của bạn >> 5</span>
        </div>
        <div>[+] Đang chạy tiến trình khởi động máy chủ...</div>
        <div>[+] Laravel development server started: <span class="bold">http://127.0.0.1:8000</span></div>
        <div>[+] Vite asset bundler started in development mode.</div>
        <div>========================================================================</div>
    </div>
    
    <div class="footer-bar">
        <div>Hệ thống siêu thị điện máy DIENMAY PRO</div>
        <div>Bản in Đồ án / Phụ lục Báo cáo đặc tả hệ thống</div>
    </div>
</div>
</body>
</html>"""

async def run():
    print("Starting Playwright wireframe generator...")
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page(device_scale_factor=2)
        
        output_dirs = [
            r"d:\repogist\ThuongMaiDienTu\images",
            r"d:\HOC\Hoc4\pywword\images"
        ]
        
        # Ensure directories exist
        for d in output_dirs:
            os.makedirs(d, exist_ok=True)
            
        temp_html_path = "temp_wireframe.html"
        
        for fid, html in wireframes.items():
            print(f"Generating wireframe for function 7.{fid}...")
            with open(temp_html_path, "w", encoding="utf-8") as f:
                f.write(html)
                
            # Load in browser
            abs_url = "file:///" + os.path.abspath(temp_html_path).replace("\\", "/")
            await page.goto(abs_url)
            await page.wait_for_timeout(500) # Give it a brief moment
            
            # Capture the container element precisely
            container = page.locator(".container")
            
            # Save screenshots to both directories
            for out_dir in output_dirs:
                dest_path = os.path.join(out_dir, f"ui_layout_{fid}.png")
                await container.screenshot(path=dest_path)
                print(f"  Saved: {dest_path}")
                
        await browser.close()
        if os.path.exists(temp_html_path):
            os.remove(temp_html_path)
    print("All wireframes generated successfully!")

if __name__ == "__main__":
    asyncio.run(run())
