<?php
$project_dir = 'd:/repogist/ThuongMaiDienTu';
$main_report_path = $project_dir . '/BaoCao_ChiTiet_DuAn.md';
$appendix_path = $project_dir . '/BaoCao_DacTa_ChiTiet_ChucNang.md';
$target_path = $project_dir . '/baocaotong.md';

if (!file_exists($main_report_path)) {
    die("Main report not found at $main_report_path\n");
}
if (!file_exists($appendix_path)) {
    die("Appendix report not found at $appendix_path\n");
}

$main_content = file_get_contents($main_report_path);
$appendix_content = file_get_contents($appendix_path);

// Extract all headers from the appendix that look like "## 7.X. TITLE"
preg_match_all('/^## (7\.\d+)\.\s+(.*)$/m', $appendix_content, $matches, PREG_SET_ORDER);

$vietnamese_map = [
    'á'=>'a','à'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a','ă'=>'a','ắ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a','â'=>'a','ấ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
    'é'=>'e','è'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e','ê'=>'e','ế'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
    'í'=>'i','ì'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
    'ó'=>'o','ò'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o','ô'=>'o','ố'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o','ơ'=>'o','ớ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
    'ú'=>'u','ù'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u','ư'=>'u','ứ'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
    'ý'=>'y','ỳ'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y',
    'đ'=>'d',
    'Á'=>'a','À'=>'a','Ả'=>'a','Ã'=>'a','Ạ'=>'a','Ă'=>'a','Ắ'=>'a','Ằ'=>'a','Ẳ'=>'a','Ẵ'=>'a','Ặ'=>'a','Â'=>'a','Ấ'=>'a','Ầ'=>'a','Ẩ'=>'a','Ẫ'=>'a','Ậ'=>'a',
    'É'=>'e','È'=>'e','Ẻ'=>'e','Ẽ'=>'e','Ẹ'=>'e','Ê'=>'e','Ế'=>'e','Ề'=>'e','Ể'=>'e','Ễ'=>'e','Ệ'=>'e',
    'Í'=>'i','Ì'=>'i','Ỉ'=>'i','Ĩ'=>'i','Ị'=>'i',
    'Ó'=>'o','Ò'=>'o','Ỏ'=>'o','Õ'=>'o','Ọ'=>'o','Ô'=>'o','Ố'=>'o','Ồ'=>'o','Ổ'=>'o','Ỗ'=>'o','Ộ'=>'o','Ơ'=>'o','Ớ'=>'o','Ờ'=>'o','Ở'=>'o','Ỡ'=>'o','Ợ'=>'o',
    'Ú'=>'u','Ù'=>'u','Ủ'=>'u','Ũ'=>'u','Ụ'=>'u','Ư'=>'u','Ứ'=>'u','Ừ'=>'u','Ử'=>'u','Ữ'=>'u','Ự'=>'u',
    'Ý'=>'y','Ỳ'=>'y','Ỷ'=>'y','Ỹ'=>'y','Ỵ'=>'y',
    'Đ'=>'d',
    '/'=>' ', '('=>' ', ')'=>' ', ':'=>' ', ','=>' ', '.'=>' ', '&'=>' '
];

$toc_items = [];
foreach ($matches as $match) {
    $num = $match[1];
    $title = trim($match[2]);
    
    // Generate slug using the map
    $raw_anchor = $match[0];
    $raw_anchor = ltrim($raw_anchor, '#');
    $raw_anchor = trim($raw_anchor);
    
    $slug = strtr($raw_anchor, $vietnamese_map);
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    $toc_items[] = "   - $num. [$title](#$slug)";
}

echo "Found " . count($toc_items) . " functions in Appendix.\n";

// Now, locate the Table of Contents in the main report and replace it
// Let's locate ## MỤC LỤC
$toc_start = strpos($main_content, "## MỤC LỤC");
if ($toc_start === false) {
    die("Error: Could not find ## MỤC LỤC in main report.\n");
}

// Find the divider "---" after ## MỤC LỤC
$toc_end = strpos($main_content, "---", $toc_start);
if ($toc_end === false) {
    die("Error: Could not find end divider for TOC in main report.\n");
}

// Construct the new Table of Contents
$new_toc = "## MỤC LỤC\n";
$new_toc .= "1. [CHƯƠNG 1: KẾ HOẠCH LÀM VIỆC NHÓM](#chuong-1-ke-hoach-lam-viec-nhom)\n";
$new_toc .= "   - 1.1. Bảng phân chia công việc tổng quan\n";
$new_toc .= "   - 1.2. Bảng phân chia chức năng và file mã nguồn chi tiết theo thành viên\n";
$new_toc .= "2. [CHƯƠNG 2: LÝ DO CHỌN ĐỀ TÀI VÀ MÔ TẢ NGHIỆP VỤ](#chuong-2-ly-do-chon-de-tai-va-mo-ta-nghiep-vu)\n";
$new_toc .= "   - 2.1. Lý do chọn đề tài (Kinh tế tuần hoàn, Right to Repair, Mini-ERP)\n";
$new_toc .= "   - 2.2. Danh mục mô tả chi tiết nghiệp vụ 9 phân hệ (51 chức năng)\n";
$new_toc .= "3. [CHƯƠNG 3: CƠ SỞ DỮ LIỆU VẬT LÝ & MÔ HÌNH ERD](#chuong-3-co-so-du-lieu-vat-ly--mo-hinh-erd)\n";
$new_toc .= "   - 3.1. Đặc tả chi tiết 61 bảng cơ sở dữ liệu trong phpMyAdmin\n";
$new_toc .= "   - 3.2. Sơ đồ quan hệ thực thể (ERD) bằng Mermaid\n";
$new_toc .= "4. [CHƯƠNG 4: THIẾT KẾ GIAO DIỆN & ĐẶC TẢ CHI TIẾT UI/UX (SRS FORMAT)](#chuong-4-thiet-ke-giao-dien--dac-ta-chi-tiet-uiux-srs-format)\n";
$new_toc .= "   - 4.1. Giao diện Đăng nhập / Đăng ký & Xác thực 2 lớp (2FA)\n";
$new_toc .= "   - 4.2. Giao diện Giỏ hàng & Thanh toán QR Động (PayOS)\n";
$new_toc .= "   - 4.3. Giao diện Cổng thông tin khách hàng, Tra cứu bảo hành & Đặt lịch sửa chữa\n";
$new_toc .= "   - 4.4. Giao diện Vòng quay may mắn & Đổi điểm thưởng (Loyalty)\n";
$new_toc .= "   - 4.5. Giao diện Trợ lý ảo AI Chatbot tư vấn RAG\n";
$new_toc .= "   - 4.6. Giao diện Bộ lọc nâng cao & So sánh sản phẩm\n";
$new_toc .= "   - 4.7. Giao diện Admin: Quét duyệt đơn hàng bằng AI & Nhập kho IMEI\n";
$new_toc .= "5. [CHƯƠNG 5: DANH MỤC MÃ LỖI & THÔNG BÁO HỆ THỐNG (MESSAGE LIST)](#chuong-5-danh-muc-ma-loi--thong-bao-he-thong-message-list)\n";
$new_toc .= "6. [CHƯƠNG 6: TÀI LIỆU THAM KHẢO](#chuong-6-tai-lieu-tham-khao)\n";
$new_toc .= "7. [PHỤ LỤC: ĐẶC TẢ CHI TIẾT 51 CHỨC NĂNG (SRS APPENDIX)](#phu-luc-dac-ta-chi-tiet-chuc-nang-srs-appendix)\n";
$new_toc .= implode("\n", $toc_items) . "\n";

// Replace the TOC in main report content
$updated_main_content = substr_replace($main_content, $new_toc, $toc_start, $toc_end - $toc_start);

// Also write this updated main report back to its original file so both remain synchronized
file_put_contents($main_report_path, $updated_main_content);
echo "Successfully updated TOC in main report $main_report_path\n";

// Combine updated main report and appendix into the target file
$combined_content = $updated_main_content . "\n\n---\n\n" . $appendix_content;
file_put_contents($target_path, $combined_content);

echo "Successfully merged files into $target_path. Total size: " . strlen($combined_content) . " bytes.\n";
