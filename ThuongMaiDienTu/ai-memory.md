# AI Memory - Dự Án Thương Mại Điện Tử

## Tiến độ và Ngữ cảnh hiện tại
Dự án e-commerce xây dựng trên Laravel, tập trung vào cấu trúc ERP/CMS chuyên nghiệp.

## Các công việc đã hoàn thành:
- **Hạ tầng & Database:**
    - Tạo mới 20 file migration và 20 Eloquent Models hoàn chỉnh (đã xóa migration mặc định của Laravel).
    - Thiết lập cấu trúc thư mục chuẩn ERP/E-commerce (Enums, Services, Requests, Partials).
    - Cấu hình Tailwind CSS xịn qua Vite/NPM.
    - Thiết lập `RoleSeeder` và `UserSeeder` với dữ liệu mẫu (Admin, Manager, 20 Khách hàng).
- **Xác thực (Authentication):**
    - Chuyển logic Auth từ Blade sang `AuthController`.
    - Fix lỗi mất Session (không lưu đăng nhập).
    - Phân luồng: Đăng nhập xong về Trang chủ. Admin có link vào Dashboard trên Header.
- **Phân hệ Admin (Giai đoạn 1):**
    - Hoàn thành **Layout Admin Premium** với Sidebar tách biệt (`admin.partials.sidebar`).
    - Hoàn thành **CRUD Khách hàng** (Tài khoản) với giao diện hiện đại, Modal AJAX, tìm kiếm và phân trang.
    - Tích hợp SweetAlert2 cho thông báo và xác nhận.
    - Tạo `AutoLoginAdmin` middleware để thuận tiện dev (tự động đăng nhập Admin khi vào `/admin`).
- **Frontend Trang chủ & Khách hàng:**
  - `resources/views/layouts/app.blade.php`: Giao diện Master (chứa style tổng thể và gọi include).
  - `resources/views/partials/header.blade.php`: Tách module Header và Topbar.
  - `resources/views/partials/footer.blade.php`: Tách module Footer.
  - `resources/views/home.blade.php`: Trang chủ bao gồm Sidebar Menu, Banner Hero, Flash Sale, Sản phẩm nổi bật.
  - Giỏ hàng (`shoppingcart.blade.php`) tích hợp dữ liệu từ Database.
  - `app/Http/Controllers/HomeController.php`: Xử lý logic route trang chủ.

## Thông tin kỹ thuật:
- Auth: `user_id`, `password_hash`, custom primary key.
- Phân quyền: Admin (1), Quản lý (2), Khách hàng (3), Nhân viên (4).

## TODO (Việc cần làm tiếp theo)
- [x] Chạy lệnh `php artisan migrate` để đồng bộ Database.
- [x] Khởi tạo các Eloquent Model tương ứng với 20 bảng cơ sở dữ liệu.
- [x] Khởi tạo cấu trúc thư mục App & Views.
- [x] Tạo file Seeder để chèn dữ liệu mẫu (Roles & Users).
- [ ] **Giai đoạn 2:** Triển khai CRUD Danh mục và CRUD Sản phẩm (kèm biến thể).
- [ ] **Giai đoạn 2:** Hiển thị sản phẩm lên trang chủ khách hàng (Frontend, lấy dữ liệu thật thay demo).
- [ ] Bắt đầu viết logic trong `CartService` và `InventoryService`.
- [ ] Kết nối dự án Laravel với Database thật (sửa file `.env`).
- [ ] Hoàn thiện logic thêm sản phẩm vào giỏ hàng thật (Session-based).

## Ghi chú quan trọng:
- Đã tách Sidebar thành `resources/views/admin/partials/sidebar.blade.php` để team dễ phối hợp.
- Sử dụng `primaryKey = 'user_id'` và `password_hash` thay cho mặc định của Laravel để khớp với yêu cầu DB.
