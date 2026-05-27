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
- Đã merge và push nhánh Hien/Video sạch sẽ lên remote master trước đó.
- Khôi phục lại VideoSeeder của nhánh `Hien/Video` sử dụng 4 video cục bộ (bỏ YouTube link), đồng thời reset và chạy lại seeding dữ liệu database video cục bộ thành công theo yêu cầu của user.

### 5. Phân hệ Đa Ngôn Ngữ (Dynamic Localization) – Branch `Hien/dangonngu`
- **Hạ tầng dịch thuật:**
  - `app/Services/TranslationService.php`: Dịch tự động qua Google Cloud API hoặc fallback Free Google Translate GTX API (miễn phí, không cần API key).
  - `app/Traits/BaseTranslationTrait.php`: Trait gắn vào Model, tự động dịch khi `saved()`, intercept `getAttribute()` trả về bản dịch theo locale hiện tại.
  - `app/Observers/BaseTranslationObserver.php`: Observer tách riêng vòng đời dịch.
  - `app/Jobs/TranslateModelJob.php`: Job queue dịch bất đồng bộ (tùy config `translatable.observer.queue_if_available`).
  - `config/translatable.php`: Cấu hình source_locale (vi), target_locale (en), supported_locales, auto_translate, observer settings.
- **Database Translation Tables (Separate Translation Table pattern):**
  - `product_translations`: migration `2026_05_27_000004`, model `ProductTranslation.php`.
  - `category_translations`: migration `2026_05_27_000001`, model `CategoryTranslation.php`.
  - `attribute_translations`: migration `2026_05_27_000002`, model `AttributeTranslation.php`.
  - `page_translations`: migration `2026_05_27_000003`, model `PageTranslation.php`.
  - Các bảng mới: `attributes` (migration `2026_05_26_000001`), `pages` (migration `2026_05_26_000002`).
  - Bổ sung cột `description`, `seo_description`, `sort_order`, `is_active` vào bảng `categories` (migration `2026_05_27_000005`).
- **API Đa Ngôn Ngữ:**
  - `routes/api.php`: API v1 với middleware `ResolveApiLocale` hỗ trợ `?locale=en` hoặc header `X-Locale`.
  - Controllers: `Api\ProductController`, `Api\CategoryController`, `Api\PageController`, `Api\AttributeController`.
  - Resources: `ProductResource`, `CategoryResource`, `PageResource`, `AttributeResource` dùng trait `TranslatesResource`.
  - `ApiWrapsResponse` trait bổ sung `locale` vào mọi API response.
- **Batch Translation Command:**
  - `app/Console/Commands/TranslateAllModels.php`: Lệnh `php artisan translate:all` dịch hàng loạt cho tất cả hoặc model cụ thể (`--model=Product`), hỗ trợ `--force` để dịch lại.
  - Đã chạy thành công: 21 categories + 34 products đã được dịch tự động sang tiếng Anh.
- **Language Switcher trên Frontend:**
  - Đã thêm route `/locale/{locale}` đặt tại `routes/web.php` giúp lưu lựa chọn ngôn ngữ vào Session.
  - `app/Http/Middleware/SetLocaleFromSession.php`: Middleware tự động kiểm tra và cấu hình ngôn ngữ `app()->setLocale()` dựa trên Session của mỗi Request. Đã đăng ký vào group `web` trong `bootstrap/app.php`.
  - Đã tích hợp nút bấm và Menu thả xuống (Language Switcher dropdown) cực kỳ mượt mà, trực quan ngay góc trên bên phải của Top Bar tại `resources/views/partials/header.blade.php`, hỗ trợ lưu trạng thái cờ và ngôn ngữ được lựa chọn. Nút được thiết kế tinh giản chữ trơn màu trắng không viền, không nền để ăn nhập hoàn hảo với thanh Top Bar.
  - **Sửa lỗi màu sắc Header:** Thiết lập màu nền của thanh **Top Bar** thành dải màu gradient chuyển từ Xanh dương sang Tím rồi tới Đỏ (`linear-gradient(90deg, #0046ab 0%, #6b21a8 50%, #d70018 100%)`).
- **Dịch Text Tĩnh Giao Diện (Static UI Translation):**
  - Tạo thư mục `lang/vi/ui.php` và `lang/en/ui.php` chứa toàn bộ chuỗi text tĩnh của giao diện (header, footer, mega menu, province modal).
  - Đã cập nhật `resources/views/partials/header.blade.php`: Toàn bộ text tĩnh tiếng Việt (top bar, nút danh mục, thanh tìm kiếm, thông báo, giỏ hàng, đăng nhập/xuất, mega menu, province modal) đều dùng `__('ui.key')`.
  - Đã cập nhật `resources/views/partials/footer.blade.php`: Toàn bộ text tĩnh (hotline, về công ty, chính sách, kết nối) đều dùng `__('ui.key')`.
  - Khi chuyển ngôn ngữ sang EN, toàn bộ giao diện header + footer tự động hiển thị tiếng Anh.
  - **Sửa Lỗi Icon Danh Mục Khi Dịch:** Khi dịch tên danh mục sang Tiếng Anh (`Smartphones`, `Laptops`...), map `$categoryIcons` và `$sidebarIcons`/`$quickLinkIcons` bị mất do key cũ là tiếng Việt. Đã bổ sung cả các key Tiếng Anh tương ứng vào các mảng này tại `resources/views/partials/header.blade.php` và `resources/views/home.blade.php` để giữ nguyên icon và ảnh đại diện cực kỳ chuẩn xác.
  - **Tối Ưu Chống Giật Giao Diện (Layout Shift & Responsiveness):** Đã nâng cấp cơ chế chống giật sang dạng **co giãn mềm dẻo (Fluid responsive)** để vừa ngăn layout shift vừa không gây tràn/chồng chéo chữ trên mọi kích thước màn hình:
    - Thiết lập `.logo` (`min-width: 160px`), `.header-category-btn` (`min-width: 110px`), `.header-province-btn` (`min-width: 120px`), `.action-item` (`min-width: 65px`), `.lang-switcher-btn` (`min-width: 60px`) kết hợp cùng padding mềm mại để các nút tự điều chỉnh theo độ dài chữ của từng ngôn ngữ mà không bị ép cứng chật chội.
    - Khôi phục bản dịch tiếng Anh đầy đủ cho Top Bar (`lang/en/ui.php`) để khớp 100% ngữ nghĩa và số lượng cột của Tiếng Việt. Đồng thời thu gọn kích cỡ chữ Top Bar (`font-size: 11px`) để chứa trọn vẹn cả hai ngôn ngữ mà không bị lỗi hay lệch dòng.
    - Loại bỏ thuộc tính `gap` (`gap: 0`) ở `.top-bar-left` và `.top-bar-right`, đồng thời thiết lập padding đồng đều `padding: 2px 8px` cho toàn bộ phần tử `span` và `.lang-switcher-btn`. Điều này giúp các vạch ngăn cách (divider border) được hiển thị đối xứng hoàn hảo, khoảng cách cực kỳ gọn gàng và tinh tế, không bị quá xa hay lệch.
- **Provider Registration:**
  - `bootstrap/providers.php`: Đã thêm `TranslationServiceProvider` và `TranslatableHelperServiceProvider`.
  - **Fix:** Đã xóa key `providers` sai trong `config/app.php` (Laravel 11 dùng `bootstrap/providers.php` thay vì `config/app.php` để đăng ký providers).
- **TranslationService Fallback (Free Google Translate):**
  - Khi không có `GOOGLE_TRANSLATE_API_KEY` trong `.env`, hệ thống tự động fallback sang endpoint miễn phí: `https://translate.googleapis.com/translate_a/single?client=gtx`.
  - Hỗ trợ `withoutVerifying()` khi `APP_ENV=local` để tránh lỗi SSL trên XAMPP.
')`) thay vì danh mục chứa sản phẩm.
- Liên kết nút "Trợ giúp trực tuyến" ở trang phát video với Trợ lý AI (gọi function `chatbotToggle()`).
- Đã merge và push nhánh Hien/Video sạch sẽ lên remote master trước đó.
- Khôi phục lại VideoSeeder của nhánh `Hien/Video` sử dụng 4 video cục bộ (bỏ YouTube link), đồng thời reset và chạy lại seeding dữ liệu database video cục bộ thành công theo yêu cầu của user.

### 5. Phân hệ Đa Ngôn Ngữ (Dynamic Localization) – Branch `Hien/dangonngu`
- **Hạ tầng dịch thuật:**
  - `app/Services/TranslationService.php`: Dịch tự động qua Google Cloud API hoặc fallback Free Google Translate GTX API (miễn phí, không cần API key).
  - `app/Traits/BaseTranslationTrait.php`: Trait gắn vào Model, tự động dịch khi `saved()`, intercept `getAttribute()` trả về bản dịch theo locale hiện tại.
  - `app/Observers/BaseTranslationObserver.php`: Observer tách riêng vòng đời dịch.
  - `app/Jobs/TranslateModelJob.php`: Job queue dịch bất đồng bộ (tùy config `translatable.observer.queue_if_available`).
  - `config/translatable.php`: Cấu hình source_locale (vi), target_locale (en), supported_locales, auto_translate, observer settings.
- **Database Translation Tables (Separate Translation Table pattern):**
  - `product_translations`: migration `2026_05_27_000004`, model `ProductTranslation.php`.
  - `category_translations`: migration `2026_05_27_000001`, model `CategoryTranslation.php`.
  - `attribute_translations`: migration `2026_05_27_000002`, model `AttributeTranslation.php`.
  - `page_translations`: migration `2026_05_27_000003`, model `PageTranslation.php`.
  - Các bảng mới: `attributes` (migration `2026_05_26_000001`), `pages` (migration `2026_05_26_000002`).
  - Bổ sung cột `description`, `seo_description`, `sort_order`, `is_active` vào bảng `categories` (migration `2026_05_27_000005`).
- **API Đa Ngôn Ngữ:**
  - `routes/api.php`: API v1 với middleware `ResolveApiLocale` hỗ trợ `?locale=en` hoặc header `X-Locale`.
  - Controllers: `Api\ProductController`, `Api\CategoryController`, `Api\PageController`, `Api\AttributeController`.
  - Resources: `ProductResource`, `CategoryResource`, `PageResource`, `AttributeResource` dùng trait `TranslatesResource`.
  - `ApiWrapsResponse` trait bổ sung `locale` vào mọi API response.
- **Batch Translation Command:**
  - `app/Console/Commands/TranslateAllModels.php`: Lệnh `php artisan translate:all` dịch hàng loạt cho tất cả hoặc model cụ thể (`--model=Product`), hỗ trợ `--force` để dịch lại.
  - Đã chạy thành công: 21 categories + 34 products đã được dịch tự động sang tiếng Anh.
- **Provider Registration:**
  - `bootstrap/providers.php`: Đã thêm `TranslationServiceProvider` và `TranslatableHelperServiceProvider`.
  - **Fix:** Đã xóa key `providers` sai trong `config/app.php` (Laravel 11 dùng `bootstrap/providers.php` thay vì `config/app.php` để đăng ký providers).
- **TranslationService Fallback (Free Google Translate):**
  - Khi không có `GOOGLE_TRANSLATE_API_KEY` trong `.env`, hệ thống tự động fallback sang endpoint miễn phí: `https://translate.googleapis.com/translate_a/single?client=gtx`.
- **TranslateHtmlResponse Middleware (Dịch tự động trang tĩnh):**
  - Đã viết middleware `App\Http\Middleware\TranslateHtmlResponse` tự động bắt nội dung HTML phản hồi khi `locale = en`.
  - Tách/trích xuất toàn bộ thẻ `<script>` và `<style>` ra ngoài trước khi dùng `DOMDocument` bằng các placeholder tạm thời, giúp bảo vệ mã nguồn JavaScript (bao gồm cả template literals chứa mã HTML) khỏi bị trình phân tích DOM biến đổi hay hiển thị nhầm ra ngoài màn hình. Sau khi dịch xong sẽ khôi phục lại nguyên vẹn.
  - Sử dụng `DOMDocument` để lọc và duyệt qua các text node cũng như các thuộc tính tĩnh (`placeholder`, `title`, `alt`), dịch tự động từ tiếng Việt sang tiếng Anh qua `TranslationService` (endpoint miễn phí của Google GTX).
  - Tối ưu hóa xử lý lỗi quá tải API/Timeout bằng cách gộp nhóm dịch hàng loạt (Batch translation theo từng cụm 15 từ) trong 1 cuộc gọi duy nhất thay vì gọi riêng rẽ cho mỗi từ, kèm theo cơ chế fallback chuẩn xác và nâng thời gian thực thi tối đa lên 120 giây.
  - Tích hợp lưu cache trọn đời bằng `Cache::rememberForever` với key hash md5 của nội dung dịch để tối ưu hiệu năng và tránh bị giới hạn API Google.
  - Đã đăng ký middleware vào group `web` trong `bootstrap/app.php` sau middleware `SetLocaleFromSession`.

- **Căn chỉnh khoảng cách Top Bar:**
  - Sửa `.top-bar .container` sang dùng `justify-content: center` kèm `gap: 40px` để thu hẹp khoảng trống lớn ở giữa nhóm bên trái và bên phải trên màn hình lớn.

- **Đa ngôn ngữ cho Chatbot AI:**
  - Đã dịch thuật toàn bộ giao diện Chatbot (`chatbot.blade.php`) gồm: tiêu đề, trạng thái hoạt động, placeholder ô nhập, tin nhắn chào mừng (greeting), tin nhắn lỗi/loading và các nút gợi ý nhanh.
  - Sử dụng `@json` để xuất trực tiếp biến bản dịch của Laravel vào mã script JavaScript của Chatbot, giúp Chatbot hiển thị tiếng Anh chuẩn xác 100% khi người dùng đổi ngôn ngữ.
  - Trí tuệ nhân tạo Gemini của Chatbot hoạt động theo cơ chế phát hiện ngôn ngữ tự động (khách hỏi tiếng Anh trả lời tiếng Anh, khách hỏi tiếng Việt trả lời tiếng Việt).

- **Đa ngôn ngữ cho Modal Theo dõi Tiến độ Sửa chữa (Repair Progress):**
  - Cập nhật middleware `TranslateHtmlResponse` để bỏ qua việc duyệt qua các thẻ con của `<textarea>` (tránh dịch dữ liệu người dùng nhập) nhưng vẫn dịch đầy đủ thuộc tính `placeholder` (như ô *"Mô tả chi tiết tình trạng máy lỗi và linh kiện cần thay thế..."*).
  - Tích hợp hệ thống bản dịch vào script điều khiển Modal Theo dõi Tiến độ Sửa chữa ở trang cá nhân (`profile.blade.php`). Toàn bộ các dòng trạng thái tĩnh lẫn động được cập nhật qua JS (`step-received-desc`, `step-checking-desc`, `step-repairing-desc`, `step-done-desc`, `track-tech`) đã được đa ngôn ngữ hóa bằng `@json(__('ui.key'))`.
  - Hỗ trợ dịch động tên Kỹ thuật viên phụ trách (Ví dụ: `Quản Trị Viên` thành `Administrator` khi chuyển sang ngôn ngữ tiếng Anh).

- **Tự động dịch phản hồi JSON (AJAX/API Requests):**
  - Nâng cấp middleware `TranslateHtmlResponse` để tự động phát hiện các phản hồi JSON (`JsonResponse` hoặc Content-Type `application/json`) từ controller hoặc API.
  - Phân tích cú pháp JSON, duyệt đệ quy qua các giá trị chuỗi (values) và dịch tự động các chuỗi tiếng Việt sang tiếng Anh bằng `TranslationService` (vẫn tận dụng cache và dịch hàng loạt batch translation).
  - Tối ưu hóa bộ lọc dịch `shouldTranslate` để bỏ qua các trường dữ liệu hệ thống như: URL (`http/https`), đường dẫn tương đối (`/storage`, `/assets`), tên tệp tin ảnh/assets nhằm đảm bảo giữ nguyên cấu trúc hệ thống.
  - Hỗ trợ dịch tự động tất cả các thông báo lỗi AJAX động (như thông báo "Không tìm thấy thiết bị..." ở popup tra cứu bảo hành, hoặc các thông báo lỗi Validation Form).

- **Tự động dịch thuộc tính JSON lồng trong HTML (React/Vue components props):**
  - Mở rộng middleware `TranslateHtmlResponse` khi xử lý trang HTML để quét qua toàn bộ các thuộc tính HTML chứa chuỗi JSON (ví dụ các thẻ `<div id="joly-admin-sidebar" data-props="...">` hoặc các thuộc tính chứa từ khóa `props` hay `data-`).
  - Giải mã tự động chuỗi JSON trong thuộc tính đó, dịch đệ quy các giá trị chuỗi tiếng Việt thành tiếng Anh, sau đó mã hóa ngược lại HTML-safe entity (`JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP`).
  - Giúp toàn bộ giao diện Admin (bao gồm cả Sidebar và Topbar vốn được dựng bằng React và nạp dữ liệu qua `data-props`) tự động chuyển sang tiếng Anh hoàn chỉnh khi chuyển đổi ngôn ngữ, không cần phải sửa thủ công bất kỳ component React nào.

- **Tự động dịch chuỗi văn bản cứng trong thẻ JavaScript (`<script>`):**
  - Nâng cấp middleware `TranslateHtmlResponse` để tự động trích xuất, phát hiện các hằng số chuỗi (string literals trong dấu nháy đơn, nháy kép, nháy ngược backtick) nằm bên trong khối lệnh JS của các thẻ `<script>`.
  - Thu thập tất cả các chuỗi có chứa ký tự tiếng Việt (như các câu thông báo Toast, SweetAlert2, các từ điển trạng thái động như `'Chưa kích hoạt'`, `'Còn bảo hành'`,...).
  - Gộp chung vào đợt dịch hàng loạt (Batch translation) để giảm thiểu tối đa số cuộc gọi API, sau đó thay thế trực tiếp vào mã JS trước khi trả về cho client mà không làm thay đổi các biến hay cú pháp logic hệ thống.
  - Xử lý triệt để toàn bộ các form ẩn, hộp thoại ẩn, cảnh báo động và popup trên toàn hệ thống (bao gồm cả trang tra cứu bảo hành, trang xem video, trang giỏ hàng,...).

- **Tự động dịch thuộc tính `value` của nút bấm (`<input type="submit|button|reset">`):**
  - Mở rộng phạm vi của `TranslateHtmlResponse` để tự động thu thập và dịch thuộc tính `value` của các thẻ `<input>` thuộc loại nút bấm.
  - Đảm bảo các nút bấm như `<input type="submit" value="Lưu lại">` hoặc `<input type="button" value="Hủy">` được tự động chuyển ngữ sang tiếng Anh chính xác mà không dịch nhầm các ô nhập văn bản (text input).

- **Từ điển ánh xạ dịch ưu tiên (Custom Translation Dictionary Overrides):**
  - Tích hợp từ điển ưu tiên trong `TranslationService` để thay thế trực tiếp các thuật ngữ quản trị phổ biến thay vì dùng Google Translate API mặc định.
  - Đảm bảo từ "Bảng điều khiển" luôn được dịch chuẩn xác là "Dashboard" (thay vì "Control panel" của Google Translate).
  - Đồng thời chuẩn hóa dịch nhanh các mục menu khác của trang Admin như: "Sổ Quỹ & Thu chi" -> "Cashbook & Expenses", "Phiếu sửa chữa" -> "Repair Tickets", "Hóa đơn dịch vụ" -> "Service Invoices", "Điều chuyển kho" -> "Warehouse Transfer", "Đổi thưởng" -> "Rewards",...

- **Đa ngôn ngữ hóa trang Thống kê KPI Nhân sự (admin/kpi):**
  - Dịch tiêu đề trang và placeholder tải dữ liệu trong file blade `resources/views/admin/kpi/index.blade.php` dựa trên locale hiện tại (`app()->getLocale() === 'en'`).
  - Hỗ trợ dịch thuật toàn diện các nhãn tĩnh, bộ lọc ngày tùy chỉnh, bảng vàng doanh thu của nhân viên kinh doanh & kỹ thuật trong component React `KPIDashboard.tsx` thông qua hàm `t(...)` có sẵn.
  - Tối ưu hóa sidebar quản trị `AdminSidebar.tsx` và `sidebar.blade.php` để chuyển đổi toàn bộ nhãn menu (như "Bảng điều khiển" thành "Dashboard", "Thống kê KPI" thành "KPI Statistics", v.v.) và các tên danh mục quản trị theo locale hiện tại, đồng thời đảm bảo màu sắc icon hoạt động (`getActiveIconColor`) khớp chính xác bất kể nhãn là tiếng Anh hay tiếng Việt.
  - Biên dịch thành công mã nguồn CSS/JS của Admin Dashboard và Sidebar bằng Vite (`npm run build`).

- **Đa ngôn ngữ hóa Thanh công cụ Quản trị (AdminTopbar):**
  - Khắc phục lỗi hiển thị thứ ngày bị cứng tiếng Việt bằng cách cấu hình phương thức `toLocaleDateString` và `toLocaleTimeString` trong component `AdminTopbar.tsx` sử dụng mã ngôn ngữ động dựa trên locale hiện tại (`isEn() ? 'en-US' : 'vi-VN'`).
  - Thiết kế và triển khai menu thả xuống (Dropdown selector) được kích hoạt bởi biểu tượng quả địa cầu (Globe icon) kết hợp nhãn ngôn ngữ hiện tại (VI/EN) nằm ngay trước nút phóng to màn hình (fullscreen) trên `AdminTopbar.tsx`. Dropdown này hiện tại hỗ trợ lựa chọn Tiếng Việt (🇻🇳 Tiếng Việt) và English (🇺🇸 English), đồng thời được thiết kế theo dạng danh sách mở rộng (array config) để dễ dàng tích hợp thêm các ngôn ngữ khác trong tương lai.
  - Tự động bắt sự kiện click-outside để đóng menu và biên dịch thành công mã nguồn với Vite.

- **Tài liệu hóa & Comment chi tiết mã nguồn:**
  - Viết chú thích (comments) bằng tiếng Việt cực kỳ chi tiết cho các file cốt lõi của tính năng gồm:
    - `app/Http/Middleware/TranslateHtmlResponse.php`
    - `app/Services/TranslationService.php`
    - `app/Traits/BaseTranslationTrait.php`
    - `resources/js/helpers.ts`
    - `resources/js/components/AdminTopbar.tsx`
  - Đảm bảo các kỹ sư tiếp quản dễ dàng nắm vững kiến trúc, các bước hoạt động (quét DOM, dịch gộp, bộ lọc dịch, cách intercept model, click-outside, v.v.).



