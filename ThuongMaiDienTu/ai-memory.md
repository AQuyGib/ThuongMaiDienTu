# AI Memory - Dự án Thương Mại Điện Tử

## Tiết độ và Ngữ cảnh hiện tại
Dự án được khởi tạo cấu trúc cơ sở dữ liệu dựa trên file `databasev1.sql`.
Toàn bộ 20 bảng SQL đã được chuyển đổi sang chuẩn Migration của Laravel, bao gồm các ràng buộc khóa ngoại, timestamp và soft deletes.

## Các file đã tạo / chỉnh sửa:
- Đã **xóa** các file migration mặc định của Laravel (users, cache, jobs, etc.) để tránh xung đột.
- Đã **tạo mới 20 file migration** nằm trong thư mục `database/migrations/`.
- Đã thiết lập đầy đủ 20 Eloquent Models tại thư mục `app/Models/`.
- **Khởi tạo cấu trúc thư mục dự án:**
    - `app/Enums/`: Tạo `OrderStatus.php`, `RoleType.php`.
    - `app/Http/Controllers/`: Tạo các sub-folder `Admin/`, `Frontend/`, `Api/`.
    - `app/Http/Requests/`: Thư mục chứa validate.
    - `app/Services/`: Tạo `InventoryService.php`, `CartService.php`, `RewardPointService.php`.
    - `app/Observers/`: Thư mục lắng nghe sự kiện Model.
    - `resources/views/`: Chia folder `admin/`, `frontend/`, `pos/`, `components/`, `emails/`.
    - `public/assets/`: Chia folder `admin/`, `frontend/` (css, js, img).
    - `public/uploads/`: Chia folder `products/`, `banners/`.
    - `routes/admin.php`: Tạo mới file route cho Admin và đăng ký trong `bootstrap/app.php` với tiền tố `/admin`.

## Logic Database quan trọng
- **Phân nhóm bảng (chuỗi tạo khóa ngoại an toàn):** Các file đã được sắp xếp tiền tố theo thứ tự an toàn để khi chạy `php artisan migrate`, SQL engine sẽ không báo lỗi thiếu bảng tham chiếu.
- **Khóa chính**: Thay vì `$table->id()`, dự án dùng `$table->increments('pk_id')` cho khớp cấu trúc MySQL thuần `INT UNSIGNED AUTO_INCREMENT`.
- **Kiểu dữ liệu tiền tệ**: Sử dụng `unsignedBigInteger`.

## TODO (Việc cần làm tiếp theo)
- [ ] Kết nối dự án Laravel với Database thật (sửa file `.env`).
- [ ] Chạy lệnh `php artisan migrate` để đồng bộ Database.
- [x] Khởi tạo các Eloquent Model tương ứng với 20 bảng cơ sở dữ liệu.
- [x] Khởi tạo cấu trúc thư mục App (Enums, Services, Controllers, Requests).
- [x] Khởi tạo cấu trúc thư mục Views (Admin, Frontend, POS).
- [x] Khởi tạo cấu trúc thư mục Public (Assets, Uploads).
- [ ] Tạo file Seeder để chèn dữ liệu mẫu.
- [ ] Bắt đầu viết logic trong `CartService` và `InventoryService`.

## Ghi chú về Models:
Đã thiết lập đầy đủ 20 Eloquent Models tại thư mục `app/Models/` (Role, Category, Supplier, v.v.). Các Model được gán toàn bộ relation (`hasMany`, `belongsTo`), khóa chính custom (`$primaryKey`), và trạng thái timestamp theo đúng Schema.
