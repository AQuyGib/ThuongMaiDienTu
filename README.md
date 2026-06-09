<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11"/>
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2"/>
  <img src="https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react&logoColor=black" alt="React 19"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/>
  <img src="https://img.shields.io/badge/TailwindCSS-3.4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="TailwindCSS"/>
  <img src="https://img.shields.io/badge/Vite-5-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 5"/>
</p>

# 🛒 DienMayPro — Hệ Thống Thương Mại Điện Tử & Mini-ERP

> **Dự án nhóm 5 thành viên** · Phát triển từ 04/2026 · **683+ commits** · Full-stack E-commerce kết hợp ERP quản trị doanh nghiệp

---

## 📋 Mục Lục

- [Tổng Quan](#-tổng-quan)
- [Công Nghệ Sử Dụng](#-công-nghệ-sử-dụng)
- [Kiến Trúc Hệ Thống](#-kiến-trúc-hệ-thống)
- [Tính Năng Chính](#-tính-năng-chính)
- [Bảo Mật & Kiểm Thử](#-bảo-mật--kiểm-thử)
- [Tích Hợp AI](#-tích-hợp-ai)
- [Cơ Sở Dữ Liệu](#-cơ-sở-dữ-liệu)
- [Cài Đặt & Chạy Dự Án](#-cài-đặt--chạy-dự-án)
- [Cấu Trúc Thư Mục](#-cấu-trúc-thư-mục)
- [Đội Ngũ Phát Triển](#-đội-ngũ-phát-triển)

---

## 🎯 Tổng Quan

**DienMayPro** là hệ thống Thương mại Điện tử chuyên ngành điện máy, kết hợp mô-đun **Mini-ERP** quản trị nội bộ. Dự án được xây dựng theo mô hình **MVC + Service Layer** trên nền tảng Laravel 11, tích hợp React 19 cho các component tương tác cao cấp.

### Điểm nổi bật

| Chỉ số | Giá trị |
|--------|---------|
| **Commits** | 683+ |
| **Database Migrations** | 118 files |
| **Eloquent Models** | 58+ |
| **Admin Controllers** | 40 |
| **Service Classes** | 19 |
| **Feature Tests** | 13 suites · 56+ test cases |
| **Thành viên** | 5 lập trình viên |
| **Ngôn ngữ** | Song ngữ Việt – Anh |

---

## 🛠 Công Nghệ Sử Dụng

### Backend
| Công nghệ | Phiên bản | Vai trò |
|-----------|-----------|---------|
| **PHP** | 8.2+ | Ngôn ngữ chính |
| **Laravel** | 11.x | Framework MVC |
| **Laravel Sanctum** | 4.0 | API Authentication (Token-based) |
| **Laravel Socialite** | 5.27 | Đăng nhập Google OAuth |
| **Maatwebsite Excel** | 3.1 | Xuất báo cáo Excel |
| **DomPDF** | 3.1 | Xuất PDF (Hóa đơn, Báo cáo) |
| **MySQL** | 8.0 | Cơ sở dữ liệu quan hệ |

### Frontend
| Công nghệ | Phiên bản | Vai trò |
|-----------|-----------|---------|
| **React** | 19 | Component tương tác cao cấp |
| **Vite** | 5 | Build tool & HMR |
| **TailwindCSS** | 3.4 | Utility-first CSS |
| **Chart.js** | 4.5 | Biểu đồ KPI Dashboard |
| **SWR** | 2.4 | Data fetching & caching |
| **Blade Template** | — | Server-side rendering |
| **Lucide React** | 1.14 | Icon system |

### DevOps & Testing
| Công nghệ | Vai trò |
|-----------|---------|
| **Git / GitHub** | Version control · Gitflow branching |
| **PHPUnit** | 11.x · Unit & Feature Testing |
| **XAMPP** | Local dev environment |
| **Postman** | API testing & documentation |

---

## 🏗 Kiến Trúc Hệ Thống

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT (Browser)                     │
│  Blade SSR  ·  React 19 SPA Components  ·  Vite HMR    │
└────────────────────────┬────────────────────────────────┘
                         │ HTTP / AJAX / REST API
┌────────────────────────▼────────────────────────────────┐
│                  LARAVEL 11 BACKEND                     │
│  ┌──────────┐  ┌────────────┐  ┌─────────────────────┐  │
│  │ Routing  │→ │ Middleware │→ │    Controllers       │  │
│  │ web.php  │  │ IsAdmin    │  │ Admin/ · Frontend/   │  │
│  │ admin.php│  │ CheckRole  │  │ Api/  · Auth/        │  │
│  │ api.php  │  │ Sanctum    │  └──────────┬──────────┘  │
│  └──────────┘  └────────────┘             │             │
│                              ┌────────────▼──────────┐  │
│                              │   Service Layer (19)  │  │
│                              │ CartService           │  │
│                              │ InventoryService      │  │
│                              │ PointsService         │  │
│                              │ RewardsService        │  │
│                              │ NotificationService   │  │
│                              │ FlashSaleService      │  │
│                              │ InstallmentAIService  │  │
│                              │ RepairAIService       │  │
│                              │ ...                   │  │
│                              └────────────┬──────────┘  │
│                              ┌────────────▼──────────┐  │
│                              │  Eloquent ORM (58+)   │  │
│                              │  Traits · Observers   │  │
│                              │  HasAuditLog          │  │
│                              └────────────┬──────────┘  │
└───────────────────────────────────────────┼─────────────┘
                               ┌────────────▼──────────┐
                               │   MySQL Database      │
                               │   118 Migrations      │
                               └───────────────────────┘
```

### Design Patterns áp dụng
- **MVC** — Tách biệt Controller, Model, View
- **Service Layer** — Đóng gói business logic phức tạp, tái sử dụng
- **Repository Pattern** — Eloquent ORM với eager loading tối ưu
- **Observer Pattern** — Tự động ghi nhật ký, cập nhật tồn kho
- **Job Queue** — Xử lý tác vụ nền (Audit Logs, Notifications)
- **Event Delegation** — Frontend DOM event handling tối ưu

---

## ✨ Tính Năng Chính

### 🛍 Module Thương Mại Điện Tử (Storefront)

| Tính năng | Mô tả |
|-----------|-------|
| **Quản lý Sản phẩm** | CRUD sản phẩm, biến thể (RAM/CPU/GPU), thông số kỹ thuật JSON |
| **Giỏ hàng & Thanh toán** | Giỏ hàng AJAX, validation real-time, QR Code thanh toán |
| **Flash Sale** | Chiến dịch giảm giá, đếm ngược, quản lý tồn kho atomic |
| **Mã giảm giá (Voucher)** | Giảm theo tiền/phần trăm, giới hạn lượt dùng |
| **Đơn hàng** | Lifecycle đầy đủ: Pending → Confirmed → Shipping → Delivered → Cancelled |
| **Tra cứu đơn hàng** | Tìm kiếm AJAX, highlight đơn mới, hỗ trợ khách vãng lai |
| **So sánh sản phẩm** | So sánh đa sản phẩm, floating bar |
| **Lọc nâng cao** | Lọc theo giá, thương hiệu, danh mục, thông số — partial AJAX rendering |
| **Wishlist** | Danh sách yêu thích, xóa Optimistic UI |
| **Đánh giá & Bình luận** | Review kèm media, reply thread, AI moderation |
| **Bài viết Lifestyle** | UGC (User-Generated Content), duyệt bài cộng điểm |
| **Song ngữ Việt – Anh** | Hệ thống dịch đa ngôn ngữ hoàn chỉnh |

### 📊 Module Quản Trị (Admin Dashboard)

| Tính năng | Mô tả |
|-----------|-------|
| **KPI Dashboard** | Biểu đồ doanh thu, tỷ lệ hoàn thành, Leaderboard Top 10, Drawer chi tiết nhân viên |
| **Quản lý Nhân viên** | CRUD, batch actions, toggle status, Chart.js Doughnut, CSV/Excel/PDF export |
| **Quản lý Khách hàng** | CRUD, phân hạng thành viên (Đồng → Kim Cương), soft delete |
| **Quản lý Kho** | Tồn kho real-time, nhập hàng PO, điều chuyển kho, kiểm kê, cảnh báo tồn kho thấp |
| **Phiếu Sửa chữa** | Lifecycle: Received → Checking → Under_Repair → Done, xuất hóa đơn dịch vụ |
| **Sổ Quỹ (Cashbook)** | Thu chi nội bộ, đồng bộ đơn hàng |
| **Trả góp (Installment)** | Kế hoạch trả góp, thanh toán kỳ, AI đánh giá rủi ro |
| **Bảo hành & Đổi trả** | Quản lý phiếu bảo hành, yêu cầu đổi trả |
| **Thông báo đa kênh** | Thông báo hệ thống, email SMTP, chiến dịch push |
| **Nhật ký hoạt động** | Audit log bảo mật 22 model, mã băm lũy tiến, live feed polling 5s |
| **Live Theme Customizer** | Tùy biến Header/Footer real-time qua iframe đồng bộ |
| **Communication Hub** | Chat nội bộ, phân quyền Leader/Co-leader/Member, emoji reactions |

### 🎰 Module Tích Điểm & Phần Thưởng

| Tính năng | Mô tả |
|-----------|-------|
| **Tích điểm tự động** | Cộng điểm khi mua hàng, viết bài, đánh giá |
| **Phân hạng thành viên** | Đồng → Bạc → Vàng → Kim Cương |
| **Vòng quay may mắn** | Multi-wheel, giới hạn theo hạng, admin tùy chỉnh |
| **Đổi thưởng** | Catalog quà tặng, voucher, quy đổi điểm |

---

## 🔒 Bảo Mật & Kiểm Thử

### Bảo mật đa lớp

| Lớp bảo vệ | Chi tiết |
|-------------|----------|
| **Authentication** | Laravel Sanctum (Token-based API), Google OAuth 2.0, Session-based Web |
| **2FA** | Xác thực hai lớp qua OTP Email |
| **RBAC** | 4 vai trò: Admin, Quản lý, Nhân viên, Khách hàng · Middleware `CheckRole` |
| **Anti-XSS** | DOMPurify sanitize, `strip_tags()`, regex input filtering |
| **Anti-BOLA** | Server-side authorization check trên mỗi resource |
| **Anti-Spam** | Keyboard smash detection, rate limiting, chatbot ban mechanism |
| **Optimistic Locking** | `version` column check chống xung đột cập nhật đồng thời |
| **Audit Trail** | 22 model tích hợp `HasAuditLog`, mã băm SHA-256 chuỗi lũy tiến |
| **SMTP Security** | OTP qua email thực, không hiển thị mã trên client |

### Kiểm thử tự động

```
php artisan test
───────────────────────────────────────
 Tests:  56 passed (100%)
 Suites: 13 Feature Test files
───────────────────────────────────────
```

| Test Suite | Phạm vi kiểm thử |
|------------|-------------------|
| `ApiAuthTest` | Sanctum login/logout/token lifecycle |
| `ChatbotSecurityTest` | XSS, spam, rate limit, session isolation |
| `FlashSaleEndToEndTest` | Luồng mua hàng Flash Sale đầy đủ |
| `AuditLogTest` | Ghi nhật ký & xác minh toàn vẹn hash |
| `InventorySyncTest` | Đồng bộ tồn kho atomic |
| `NotificationTest` | Chiến dịch thông báo đa kênh |
| `WarrantyClaimTest` | Luồng bảo hành & đổi trả |
| `ArticleModerationTest` | AI kiểm duyệt nội dung UGC |
| ... | _và nhiều test khác_ |

---

## 🤖 Tích Hợp AI

| Module AI | Công nghệ | Chức năng |
|-----------|-----------|-----------|
| **Chatbot hỗ trợ** | Google Gemini API | Trả lời câu hỏi sản phẩm, chính sách bảo hành, hỗ trợ đặt hàng |
| **AI Đánh giá rủi ro trả góp** | Gemini + Heuristic Fallback | Phân tích hồ sơ khách hàng, đề xuất phương án trả góp |
| **AI Chẩn đoán sửa chữa** | Gemini API | Chẩn đoán lỗi thiết bị qua mô tả triệu chứng |
| **AI Tối ưu SEO bài viết** | Gemini API | Gợi ý tiêu đề, mô tả meta, từ khóa cho bài viết |
| **AI Kiểm duyệt nội dung** | Gemini API | Tự động lọc nội dung vi phạm trong bài viết UGC |
| **AI Dự đoán đơn hàng** | Gemini API | Phân tích xu hướng và dự báo |

---

## 🗄 Cơ Sở Dữ Liệu

- **118 migration files** — thiết kế quan hệ chuẩn hóa, hỗ trợ soft delete
- **58+ Eloquent Models** — với relationships, scopes, accessors, mutators
- **Seeder dữ liệu mẫu** — 50+ nhân viên, sản phẩm, đơn hàng, phiếu sửa chữa

### Sơ đồ các module chính

```
Users ─── Orders ─── OrderDetails ─── Products
  │          │                           │
  │          └── Installments            ├── ProductVariants
  │                                      ├── ProductSpecs
  ├── RepairTickets ── ServiceInvoices   ├── FlashSaleProducts
  ├── UserPoints ── PointTransactions    └── Reviews
  ├── RewardRedemptions                      
  ├── LuckyWheelSpins                    Categories
  ├── Notifications                      Suppliers
  ├── ChatRoomMembers ── ChatMessages    CouponFlashSales
  ├── LoginHistories                     ActivityLogs
  └── Warranties ── WarrantyClaims       Cashbooks
```

---

## 🚀 Cài Đặt & Chạy Dự Án

### Yêu cầu hệ thống

- PHP ≥ 8.2 (bật extensions: `pdo_mysql`, `mbstring`, `openssl`, `zip`, `gd`)
- MySQL ≥ 8.0
- Composer ≥ 2.x
- Node.js ≥ 18.x & npm
- XAMPP / WAMP hoặc tương đương

### Hướng dẫn cài đặt

```bash
# 1. Clone repository
git clone https://github.com/AQuyGib/ThuongMaiDienTu.git
cd ThuongMaiDienTu/ThuongMaiDienTu

# 2. Cài đặt dependencies PHP
composer install

# 3. Cài đặt dependencies Node.js
npm install

# 4. Cấu hình môi trường
cp .env.example .env
php artisan key:generate
# → Chỉnh sửa DB_DATABASE, DB_USERNAME, DB_PASSWORD trong .env

# 5. Khởi tạo database
php artisan migrate --seed

# 6. Build assets
npm run build

# 7. Khởi chạy server
php artisan serve
```

### Chạy nhanh với script tự động

```bash
# Windows — Script khởi chạy 1-Click tự động
.\start.bat
# → Tự động kiểm tra MySQL, migrate, build assets, khởi chạy server
```

---

## 📁 Cấu Trúc Thư Mục

```
ThuongMaiDienTu/
├── app/
│   ├── Enums/                    # Hằng số, trạng thái (OrderStatus, RoleType)
│   ├── Exports/                  # Export Excel/PDF (EmployeeExport)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/            # 40 controllers quản trị
│   │   │   ├── Frontend/         # Controllers storefront
│   │   │   ├── Api/              # REST API (Sanctum Auth)
│   │   │   └── Auth/             # Login, Register, OAuth, 2FA
│   │   ├── Middleware/           # IsAdmin, CheckRole, ApiAuth
│   │   └── Requests/            # Form Request Validation
│   ├── Models/                   # 58+ Eloquent Models
│   ├── Observers/                # Model event listeners
│   ├── Services/                 # 19 Service classes (Business Logic)
│   ├── Traits/                   # HasAuditLog, etc.
│   └── Jobs/                     # Queue jobs (LogAuditEventJob)
├── database/
│   ├── migrations/               # 118 migration files
│   ├── seeders/                  # Dữ liệu mẫu phong phú
│   └── factories/                # Model factories cho testing
├── resources/
│   ├── js/
│   │   └── components/           # React 19 (KPI, Employee, Chat Hub, etc.)
│   └── views/
│       ├── admin/                # Blade views quản trị
│       ├── frontend/             # Blade views storefront
│       └── emails/               # Email templates (OTP, thông báo)
├── routes/
│   ├── web.php                   # Routes khách hàng
│   ├── admin.php                 # Routes quản trị (RBAC middleware)
│   └── api.php                   # REST API routes (Sanctum)
├── tests/
│   └── Feature/                  # 13 Feature test suites
├── public/assets/                # Static assets (CSS, JS, Images)
└── start.bat                     # Script khởi chạy 1-Click
```

---

## 👥 Đội Ngũ Phát Triển

| Thành viên | Vai trò | Đóng góp chính |
|-----------|---------|----------------|
| **Anh Quý** | Team Lead · Full-stack | Kiến trúc hệ thống, Storefront, AI Integration, Bảo mật, Testing |
| **Ehin (Hiền)** | Backend · Frontend | Dashboard, KPI, Sổ quỹ, Video, Bảo hành, Trả góp |
| **Xuân Hòa** | Backend · Frontend | 2FA, RBAC, CRUD Nhân viên, KPI Dashboard, Theme Customizer |
| **Vĩnh Em** | Backend · Frontend | Giỏ hàng, Thanh toán, QR Code, Mã giảm giá, Phí vận chuyển |
| **Đăng Nguyên** | Backend | CRUD Sản phẩm, Nhà cung cấp, Kho vận, Nhập/Xuất hàng |

---

## 📊 Quy Trình Phát Triển

- **Version Control:** Git + GitHub · Gitflow branching model
- **Branching:** `main` (stable) ← `master` (integration) ← `feature/*` branches
- **Code Review:** Pull Request trước khi merge vào master
- **Testing:** PHPUnit tự động trước mỗi lần merge
- **Documentation:** Comment tiếng Việt chi tiết trong toàn bộ codebase

---

## 📄 Giấy Phép

Dự án được phát triển cho mục đích học tập và nghiên cứu.

---

<p align="center">
  <i>Được xây dựng với ❤️ bởi nhóm DienMayPro — 2026</i>
</p>