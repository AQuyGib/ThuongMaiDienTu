# AI Memory - Dự Án Thương Mại Điện Tử

## Tiến độ và Ngữ cảnh hiện tại
Dự án e-commerce xây dựng trên Laravel, tập trung vào cấu trúc ERP/CMS chuyên nghiệp.

## Các công việc đã hoàn thành:
- **Hạ tầng & Database:**
    - Migration & Models cho 20 bảng SQL.
    - RoleSeeder & UserSeeder (Admin: admin@techzone.vn / admin123).
- **Xác thực (Authentication):**
    - Chuyển logic Auth từ Blade sang `AuthController`.
    - Fix lỗi mất Session (không lưu đăng nhập).
    - Phân luồng: Đăng nhập xong về Trang chủ. Admin có link vào Dashboard trên Header.
- **Phân hệ Admin:**
    - Layout Admin Premium + Sidebar.
    - CRUD Tài khoản người dùng (Modal AJAX + SweetAlert2).
- **Frontend:**
    - Master Layout (app.blade.php).
    - Trang chủ (home.blade.php) tích hợp Header/Footer partials.
    - Giỏ hàng (shoppingcart.blade.php) tích hợp dữ liệu từ Database.

## Thông tin kỹ thuật:
- Auth: `user_id`, `password_hash`, custom primary key.
- Phân quyền: Admin (1), Quản lý (2), Khách hàng (3), Nhân viên (4).

## TODO:
- [ ] Giai đoạn 2: Triển khai CRUD Danh mục và Sản phẩm.
- [ ] Xây dựng InventoryService để quản lý kho.
- [ ] Hoàn thiện logic thêm sản phẩm vào giỏ hàng thật (Session-based).
