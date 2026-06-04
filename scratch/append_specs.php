<?php
$file = 'd:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md';
$content = <<<'MD'

---

## 7.48. QUẢN LÝ KHÁCH HÀNG (CRM) & HỆ THỐNG XỬ PHẠT (BANNING SYSTEM)

### 7.48.1. Tổng quan (Overview)
Cổng quản trị khách hàng tập trung (CRM) dạng Single Page App điều hướng mềm không tải lại trang, cho phép Admin quản lý toàn bộ thông tin khách hàng đã đăng ký. Tích hợp hệ thống xử phạt thành viên vi phạm tiêu chuẩn cộng đồng (spam bình luận tục tĩu, đánh giá giả mạo): khi Admin xóa bình luận vi phạm, hệ thống mở popup SweetAlert2 với các mức cấm (1 ngày, 3 ngày, Vĩnh viễn). Người dùng bị cấm sẽ bị cập nhật trường `comment_banned_until` và bị chặn mọi tương tác viết bình luận/đánh giá.

### 7.48.2. Bố cục giao diện (UI Layout)
*   **Danh sách khách hàng (DataTable):** Bảng dữ liệu có phân trang, tìm kiếm nhanh, cột: Ảnh, Tên, Email, SĐT, Hạng VIP, Điểm tích lũy, Trạng thái (Active/Banned), Hành động.
*   **Popup xử phạt (SweetAlert2):** Khi Admin xóa bình luận vi phạm, mở popup lựa chọn: *Chỉ xóa*, *Xóa + Cấm 1 ngày*, *3 ngày*, *Vĩnh viễn*.

### 7.48.3. Các tác nhân tương tác (Actors) & Giới hạn quyền hạn
*   **Admin:** Xem, sửa, xóa thông tin khách hàng. Xóa bình luận vi phạm và áp dụng lệnh cấm tương tác.
*   **System:** Tự động chặn request viết bình luận/đánh giá dựa trên `comment_banned_until`. Cascade Delete phản hồi con khi xóa bình luận gốc.

### 7.48.4. Sơ đồ Use-case (Mermaid)
```mermaid
usecaseDiagram
    actor Admin as "Quản trị viên"
    actor System as "Hệ thống tự động"

    Admin --> (Xem danh sách khách hàng)
    Admin --> (Xóa bình luận vi phạm)
    Admin --> (Áp dụng lệnh cấm tương tác)
    System --> (Cascade Delete phản hồi con)
    System --> (Gửi thông báo vi phạm cho User)
    System --> (Chặn request viết bình luận khi bị cấm)
```

### 7.48.5. Sơ đồ hoạt động (Activity Diagram - Mermaid)
```mermaid
flowchart TD
    Start([Admin phát hiện bình luận vi phạm]) --> ClickDelete[Click nút Xóa bình luận]
    ClickDelete --> ShowPopup[Hiển thị SweetAlert2 chọn mức phạt]
    ShowPopup --> SelectLevel{Admin chọn mức?}
    SelectLevel -- Chỉ xóa --> DeleteOnly[Xóa bình luận + Cascade Delete replies]
    SelectLevel -- Cấm 1/3 ngày --> BanTemp["Cập nhật comment_banned_until = now + N ngày"]
    SelectLevel -- Vĩnh viễn --> BanPerm["Cập nhật comment_banned_until = 9999-12-31"]
    DeleteOnly --> NotifyUser[Gửi thông báo vi phạm] --> End([Kết thúc])
    BanTemp --> DeleteComment[Xóa bình luận + Cascade] --> NotifyBan[Gửi thông báo cấm] --> End
    BanPerm --> DeleteComment
```

### 7.48.6. Bảng kịch bản hoạt động (Workflows)

#### Kịch bản 1: Xóa bình luận và cấm tương tác 3 ngày (Happy Case)
| Bước | Tác nhân | Hành động | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Admin | Phát hiện bình luận spam, nhấn nút Xóa. | Mở SweetAlert2 với 4 lựa chọn mức phạt. |
| 2 | Admin | Chọn "Xóa và cấm 3 ngày". | Xóa bình luận gốc + Cascade Delete reply con, cập nhật `comment_banned_until`. Gửi 2 thông báo tự động vào Notification Center của User. |

#### Kịch bản 2: Người dùng bị cấm cố viết bình luận (Exception Case)
| Bước | Tác nhân | Hành động | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Customer | Cố gửi đánh giá sản phẩm khi đang bị cấm. | Server kiểm tra `comment_banned_until > now()`. Trả về lỗi 403 kèm thời hạn cấm. |

### 7.48.7. Quy tắc nghiệp vụ ngầm (Business Rules)
1. **Cascade Delete:** Xóa bình luận vi phạm bắt buộc xóa theo tầng tất cả phản hồi con (Replies).
2. **Chặn đa kênh:** Lệnh cấm phải được kiểm tra ở cả `ReviewController` và `VideoController`.
3. **Thông báo tự động:** Gửi cả thông báo gỡ bài lẫn thông báo cấm hoạt động vào Notification Center.

### 7.48.8. Đặc tả màn hình giao diện (Screen Descriptions)

#### Màn hình Danh sách khách hàng (Admin Side)
| No | Field name | Control type | Constraint / Validation Rule | Required | Default value |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Ô tìm kiếm | Textbox | Tối đa 50 ký tự. Tìm theo tên, email, SĐT. | No | Trống |
| 2 | Bộ lọc hạng VIP | Dropdown | Tất cả, Đồng, Bạc, Vàng, Kim Cương. | No | Tất cả |
| 3 | Bộ lọc trạng thái | Dropdown | Tất cả, Active, Banned. | No | Tất cả |

---

## 7.49. QUẢN LÝ BÀI VIẾT & BLOG CÔNG NGHỆ (CRUD ARTICLES)

### 7.49.1. Tổng quan (Overview)
Giao diện viết bài, cập nhật blog tin tức công nghệ cho Admin và người dùng. Soạn thảo 2 cột: cột trái Rich Text Editor, cột phải Preview render thời gian thực. Tích hợp AI kiểm duyệt UGC tự động và cộng điểm Loyalty cho bài viết chất lượng (điểm AI ≥ 80).

### 7.49.2. Bố cục giao diện (UI Layout)
*   **Danh sách bài viết (Admin):** DataTable: tiêu đề, tác giả, trạng thái (Draft/Pending/Published/Rejected), điểm AI, ngày tạo.
*   **Form soạn thảo 2 cột:** Cột trái: tiêu đề, Rich Text Editor, danh mục, thẻ tag, ảnh. Cột phải: Preview real-time.
*   **Nút Bulk Approve:** Nút xanh ngọc quét tất cả bài UGC `pending` đã được AI gắn `approved` điểm ≥ 80 để duyệt hàng loạt.

### 7.49.3. Các tác nhân tương tác (Actors) & Giới hạn quyền hạn
*   **Admin:** CRUD bài viết, duyệt/từ chối bài UGC, Bulk Approve hàng loạt.
*   **Customer:** Viết bài UGC, chỉnh sửa bài cá nhân khi còn Draft/Pending.
*   **System (AI):** Kiểm duyệt nội dung, chấm điểm SEO, sinh metadata SEO, cộng điểm Loyalty.

### 7.49.4. Sơ đồ Use-case (Mermaid)
```mermaid
usecaseDiagram
    actor Admin as "Quản trị viên"
    actor Customer as "Khách hàng"
    actor AI as "AI Gemini"

    Admin --> (Quản lý bài viết CRUD)
    Admin --> (Duyệt hàng loạt bài UGC đạt chuẩn AI)
    Customer --> (Viết bài UGC Techblog)
    AI --> (Kiểm duyệt nội dung tự động)
    AI --> (Sinh metadata SEO)
```

### 7.49.5. Sơ đồ hoạt động (Activity Diagram - Mermaid)
```mermaid
flowchart TD
    Start([Người dùng lưu bài viết]) --> SaveDB[Lưu bài viết trạng thái Pending]
    SaveDB --> CallAI[ArticleAIService gửi nội dung cho Gemini API]
    CallAI --> CheckSafe{Nội dung an toàn?}
    CheckSafe -- Không --> Reject[Chuyển trạng thái Rejected] --> End([Kết thúc])
    CheckSafe -- Có --> CheckScore{Điểm chất lượng >= 80?}
    CheckScore -- Có --> AutoApprove[Duyệt Published + Cộng 20 điểm Loyalty] --> GenSEO[Sinh seo_title, seo_description, seo_keywords] --> End
    CheckScore -- Không --> KeepPending[Giữ Pending chờ Admin duyệt] --> GenSEO
```

### 7.49.6. Bảng kịch bản hoạt động (Workflows)

#### Kịch bản 1: Viết bài UGC được AI duyệt tự động (Happy Case)
| Bước | Tác nhân | Hành động | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Customer | Soạn bài review "Trải nghiệm máy giặt Samsung AI" 500+ từ. | Lưu Pending, gọi ArticleAIService. |
| 2 | AI | Phân tích: an toàn, điểm 85/100. | Duyệt Published, sinh SEO metadata, cộng 20 điểm Loyalty. |

#### Kịch bản 2: Bài viết vi phạm (Exception Case)
| Bước | Tác nhân | Hành động | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Customer | Viết bài chứa link quảng cáo đối thủ. | AI gắn nhãn `rejected`. Thông báo vi phạm cho tác giả. |

### 7.49.7. Quy tắc nghiệp vụ ngầm (Business Rules)
1. **Chống cộng điểm trùng:** Cờ `reward_points_awarded` ngăn cộng Loyalty nhiều lần khi sửa bài.
2. **Bulk Approve:** Chỉ quét bài `status=pending` AND `ai_moderation_verdict=approved` AND `ai_quality_score >= 80`.

### 7.49.8. Đặc tả màn hình giao diện (Screen Descriptions)

#### Màn hình Soạn thảo bài viết
| No | Field name | Control type | Constraint / Validation Rule | Required | Default value |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Tiêu đề bài viết | Textbox | Tối đa 200 ký tự. | Yes | Trống |
| 2 | Nội dung | Rich Text Editor | Tối thiểu 100 ký tự. Hỗ trợ ảnh, video nhúng. | Yes | Trống |
| 3 | Danh mục | Dropdown | Chọn từ danh sách sẵn. | Yes | N/A |
| 4 | Thẻ tag | Tag Input | Tối đa 5 tag, mỗi tag tối đa 30 ký tự. | No | Trống |
| 5 | Ảnh đại diện | File Upload | JPG, PNG, WEBP. Tối đa 2MB. | No | N/A |

---

## 7.50. TÙY BIẾN GIAO DIỆN HEADER/FOOTER (THEME CUSTOMIZER)

### 7.50.1. Tổng quan (Overview)
Phân hệ Theme Customizer cao cấp cho phép Admin tùy biến sâu Header/Topbar và Footer/Social links/Copyright của website. Sử dụng kỹ thuật Same-Origin DOM Sync Iframe để can thiệp trực tiếp CSS `:root` và cấu trúc DOM của trang chủ thật nhúng trong Iframe, mang lại Live Preview 100% chính xác thay vì Mockup CSS tĩnh.

### 7.50.2. Bố cục giao diện (UI Layout)
*   **Bảng cấu hình React (2 tab):** Tab "Đầu trang" cấu hình màu nền, hotline, logo. Tab "Chân trang" cấu hình liên kết mạng xã hội, copyright, màu sắc.
*   **Iframe Live Preview:** Nhúng trang chủ thật trong `<iframe>`, JS can thiệp Same-Origin sửa DOM và CSS `:root` real-time.
*   **Highlight Overlay:** Đường nét đứt đỏ bao quanh khu vực đang chỉnh sửa.

### 7.50.3. Các tác nhân tương tác (Actors) & Giới hạn quyền hạn
*   **Admin:** Chỉnh sửa cấu hình giao diện Header/Footer và xem Live Preview.
*   **System:** Áp dụng CSS variables và cập nhật DOM trong Iframe real-time.

### 7.50.4. Sơ đồ Use-case (Mermaid)
```mermaid
usecaseDiagram
    actor Admin as "Quản trị viên"
    actor System as "Hệ thống"

    Admin --> (Chỉnh sửa màu sắc Header)
    Admin --> (Thay đổi hotline và logo)
    Admin --> (Cấu hình liên kết Footer)
    Admin --> (Xem Live Preview Iframe)
    System --> (Same-Origin DOM Sync)
    System --> (Auto-Scroll Focus theo tab)
```

### 7.50.5. Sơ đồ hoạt động (Activity Diagram - Mermaid)
```mermaid
flowchart TD
    Start([Admin mở Theme Customizer]) --> LoadIframe[Nhúng Iframe trang chủ thật]
    LoadIframe --> SelectTab{Chọn tab?}
    SelectTab -- Đầu trang --> ScrollTop[Iframe tự cuộn lên đỉnh]
    SelectTab -- Chân trang --> ScrollBottom[Iframe tự cuộn xuống đáy]
    ScrollTop --> EditConfig[Admin chỉnh sửa màu sắc, hotline, logo]
    ScrollBottom --> EditConfig
    EditConfig --> SyncDOM["JS can thiệp Same-Origin: sửa DOM + CSS :root"]
    SyncDOM --> HighlightArea[Vẽ Highlight Overlay nét đứt đỏ] --> LiveResult[Iframe cập nhật real-time]
    LiveResult --> SaveBtn{Admin nhấn Lưu?}
    SaveBtn -- Có --> SaveDB[Lưu cấu hình vào DB] --> End([Kết thúc])
    SaveBtn -- Không --> EditConfig
```

### 7.50.6. Bảng kịch bản hoạt động (Workflows)

#### Kịch bản 1: Thay đổi màu Header (Happy Case)
| Bước | Tác nhân | Hành động | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Admin | Mở Theme Customizer, chọn tab "Đầu trang", đổi màu nền sang #1a1a2e. | JS cập nhật CSS variable `--header-bg` trong Iframe DOM. Iframe hiển thị Header màu mới ngay lập tức. |
| 2 | Admin | Nhấn nút "Lưu cấu hình". | Lưu vào DB, tất cả trang Frontend áp dụng màu mới. |

### 7.50.7. Quy tắc nghiệp vụ ngầm (Business Rules)
1. **Same-Origin Policy:** Iframe chỉ hoạt động khi trang Admin và trang chủ cùng domain (Same-Origin).
2. **Auto-Scroll Focus:** Chuyển tab Đầu trang → cuộn Iframe lên đỉnh; Chân trang → cuộn xuống đáy.
3. **Highlight Overlay:** CSS nét đứt đỏ không được làm lệch hay bóp méo giao diện thật.

### 7.50.8. Đặc tả màn hình giao diện (Screen Descriptions)

#### Màn hình Theme Customizer (Admin Side)
| No | Field name | Control type | Constraint / Validation Rule | Required | Default value |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Màu nền Header | Color Picker | Mã HEX hợp lệ (VD: #1a1a2e). | Yes | Giá trị hiện tại |
| 2 | Số hotline | Textbox | 10-11 chữ số. Định dạng: 0xxx.xxx.xxx. | Yes | Giá trị hiện tại |
| 3 | Logo | File Upload | JPG, PNG, SVG. Tối đa 1MB. | No | Logo hiện tại |
| 4 | Liên kết mạng xã hội | URL Input | URL hợp lệ (https://...). Tối đa 5 liên kết. | No | Trống |

---

## 7.51. SMART SETUP WIZARD & CLI ORCHESTRATOR (START.BAT)

### 7.51.1. Tổng quan (Overview)
Bộ công cụ tự động hóa cấu hình, cài đặt và khởi chạy dự án cho thành viên nhóm và hội đồng chấm thi chỉ với một click. Batch Script tương tác cao cấp hỗ trợ: chọn CSDL SQLite/MySQL, tự tạo `.env`, phát sinh `APP_KEY`, liên kết `storage:link`, cài đặt Composer/NPM dependencies, chạy migration và build Vite.

### 7.51.2. Bố cục giao diện (UI Layout)
*   **Menu chính CLI:** Hiển thị danh sách Option số (1-6) trong cửa sổ Terminal/CMD.
*   **Option [6] INITIALIZE:** Kiểm tra `vendor/`, `node_modules/` đã tồn tại để gợi ý bỏ qua. Chọn SQLite/MySQL. Tự tạo `.env` và `APP_KEY`.
*   **Option [5] FAST REBUILD & RUN:** Dọn cache, chạy migration, build Vite, khởi động server demo.

### 7.51.3. Các tác nhân tương tác (Actors) & Giới hạn quyền hạn
*   **Developer / Giám khảo:** Chạy `start.bat` và chọn Option từ menu CLI.
*   **System (Batch Script):** Thực thi các lệnh Artisan, Composer, NPM tự động.

### 7.51.4. Sơ đồ Use-case (Mermaid)
```mermaid
usecaseDiagram
    actor Dev as "Developer / Giám khảo"
    actor System as "Batch Script"

    Dev --> (Chạy start.bat)
    Dev --> (Chọn Option khởi tạo dự án)
    Dev --> (Chọn Option Fast Rebuild)
    System --> (Kiểm tra dependencies đã cài chưa)
    System --> (Tự tạo .env và APP_KEY)
    System --> (Chạy migration và seed DB)
    System --> (Build Vite và khởi động server)
```

### 7.51.5. Sơ đồ hoạt động (Activity Diagram - Mermaid)
```mermaid
flowchart TD
    Start([Người dùng chạy start.bat]) --> ShowMenu[Hiển thị Menu CLI 6 Option]
    ShowMenu --> SelectOption{Chọn Option?}
    SelectOption -- Option 6 INIT --> CheckVendor{vendor/ đã tồn tại?}
    CheckVendor -- Có --> SkipComposer[Gợi ý bỏ qua Composer install]
    CheckVendor -- Không --> RunComposer[Chạy composer install]
    SkipComposer --> SelectDB{Chọn CSDL?}
    RunComposer --> SelectDB
    SelectDB -- SQLite --> CreateSQLite[Tạo file .sqlite, dọn config MySQL trong .env]
    SelectDB -- MySQL --> InputMySQL[Nhập Host, Port, Database, User]
    CreateSQLite --> GenKey[Tạo .env + php artisan key:generate] --> StorageLink[php artisan storage:link] --> RunMigrate[php artisan migrate --seed] --> End([Hoàn thành])
    InputMySQL --> GenKey
    SelectOption -- Option 5 REBUILD --> ClearCache[php artisan cache:clear + config:clear] --> Migrate2[php artisan migrate] --> BuildVite[npm run build] --> Serve[php artisan serve] --> End
```

### 7.51.6. Bảng kịch bản hoạt động (Workflows)

#### Kịch bản 1: Khởi tạo dự án lần đầu với SQLite (Happy Case)
| Bước | Tác nhân | Hành động | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Dev | Chạy `start.bat`, chọn Option 6. | Kiểm tra `vendor/` chưa tồn tại, chạy `composer install`. |
| 2 | Dev | Chọn SQLite khi được hỏi loại CSDL. | Tạo file `.sqlite`, dọn config MySQL, tạo `.env`, sinh `APP_KEY`, chạy migration + seed. Server khởi động. |

#### Kịch bản 2: Fast Rebuild cho demo nhanh (Happy Case)
| Bước | Tác nhân | Hành động | Phản hồi hệ thống |
| :--- | :--- | :--- | :--- |
| 1 | Giám khảo | Chạy `start.bat`, chọn Option 5. | Dọn cache, chạy migration, build Vite, khởi động `php artisan serve`. |

### 7.51.7. Quy tắc nghiệp vụ ngầm (Business Rules)
1. **Smart Detection:** Tự động phát hiện `vendor/` và `node_modules/` để gợi ý bỏ qua bước cài đặt, tiết kiệm thời gian.
2. **Dynamic Driver Setup:** Khi chọn MySQL, giá trị mặc định được áp dụng khi nhấn Enter (Host=127.0.0.1, Port=3306).
3. **Anti-Crash:** Sử dụng mô hình nhãn tuyến tính (`goto`) thay vì ngoặc đơn lồng để tránh crash parser Batch trên Windows.

### 7.51.8. Đặc tả màn hình giao diện (Screen Descriptions)

#### Màn hình Menu CLI (Terminal)
| No | Field name | Control type | Constraint / Validation Rule | Required | Default value |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Chọn Option | CLI Input | Chỉ nhận số từ 1 đến 6. | Yes | N/A |
| 2 | Chọn loại CSDL | CLI Input | Chỉ nhận: `sqlite` hoặc `mysql`. | Yes | sqlite |
| 3 | MySQL Host | CLI Input | Chuỗi không trống. | No | 127.0.0.1 |
| 4 | MySQL Port | CLI Input | Số nguyên dương 1-65535. | No | 3306 |
| 5 | MySQL Database | CLI Input | Chuỗi không trống, không chứa khoảng trắng. | No | dienmay_pro |

MD;

file_put_contents($file, $content, FILE_APPEND);
echo "Appended 4 specs (7.48-7.51) successfully. New size: " . filesize($file) . " bytes\n";
