# AI Memory - Dự Án Thương Mại Điện Tử

## Tiến độ và Ngữ cảnh hiện tại
Dự án e-commerce xây dựng trên Laravel, tập trung vào cấu trúc ERP/CMS chuyên nghiệp. Đang phát triển các phân hệ: Articles & Lifestyle, Storefront (So sánh & Bộ lọc), Phiếu sửa chữa & Dịch vụ (Repair Tickets & Customer Portal), và Phân hệ Quản lý & Phát Video (Videos Management).

## Các file đã tạo / chỉnh sửa & Công việc hoàn thành

### 1. Phân hệ Quản lý & Phát Video (Videos Management) - Mới hoàn thành từ branch `Hien/Video`
- **Hạ tầng & Database:**
  - Đã tạo các file migration cho bảng `videos` và `video_comments`.
  - Hỗ trợ các trường `video_path` nullable (để hỗ trợ link YouTube), các trường thông số video tự động, và liên kết khóa ngoại với bảng `users` (`user_id`).
  - Đã chạy migration thành công và seeding dữ liệu video mẫu qua `VideoSeeder`.
- **Giao diện Admin (Quản trị Video):**
  - Danh sách video (`admin/videos/index.blade.php`): Thiết kế lại bảng chuyên nghiệp, hiển thị ID (số thứ tự thực tế tăng dần), "Ảnh minh họa" (thumbnail), "Tiêu đề", "Mô tả chi tiết" (line-clamp 2 dòng), và các nút trạng thái Ẩn/Hiện bằng AJAX, nút Sửa, Xóa với xác nhận SweetAlert2.
  - Trang đăng video (`admin/videos/create.blade.php`): Giao diện 2 cột cân đối, thanh thoát, không lồng card. Thêm vách ngăn dọc và tối ưu chiều cao form để tránh cuộn trang. Hỗ trợ upload video nội bộ (kéo thả drag & drop, validate size lên 100MB) hoặc nhúng link YouTube. Tự động lấy thời lượng video bằng JS.
  - Trang sửa video (`admin/videos/edit.blade.php`): Cho phép chỉnh sửa thông tin video linh hoạt.
  - Trang chi tiết video (`admin/videos/show.blade.php`): Layout 2 cột premium, hiển thị trình phát video (hoặc iframe YouTube), hiển thị thông tin chi tiết qua lưới thuộc tính sinh động, hiển thị danh sách bình luận kèm form xóa bình luận trực quan cho Admin.
- **Giao diện Frontend (Khách hàng xem Video):**
  - Trang phát video (`videos/index.blade.php`): Trình phát video HTML5 xịn sò, tự động phát đúng nguồn (YouTube hoặc local MP4). Tự động ẩn bộ chuyển đổi nếu chỉ có 1 nguồn.
  - Cải tiến phát video: Hỗ trợ tua bằng phím mũi tên Trái/Phải (10s), Spacebar để play/pause, Double click trái/phải màn hình video để tua.
  - Sửa lỗi Range Requests (tua video bị đơ trên Local PHP server) bằng cách tạo route stream video riêng `/videos/{video}/stream` trả về response `206 Partial Content` (Accept-Ranges).
  - Tương tác Video: Tự động đếm lượt xem khi tải trang, hỗ trợ Like/Unlike AJAX và cập nhật real-time lượt thích trên playlist bên phải.
  - Bình luận Video: Hệ thống bình luận AJAX, hỗ trợ phân cấp bình luận gốc và các phản hồi (replies), tự động ẩn/hiện reply nếu có >2 phản hồi, hỗ trợ reply lồng sâu (tag mention @username), escape HTML chống XSS, cho phép người dùng/admin xóa bình luận của mình.
- **Cấu hình Server:**
  - Nâng giới hạn upload file từ 10MB/20MB lên 100MB ở Laravel backend, frontend JS, file `.htaccess`, `.user.ini`, và cấu hình `php.ini` của XAMPP.

### 2. Phân hệ Phiếu sửa chữa & Dịch vụ (Repair Tickets & Customer Profile)
- **Repair Tickets CRUD & Invoicing Link:**
  - Mở rộng `RepairTicketInvoiceController` hỗ trợ toàn bộ vòng đời phiếu sửa chữa (tạo, sửa, xóa).
  - Migration cho phép `user_id` nullable trên bảng `repair_tickets` để hỗ trợ khách vãng lai, bổ sung thông tin địa chỉ, email, nguồn khách hàng.
  - Nhập liệu thông minh: Tự động truy vấn và điền thông tin khách hàng bằng AJAX autocomplete khi nhập số điện thoại đã tồn tại.
  - Quản lý hóa đơn dịch vụ (`ServiceInvoiceController`): Hỗ trợ xuất hóa đơn dịch vụ trực tiếp từ phiếu sửa chữa (chỉ cho phép khi phiếu có trạng thái `Done`), tự động đồng bộ hóa đơn với phiếu sửa chữa, hỗ trợ VAT (%) tính toán real-time bằng JS, in hóa đơn (`print.blade.php`).
  - Thêm trạng thái sửa chữa `Under_Repair` (Đang sửa chữa). Đồng bộ tiền tố mã phiếu sửa chữa là `#RT-` trên toàn hệ thống.
  - Tự động liên kết tài khoản user dựa trên số điện thoại khi admin lưu phiếu.
- **Đăng ký Sửa chữa Online (Customer Portal):**
  - Tích hợp tab Lịch sử & Đặt lịch sửa chữa trong trang Profile khách hàng.
  - Form đăng ký sửa chữa online (tên khách hàng, SĐT, email, địa chỉ, số IMEI/Serial, ngày hẹn, mô tả lỗi). Tự động gán kỹ thuật viên mặc định khi đăng ký.
  - Stepper theo dõi tiến độ sửa chữa trực quan theo chiều dọc hiển thị các bước (`Received` -> `Checking` -> `Under_Repair` / `Waiting_Parts` -> `Done`), hiển thị chi phí dự kiến và hóa đơn kèm theo.

### 3. Phân hệ Articles & Lifestyle CRUD
- Tích hợp bộ lọc theo thẻ (tags) ở trang danh sách bài viết frontend.
- Đồng bộ bộ lọc tìm kiếm và trạng thái bài viết ở trang quản lý bài viết Admin.
- Tối ưu hóa giao diện soạn thảo bài viết và preview responsive trên thiết bị di động.

### 4. Tính năng So sánh Sản phẩm & Bộ lọc Nâng cao (Compare & Filter)
- Triển khai tính năng so sánh tối đa 3 sản phẩm cùng danh mục, so sánh thông số kỹ thuật tự động từ cột JSON `specifications` hoặc bảng phụ `product_specifications`.
- Floating bar so sánh sản phẩm ở dưới chân trang, cho phép click tìm kiếm nhanh sản phẩm trống từ modal AJAX.
- Bộ lọc nâng cao ở trang danh mục sản phẩm (sử dụng `ProductFilterService` xử lý AJAX lọc động theo thông số kỹ thuật chi tiết).

## Thông tin kỹ thuật & Cấu trúc cơ sở dữ liệu
- **Xác thực & Người dùng:**
  - Khóa chính bảng users: `user_id`. Mã hóa mật khẩu qua cột `password_hash` (custom auth).
  - Phân quyền (Roles): Admin (1), Quản lý (2), Khách hàng (3), Nhân viên (4).
- **Video:**
  - Bảng `videos`: khóa chính `video_id`.
  - Bảng `video_comments`: khóa chính `comment_id`, khóa ngoại `user_id` và `video_id`, hỗ trợ `parent_id` cho bình luận phân cấp.
- **Hóa đơn & Phiếu sửa chữa:**
  - Khóa chính bảng `repair_tickets`: `ticket_id`. Khóa chính bảng `service_invoices`: `invoice_id`.

## TODO (Các việc cần làm tiếp theo)
- [ ] Kết nối dự án Laravel với Database thật (sửa file `.env`).
- [ ] Tích hợp lấy dữ liệu động từ Database hiển thị ra trang chủ khách hàng (Frontend) thay cho giao diện demo hiện tại.
- [ ] Triển khai CRUD Danh mục và CRUD Sản phẩm (kèm biến thể) - Giai đoạn 2.
- [ ] Hiển thị sản phẩm lên trang chủ khách hàng (Frontend).
- [ ] Phát triển logic trong `CartService` và `InventoryService`.
- [ ] Tối ưu hóa hiệu năng load video và caching lượt xem/likes để giảm tải cho DB.

### 5. Cấu hình Giảm giá Combo Mua Kèm (Combo Discounts)
- **Hạ tầng & Database:**
  - Tạo migration bổ sung cột `discount_type` (mặc định 'fixed') và `discount_value` vào bảng `product_combos`.
  - Cập nhật quan hệ `comboProducts()` trong model `Product` hỗ trợ `withPivot('discount_type', 'discount_value')`.
  - Xây dựng `ProductComboSeeder.php` để tạo dữ liệu combo mẫu đa dạng cho các thiết bị như iPhone 15 Pro Max, Samsung Galaxy S24 Ultra và MacBook Air, đồng thời đăng ký vào `DatabaseSeeder.php`.
- **Giao diện Admin (Quản trị Combo & Cross-sell):**
  - Cải tiến màn hình `ProductDetail.blade.php`: Chuyển đổi hai hộp cấu hình trực tiếp (Cross-sell và Combo) thành hai thẻ mở Modal lớn, rộng rãi, thiết kế cao cấp.
  - Tích hợp Select2 có thumbnail trực tiếp trong modal.
  - Khi chọn sản phẩm phụ kiện trong combo, hệ thống tự động render bảng cấu hình chi tiết, cho phép chọn loại giảm giá (đ hoặc %) và nhập mức giảm kèm tính toán giá sau khi giảm trực tiếp bằng JS.
  - Đồng bộ và lưu trữ cấu hình giảm giá qua hàm `syncCombos` trong `Admin\ProductController.php`.
- **Logic Giỏ hàng & Frontend:**
  - Cập nhật `_combo_bundle.blade.php` ngoài Frontend để hiển thị giá gốc bị gạch chéo, giá đã giảm kèm nhãn mức giảm (VD: -10% hoặc -50.000đ). Hiển thị tổng số tiền tiết kiệm được khi mua combo.
  - Khi người dùng click thêm combo vào giỏ hàng, hệ thống gửi request `/cart/add` kèm theo tham số `parent_id` cho các sản phẩm phụ kiện.
  - Cập nhật `add` method trong `CartController.php` để xử lý kiểm tra `parent_id` trên server-side, tự động lấy cấu hình giảm giá từ bảng trung gian và cập nhật giá giảm giá vào giỏ hàng một cách bảo mật (không lo bị sửa giá ở client).

## Ghi chú quan trọng
- Sidebar Admin đã được tách biệt thành `resources/views/admin/partials/sidebar.blade.php` và `resources/js/components/AdminSidebar.tsx` để dễ quản lý.
- Toàn bộ tính năng video đã được merge thành công từ branch `Hien/Video` vào `master`, không xảy ra xung đột mã nguồn.
- Tính năng Combo giảm giá và cấu hình Modal đã được triển khai hoàn chỉnh cả Backend lẫn Frontend.
