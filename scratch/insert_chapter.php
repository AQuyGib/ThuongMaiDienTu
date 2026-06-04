<?php
$project_dir = 'd:/repogist/ThuongMaiDienTu';
$main_report_path = $project_dir . '/BaoCao_ChiTiet_DuAn.md';
$cn_path = 'd:/HOC/Hoc4/pywword/cn.md';

if (!file_exists($main_report_path)) {
    die("Main report not found at $main_report_path\n");
}
if (!file_exists($cn_path)) {
    die("cn.md not found at $cn_path\n");
}

$main_content = file_get_contents($main_report_path);
$cn_content = file_get_contents($cn_path);

// Let's transform cn.md content to fit into Chapter 6:
// 1. Remove the main title and info at the top (lines 1 to 8)
$lines = explode("\n", $cn_content);
$cleaned_lines = [];
$skip_headers = true;
foreach ($lines as $line) {
    if ($skip_headers) {
        if (trim($line) === '---') {
            $skip_headers = false;
        }
        continue;
    }
    $cleaned_lines[] = $line;
}
$cn_body = implode("\n", $cleaned_lines);

// Apply transformations to headers
$transformations = [
    '/^## TỔNG QUAN VAI TRÒ & PHÂN CHIA CÔNG VIỆC/m' => '### 6.1. TỔNG QUAN VAI TRÒ & PHÂN CHIA CÔNG VIỆC',
    '/^## PHẦN I: PHÂN HỆ TRÍ TUỆ NHÂN TẠO \(AI INTEGRATION SUITE\)/m' => '### 6.2. PHẦN I: PHÂN HỆ TRÍ TUỆ NHÂN TẠO (AI INTEGRATION SUITE)',
    '/^### 1\. Chatbot AI Hỗ Trợ Khách Hàng Nâng Cao \(Gemini RAG & Booking\)/m' => '#### 6.2.1. Chatbot AI Hỗ Trợ Khách Hàng Nâng Cao (Gemini RAG & Booking)',
    '/^### 2\. Gợi Ý Bán Chéo Cá Nhân Hóa & Định Giá Combo Động \(AI Recommendation & Combo Pricing\)/m' => '#### 6.2.2. Gợi Ý Bán Chéo Cá Nhân Hóa & Định Giá Combo Động (AI Recommendation & Combo Pricing)',
    '/^### 3\. Phân Hệ Kiểm Duyệt UGC & Trợ Lý SEO Bằng AI \(Article UGC Moderation & SEO Assistant\)/m' => '#### 6.2.3. Phân Hệ Kiểm Duyệt UGC & Trợ Lý SEO Bằng AI (Article UGC Moderation & SEO Assistant)',
    '/^### 4\. AI Chẩn Đoán Lỗi Thiết Bị & Tự Động Phân Công Kỹ Thuật Viên \(AI Diagnosis & Smart Dispatching\)/m' => '#### 6.2.4. AI Chẩn Đoán Lỗi Thiết Bị & Tự Động Phân Công Kỹ Thuật Viên (AI Diagnosis & Smart Dispatching)',
    '/^### 5\. AI Tự Động Phân Tích & Duyệt Đơn Hàng \(AI Order Auto-Processing\)/m' => '#### 6.2.5. AI Tự Động Phân Tích & Duyệt Đơn Hàng (AI Order Auto-Processing)',
    
    '/^## PHẦN II: PHẦN HỆ TRẢI NGHIỆM KHÁCH HÀNG & LOYALTY \(RETENTION\)/m' => '### 6.3. PHẦN II: PHÂN HỆ TRẢI NGHIỆM KHÁCH HÀNG & LOYALTY (RETENTION)',
    '/^### 6\. Bộ Lọc Sản Phẩm Nâng Cao \(Faceted AJAX Filter\)/m' => '#### 6.3.1. Bộ Lọc Sản Phẩm Nâng Cao (Faceted AJAX Filter)',
    '/^### 7\. So Sánh Sản Phẩm Đa Thuộc Tính \(Product Comparison Matrix\)/m' => '#### 6.3.2. So Sánh Sản Phẩm Đa Thuộc Tính (Product Comparison Matrix)',
    '/^### 8\. Hệ Thống Tích Điểm & Vòng Quay May Mắn \(Loyalty Points & Lucky Wheel\)/m' => '#### 6.3.3. Hệ Thống Tích Điểm & Vòng Quay May Mắn (Loyalty Points & Lucky Wheel)',
    '/^### 9\. Phân Hệ Flash Sale & Đặt Hàng An Toàn \(Flash Sale & Anti-Overselling\)/m' => '#### 6.3.4. Phân Hệ Flash Sale & Đặt Hàng An Toàn (Flash Sale & Anti-Overselling)',
    
    '/^## PHẦN III: PHẦN CRM, CMS & TƯƠNG TÁC CỘNG ĐỒNG \(COMMUNITY\)/m' => '### 6.4. PHẦN III: PHÂN HỆ CRM, CMS & TƯƠNG TÁC CỘNG ĐỒNG (COMMUNITY)',
    '/^### 10\. Quản Lý Khách Hàng \(Customer CRUD\) & Hệ Thống Xử Phạt \(Banning System\)/m' => '#### 6.4.1. Quản Lý Khách Hàng (Customer CRUD) & Hệ Thống Xử Phạt (Banning System)',
    '/^### 11\. Quản Lý Bài Viết & Blog Công Nghệ \(CRUD Articles\)/m' => '#### 6.4.2. Quản Lý Bài Viết & Blog Công Nghệ (CRUD Articles)',
    '/^### 12\. Hệ Thống Thông Báo Đa Kênh & Laravel Queue \(Notification System\)/m' => '#### 6.4.3. Hệ Thống Thông Báo Đa Kênh & Laravel Queue (Notification System)',
    
    '/^## PHẦN IV: QUẢN TRỊ GIAO DIỆN & HẠ TẦNG HỆ THỐNG \(THEME & INFRASTRUCTURE\)/m' => '### 6.5. PHẦN IV: QUẢN TRỊ GIAO DIỆN & HẠ TẦNG HỆ THỐNG (THEME & INFRASTRUCTURE)',
    '/^### 13\. Tùy Biến Giao Diện Header\/Footer & Live Iframe Same-Origin DOM Sync/m' => '#### 6.5.1. Tùy Biến Giao Diện Header/Footer & Live Iframe Same-Origin DOM Sync',
    '/^### 14\. Smart Setup Wizard & CLI Orchestrator \(`start\.bat`\)/m' => '#### 6.5.2. Smart Setup Wizard & CLI Orchestrator (start.bat)',
    
    '/^## KẾT LUẬN & ĐÁNH GIÁ HIỆU QUẢ KỸ THUẬT/m' => '### 6.6. KẾT LUẬN & ĐÁNH GIÁ HIỆU QUẢ KỸ THUẬT'
];

foreach ($transformations as $pattern => $replacement) {
    $cn_body = preg_replace($pattern, $replacement, $cn_body);
}

// Construct the new Chapter 6 content block
$chapter6_content = "\n\n## CHƯƠNG 6: BÁO CÁO ĐÓNG GÓP CHỨC NĂNG VÀ PHÂN TÍCH KỸ THUẬT CHUYÊN SÂU - SINH VIÊN NGUYỄN ANH QUÝ\n";
$chapter6_content .= "Mã số sinh viên (MSSV): 24211TT3159\n";
$chapter6_content .= "Vai trò: Phát triển CRM, Trải nghiệm khách hàng (Customer Experience), Chiến dịch Marketing/Khuyến mãi (Flash Sale & Loyalty), và Phân hệ Tích hợp Trí tuệ Nhân tạo (AI Integration).\n";
$chapter6_content .= "Công nghệ sử dụng: Laravel 11/12, Vite, Tailwind CSS, MySQL, Google Gemini API, Pusher WebSockets.\n\n";
$chapter6_content .= $cn_body;

// Now, update Table of Contents in the main report content:
// Replace "6. [CHƯƠNG 6: TÀI LIỆU THAM KHẢO](#chuong-6-tai-lieu-tham-khao)"
// with the new Chapter 6 and Chapter 7 items.
$old_toc = "6. [CHƯƠNG 6: TÀI LIỆU THAM KHẢO](#chuong-6-tai-lieu-tham-khao)";
$new_toc = "6. [CHƯƠNG 6: BÁO CÁO ĐÓNG GÓP CHỨC NĂNG VÀ PHÂN TÍCH KỸ THUẬT CHUYÊN SÂU - SINH VIÊN NGUYỄN ANH QUÝ](#chuong-6-bao-cao-dong-gop-chuc-nang-va-phan-tich-ky-thuat-chuyen-sau-sinh-vien-nguyen-anh-quy)
   - 6.1. Tổng quan vai trò & phân chia công việc
   - 6.2. Phân hệ Trí tuệ nhân tạo (AI Integration Suite)
   - 6.3. Phân hệ Trải nghiệm khách hàng & Loyalty (Retention)
   - 6.4. Phân hệ CRM, CMS & Tương tác cộng đồng (Community)
   - 6.5. Quản trị giao diện & Hạ tầng hệ thống (Theme & Infrastructure)
   - 6.6. Kết luận & Đánh giá hiệu quả kỹ thuật
7. [CHƯƠNG 7: TÀI LIỆU THAM KHẢO](#chuong-7-tai-lieu-tham-khao)";

$main_content = str_replace($old_toc, $new_toc, $main_content);

// Replace "## CHƯƠNG 6: TÀI LIỆU THAM KHẢO" in the body
// with the new Chapter 6 content followed by Chapter 7 header.
$old_body_header = "## CHƯƠNG 6: TÀI LIỆU THAM KHẢO";
$new_body_block = $chapter6_content . "\n\n---\n\n## CHƯƠNG 7: TÀI LIỆU THAM KHẢO";

$main_content = str_replace($old_body_header, $new_body_block, $main_content);

// Write back to main report
file_put_contents($main_report_path, $main_content);
echo "Successfully integrated cn.md as Chapter 6 in $main_report_path.\n";
