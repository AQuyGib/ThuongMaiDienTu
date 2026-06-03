# 🚀 Hướng dẫn chạy dự án DIENMAYPRO

Tài liệu này hướng dẫn bạn cách sử dụng công cụ chạy tự động `chay_du_an.bat` để khởi động môi trường phát triển một cách nhanh nhất.

---

## 🛠 Yêu cầu hệ thống
1. **XAMPP**: Đã cài đặt và đang chạy **MySQL** (Port 3306).
2. **Node.js**: Đã cài đặt trên máy.
3. **PHP**: Đã cài đặt (thường đi kèm XAMPP).

---

## 🏃‍♂️ Các bước thực hiện

### 1. Khởi động MySQL
Mở **XAMPP Control Panel** và nhấn **Start** cho dịch vụ MySQL.

### 2. Chạy File tự động
Tìm file `chay_du_an.bat` ở thư mục gốc của dự án và **Click đúp** để chạy.

### 3. Theo dõi quá trình
Cửa sổ lệnh sẽ thực hiện 5 bước:
- **Bước 1**: Kiểm tra kết nối Database.
- **Bước 2**: Dọn dẹp cache và file rác (tránh lỗi giao diện).
- **Bước 3**: Kiểm tra file cấu hình `.env`.
- **Bước 4**: Khởi chạy Laravel Backend Server.
- **Bước 5**: Khởi chạy Vite Frontend (React) và mở trình duyệt.

---

## 🛑 Cách dừng dự án
Để tắt dự án một cách an toàn và dọn dẹp tài nguyên máy tính:
1. Quay lại cửa sổ dòng lệnh chính.
2. Nhấn **phím bất kỳ**.
3. Hệ thống sẽ tự động đóng các server đang chạy ngầm.

---

## ❓ Xử lý lỗi thường gặp

| Lỗi | Nguyên nhân | Cách xử lý |
|:--- |:--- |:--- |
| **Cảnh báo MySQL chưa bật** | Bạn chưa Start MySQL trong XAMPP | Mở XAMPP và Start MySQL, sau đó chạy lại file .bat |
| **Trang web báo lỗi 500** | Chưa có Key bảo mật | Script sẽ tự tạo Key ở lần chạy đầu tiên, hãy thử F5 trang web |
| **Giao diện không đổi khi sửa code** | Vite Server chưa chạy xong | Đợi cửa sổ Vite hiện chữ "ready" rồi tải lại trang |

---
*Chúc bạn có trải nghiệm lập trình tuyệt vời!*
