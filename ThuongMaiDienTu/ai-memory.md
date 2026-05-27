# AI Memory - Dự Án Thương Mại Điện Tử

## Tiến độ và Ngữ cảnh hiện tại
Dự án e-commerce xây dựng trên Laravel, tập trung vào cấu trúc ERP/CMS chuyên nghiệp. Đang phát triển các phân hệ: Articles & Lifestyle, Storefront (So sánh & Bộ lọc), Phiếu sửa chữa & Dịch vụ (Repair Tickets & Customer Portal), và Phân hệ Quản lý & Phát Video (Videos Management).

## Các file đã tạo / chỉnh sửa & Công việc hoàn thành

### 1. Phân hệ Quản lý & Phát Video (Videos Management) - Mới hoàn thành từ branch `Hien/Video`
- **Hạ tầng & Database:**
    - Đã **xóa** các file migration mặc định của Laravel (users, cache, jobs, etc.) để tránh xung đột.
    - Đã **tạo mới 20 file migration** nằm trong thư mục `database/migrations/` và 20 Eloquent Models hoàn chỉnh.
    - Thiết lập cấu trúc thư mục chuẩn ERP/E-commerce (Enums, Services, Requests, Partials).
    - Cấu hình Tailwind CSS xịn qua Vite/NPM.
    - Thiết lập `RoleSeeder` và `UserSeeder` với dữ liệu mẫu (Admin: admin@techzone.vn / admin123).
- **Xác thực (Authentication):**
    - Chuyển logic Auth từ Blade sang `AuthController`.
    - Fix lỗi mất Session (không lưu đăng nhập).
    - Phân luồng: Đăng nhập xong về Trang chủ. Admin có link vào Dashboard trên Header.
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
- **Phân hệ Video Admin & Frontend:**
    - Thay đổi nhãn sidebar từ "Góc video" thành "Video" (cập nhật file `resources/views/admin/partials/sidebar.blade.php` và `resources/js/components/AdminSidebar.tsx`).
    - Thiết kế lại toàn bộ giao diện đăng video của admin (`resources/views/admin/videos/create.blade.php`) dạng 2 cột cân đối và thanh thoát (loại bỏ khung card lồng nhau, thêm vách ngăn đứng dọc giữa trang, đồng bộ hóa chiều cao ô tải tệp video và đường dẫn YouTube cùng kích thước nhỏ gọn 120px giúp hiển thị toàn bộ form và mô tả trong một khung hình không cần cuộn trang), tích hợp SweetAlert2 báo lỗi, loại bỏ chọn "Sản phẩm liên quan" chỉ giữ lại "Danh mục sản phẩm" (lấy danh mục chứa sản phẩm), loại bỏ trường nhập thời lượng thủ công (thay bằng cơ chế tự động phân tích metadata của video bằng JS), bổ sung các vùng kéo thả dropzone tải tệp kèm preview ảnh trực tiếp và tab chuyển đổi linh hoạt giữa tệp MP4 nội bộ hoặc liên kết YouTube.
    - Đồng bộ bộ lọc danh mục ở trang xem video phía người dùng (`VideoController.php`) chỉ lấy các danh mục có chứa sản phẩm (`whereHas('products')` và sắp xếp theo tên) giống như bên trang sản phẩm/admin.
    - Bổ sung iframe phát YouTube cho chi tiết video admin (`resources/views/admin/videos/show.blade.php`).
    - Thêm các accessor `thumbnail_url` và `video_url` cho Model `Video` (`app/Models/Video.php`) để hỗ trợ hiển thị thumbnail YouTube tự động.
    - Tối giản hóa bộ lọc danh sách video admin (`resources/views/admin/videos/index.blade.php`) chỉ giữ lại: Tất cả, Đang hiển thị, Đang ẩn. Cập nhật các nút hành động chính gồm Đăng video (nút ở trên), Ẩn/Hiện video (tự động đảo trạng thái), và Xóa video, đi kèm xác nhận SweetAlert2 đẹp mắt.
    - Tạo migration và chạy `php artisan migrate` chuyển cột `video_path` trong bảng `videos` sang trạng thái `nullable` để hỗ trợ đăng video YouTube mà không bắt buộc tải lên tệp tin cục bộ.
    - Nâng cấp trình phát video ở giao diện frontend (`resources/views/videos/index.blade.php`) tự động phát đúng nguồn (YouTube hoặc MP4 cục bộ) dựa trên tệp tin có sẵn, đồng thời tự động ẩn bộ chuyển đổi nguồn phát (source-switcher) nếu video chỉ có duy nhất một nguồn để tránh gây lỗi hiển thị màn hình trống.
    - Thiết kế lại cấu trúc bảng danh sách video admin (`resources/views/admin/videos/index.blade.php`), đưa cột ID (số thứ tự từ 1 đến 6 tăng dần liên tục theo số dòng thực tế) làm cột đầu tiên của bảng và loại bỏ badge ID ở cột Tiêu đề giúp giao diện chuyên nghiệp và rõ ràng.
    - Loại bỏ hoàn toàn nhãn 'Admin upload' trùng lặp và rườm rà ở cả trang danh sách và trang chi tiết video của admin, xóa cột 'Trạng thái' khỏi bảng và thay đổi tiêu đề cột 'Video' thành 'Ảnh minh họa' cho khớp với phần hình ảnh thumbnail. Đồng thời sửa lỗi hiển thị người đăng bị 'N/A' bằng cách gọi thuộc tính `full_name` của Model User thay thế cho `name` không tồn tại.
    - Nâng giới hạn kích thước tải tệp video tối đa từ 20MB lên 100MB ở cả phần validate phía Laravel backend (`VideoManagementController.php`), kiểm tra điều kiện phía Javascript frontend (`create.blade.php`), và cấu hình cấu hình PHP web server trong `.htaccess` (`upload_max_filesize 100M` và `post_max_size 110M`), đồng thời cập nhật các nhãn mô tả chỉ dẫn tương ứng.
    - Sửa lỗi không thể upload video >10MB: file `public/.user.ini` giới hạn `upload_max_filesize=10M` và `php.ini` XAMPP (`D:\Xampp\php\php.ini`) có `post_max_size=10M` quá thấp. Đã nâng cả hai file lên 100M/110M cho đồng bộ.
    - Thêm chức năng **Sửa video** (Edit): route `admin.videos.edit` / `admin.videos.update`, method `edit()` và `update()` trong `VideoManagementController`, view `resources/views/admin/videos/edit.blade.php` với form điền sẵn dữ liệu, nút sửa (icon bút) ở cột Hành động trang danh sách video, và nút sửa trong trang chi tiết video. Đồng thời loại bỏ hiển thị "Ghi chú admin" ở trang chi tiết video.
    - Cải tiến cập nhật tương tác trực tiếp ở frontend: Tự động chạy API tăng lượt xem (`incrementViews`) khi người dùng mới load trang cho video mặc định (tránh lỗi chỉ tăng khi click đổi video), hiển thị đồng thời cả lượt thích (likes) ngay cạnh lượt xem ở danh sách playlist bên phải và cập nhật số lượt thích trực tiếp ở playlist này khi bấm nút Thích (like/unlike). Thêm hiển thị lượt xem và lượt thích vào trang chi tiết của Admin.
    - Thiết kế lại toàn bộ giao diện xem chi tiết video phía Admin (`admin/videos/show.blade.php`) theo phong cách premium dashboard hiện đại: Bố cục 2 cột gọn gàng, trình phát video/iframe YouTube ở khung tối chuyên nghiệp, mô tả chi tiết được trình bày chỉn chu, bổ sung khung xem trước ảnh đại diện (thumbnail preview) và một lưới chi tiết thuộc tính có màu sắc icon phân biệt sinh động (Người đăng, Trạng thái, Dung lượng, Mime type, Lượt xem, Lượt thích, Danh mục, Ngày đăng).
    - Tách phần mô tả video ra một cột riêng có tiêu đề **Mô tả chi tiết** trong bảng danh sách quản lý video admin (`admin/videos/index.blade.php`) thay vì gộp chung dưới tên tiêu đề như trước, sử dụng line-clamp 2 dòng để hiển thị gọn gàng, đồng thời cập nhật `colspan="7"` cho dòng thông báo khi trống.
    - Dọn dẹp code thừa: Xóa file view dư thừa không dùng `resources/views/videos/create.blade.php`, loại bỏ các route `videos.create`, `videos.store` dư thừa khỏi `routes/web.php` và xóa hai hàm tương ứng `create()`, `store()` khỏi `VideoController.php` phía khách hàng.
    - Phát triển tính năng **Bình luận video (Video Comments)**:
        - Tạo và sửa lại thứ tự migration để chạy thành công: đổi tên file migration add_parent_id (`2026_05_26_093959_add_parent_id_to_video_comments_table.php` -> `2026_05_26_163259_add_parent_id_to_video_comments_table.php`) để chạy sau khi đã tạo bảng `video_comments` (`2026_05_26_163152_create_video_comments_table.php`).
        - Tạo Model `VideoComment.php`, định nghĩa quan hệ tương ứng trong Model `Video` (`comments()`) và Model `User` (`videoComments()`).
        - Xây dựng API load bình luận `/videos/{video}/comments` và post bình luận `/videos/{video}/comments` trong `VideoController.php` hỗ trợ thuộc tính `parent_id` phục vụ trả lời bình luận.
        - Tích hợp khung bình luận ở trang chi tiết video phía frontend (`resources/views/videos/index.blade.php`): Cho phép người dùng đã đăng nhập viết bình luận bằng văn bản, tự động load bình luận qua AJAX khi chuyển đổi video trong playlist, hiển thị badge số lượng bình luận (bao gồm cả phản hồi). Hỗ trợ nút "Trả lời" dưới mỗi bình luận chính để mở form trả lời nhanh qua AJAX. Đồng thời hỗ trợ nút "Trả lời" dưới từng phản hồi con (reply-item) để mở và di chuyển form nhập liệu xuống ngay phía dưới phản hồi đó, tự động điền tag `@username: ` để mention tên người dùng. Nếu một bình luận có từ 2 phản hồi trở lên, chỉ hiển thị 1 phản hồi đầu tiên kèm nút "Xem thêm X phản hồi" (khi click sẽ bung toàn bộ câu trả lời). Chống tấn công XSS (sử dụng escapeHTML).
        - Cho phép admin, quản lý (hoặc chính chủ sở hữu bình luận) xóa bình luận/phản hồi trực tiếp trên giao diện frontend thông qua route DELETE `/videos/comments/{comment}`.
        - Thêm giao diện Quản lý bình luận trong trang chi tiết video của Admin (`resources/views/admin/videos/show.blade.php`) dưới dạng bảng danh sách trực quan, phân biệt rõ ràng bình luận gốc và các phản hồi (được thụt lề, tô màu nền khác và có mũi tên chỉ hướng phản hồi). Tích hợp form và route DELETE `/admin/videos/comments/{comment}` để xóa bình luận/phản hồi không phù hợp.
    - Cải tiến phát video và tương tác:
        - Xóa từ "duyệt" trong thông báo thành công khi admin công khai video (`VideoManagementController.php`), chuyển từ "Video đã được duyệt và công khai." sang "Video đã được công khai.".
        - Cho phép và tối ưu hóa khả năng tua video (seeking) tại giao diện frontend: Hỗ trợ phím mũi tên trái/phải (`ArrowLeft` / `ArrowRight`) để tua lùi/tiến 10 giây, phím cách (`Spacebar`) để tạm dừng hoặc tiếp tục phát video; đồng thời hỗ trợ nhấp đúp chuột (Double click) ở nửa trái/nửa phải màn hình video để tua lùi/tiến 10 giây rất tiện lợi.
        - Sửa triệt để lỗi không tua được video (chủ yếu xảy ra trên `php artisan serve` do không hỗ trợ range requests): Xây dựng route stream video `/videos/{video}/stream` trỏ đến phương thức `stream` của `VideoController` để trả về phản hồi `206 Partial Content` (Range Request) kèm theo header `Accept-Ranges: bytes` phù hợp. Áp dụng route stream này cho toàn bộ trình phát video HTML5 ở cả trang người dùng và trang chi tiết Admin.
        - Sửa lỗi "Undefined variable $defaultSource" xảy ra khi danh sách video trống bằng cách khai báo trước các biến `$defaultSource`, `$hasMp4`, `$hasYoutube` tại khối khởi tạo `@php` ở đầu view `resources/views/videos/index.blade.php`.
        - Cập nhật `VideoSeeder.php` để lấy 4 video ảo cục bộ (`Iphone 17.mp4`, `Top10maylocnc.mp4`, `dieuhoa.mp4`, `maylanh.mp4`) nằm trong thư mục `public/uploads/video/` làm dữ liệu seeder mẫu, tự động đo dung lượng thực tế (file_size) và gán định dạng (mime_type) khi chạy seeding.
        - Chuyển đổi ảnh đại diện (thumbnail) của video trong seeder sang các tệp tin hình ảnh cục bộ (`public/uploads/video/*_thumb.png`) được tạo riêng để tránh lỗi không tải được ảnh do các nhà mạng Việt Nam chặn tên miền Unsplash (`images.unsplash.com`).
        - Cập nhật accessor `getThumbnailUrlAttribute` trong Model `Video.php` và hàm helper `$getThumbUrl` trong frontend `index.blade.php` tự động kiểm tra và trả về đường dẫn `public_path()` nếu ảnh đại diện tồn tại cục bộ dưới thư mục public.
        - Thay đổi cơ chế hiển thị thanh lọc danh mục "Xem theo danh mục" tại trang video frontend: tự động thu thập và xây dựng danh sách các danh mục trực tiếp từ tập hợp các video đã công khai (published videos) thay vì truy vấn các danh mục có chứa sản phẩm từ database, đồng thời tự động lấy danh mục cha cao nhất (root category) bằng đệ quy thông qua phương thức `getRootCategory()` của `Video` model để gom nhóm và hiển thị làm bộ lọc, giúp loại bỏ hoàn toàn các lỗi thiếu hoặc sai lệch danh mục.
        - Cải tiến hàm `stream()` tại `VideoController.php` hỗ trợ tìm và stream video từ thư mục `public_path()` nếu không tìm thấy trong `storage_path()`.
        - Cập nhật `.gitignore` loại trừ thư mục `/public/uploads/video/` tránh commit tệp tin video nặng lên git.
- **Giao diện Admin (Quản trị Video):**
  - Danh sách video (`admin/videos/index.blade.php`): Thiết kế lại bảng chuyên nghiệp, hiển thị ID (số thứ tự thực tế tăng dần), "Ảnh minh họa" (thumbnail), "Tiêu đề", "Mô tả chi tiết" (line-clamp 2 dòng), và các nút trạng thái Ẩn/Hiện bằng AJAX, nút Sửa, Xóa với xác nhận SweetAlert2.
  - Trang đăng video (`admin/videos/create.blade.php`): Giao diện 2 cột cân đối, thanh thoát, không lồng card. Thêm vách ngăn dọc và tối ưu chiều cao form để tránh cuộn trang. Hỗ trợ upload video nội bộ (kéo thả drag & drop, validate size lên 100MB) hoặc nhúng link YouTube. Tự động lấy thời lượng video bằng JS.
  - Trang sửa video (`admin/videos/edit.blade.php`): Cho phép chỉnh sửa thông tin video linh hoạt.
  - Trang chi tiết video (`admin/videos/show.blade.php`): Layout 2 cột premium, hiển thị trình phát video (hoặc iframe YouTube), hiển thị thông tin chi tiết qua lưới thuộc tính sinh động, hiển thị danh sách bình luận kèm form xóa bình luận trực quan cho Admin.
- **Giao diện Frontend (Khách hàng xem Video):**
  - Trang phát video (`videos/index.blade.php`): Trình phát video HTML5 xịn sò, tự động phát đúng nguồn (YouTube hoặc local MP4). Tự động ẩn bộ chuyển đổi nếu chỉ có 1 nguồn.
  - Cải tiến phát video: Hỗ trợ tua bằng phím mũi tên Trái/Phải (10s), Spacebar để play/pause, Double click trái/phải màn hình video để tua. Sửa lỗi "Undefined variable $defaultSource" khi danh sách video trống bằng cách khai báo trước các biến `$defaultSource`, `$hasMp4`, `$hasYoutube` tại khối khởi tạo `@php` ở đầu view `resources/views/videos/index.blade.php`.
  - Sửa lỗi Range Requests (tua video bị đơ trên Local PHP server) bằng cách tạo route stream video riêng `/videos/{video}/stream` trả về response `206 Partial Content` (Accept-Ranges).
  - Tương tác Video: Tự động đếm lượt xem khi tải trang, hỗ trợ Like/Unlike AJAX và cập nhật real-time lượt thích trên playlist bên phải.
  - Bình luận Video: Hệ thống bình luận AJAX, hỗ trợ phân cấp bình luận gốc và các phản hồi (replies), tự động ẩn/hiện reply nếu có >2 phản hồi, hỗ trợ reply lồng sâu (tag mention @username), escape HTML chống XSS, cho phép người dùng/admin xóa bình luận của mình.
- **Cấu hình Server:**
  - Nâng giới hạn upload file từ 10MB/20MB lên 100MB ở Laravel backend, frontend JS, file `.htaccess`, `.user.ini`, và cấu hình `php.ini` của XAMPP.
>>>>>>> 868cc549ac74a8fdf8ab553fd4b2a9d3bfbd2469

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

## Ghi chú quan trọng
- Sidebar Admin đã được tách biệt thành `resources/views/admin/partials/sidebar.blade.php` và `resources/js/components/AdminSidebar.tsx` để dễ quản lý.
- Toàn bộ tính năng video đã được merge thành công từ branch `Hien/Video` vào `master`, không xảy ra xung đột mã nguồn.
- Chỉnh sửa form Thêm/Sửa video ở Admin chỉ lấy danh mục cha (`whereNull('parent_id')`) thay vì danh mục chứa sản phẩm.
- Liên kết nút "Trợ giúp trực tuyến" ở trang phát video với Trợ lý AI (gọi function `chatbotToggle()`).
- Đã merge và push nhánh `Hien/Video` sạch sẽ lên remote `master`.
