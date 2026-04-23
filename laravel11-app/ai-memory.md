# AI Memory - Dự án Thương Mại Điện Tử

## Tiết độ và Ngữ cảnh hiện tại
Dự án được khởi tạo cấu trúc cơ sở dữ liệu dựa trên file `databasev1.sql`.
Toàn bộ 20 bảng SQL đã được chuyển đổi sang chuẩn Migration của Laravel, bao gồm các ràng buộc khóa ngoại, timestamp và soft deletes.

## Các file đã tạo / chỉnh sửa:
- Đã **xóa** các file migration mặc định của Laravel (users, cache, jobs, etc.) để tránh xung đột.
- Đã **tạo mới 20 file migration** nằm trong thư mục `database/migrations/`:
  1. `2026_01_01_000001_create_roles_table.php`
  2. `2026_01_01_000002_create_categories_table.php`
  3. `2026_01_01_000003_create_suppliers_table.php`
  4. `2026_01_01_000004_create_settings_table.php`
  5. `2026_01_01_000005_create_coupons_flash_sales_table.php`
  6. `2026_01_01_000006_create_users_table.php`
  7. `2026_01_01_000007_create_products_table.php`
  8. `2026_01_01_000008_create_purchase_orders_table.php`
  9. `2026_01_01_000009_create_user_sessions_table.php`
  10. `2026_01_01_000010_create_activity_logs_table.php`
  11. `2026_01_01_000011_create_product_specifications_table.php`
  12. `2026_01_01_000012_create_product_variants_table.php`
  13. `2026_01_01_000013_create_wishlists_recently_viewed_table.php`
  14. `2026_01_01_000014_create_orders_table.php`
  15. `2026_01_01_000015_create_ai_chatbot_history_table.php`
  16. `2026_01_01_000016_create_repair_tickets_table.php`
  17. `2026_01_01_000017_create_inventory_items_table.php`
  18. `2026_01_01_000018_create_order_details_table.php`
  19. `2026_01_01_000019_create_reward_points_table.php`
  20. `2026_01_01_000020_create_cashbooks_table.php`

## Logic Database quan trọng
- **Phân nhóm bảng (chuỗi tạo khóa ngoại an toàn):** Các file đã được sắp xếp tiền tố theo thứ tự an toàn (Group 1 đến Group 4) để khi chạy `php artisan migrate`, SQL engine sẽ không báo lỗi thiếu bảng tham chiếu (ví dụ: `roles` tạo xong mới tới `users`).
- **Khóa chính**: Thay vì `$table->id()`, dự án dùng `$table->increments('pk_id')` cho khớp cấu trúc MySQL thuần `INT UNSIGNED AUTO_INCREMENT`.
- **Kiểu dữ liệu tiền tệ**: Sử dụng `unsignedBigInteger` thay vì kiểu `int` thông thường.

## TODO (Việc cần làm tiếp theo)
- [ ] Kết nối dự án Laravel với Database thật (sửa file `.env`).
- [ ] Chạy lệnh `php artisan migrate` để đồng bộ Database.
- [x] Khởi tạo các Eloquent Model tương ứng với 20 bảng cơ sở dữ liệu để thao tác logic ORM.
- [ ] Tạo file Seeder để chèn dữ liệu mẫu (đặc biệt là bảng Roles và Categories) phục vụ kiểm duyệt.

## Ghi chú về Models:
Đã thiết lập đầy đủ 20 Eloquent Models tại thư mục `app/Models/` (Role, Category, Supplier, v.v.). Các Model được gán toàn bộ relation (`hasMany`, `belongsTo`), khóa chính custom (`$primaryKey`), và trạng thái timestamp theo đúng Schema.
