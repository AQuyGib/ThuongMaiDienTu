# AI Memory - Dự Án Thương Mại Điện Tử

## Tiến độ và Ngữ cảnh hiện tại
Dự án e-commerce xây dựng trên Laravel, tập trung vào cấu trúc ERP/CMS chuyên nghiệp. Đang phát triển các phân hệ: Articles & Lifestyle, Storefront (So sánh & Bộ lọc), Phiếu sửa chữa & Dịch vụ (Repair Tickets & Customer Portal), và Phân hệ Quản lý & Phát Video (Videos Management).

## Các file đã tạo / chỉnh sửa & Công việc hoàn thành

### 23. Tính năng Tra cứu hành trình Đơn hàng (Order Tracking System)
- **Hạ tầng & API:**
  - Bổ sung phương thức `searchOrder` vào `CartController.php` để truy xuất dữ liệu đơn hàng thực tế từ database theo `order_code` hoặc `order_id`.
  - Tự động gom nhóm các sản phẩm trùng lặp trong đơn hàng chi tiết (nếu có) và tính toán số lượng/đơn giá chính xác.
  - Áp dụng ánh xạ trạng thái đơn hàng (`Pending`, `BaoCK`, `Shipping`, `Delivered`, `Cancelled`) sang các nhãn và badge màu CSS tương ứng.
  - Định nghĩa route GET `/orders/search` phục vụ gọi AJAX tra cứu từ client trong `routes/web.php`.
- **Giao diện Khách hàng (Frontend):**
  - Cải tiến view `resources/views/frontend/cart/ordertracking.blade.php`.
  - Sửa logic JS `doSearch` chuyển từ giả lập hardcode sang fetch API thật `/orders/search` để tải thông tin động.
  - Bổ sung khối hiển thị danh sách sản phẩm đã đặt (ảnh đại diện, tên, số lượng, đơn giá) đẹp mắt.
  - Viết lại logic `updateTimeline` tự động điều chỉnh các bước hoàn thành (`step-completed`) và bước hoạt động hiện tại (`step-active`) của Timeline hành trình đơn hàng dựa trên trạng thái đơn hàng thực tế.


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

## TODO (Các việc cần làm tiếp theo)
- [ ] Kết nối dự án Laravel với Database thật (sửa file `.env`).
- [ ] Tích hợp lấy dữ liệu động từ Database hiển thị ra trang chủ khách hàng (Frontend) thay cho giao diện demo hiện tại.
- [ ] Triển khai CRUD Danh mục và CRUD Sản phẩm (kèm biến thể) - Giai đoạn 2.
- [ ] Hiển thị sản phẩm lên trang chủ khách hàng (Frontend).
- [ ] Phát triển logic trong `CartService` và `InventoryService`.
- [ ] Tối ưu hóa hiệu năng load video và caching lượt xem/likes để giảm tải cho DB.
- [x] Nâng cấp hệ thống thông báo theo bản kế hoạch (sửa cache admin, sửa badge đếm chưa đọc của admin, đồng bộ form create, và tích hợp Laravel Queue).

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
- Chỉnh sửa form Thêm/Sửa video ở Admin chỉ lấy danh mục cha (`whereNull('parent_id')`) thay vì danh mục chứa sản phẩm.
- Liên kết nút "Trợ giúp trực tuyến" ở trang phát video với Trợ lý AI (gọi function `chatbotToggle()`).
- Đã merge và push nhánh Hien/Video sạch sẽ lên remote master trước đó.
- Khôi phục lại VideoSeeder của nhánh `Hien/Video` sử dụng 4 video cục bộ (bỏ YouTube link), đồng thời reset và chạy lại seeding dữ liệu database video cục bộ thành công theo yêu cầu của user.
- Tính năng Combo giảm giá và cấu hình Modal đã được triển khai hoàn chỉnh cả Backend lẫn Frontend.
- Đã gộp (merge) nhánh `master` vào nhánh `AnhQuy/ThongBao` sạch sẽ, không có xung đột.

### 6. Nâng cấp hệ thống Thông báo (Notification System Upgrade)
- **Sửa lỗi Cache thống kê Admin:** Thêm `Cache::forget('admin_notifications_index_stats_and_charts')` vào `store()`, `destroy()`, `bulkDestroy()`, `lowStockCheck()` trong `NotificationCampaignController.php`.
- **Sửa lỗi Badge đếm thông báo Admin:** Lọc `unreadCount` theo `user_id` của Admin đang đăng nhập thay vì đếm toàn hệ thống (cả `NotificationCampaignController@unreadCount` và `topbar.blade.php`).
- **Tích hợp Laravel Queue:** Tạo `SendNotificationCampaignJob.php` xử lý gửi thông báo hàng loạt chạy ngầm, tránh timeout. Controller `store()` gọi `SendNotificationCampaignJob::dispatch()` thay vì xử lý đồng bộ.
- **Nâng cấp form tạo chiến dịch:** Viết lại `admin/notifications/create.blade.php` từ layout cũ (`layouts.app`) sang layout admin (`admin.layouts.master`), thêm multi-select AJAX cho sản phẩm (`product_ids[]`), khuyến mãi (`promo_ids[]`), và tìm chọn tài khoản cụ thể (`user_ids[]`) giống Modal ở trang Index.
- Đã gộp (merge) nhánh `master` vào nhánh hiện tại `AnhQuy/ThongBao` sạch sẽ, không có xung đột.
- Đã lập bản kế hoạch và triển khai nâng cấp thành công hệ thống thông báo:
  - Sửa lỗi Stale Cache thống kê Dashboard Admin: Xóa cache `admin_notifications_index_stats_and_charts` khi tạo mới, xóa, xóa hàng loạt, hoặc quét tồn kho thấp.
  - Sửa lỗi lọc Badge Admin (Admin Unread Count): Badge trên topbar Admin chỉ đếm số thông báo chưa đọc của chính Admin đang đăng nhập.
  - Đồng bộ Form tạo mới chiến dịch: Merge tự động các input đơn lẻ `product_id` và `promo_id` thành các mảng `product_ids` và `promo_ids` ở controller để tương thích hoàn toàn với cả form tạo mới trang lẻ và modal index.
  - Tích hợp hàng đợi Laravel Queue: Tạo `SendNotificationCampaignJob` để xử lý gửi thông báo chiến dịch hàng loạt dưới background, giảm thiểu rủi ro Timeout.
  - Viết thêm Unit Test kiểm thử thành công cơ chế Dispatch Job và Xóa cache trong `tests/Feature/NotificationTest.php`.

- **Tài liệu hóa & Comment chi tiết mã nguồn:**
  - Viết chú thích (comments) bằng tiếng Việt cực kỳ chi tiết cho các file cốt lõi của tính năng gồm:
    - `app/Http/Middleware/TranslateHtmlResponse.php`
    - `app/Services/TranslationService.php`
    - `app/Traits/BaseTranslationTrait.php`
    - `resources/js/helpers.ts`
    - `resources/js/components/AdminTopbar.tsx`
  - Đảm bảo các kỹ sư tiếp quản dễ dàng nắm vững kiến trúc, các bước hoạt động (quét DOM, dịch gộp, bộ lọc dịch, cách intercept model, click-outside, v.v.).

<<<<<<< HEAD
### 7. Phân hệ Đa Ngôn Ngữ (Dynamic Localization) - Gộp nhánh thành công
- **Khắc phục lỗi View Product list:**
  - Đã khôi phục và đồng bộ chính xác file `ProductController.php` và `Product.blade.php` trên nhánh `Hien/dangonngu` trước khi merge vào `master`.
  - Tích hợp eager loading bản dịch (`withTranslation()` và `category.translations`) cho danh sách sản phẩm giúp trang quản trị hiển thị mượt mà.
  - Sau đó gộp (merge) sạch sẽ nhánh `Hien/dangonngu` vào `master`. Trang quản trị sản phẩm hiện tại hoạt động bình thường, không còn lỗi.

### 10. Chức năng Lịch sử Biến động Kho (Inventory Movements Ledger) - Đăng Nguyên
- **Hạ tầng & Model:**
  - Tạo mới `app/Models/InventoryMovement.php` model.
  - Tạo `database/seeders/InventoryMovementSeeder.php` sinh ngẫu nhiên lịch sử phong phú (sale, import, restock, return, adjustment) trong 30 ngày qua và đăng ký vào `database/seeders/DatabaseSeeder.php`.
- **Giao diện & Logic:**
  - Tạo `app/Http/Controllers/Admin/InventoryMovementController.php` xử lý bộ lọc (sản phẩm, loại biến động, ngày bắt đầu/kết thúc, từ khóa tìm kiếm) và phân trang.
  - Tạo view `resources/views/admin/inventory/movements.blade.php` với bảng biến động kho hiển thị số lượng thay đổi trực quan, lý do và link đơn hàng.

### 11. Chức năng Kiểm kê & Cân bằng Kho (Inventory Audit & Reconciliation) - Đăng Nguyên
- **Hạ tầng & Model:**
  - Tạo file migration khởi tạo bảng phiếu kiểm kê `inventory_audits` và chi tiết chênh lệch `inventory_audit_details`.
  - Tạo các model `app/Models/InventoryAudit.php` và `app/Models/InventoryAuditDetail.php`.
- **Giao diện & Logic:**
  - Tạo `app/Http/Controllers/Admin/InventoryAuditController.php` quản lý danh sách, form tạo phiếu động bằng JS và xem chi tiết đối chiếu chênh lệch thừa/thiếu.
  - Tích hợp logic **Cân bằng kho (Reconcile)**:
    - Nếu **Thiếu** hàng: Cập nhật trạng thái các IMEI tương ứng thành `Defective` và gọi `InventoryService->deductStock()` để trừ tồn kho.
    - Nếu **Thừa** hàng: Tự động sinh mã IMEI tạm thời `SYS-AUD-XXXX` và gọi `InventoryService->restoreStock()` để cộng thêm tồn kho.
    - Khóa phiếu sang trạng thái `Completed`.
  - Tạo các view: `resources/views/admin/inventory/audits/index.blade.php`, `resources/views/admin/inventory/audits/create.blade.php`, `resources/views/admin/inventory/audits/show.blade.php` kèm cảnh báo xác nhận qua SweetAlert2.
  - Tích hợp 2 tab "Biến động kho" và "Kiểm kê kho" vào thanh tab điều hướng `resources/views/admin/partials/inventory-nav.blade.php`.

### 12. Nâng cấp Xác thực & Giới hạn Đánh giá sản phẩm (Product Reviews Validation)
- **Bảo mật & Phân quyền:**
  - Bắt buộc đăng nhập mới được viết đánh giá (Backend kiểm tra qua `ReviewController@store` và trả về lỗi `401 Unauthorized` nếu chưa Login).
  - Ẩn form viết đánh giá ngoài Frontend đối với khách vãng lai, thay bằng thông báo kèm nút dẫn đến trang Đăng nhập.
  - Chỉ cho phép người dùng đã đăng nhập phản hồi (Reply) đánh giá (ẩn toàn bộ nút "Trả lời" và form Reply với khách vãng lai).
- **Giới hạn số lượng ảnh/video & Hỗ trợ định dạng:**
  - Bổ sung logic kiểm tra MimeType trên Backend để giới hạn tối đa chỉ cho phép upload 5 tệp tin hình ảnh cho mỗi đánh giá.
  - Nâng giới hạn dung lượng tải video trong đánh giá sản phẩm lên **100MB** (ngang hàng với giới hạn đăng video ở trang quản lý của Admin), trong khi giữ giới hạn ảnh tối đa là **5MB**.
  - Mở rộng danh sách định dạng ảnh hợp lệ tại Backend bao gồm cả `webp`, `gif`, và `jfif` nhằm tránh lỗi xác thực khi người dùng tải lên hình ảnh từ trình duyệt hiện đại hoặc screenshot.
  - Việt hóa hoàn toàn các thông báo lỗi xác thực của Laravel để hiển thị thông tin rõ ràng và thân thiện.
  - Đồng bộ nhãn chỉ dẫn tải tệp phía Frontend thành "Thêm ảnh / video (tối đa 5 ảnh, video < 100MB)".

### 13. Trang quản lý Bình luận & Đánh giá trong Admin (Admin Comment Management)
- **Bộ điều khiển (Controller):**
  - Tạo mới `app/Http/Controllers/Admin/CommentManagementController.php` để xử lý kiểm duyệt đánh giá sản phẩm (`reviews`) và bình luận video (`video_comments`).
  - Hỗ trợ phân trang độc lập, tìm kiếm theo từ khóa (tên người dùng, nội dung bình luận, tên sản phẩm/video), bộ lọc số sao (đối với đánh giá).
  - Tích hợp tính năng xóa bình luận/đánh giá cha đồng thời tự động xóa (cascade) các phản hồi liên quan để giữ sạch dữ liệu.
  - Tích hợp tính năng phản hồi (Reply) trực tiếp từ Admin.
- **Định tuyến (Routes):**
  - Đăng ký nhóm route `/admin/comments` cho phép xem danh sách, xóa và phản hồi cho cả 2 thực thể bình luận và đánh giá trong `routes/web.php`.
- **Giao diện (Frontend & Sidebar):**
  - Thêm menu điều hướng "Bình luận & Đánh giá" với icon `fa-comments` vào Sidebar Admin (`sidebar.blade.php`).
  - Tạo view `resources/views/admin/comments/index.blade.php` sử dụng thiết kế dạng thẻ hiện đại, phân chia tab rõ ràng, hiển thị trực quan tệp đính kèm (ảnh/video với lightbox phóng to) và tích hợp các hộp thoại phản hồi/xác nhận xóa qua SweetAlert & Bootstrap Modal.

### 14. Hệ thống kiểm duyệt Bình luận & Đánh giá (Comment Moderation & JS Fix)
- **Hạ tầng & Database:**
  - Tạo migration thêm cột `is_approved` (boolean, mặc định `true` cho các bản ghi cũ) vào bảng `reviews` và `video_comments` để giữ hiển thị cho dữ liệu lịch sử.
  - Cấu hình fillable `is_approved` trong hai Model tương ứng.
- **Quy trình Kiểm duyệt (Approval Flow):**
  - Frontend: Khi người dùng bình thường đăng đánh giá sản phẩm hoặc bình luận góc video, giá trị `is_approved` mặc định lưu là `0` (Chờ duyệt). Trình duyệt sẽ nhận được thông báo phản hồi thân thiện về việc đang chờ duyệt. Đối với Admin/Manager (role 1, 2) hoặc các câu trả lời do Admin gửi, bình luận sẽ tự động duyệt ngay lập tức (`is_approved = 1`).
  - Lọc hiển thị Frontend: Cập nhật `ProductController@show` và `VideoController@getComments` chỉ tải những đánh giá, bình luận gốc và các phản hồi con đã được kiểm duyệt (`where('is_approved', 1)`).
- **Hành động trong Admin:**
  - Thêm hai phương thức `approveReview` và `approveVideoComment` trong `CommentManagementController` và định nghĩa route tương ứng.
  - Hiển thị cột "Trạng thái" dạng badge màu sắc trực quan (Đã duyệt / Chờ duyệt) ở trang danh sách admin. Bổ sung nút **Duyệt** màu xanh lá đối với các dòng đang chờ duyệt.
  - Sửa lỗi nút **Xóa phản hồi** (Reply Delete) không hoạt động và lỗi JS liên quan đến Bootstrap modal khởi tạo thủ công bằng cách chuyển sang sử dụng hoàn toàn cơ chế kích hoạt khai báo qua các thuộc tính data-attributes HTML5 (`data-bs-toggle="modal"`, `data-bs-target`) và lắng nghe sự kiện Native Bootstrap (`show.bs.modal`, `hidden.bs.modal`), triệt tiêu hoàn toàn sự phụ thuộc trực tiếp vào biến toàn cục `bootstrap` trong Javascript.

### 15. Hệ thống báo cáo vi phạm bình luận từ người dùng (User Comment Reporting & Auto-moderation)
- **Cơ chế báo cáo vi phạm:**
  - Người dùng có thể click nút "Báo cáo" cạnh mỗi đánh giá sản phẩm hoặc bình luận video.
  - POST request gửi tới route `/reviews/{id}/report` hoặc `/videos/comments/{comment}/report` để tăng cột `report_count` lên 1.
  - Ngưỡng tự động ẩn: Khi `report_count >= 3`, trạng thái `is_approved` tự động chuyển sang `0` (ẩn khỏi trang người dùng để chờ quản trị viên phê duyệt lại).
- **Trang quản trị (Admin Panel):**
  - Cột trạng thái hiển thị thêm badge báo động đỏ `Báo cáo: X` nếu `report_count > 0`.
  - Nút hành động **Bỏ báo cáo** được tích hợp nhằm cho phép Admin xóa toàn bộ báo cáo vi phạm (`report_count = 0`), đồng thời tự động khôi phục và duyệt lại nội dung đó (`is_approved = 1`).
- **Các file sửa đổi:**
  - `routes/web.php`
  - `app/Http/Controllers/ReviewController.php`
  - `app/Http/Controllers/VideoController.php`
  - `app/Http/Controllers/Admin/CommentManagementController.php`
  - `resources/views/admin/comments/index.blade.php`
  - `resources/views/frontend/products/partials/reviews.blade.php`
  - `resources/views/videos/index.blade.php`
- **Tích hợp Gemini AI:**
  - Đã tích hợp Google Gemini AI (model `gemini-2.5-flash`) vào `CommentModerationService.php`.
  - Tự động gọi API phân tích nội dung bình luận khi cấu hình `GEMINI_API_KEY` trong file `.env`.
  - **Tối ưu hóa Fallback:** Nếu có API Key, hệ thống bỏ qua kiểm tra blacklist cục bộ. Nếu không cấu hình API Key, hệ thống sẽ sử dụng danh sách từ khóa đen tiếng Việt cục bộ (bao gồm các từ tục tĩu) và quét regex link spam để bảo vệ trang web.

### 16. Hoàn thiện thông báo kiểm duyệt & Cơ chế Fallback an toàn (Comment Moderation Alert & Fallback Fixes)
- **Cơ chế thông báo kiểm duyệt động & Cuộn mượt:**
  - Cập nhật JavaScript frontend của đánh giá sản phẩm (`reviews.blade.php`) và bình luận video (`videos/index.blade.php`) để hiển thị thông báo phản hồi động từ server thay vì cố định thông báo "Đăng bình luận thành công" như trước.
  - Tích hợp thêm kiểu dáng Warning cho Toast/Modal khi bình luận bị đánh dấu nhạy cảm và chờ duyệt để người dùng nắm rõ trạng thái.
  - Thêm cơ chế tự động cuộn mượt (smooth scroll) về vị trí phần đánh giá (`#reviews-section`) sau khi trang tải lại để tránh tình trạng màn hình tự nhảy lên đầu trang.
- **Tối ưu hóa Fallback kiểm duyệt:**
  - Sửa lỗi trong `CommentModerationService.php`: Trong trường hợp có API Key nhưng cuộc gọi API Gemini gặp lỗi hoặc quá tải (trả về null), hệ thống sẽ tự động sử dụng danh sách từ khóa đen (blacklist) cục bộ làm phương án dự phòng thay vì tự động phê duyệt bình luận.
- **Sửa quan hệ trong Eloquent:**
  - Khắc phục quan hệ `user` trong Model `Review.php` bằng cách định nghĩa tường minh khóa ngoại và khóa chính (`user_id`, `user_id`) để tương thích với cấu trúc DB hiện tại.
- **Các file sửa đổi:**
  - `app/Services/CommentModerationService.php`
  - `app/Models/Review.php`
  - `resources/views/frontend/products/partials/reviews.blade.php`
  - `resources/views/videos/index.blade.php`
  - `app/Http/Controllers/Frontend/ProductController.php` (lọc các đánh giá và phản hồi chưa duyệt)
  - `resources/views/admin/comments/index.blade.php` (sửa lỗi gửi POST nhầm sang admin/comments khi chạy dự án trong thư mục con)

### 17. Xóa hàng loạt bình luận & Báo cáo comment phụ
- **Xóa hàng loạt (Bulk Delete):**
  - Thêm checkbox "Chọn tất cả" vào header bảng đánh giá và bảng bình luận video.
  - Thêm checkbox từng dòng cho mỗi bình luận/đánh giá.
  - Thanh hành động hàng loạt (bulk action bar) hiển thị khi có item được chọn, cho phép xóa nhiều bình luận cùng lúc với xác nhận SweetAlert.
  - Backend: 2 route POST mới (`bulk-delete`) + 2 method mới trong `CommentManagementController`.
- **Báo cáo comment phụ & Modal xác nhận báo cáo:**
  - Thêm nút "Báo cáo" vào các reply (comment phụ) trong trang chi tiết sản phẩm (`reviews.blade.php`).
  - Thay thế hộp thoại xác nhận báo cáo mặc định của trình duyệt (`confirm`) bằng Modal xác nhận tùy chỉnh (`showConfirm`) với thiết kế cảnh báo màu cam (`warning` theme) để tăng trải nghiệm người dùng đồng nhất.
- **Các file sửa đổi:**
  - `routes/web.php`
  - `app/Http/Controllers/Admin/CommentManagementController.php`
  - `resources/views/admin/comments/index.blade.php`
  - `resources/views/frontend/products/partials/reviews.blade.php`

- **Khắc phục hoàn toàn việc nhảy trang lên đầu sau khi reload (Submit Review/Reply/Report):**
  - Tắt tính năng tự động khôi phục cuộn của trình duyệt (`history.scrollRestoration = 'manual'`) trước khi cuộn để tránh xung đột.
  - Sử dụng cờ chuyên biệt `scroll_to_reviews` trong `sessionStorage` kết hợp với `setTimeout(..., 100/150)` nhằm đợi toàn bộ cấu trúc trang (DOM & Layout) ổn định rồi mới thực hiện cuộn mượt xuống đúng vị trí `#reviews-section`.
  - Áp dụng đồng bộ cho cả 3 thao tác: Gửi đánh giá mới, Phản hồi (Reply) và Gửi báo cáo (Report).

- **Tối giản hóa và tối ưu giao diện bảng quản lý Admin:**
  - Ngăn chặn hoàn toàn việc vỡ chữ, xuống dòng đứng ở các tiêu đề bảng bằng cách thêm `white-space: nowrap !important;` cho `th` và định dạng khoảng cách cột hợp lý.
  - Rút ngắn nhãn cột: "Nội dung đánh giá & Phản hồi" -> "Nội dung & Phản hồi", "Tệp đính kèm" -> "Đính kèm".
  - Chuyển đổi các nút hành động (Duyệt, Bỏ báo cáo, Phản hồi, Xóa) sang dạng Icon-only nhỏ gọn, kèm Tooltip chi tiết để giải phóng diện tích hiển thị cho phần nội dung chính, tránh tình trạng bảng bị bóp nghẹt.

- **Tính năng xử phạt thành viên vi phạm (Cấm bình luận):**
  - Đã thêm cột `comment_banned_until` kiểu `timestamp` (cho phép NULL) vào bảng `users`.
  - Tích hợp SweetAlert lựa chọn hình phạt trực quan khi Admin nhấn nút **Xóa** bình luận/đánh giá (Các tùy chọn: *Không cấm*, *Cấm bình luận 1 ngày*, *Cấm bình luận 3 ngày*, *Cấm bình luận vĩnh viễn*).
  - Cập nhật hàm xóa trong `CommentManagementController` để đọc tham số `penalty` và áp đặt hạn cấm bình luận tương ứng lên tài khoản người viết.
  - Cập nhật cả 2 cổng API gửi bình luận chính (`ReviewController@store` cho đánh giá sản phẩm và `VideoController@storeComment` cho bình luận Góc video) để kiểm tra cột hạn cấm và trả về thông báo lỗi chi tiết, chặn tuyệt đối hành vi lách luật.

- **Tối ưu hóa Hệ thống thông báo Đánh giá & Xử phạt:**
  - Tắt thông báo tự động "Đánh giá của bạn đã được ghi nhận" khi người dùng tạo đánh giá mới (xóa trong Event Booted của `Review` model).
  - Tự động gửi thông báo vi phạm khi Admin xóa đánh giá hoặc bình luận video ("Đánh giá/Bình luận đã bị gỡ bỏ do vi phạm tiêu chuẩn cộng đồng").
  - Tự động gửi thông báo cấm hoạt động ("Tài khoản bị hạn chế [Thời gian]") khi Admin áp đặt lệnh cấm.
  - Tự động gửi thông báo khôi phục quyền bình luận khi Admin thực hiện thao tác gỡ cấm.

- **Bổ sung Tải ảnh / video khi phản hồi (Reply):**
  - Tích hợp nút upload chung ảnh/video cùng khung hiển thị preview bên trong form trả lời bình luận (`reply-form`) tại `reviews.blade.php`.
  - Hỗ trợ append file tải lên (`media[]`) trong AJAX request của `submitReply`.
  - Hiển thị danh sách file đính kèm dưới nội dung câu trả lời ở cả giao diện người dùng và trang quản trị Admin.

- **Tối ưu hóa số thứ tự (STT) trong bảng quản trị:**
  - Chuyển cột hiển thị mã `ID` của đánh giá/bình luận thành số thứ tự `STT` tăng dần liên tục từ 1 (đã tính toán theo trang hiện tại của dữ liệu phân trang).

- **Sửa lỗi không hiển thị popup xác nhận xóa (SweetAlert):**
  - Chuyển cơ chế lắng nghe sự kiện click của các nút xóa `.btn-action-trigger` sang Event Delegation (`document.addEventListener('click', ...)`) để hỗ trợ tốt nhất cho mọi phần tử trong DOM.
  - Kiểm tra `document.readyState` để thực thi script ngay lập tức nếu DOM đã sẵn sàng (tránh lỗi timing khi script chạy ở chế độ async/defer).
  - Cấu hình trong `resources/js/app.tsx` để bỏ qua Soft Navigation (SPA router) và ép trình duyệt tải lại toàn bộ trang (Full Page Reload) khi truy cập `/admin/comments` giúp các script đi kèm trang quản trị bình luận/đánh giá được khởi tạo hoàn chỉnh và chính xác ngay từ lần đầu truy cập.

- **Bổ sung Seeder bình luận & đánh giá:**
  - Tạo `CommentSeeder.php` để tự động tạo dữ liệu đánh giá sản phẩm (Review) với các mức điểm 1-5 sao, nội dung bình luận ngẫu nhiên, các phản hồi (reply) từ quản trị viên, đính kèm ảnh mẫu và một số đánh giá bị báo cáo vi phạm.
  - Tự động tạo dữ liệu bình luận video (VideoComment) kèm phản hồi mẫu và trạng thái bị báo cáo.
  - Đăng ký `CommentSeeder` vào danh sách chạy mặc định trong `DatabaseSeeder.php`.

- **Thêm chú thích chi tiết (Inline Comments & PHPDoc) trong code:**
  - **`CommentManagementController.php`**: PHPDoc giải thích rõ ràng tham số đầu vào, quy trình kiểm soát hình phạt cấm tài khoản, logic gửi thông báo đa trạng thái (xóa/cấm) và quy tắc xóa theo tầng (cascade) của `destroyReview`, `destroyVideoComment`, `unbanUser`.
  - **`ReviewController.php` & `VideoController.php`**: PHPDoc mô tả chi tiết cơ chế cấm người dùng bằng trường `comment_banned_until` trong `store`/`storeComment` và logic đếm lượt báo cáo tự động ẩn khi đạt từ $\ge 3$ lượt báo cáo trong `report`/`reportComment`.
  - **`index.blade.php` (Admin)**: Chú thích chi tiết kỹ thuật Event Delegation lắng nghe từ document gốc và cơ chế readyState kiểm tra trạng thái DOM tải bất đồng bộ/SPA router.
  - **`reviews.blade.php` (Frontend)**: Chú thích chi tiết kỹ thuật gom tệp tải lên qua `FormData`, xử lý AJAX, kiểm tra HTTP Status 403 để đổi tiêu đề popup từ "Lỗi kết nối" thành "Vi phạm".

### 20. Hệ thống đa ngôn ngữ cho giao diện Đăng nhập / Đăng ký & Bảo mật phiên làm việc
- **Sửa lỗi tính năng thích/yêu thích bị đứng ở tiếng Anh (JSON control values translation bypass):**
  - Khắc phục triệt để lỗi khi người dùng chuyển sang tiếng Anh, việc bấm Thêm vào yêu thích (Wishlist) hoặc Like video bị đứng giao diện (không đổi trạng thái nút bấm). Nguyên nhân do Middleware `TranslateHtmlResponse` tự động dịch mọi chuỗi văn bản trong response JSON, vô tình dịch luôn các mã trạng thái điều khiển máy như `'added'` thành `'added. added'` (do API Google Translate dịch sai) hoặc các mã khác, làm sai lệch điều kiện so khớp JavaScript (`data.status === 'added'`).
  - Đã thiết kế phương thức lọc `isMachineKey` trong `TranslateHtmlResponse.php` để bỏ qua các key điều khiển hệ thống (`status`, `success`, `code`, `action`, `type`, `id`, `product_id`, `user_id`, v.v.) khi dịch JSON response, đảm bảo logic JavaScript frontend hoạt động chính xác 100% trong mọi ngôn ngữ.
- **Hệ thống đa ngôn ngữ cho giao diện Đăng nhập / Đăng ký:**
  - Thiết kế và triển khai nút chuyển đổi ngôn ngữ (VI/EN) trực tiếp tại góc trên bên phải của `form-panel` trong file `resources/views/Auth/login_register.blade.php`, hỗ trợ dropdown mượt mà và tự động ẩn khi click ra ngoài.
  - Tách tĩnh và thêm toàn bộ các nhãn văn bản, nút bấm (bao gồm nút "Sign in with Google" / "Đăng nhập với Google", "Trang chủ" / "Home", các tabs Đăng nhập/Đăng ký, placeholder, và nhãn input) vào các file ngôn ngữ `lang/vi/ui.php` và `lang/en/ui.php`.
  - Thay đổi toàn bộ chuỗi hardcode tiếng Việt trong `login_register.blade.php` sang hàm helper dynamic localizations `{{ __('ui.key') }}` để đảm bảo dịch thuật 100% chính xác và nhanh chóng.
  - Sửa đổi logic gán cứng `'vi'` sau khi xác thực thành công trong các Controller: `AuthController.php` (login & register), `SocialController.php` (Google/Social Login), và `TwoFactorController.php` (xác thực mã OTP 2FA). Thiết lập cơ chế tự động giữ nguyên và tiếp tục duy trì ngôn ngữ hiện tại của session (`session('locale')`) qua quá trình đăng nhập và đăng ký mà không bị ghi đè hay reset về tiếng Việt.
  - Đặt ngôn ngữ mặc định của thẻ `<html>` là `{{ app()->getLocale() }}` để tối ưu SEO.
  - Cập nhật và biên dịch thành công mọi thay đổi.


### 18. Cập nhật và Khôi phục tệp video mẫu mới cho VideoSeeder
- **Bối cảnh:** Thư mục `public/uploads/video/` bị loại trừ trong `.gitignore` dẫn đến việc thiếu tệp video mẫu khi chạy thử nghiệm trên máy cục bộ hoặc khi checkout code.
- **Giải pháp:**
  - Nhận diện 4 tệp tin video mới do người dùng tải lên thư mục `public/uploads/video/` dưới dạng các tên tệp tải về từ Youtube (savetube).
  - Thực hiện đổi tên và ghi đè chúng vào các đường dẫn cấu hình mặc định của `VideoSeeder`:
    - `cap-nhat-gia-iphone-17-...mp4` -> `Iphone 17.mp4`
    - `top-10-may-loc-nuoc-...mp4` -> `Top10maylocnc.mp4`
    - `top-5-dieu-hoa-di-dong-...mp4` -> `dieuhoa.mp4`
    - `top-10-may-lanh-ban-chay-...mp4` -> `maylanh.mp4`
  - Thực hiện chạy lệnh `php artisan db:seed --class=VideoSeeder` thành công để đồng bộ thông tin dung lượng và định dạng video mới vào cơ sở dữ liệu.
  - Thiết kế và cập nhật 4 tệp tin ảnh thumbnail chất lượng cao (premium studio mockups) tương thích hoàn toàn với các chủ đề video mới.
- **Các file hiện tại trong thư mục `public/uploads/video/`:**
  - `Iphone 17.mp4` (49.4 MB)
  - `Top10maylocnc.mp4` (24.5 MB)
  - `dieuhoa.mp4` (46.2 MB)
  - `maylanh.mp4` (21.4 MB)
  - `iphone17_thumb.png` (Ảnh thiết kế premium của iPhone 17)
  - `maylocnuoc_thumb.png` (Ảnh thiết kế premium của Máy lọc nước)
  - `dieuhoa_thumb.png` (Ảnh thiết kế premium của Điều hòa di động)
  - `maylanh_thumb.png` (Ảnh thiết kế premium của Máy lạnh phòng ngủ)

### 19. Cải tiến cơ chế đếm lượt xem video & thời lượng thực tế
- **Cập nhật thời lượng thực tế:**
  - Đo đạc chính xác thời lượng của 4 video mới trong thư mục `public/uploads/video/`.
  - Cập nhật thời lượng thực tế của các video vào `VideoSeeder.php` thay cho các số liệu giả lập cũ:
    - iPhone 17: `04:23`
    - Máy lọc nước: `05:39`
    - Điều hòa di động: `07:05`
    - Máy lạnh: `05:22`
  - Thực hiện chạy lại seeder để đồng bộ thời lượng thực tế lên UI.
- **Cải tiến logic tăng view:**
  - **Trước đây:** Mỗi khi người dùng bấm vào một video trong playlist (click phát video) hoặc tải trang, hệ thống ngay lập tức gọi API `/videos/{video}/view` để tăng 1 lượt xem (views).
  - **Hiện tại:** Loại bỏ hoàn toàn sự kiện tăng views tự động khi click chọn hoặc load trang. Bổ sung sự kiện lắng nghe `ended` trên thẻ phát HTML5 `<video id="main-video-player">`. Chỉ khi người dùng xem hết video, hàm `incrementViews(currentVideoId)` mới được kích hoạt để tăng lượt xem và hiển thị thông báo cảm ơn người dùng.

### 21. Sửa lỗi chatbot đa ngôn ngữ (Multilingual Chatbot & RAG Fix)
- **Bypass Dịch Thuật Middleware:** 
  - Thêm `'response'` vào `$blacklist` của `isMachineKey` trong `TranslateHtmlResponse.php` để ngăn chặn middleware tự dịch kết quả chatbot JSON từ Gemini sang tiếng Anh.
  - Bổ sung cơ chế **bypass hoàn toàn** route chatbot (`/chatbot`) tại đầu phương thức `handle` trong `TranslateHtmlResponse.php` để đảm bảo middleware không can thiệp, biến đổi cấu trúc JSON hoặc mã hóa/dịch thuật bất kỳ dữ liệu phản hồi nào từ AI.
- **Giải mã thực thể HTML ở Frontend (Client-side HTML Entity Decoding):**
  - Thêm hàm helper `decodeHtml(html)` trong `resources/views/partials/chatbot.blade.php` sử dụng thẻ `textarea` tạm thời để giải mã toàn bộ các thực thể HTML (như `&lt;br&gt;`, `&lt;a ...&gt;`) về dạng thẻ HTML thuần trước khi gán vào `innerHTML`.
  - Giúp bảo vệ giao diện, đảm bảo các thẻ xuống dòng `<br>`, in đậm `<b>` và link sản phẩm `<a class="chatbot-product-link">` luôn hiển thị chính xác dưới dạng các phần tử giao diện thay vì hiển thị dưới dạng văn bản thô (plain text).
- **Prompt Chatbot Bản Địa Hóa (Locale-Aware Prompt):** 
  - Trong `ChatbotController.php`, kiểm tra locale hiện tại `App::getLocale() === 'en'`. Nếu đang ở phiên bản tiếng Anh, hệ thống sẽ sử dụng bộ System Prompt viết hoàn toàn bằng tiếng Anh để chỉ dẫn Gemini AI, yêu cầu nó phản hồi 100% bằng tiếng Anh.
  - Sửa đổi phương thức `searchProducts` sử dụng Eloquent `Product` model thay vì query builder `DB::table` để kích hoạt `BaseTranslationTrait`, giúp tự động lấy tên sản phẩm tiếng Anh tương ứng từ bảng `product_translations`.
  - Hỗ trợ tìm kiếm khớp từ khóa cả ở bảng chính `products` và bảng dịch `product_translations` thông qua mối quan hệ `translations` bằng `orWhereHas('translations')`.
  - Chuyển ngữ các nhãn văn bản của kho dữ liệu gửi kèm (Inventory Context) và thông báo lỗi phản hồi API chatbot sang tiếng Anh một cách mượt mà.

### 22. Hóa giải lỗi dịch thuật hệ thống lọc sản phẩm (Bilingual Product Filter Page)
- **Sửa lỗi hiển thị chữ tiếng Việt ở giao diện tiếng Anh:**
  - **Khắc phục Text Node có ký tự xuống dòng:** Trong `index.blade.php`, các văn bản tĩnh `Gợi ý nhanh:`, `Kinh tế tuần hoàn:`, và `Bỏ chọn tất cả` ban đầu được viết xuống dòng thụt lề làm Middleware dịch thuật không khớp từ khóa hoặc bị lỗi dịch khoảng trắng. Đã thu gọn chúng thành dòng đơn không ngắt dòng để Middleware khớp dịch thành công.
  - **Hỗ trợ Đa Ngôn Ngữ động trong JavaScript (`product-filter.js`):**
    - Do các thẻ lọc (active filter tags như `Danh mục: Sound`, `Hãng: Asus`, `Nhu cầu: Chơi mượt Genshin`, v.v.) và popup chọn giá được tạo động bằng JavaScript ở Client-side nên Middleware backend không can thiệp được.
    - Đã thêm biến `isEn` phát hiện ngôn ngữ của trang (`document.documentElement.lang === 'en'`).
    - Bản địa hóa toàn bộ nhãn tĩnh được chèn động bởi JS như: tiêu đề popup `Filter` / `Bộ lọc`, nút `Close` / `Đóng`, nút `Apply` / `Xem kết quả`, các tag lọc (`Category`, `Price`, `Usage needs`, `Manufacturer`, `Color`, `Easy to repair`, `Environmentally friendly`), cũng như thông báo lỗi tải sản phẩm.

### 23. Thêm Seeder cho Hóa đơn dịch vụ và Phiếu sửa chữa
- **Hạ tầng & Seeders:**
  - **`database/seeders/UserSeeder.php`** (Chỉnh sửa): Cập nhật thông tin `phone_number` cho 20 khách hàng ảo để phục vụ cơ chế tự động điền / tìm kiếm theo số điện thoại khi tạo phiếu sửa chữa và hóa đơn. Đồng thời thêm 2 tài khoản Kỹ thuật viên mẫu (role 4 - Nhân viên) là `technical.nam@dienmaypro.com.vn` và `technical.hung@dienmaypro.com.vn`.
  - **`database/seeders/ServiceInvoiceSeeder.php`** (Mới tạo): Sinh dữ liệu hóa đơn dịch vụ ngẫu nhiên với nhiều trạng thái (`paid`, `issued`, `draft`, `cancelled`), tự động tính toán thuế VAT (8% hoặc 10%), giảm giá và tổng tiền từ các mẫu dịch vụ thực tế.
  - **`database/seeders/RepairTicketSeeder.php`** (Mới tạo): Sinh 19 phiếu sửa chữa với đầy đủ các trạng thái (`Received`, `Checking`, `Under_Repair`, `Waiting_Parts`, `Done`). Các phiếu trạng thái `Done` (hoàn thành) được chia làm 2 loại: một số chưa xuất hóa đơn, và một số đã liên kết trực tiếp với các hóa đơn tương ứng sinh ra từ `ServiceInvoiceSeeder` (đảm bảo đồng bộ 100% về thông tin khách hàng, số điện thoại, IMEI, tên dịch vụ và phí dịch vụ).
  - **`database/seeders/DatabaseSeeder.php`** (Chỉnh sửa): Đăng ký `ServiceInvoiceSeeder::class` và `RepairTicketSeeder::class` vào phương thức `run()` để tự động kích hoạt khi chạy lệnh seeding toàn cục.

### 24. Nâng cấp toàn diện Dashboard Admin (Dashboard Comprehensive Upgrade)
- **Controller (`app/Http/Controllers/Admin/DashboardController.php`):**
  - Bổ sung import đầy đủ các Model: `RepairTicket`, `ServiceInvoice`, `Video`, `VideoComment`, `Review`, `Carbon`.
  - Thêm thống kê Phiếu sửa chữa theo 5 trạng thái (`Received`, `Checking`, `Under_Repair`, `Waiting_Parts`, `Done`).
  - Thêm thống kê Hóa đơn dịch vụ: tổng số, doanh thu (paid), số chờ xử lý (issued/draft).
  - Thêm thống kê Video: tổng video, tổng lượt xem, tổng lượt thích.
  - Thêm thống kê kiểm duyệt: đánh giá chờ duyệt, bình luận video chờ duyệt.
  - Thêm phân bổ đơn hàng theo trạng thái (cho biểu đồ donut).
  - Thêm xu hướng doanh thu 6 tháng gần nhất (thu nhập vs chi phí theo tháng).
- **View (`resources/views/admin/dashboard.blade.php`):**
  - Giữ nguyên 4 stat card tài chính & tổng quan (Thu nhập, Chi phí, Sản phẩm, Khách hàng).
  - Thêm hàng stat card thứ 2: Đơn hàng (kèm badge trạng thái), Phiếu sửa chữa (breakdown), Hóa đơn dịch vụ (doanh thu + chờ xử lý), Video (views + likes).
  - Thêm banner cảnh báo kiểm duyệt (hiển thị khi có nội dung chờ duyệt) kèm nút "Kiểm duyệt ngay".
  - Mở rộng thanh thao tác nhanh: thêm link Sửa chữa, Hóa đơn DV.
  - Thêm biểu đồ cột (Bar Chart) xu hướng doanh thu 6 tháng bằng Chart.js CDN.
  - Thêm biểu đồ Donut đơn hàng theo trạng thái kèm legend bên dưới.
  - Thêm thanh tiến trình (stacked bar) phiếu sửa chữa theo trạng thái với màu sắc phân biệt.
  - Giữ nguyên bảng đơn hàng mới nhất, bổ sung thêm màu badge cho status `delivered`, `processing`, `shipped`.
  - Thêm bảng **Top 5 sản phẩm bán chạy** (truy vấn `order_details` GROUP BY `product_name`, SUM quantity) với xếp hạng #1→#5 có màu medal và hiển thị doanh thu kèm theo.
  - Thêm bảng **Cảnh báo tồn kho thấp** hiển thị 5 biến thể sản phẩm có ≤ 3 sản phẩm `In_Stock` trong kho, phân biệt màu đỏ (hết hàng) và cam (sắp hết).
  - Thêm banner **Flash Sale đang diễn ra** (gradient rose→orange) hiển thị các chương trình Flash Sale đang active + chưa hết hạn, kèm số lượng sản phẩm và thời gian kết thúc.
  - Thêm hàng 3 stat card phụ: Bài viết CMS, Đơn nhập kho, Tổng đánh giá.
- **Controller (bổ sung thêm):**
  - Import thêm: `Article`, `FlashSale`, `InventoryItem`, `OrderDetail`, `ProductVariant`, `PurchaseOrder`, `DB`.
  - Query Top 5 sản phẩm bán chạy từ bảng `order_details` bằng `DB::table()` GROUP BY.
  - Query cảnh báo tồn kho thấp bằng `selectSub` đếm `InventoryItem` có status `In_Stock` per variant, lọc `having <= 3`.
  - Query Flash Sale đang active (`is_active = true`, `start_at <= now`, `end_at >= now`) kèm `withCount('products')`.
  - Đếm tổng bài viết (`Article::count()`) và đơn nhập kho (`PurchaseOrder::count()`).
- **Sắp xếp, Liên kết & Seeder (Sorting, Links & Seeders):**
  - Đơn hàng mới nhất được hiển thị theo thứ tự mã từ nhỏ đến lớn (sử dụng `orderBy('order_id', 'asc')`).
  - Liên kết "Xem tất cả →" bên cạnh Đơn hàng mới nhất đã được đổi từ `admin.cart.index` sang đúng route danh sách đơn hàng của admin: `admin.orders.index`.
  - Chuyển liên kết & chỉ số **Đơn nhập kho** sang card **Cảnh báo tồn kho thấp** cho đúng ngữ cảnh và quản lý kho đồng bộ.
  - Tinh giản card **Video & Nội dung** chỉ chứa các chỉ số media truyền thông, bổ sung liên kết trực tiếp "Xem video →" và "Xem bài viết →" để admin dễ quản lý.
  - Tích hợp `ArticleSeeder::class`, `CashbookSeeder::class`, và `FlashSaleSeeder::class` vào danh sách chạy của `DatabaseSeeder.php`.
  - Cập nhật code `CashbookSeeder.php` và `FlashSaleSeeder.php` sử dụng `updateOrCreate` để đảm bảo cơ chế seed chạy được nhiều lần một cách an toàn (idempotent).
  - Thêm sinh ngẫu nhiên views/likes trong `VideoSeeder.php` để dashboard hiển thị đầy đủ dữ liệu trực quan sinh động thay vì bằng 0.
- **Tài liệu & Comment code:**
  - Viết chú thích (comments) chi tiết cho cả 12 khối logic truy vấn thống kê dữ liệu bên trong `DashboardController.php` để hỗ trợ phát triển và bảo trì.
  - Cập nhật định dạng DocBlock chi tiết cho phương thức `index()` của `DashboardController.php`, mô tả tường tận nhiệm vụ của hàm, các luồng phân quyền bảo mật, 12 nhóm dữ liệu thống kê thu thập cùng các cấu trúc dữ liệu truyền ra view.
  - Thêm đầy đủ comment trong file giao diện `dashboard.blade.php` giải thích cấu trúc layout Grid, Flexbox, các khối Blade dynamic components và phần vẽ biểu đồ bằng Javascript (Chart.js).

### 25. Sửa lỗi Chatbot hiển thị Raw Unicode & Phản hồi sai ngôn ngữ
- **Sửa lỗi Welcome Message hiển thị raw unicode escapes (`\u003Cb`, `\ud83d\udc4b`):**
  - Nguyên nhân: Directive `@json()` của Blade mặc định sử dụng flag `JSON_HEX_TAG` mã hóa `<`, `>` thành `\u003C`, `\u003E` gây hiển thị dạng text thô.
  - Khắc phục: Thay toàn bộ `@json(__('ui.chatbot_greeting'))` bằng `{!! json_encode(__('ui.chatbot_greeting'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}` để giữ nguyên HTML tags và emoji.
  - Áp dụng cho: `chatbot_greeting`, `chatbot_product_greeting`, `chatbot_error` trong `chatbot.blade.php`.
- **Sửa lỗi Chatbot trả lời sai ngôn ngữ:**
  - Nguyên nhân ban đầu: Toàn bộ System Prompt trong `ChatbotController.php` hardcode bằng tiếng Việt, không có quy tắc ngôn ngữ rõ ràng.
  - Khắc phục: Gộp thành 1 prompt duy nhất với quy tắc ngôn ngữ ưu tiên cao nhất (LANGUAGE RULE - HIGHEST PRIORITY): AI tự động nhận diện ngôn ngữ câu hỏi của khách và phản hồi 100% bằng chính ngôn ngữ đó. Hỗ trợ mọi ngôn ngữ (Việt, Anh, Nhật, Hàn, v.v.) mà không cần ép cứng theo locale.
  - Loại bỏ `use App` facade và biến `$isEnglish` không còn cần thiết.
- **Nâng cấp chất lượng phản hồi đồng đều ở mọi ngôn ngữ:**
  - Thêm CẤU TRÚC CÂU TRẢ LỜI BẮT BUỘC: Mở đầu ấm áp → Phân tích theo nhu cầu sử dụng (2-3 đoạn với emoji) → Lồng ghép chính sách tự nhiên → Kết thúc mời gọi.
  - Thêm PHONG CÁCH TƯ VẤN CHUYÊN NGHIỆP: Viết như chuyên gia trò chuyện, giải thích TẠI SAO phù hợp, KHÔNG liệt kê khô khan. Chỉ đề cập 2-4 sản phẩm liên quan nhất.
  - Đảm bảo khi trả lời tiếng Anh hay bất kỳ ngôn ngữ nào khác, đều có chất lượng phân tích sâu, bố cục đẹp như tiếng Việt.
- **Xóa lịch sử chat khi đổi ngôn ngữ:**
  - Thêm tracking `chatbot_locale` trong `localStorage`. Khi phát hiện locale hiện tại khác với locale đã lưu, tự động xóa `chatbot_history` để tránh hiển thị tin nhắn cũ sai ngôn ngữ.
- **Các file sửa đổi:**
  - `resources/views/partials/chatbot.blade.php`
  - `app/Http/Controllers/ChatbotController.php`

### 26. Sửa lỗi Chatbot làm hỏng đường dẫn URL tiếng Anh & Tối ưu RAG tiếng Anh
- **Cơ chế Prompt phân tách theo Locale:**
  - Thiết lập lại biến kiểm tra ngôn ngữ `$isEnglish` trong `ChatbotController.php` dựa trên `App::getLocale() === 'en'`.
  - Nếu ở locale tiếng Anh, sử dụng System Prompt hoàn toàn bằng tiếng Anh chuyên biệt, giúp AI hiểu sâu sắc và phản hồi mạch lạc bằng tiếng Anh với chất lượng cao nhất.
- **Quy tắc chèn Link tuyệt đối (Link Preservation Rule):**
  - Thêm chỉ dẫn cực kỳ nghiêm ngặt tại cả hai phiên bản Prompt (tiếng Anh & tiếng Việt), yêu cầu AI giữ nguyên 100% các đường dẫn URL nội bộ của hệ thống:
    - Chi tiết sản phẩm: luôn dùng `/san-pham/{id}`, không dịch thành `/product/{id}` hay `/en/san-pham/`.
    - Tìm kiếm/Thương hiệu/Danh mục: luôn dùng `/search?q={từ_khóa}`.
    - Trang chính sách: giữ nguyên `/warranty`, `/rewards`, `/chinh-sach-bao-hanh`, `/chinh-sach-doi-tra`, tuyệt đối không dịch sang `/return-policy` hay `/warranty-policy`.
- **Tối ưu hóa RAG và Lọc từ khóa tiếng Anh:**
  - Mở rộng danh sách từ dừng `$stopwords` bao gồm các từ tiếng Anh phổ biến (`cheap`, `under`, `recommend`, `which`, `suitable`, `for`, `student`, `students`, v.v.).
  - Bổ dung thuật toán chuẩn hóa từ số nhiều sang số ít tiếng Anh (singularization algorithm) in `searchProducts` để trích xuất đúng từ khóa gốc (ví dụ: `phones` -> `phone`, `laptops` -> `laptop`, `accessories` -> `accessory`), đảm bảo tìm kiếm chính xác các sản phẩm tương ứng trong database.
  - Bản địa hóa kết quả tìm kiếm kho hàng (Inventory context) động trong prompt dựa trên locale để tránh gây nhiễu ngôn ngữ cho AI.
- **Các file sửa đổi:**
  - `app/Http/Controllers/ChatbotController.php`

### 27. Bản địa hóa động cho Chatbot độc lập với Locale của trang web (Bilingual Autodetect Chatbot)
- **Tự động nhận diện ngôn ngữ của câu hỏi:**
  - Triển khai phương thức `detectLanguage` trong `ChatbotController.php` để tự động phát hiện ngôn ngữ dựa trên nội dung tin nhắn (`prompt`) của khách hàng (không phụ thuộc vào locale hiện tại của website).
  - Sử dụng regex kiểm tra ký tự có dấu đặc trưng của Tiếng Việt và thuật toán so sánh tần suất xuất hiện của các từ khóa đặc trưng tiếng Anh/tiếng Việt (bao gồm cả tiếng Việt không dấu).
- **RAG & Hệ thống Prompt đồng bộ theo ngôn ngữ phát hiện:**
  - Khi phát hiện người dùng hỏi bằng tiếng Anh, chatbot tự động kích hoạt bộ System Prompt tiếng Anh, gọi phương thức `$p->translateTo('en')` để lấy tên sản phẩm tiếng Anh làm bối cảnh RAG và trả về câu trả lời tiếng Anh.
  - Khi phát hiện người dùng hỏi bằng tiếng Việt, chatbot kích hoạt bộ System Prompt tiếng Việt, lấy tên sản phẩm tiếng Việt làm bối cảnh RAG và trả về câu trả lời tiếng Việt.
- **Cô lập hoàn toàn khỏi Middleware dịch thuật:**
  - Xác nhận Middleware `TranslateHtmlResponse` không ảnh hưởng tới chatbot nhờ cấu trúc lọc route bypass `/chatbot` ở đầu middleware, giúp dữ liệu AI phản hồi bằng mọi ngôn ngữ luôn giữ nguyên cấu trúc HTML và link chính xác.
- **Các file sửa đổi:**
  - `app/Http/Controllers/ChatbotController.php`

### 28. Khắc phục lỗi Call to unknown method: DOMNode::getAttribute()
- **Nguyên nhân:** Trong PHP DOM Document, lớp cơ sở `DOMNode` không định nghĩa phương thức `getAttribute()` (chỉ có trên lớp con `DOMElement`). Trong quá trình duyệt đệ quy cây DOM, một số node đại diện cho phần tử `<input>` có thể được định kiểu hoặc khởi tạo là `DOMNode` thông thường bởi PHP parser làm chương trình ném ra ngoại lệ chí mạng.
- **Khắc phục:** 
  - Thêm kiểm tra kiểu `$node instanceof \DOMElement` trước khi gọi trực tiếp `getAttribute('type')`.
  - Nếu không phải `DOMElement` nhưng vẫn có thuộc tính, sử dụng `$node->attributes->getNamedItem('type')` lấy từ bản đồ `DOMNamedNodeMap` một cách an toàn.
  - Sửa lỗi đồng bộ ở cả hai nơi: hàm trích xuất thuộc tính `collectUntranslatedStrings` và hàm dịch thực tế `translateNode`.
- **Các file sửa đổi:**
  - `app/Http/Middleware/TranslateHtmlResponse.php`

### 29. Khắc phục lỗi Call to unknown method: stdClass::translateTo()
- **Nguyên nhân:** Khi chạy ứng dụng, trong một số điều kiện (như trong các trường hợp test case chạy trên sqlite, mock dữ liệu, hoặc các truy vấn raw), các bản ghi sản phẩm trả về có thể là các đối tượng `stdClass` thay vì các thực thể Eloquent Model `Product`. Do đó, việc gọi trực tiếp hàm `translateTo()` từ trait đa ngôn ngữ gây ra lỗi Fatal Error vì `stdClass` không chứa phương thức này.
- **Khắc phục:**
  - Trong phương thức `searchProducts()` của `ChatbotController.php`, bổ sung kiểm tra kiểu đối tượng `$p instanceof \App\Models\Product` trước khi gọi `translateTo()`.
  - Nếu đối tượng là `stdClass`, thiết lập cơ chế dự phòng an toàn bằng cách thực hiện truy vấn Query Builder trực tiếp (`DB::table('product_translations')`) tìm kiếm bản dịch tương ứng của sản phẩm đó để lấy tên sản phẩm bằng Tiếng Anh.
- **Các file sửa đổi:**
  - `app/Http/Controllers/ChatbotController.php`

### 30. Tối ưu hóa chỉ dẫn ngôn ngữ trong System Prompt cho Gemini
- **Nguyên nhân:** Mặc dù backend đã tự động nhận diện ngôn ngữ và gửi Prompt tương ứng (Tiếng Anh/Tiếng Việt), chỉ dẫn cũ trong Prompt sử dụng câu lệnh tổng quát: "You MUST automatically detect the language...". Điều này khiến mô hình AI đôi lúc bị nhầm lẫn bởi các ngữ cảnh hỗn hợp hoặc thương hiệu tiếng Việt, dẫn đến việc chọn sai ngôn ngữ trả về (ví dụ người dùng hỏi bằng Tiếng Anh nhưng AI trả lời Tiếng Việt).
- **Khắc phục:** 
  - Điều chỉnh trực tiếp và cụ thể trong cả hai phiên bản Prompt:
    - Trong Prompt Tiếng Anh: Chỉ định rõ câu lệnh **"The customer is querying in English. You MUST respond 100% in English."** kèm yêu cầu cấm viết tiếng Việt.
    - Trong Prompt Tiếng Việt: Chỉ định rõ câu lệnh **"Khách hàng đang hỏi bằng Tiếng Việt. Bạn BẮT BUỘC phải phản hồi 100% bằng Tiếng Việt."** kèm yêu cầu cấm viết tiếng Anh.
  - Loại bỏ hoàn toàn sự tự lựa chọn của mô hình AI, buộc AI tuân thủ tuyệt đối ngôn ngữ đích đã được backend PHP phân tích trước đó.
- **Các file sửa đổi:**
  - `app/Http/Controllers/ChatbotController.php`

### 31. Khắc phục lỗi nhân đôi backslashes trong thẻ script khi dịch trang EN & Tối ưu chèn link sản phẩm trong chatbot
- **Sửa lỗi nhân đôi dấu gạch chéo ngược (`\`) trong Regex JS của Chatbot:**
  - **Nguyên nhân:** Khi người dùng đổi sang giao diện Tiếng Anh, Middleware `TranslateHtmlResponse` quét các chuỗi JS tĩnh nháy đơn/nháy kép để gửi dịch. Do regex cũ quét chuỗi lỏng lẻo, nó quét nhầm luôn các chuỗi Regex Javascript chứa dấu gạch chéo ngược (`\`) trên nhiều dòng (multiline). Khi chèn lại bằng `addcslashes`, nó nhân đôi tất cả các dấu `\` thành `\\`, làm hỏng logic replace Markdown và replace xuống dòng của Client-side.
  - **Khắc phục:** Cấu trúc lại biểu thức chính quy quét chuỗi trong `TranslateHtmlResponse.php` ở cả hai phương thức `collectUntranslatedJsStrings` và `translateJavascriptStrings` thành `/"([^"\\\\\r\n]*(?:\\\\.[^"\\\\\r\n]*)*)"/u` (bổ sung loại trừ ký tự xuống dòng `\r`, `\n`). Giải pháp giúp giới hạn quét chuỗi trong phạm vi 1 dòng đơn, giải quyết triệt để hiện tượng match nhầm multiline và loại bỏ hoàn toàn lỗi nhân đôi backslashes.
- **Tối ưu chèn link sản phẩm dưới dạng HTML đầy đủ:**
  - **Khắc phục:** Nâng cấp luật chèn link `LINK INSERTION RULES` trong cả hai bộ System Prompt (Tiếng Việt & Tiếng Anh) tại `ChatbotController.php`, yêu cầu Gemini AI chèn thẳng tên sản phẩm đầy đủ vào thẻ HTML liên kết có class `chatbot-product-link` thay vì dùng link thô hoặc điền URL làm text hiển thị (Ví dụ: `<a href="/san-pham/16" class="chatbot-product-link">ASUS ROG Strix G16 2024</a>`).
  - **Kết quả:** Kiểm thử tự động qua `scratch/test_chatbot.php` trả về phản hồi chatbot cực kỳ đẹp mắt, định dạng link HTML sản phẩm/chính sách và các dòng text hiển thị trơn tru, mượt mà ở cả hai ngôn ngữ.
- **Các file sửa đổi:**
  - `app/Http/Middleware/TranslateHtmlResponse.php`
  - `app/Http/Controllers/ChatbotController.php`

### 32. Ngăn chặn cập nhật thông tin cá nhân trùng lặp (Prevent Unchanged Profile Updates)
- **Controller (`app/Http/Controllers/ProfileController.php`):**
  - Thêm logic kiểm tra xem dữ liệu gửi từ form cập nhật thông tin cá nhân có khác biệt so với dữ liệu hiện tại trong Database hay không (so sánh `full_name`, `gender`, `dob`, `phone_number`, `address` sau khi chuẩn hóa khoảng trắng).
  - Trả về thông báo lỗi `'no_change'` với nội dung `"Không có thông tin nào thay đổi so với dữ liệu cũ."` nếu tất cả thông tin trùng khớp, giúp ngăn chặn ghi đè database dư thừa.
  - Chuyển đổi các quy tắc Validate (validation rules) dạng chuỗi nối nhau bằng ký tự ống (`|`) sang dạng mảng (`array`) để khắc phục triệt để lỗi Laravel phân tích sai ký tự đặc biệt `|` bên trong Regex của trường `full_name` và `address`.
- **View (`resources/views/frontend/profile.blade.php`):**
  - Khai báo biến kiểm tra lỗi profile `$hasProfileError` để tự động duy trì trạng thái hiển thị của Form chỉnh sửa (`editProfileForm`) và ẩn giao diện thông tin tĩnh khi trang tải lại kèm lỗi validation.
  - Hiển thị trực quan thông báo lỗi `'no_change'` và lỗi validation riêng của từng trường ngay dưới ô nhập liệu.
  - Triển khai chức năng Hủy (Cancel) bằng cách gọi hàm `resetProfileForm()` để phục hồi lại dữ liệu ban đầu trên form qua Javascript và xóa sạch các trạng thái lỗi/cảnh báo validation cũ.
  - Tích hợp sự kiện `submit` kiểm tra trước trên Client-side. Nếu toàn bộ dữ liệu khớp với giá trị ban đầu, form sẽ hủy gửi và hiển thị Toast cảnh báo *"Không có thông tin nào thay đổi so với dữ liệu cũ!"* nhanh chóng, cải thiện trải nghiệm người dùng (UX).

### 33. Sửa lỗi lưu/cập nhật địa chỉ mới (Fix Address Store/Update Validation Error)
- **Nguyên nhân lỗi:**
  - Quy tắc xác thực (validation rules) của trường `street` và `name` trong `addAddress` và `updateAddress` có chứa ký tự ống `|` trong Regex (phân loại ký tự đặc biệt). Do viết ở dạng chuỗi nên Laravel phân tích nhầm ký tự `|` này làm dấu phân tách quy tắc, gây ra ngoại lệ `preg_match(): No ending delimiter` và sập trang.
  - Phía Client sử dụng request `fetch` không truyền Header `Accept: application/json` khiến Laravel tự động phản hồi bằng HTML (redirect hoặc báo lỗi hệ thống dạng web) thay vì JSON khi xảy ra lỗi validation. Trình duyệt không thể phân tích HTML này thành JSON (`res.json()`), làm phát sinh exception JS rồi nhảy vào block `.catch` hiển thị cảnh báo sai lệch: *"Lỗi kết nối / Vui lòng kiểm tra lại đường truyền mạng."*.
- **Giải pháp xử lý:**
  - Chuyển toàn bộ quy tắc xác thực của `addAddress` và `updateAddress` trong `ProfileController.php` sang dạng mảng (`array`) để bảo toàn ký tự `|` trong Regex.
  - Loại bỏ hoàn toàn ràng buộc độ dài tối thiểu 10 ký tự (`min:10`) đối với trường địa chỉ (`street`) theo mong muốn của người dùng.
  - Cập nhật headers của fetch lưu/sửa địa chỉ trong `profile.blade.php` truyền thêm `'Accept': 'application/json'`.
  - Nâng cấp phần xử lý xác thực trên Client-side: Khi người dùng bỏ trống hoặc dữ liệu không hợp lệ, hệ thống sẽ hiển thị dòng thông báo nhắc nhở màu đỏ trực quan ngay dưới từng ô nhập liệu tương ứng, thay vì chỉ hiển thị Toast thông báo chung.
  - Xử lý fallback cho trường "Tên gợi nhớ" (`name`): Nếu để trống, hệ thống sẽ tự động lấy thông tin từ "Họ và tên" của tài khoản (`$user->full_name`). Đã cấu hình trên Controller để chuẩn hóa khoảng trắng thừa & lưu `null` nếu chuỗi trống, đồng thời dùng toán tử Elvis `?:` trên Blade để đảm bảo giao diện fallback đúng chuẩn.
  - Thêm chú thích và comment code chi tiết (JSDoc cho Javascript, Docblock cho PHP) giải thích cặn kẽ mục đích và luồng xử lý của từng hàm/chức năng vừa thay đổi.

### 34. Nâng cấp hệ thống khởi tạo dự án - Smart Setup Wizard (Orchestrator v8.0 INITIALIZE)
- **Mục tiêu:** Nâng cấp chức năng khởi tạo dự án `[6] INITIALIZE` từ một chuỗi lệnh gộp đơn giản thành một bộ công cụ **Smart Setup Wizard** có giao diện tương tác trực quan cao, nhiều lựa chọn linh hoạt và có khả năng phát hiện lỗi tự động.
- **Tính năng triển khai:**
  - **[1] FAST SETUP (Cài đặt nhanh):** Tự động phát hiện và đề xuất bỏ qua `composer install` / `npm install` nếu thư mục `vendor` / `node_modules` đã có sẵn nhằm tối ưu hóa thời gian chờ đợi. Kiểm tra và tự động sao chép cấu hình `.env`, tự tạo `APP_KEY`, tự động đồng bộ hóa liên kết thư mục `public/storage` (Storage Link), và cho phép thực thi migration + seeders dữ liệu mẫu ngay lập tức.
  - **[2] DRIVER SETUP (Thiết lập CSDL):** Chuyển đổi và thiết lập cơ sở dữ liệu động:
    - **SQLite:** Tự tạo tệp tin `database/database.sqlite` (nếu thiếu), tự cập nhật `.env` sang `DB_CONNECTION=sqlite`, đồng thời ẩn/comment-out toàn bộ cấu hình MySQL không cần thiết bằng Powershell in-place replacement siêu tốc.
    - **MySQL:** Hỗ trợ giao diện nhập cấu hình trực quan, tự lưu giá trị mặc định (Host: `127.0.0.1`, Port: `3306`, Database: `dienmay_pro`, Username: `root`) nếu người dùng chỉ nhấn Enter. Sử dụng Powershell động cập nhật chuẩn xác dữ liệu vào `.env`.
  - **[3] CLEAN REBUILD (Cài đặt lại sạch):** Trình dọn dẹp hệ thống chuyên sâu, tự động xóa sạch `vendor/`, `node_modules/`, `.env`, `package-lock.json`, và `database.sqlite` cũ, sau đó tái thiết lập toàn bộ môi trường và tải lại thư viện hoàn toàn mới 100%.
- **Các file sửa đổi:**
  - `start.bat` (Thư mục gốc dự án)

### 35. Hệ thống xác thực API Sanctum chuyên sâu (API Sanctum Authentication Suite)
- **Mục tiêu:** Triển khai hệ thống xác thực API chuẩn bảo mật bằng Laravel Sanctum thay thế cho hệ thống lưu trữ session tùy chỉnh cũ, phục vụ kết nối di động và API tích hợp.
- **Tính năng triển khai:**
  - **Sanctum Trait Integration:** Bổ sung trait `Laravel\Sanctum\HasApiTokens` vào Model `User.php`. Thêm các PHPDoc và `@mixin` block vào `User.php` và `Role.php` để hỗ trợ IDE tối đa và loại bỏ cảnh báo lỗi linting.
  - **Auth API Controller (`AuthController.php`):** Định nghĩa 3 phương thức API: `login()` (Xác thực thông tin đăng nhập, trả về Bearer Token, từ chối tài khoản Banned), `me()` (Trả về thông tin tài khoản hiện tại qua `UserResource`), và `logout()` (Thu hồi token hiện tại `currentAccessToken()->delete()`).
  - **Custom Guard Middleware (`ApiAuthMiddleware.php`):** Middleware lọc quyền truy cập API qua Sanctum guard, kiểm tra trạng thái cấm hoạt động của tài khoản (`is_banned`). Nếu bị khóa, hệ thống lập tức thu hồi toàn bộ token hoạt động của user và trả về lỗi `403 Forbidden`.
  - **Automated API Testing Suite (`ApiAuthTest.php`):** Viết đầy đủ các ca kiểm thử tự động (Feature Tests) bao gồm: Đăng nhập thành công, Đăng nhập thất bại (sai mật khẩu/email), Lỗi kiểm duyệt dữ liệu đầu vào (Validation errors), Đăng nhập bằng tài khoản bị khóa (Banned), Truy cập profile hợp lệ/không hợp lệ, và Thu hồi token hoàn chỉnh khi Logout.
  - **Postman API Collection (`DienMayPro_Auth_API.postman_collection.json`):** Cung cấp bộ sưu tập Postman hoàn chỉnh được lưu trong thư mục `scratch`, tích hợp sẵn Test Script tự động trích xuất token lưu vào biến môi trường để gọi các API tiếp theo.
- **Các file sửa đổi / tạo mới:**
  - `app/Models/User.php` (Sửa đổi)
  - `app/Models/Role.php` (Sửa đổi)
  - `app/Http/Controllers/Api/AuthController.php` (Tạo mới)
  - `app/Http/Middleware/ApiAuthMiddleware.php` (Tạo mới)
  - `bootstrap/app.php` (Sửa đổi)
  - `routes/api.php` (Sửa đổi)
  - `tests/Feature/ApiAuthTest.php` (Tạo mới)
  - `scratch/DienMayPro_Auth_API.postman_collection.json` (Tạo mới)


