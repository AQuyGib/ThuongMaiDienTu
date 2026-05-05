# AI Memory - Dự án Thương Mại Điện Tử

## Tiến độ và Ngữ cảnh hiện tại
Dự án được khởi tạo cấu trúc cơ sở dữ liệu dựa trên file `databasev1.sql`.
Toàn bộ 20 bảng SQL đã được chuyển đổi sang chuẩn Migration của Laravel, bao gồm các ràng buộc khóa ngoại, timestamp và soft deletes.

## Các file đã tạo / chỉnh sửa & Công việc hoàn thành:
- **Hạ tầng & Database:**
    - Đã **xóa** các file migration mặc định của Laravel (users, cache, jobs, etc.) để tránh xung đột.
    - Đã **tạo mới 20 file migration** nằm trong thư mục `database/migrations/` và 20 Eloquent Models hoàn chỉnh.
    - Thiết lập cấu trúc thư mục chuẩn ERP/E-commerce (Enums, Services, Requests, Partials).
    - Cấu hình Tailwind CSS xịn qua Vite/NPM.
    - Thiết lập `RoleSeeder` và `UserSeeder` với dữ liệu mẫu (Admin, Manager, 20 Khách hàng).
- **Frontend Trang chủ:**
  - `resources/views/layouts/app.blade.php`: Giao diện Master (chứa style tổng thể và gọi include).
  - `resources/views/partials/header.blade.php`: Tách module Header và Topbar.
  - `resources/views/partials/footer.blade.php`: Tách module Footer.
  - `resources/views/home.blade.php`: Trang chủ bao gồm Sidebar Menu, Banner Hero, Flash Sale, Sản phẩm nổi bật.
  - `app/Http/Controllers/HomeController.php`: Xử lý logic route trang chủ.
  - Sửa `routes/web.php` trỏ `/` về `HomeController@index`.
- **Phân hệ Admin (Giai đoạn 1):**
    - Hoàn thành **Layout Admin Premium** với Sidebar tách biệt (`admin.partials.sidebar`).
    - Hoàn thành **CRUD Khách hàng** (Tài khoản) với giao diện hiện đại, Modal AJAX, tìm kiếm và phân trang.
    - Tích hợp SweetAlert2 cho thông báo và xác nhận.
    - Tạo `AutoLoginAdmin` middleware để thuận tiện dev (tự động đăng nhập Admin khi vào `/admin`).

## Thông tin kỹ thuật:
- **Cấu trúc User:** `user_id`, `role_id`, `full_name`, `email`, `password_hash`, `is_2fa_enabled`, `two_factor_secret`, `member_tier`, `status`, `created_at`.
- **Phân quyền:** Admin (1), Quản lý (2), Khách hàng (3).
- **Vite/Build:** Đã chạy `npm run build` để tối ưu CSS/JS.

## TODO (Việc cần làm tiếp theo)
- [ ] Kết nối dự án Laravel với Database thật (sửa file `.env`).
- [x] Chạy lệnh `php artisan migrate` để đồng bộ Database.
- [x] Khởi tạo các Eloquent Model tương ứng với 20 bảng cơ sở dữ liệu.
- [x] Khởi tạo cấu trúc thư mục App & Views.
- [x] Tạo file Seeder để chèn dữ liệu mẫu (Roles & Users).
- [ ] Tích hợp lấy dữ liệu động từ Database hiển thị ra trang chủ thay cho giao diện demo hiện tại.
- [ ] **Giai đoạn 2:** Triển khai CRUD Danh mục và CRUD Sản phẩm (kèm biến thể).
- [ ] **Giai đoạn 2:** Hiển thị sản phẩm lên trang chủ khách hàng (Frontend).
- [ ] Bắt đầu viết logic trong `CartService` và `InventoryService`.

## Ghi chú quan trọng:
- Đã tách Sidebar thành `resources/views/admin/partials/sidebar.blade.php` để team dễ phối hợp.
- Sử dụng `primaryKey = 'user_id'` và `password_hash` thay cho mặc định của Laravel để khớp với yêu cầu DB.
