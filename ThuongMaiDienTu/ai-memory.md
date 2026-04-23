# AI Memory - Dự án Thương Mại Điện Tử

## Tiết độ và Ngữ cảnh hiện tại
Dự án được khởi tạo cấu trúc cơ sở dữ liệu dựa trên file `databasev1.sql`.
Toàn bộ 20 bảng SQL đã được chuyển đổi sang chuẩn Migration của Laravel, bao gồm các ràng buộc khóa ngoại, timestamp và soft deletes.

## Các file đã tạo / chỉnh sửa:
- Đã **xóa** các file migration mặc định của Laravel (users, cache, jobs, etc.) để tránh xung đột.
- Đã **tạo mới 20 file migration** nằm trong thư mục `database/migrations/`
- **Frontend Trang chủ:**
  - `resources/views/layouts/app.blade.php`: Giao diện Master (chứa style tổng thể và gọi include).
  - `resources/views/partials/header.blade.php`: Tách module Header và Topbar.
  - `resources/views/partials/footer.blade.php`: Tách module Footer.
  - `resources/views/home.blade.php`: Trang chủ bao gồm Sidebar Menu, Banner Hero, Flash Sale, Sản phẩm nổi bật.
  - `app/Http/Controllers/HomeController.php`: Xử lý logic route trang chủ.
  - Sửa `routes/web.php` trỏ `/` về `HomeController@index`.

## Logic Database quan trọng
- **Phân nhóm bảng (chuỗi tạo khóa ngoại an toàn):** Các file đã được sắp xếp tiền tố theo thứ tự an toàn (Group 1 đến Group 4) để khi chạy `php artisan migrate`, SQL engine sẽ không báo lỗi thiếu bảng tham chiếu (ví dụ: `roles` tạo xong mới tới `users`).
- **Khóa chính**: Thay vì `$table->id()`, dự án dùng `$table->increments('pk_id')` cho khớp cấu trúc MySQL thuần `INT UNSIGNED AUTO_INCREMENT`.
- **Kiểu dữ liệu tiền tệ**: Sử dụng `unsignedBigInteger` thay vì kiểu `int` thông thường.

## TODO (Việc cần làm tiếp theo)
- [ ] Kết nối dự án Laravel với Database thật (sửa file `.env`).
- [ ] Chạy lệnh `php artisan migrate` để đồng bộ Database.
- [x] Khởi tạo các Eloquent Model tương ứng với 20 bảng cơ sở dữ liệu để thao tác logic ORM.
- [ ] Tạo file Seeder để chèn dữ liệu mẫu (đặc biệt là bảng Roles và Categories) phục vụ kiểm duyệt.
- [ ] Tích hợp lấy dữ liệu động từ Database hiển thị ra trang chủ thay cho giao diện demo hiện tại.

## Ghi chú về Models:
Đã thiết lập đầy đủ 20 Eloquent Models tại thư mục `app/Models/` (Role, Category, Supplier, v.v.). Các Model được gán toàn bộ relation (`hasMany`, `belongsTo`), khóa chính custom (`$primaryKey`), và trạng thái timestamp theo đúng Schema.
