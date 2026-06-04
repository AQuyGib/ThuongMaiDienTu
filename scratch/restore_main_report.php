<?php
$project_dir = 'd:/repogist/ThuongMaiDienTu';
$main_report_path = $project_dir . '/BaoCao_ChiTiet_DuAn.md';

if (!file_exists($main_report_path)) {
    die("Main report not found.\n");
}

$content = file_get_contents($main_report_path);

// Restore Table of Contents
$new_toc = "6. [CHƯƠNG 6: BÁO CÁO ĐÓNG GÓP CHỨC NĂNG VÀ PHÂN TÍCH KỸ THUẬT CHUYÊN SÂU - SINH VIÊN NGUYỄN ANH QUÝ](#chuong-6-bao-cao-dong-gop-chuc-nang-va-phan-tich-ky-thuat-chuyen-sau-sinh-vien-nguyen-anh-quy)
   - 6.1. Tổng quan vai trò & phân chia công việc
   - 6.2. Phân hệ Trí tuệ nhân tạo (AI Integration Suite)
   - 6.3. Phân hệ Trải nghiệm khách hàng & Loyalty (Retention)
   - 6.4. Phân hệ CRM, CMS & Tương tác cộng đồng (Community)
   - 6.5. Quản trị giao diện & Hạ tầng hệ thống (Theme & Infrastructure)
   - 6.6. Kết luận & Đánh giá hiệu quả kỹ thuật
7. [CHƯƠNG 7: TÀI LIỆU THAM KHẢO](#chuong-7-tai-lieu-tham-khao)";
$old_toc = "6. [CHƯƠNG 6: TÀI LIỆU THAM KHẢO](#chuong-6-tai-lieu-tham-khao)";

$content = str_replace($new_toc, $old_toc, $content);

// Restore Body
// Find where "## CHƯƠNG 6: BÁO CÁO ĐÓNG GÓP..." starts
$pos = strpos($content, "## CHƯƠNG 6: BÁO CÁO ĐÓNG GÓP CHỨC NĂNG VÀ PHÂN TÍCH KỸ THUẬT CHUYÊN SÂU");
if ($pos !== false) {
    // Find where "## CHƯƠNG 7: TÀI LIỆU THAM KHẢO" starts
    $pos_ch7 = strpos($content, "## CHƯƠNG 7: TÀI LIỆU THAM KHẢO");
    if ($pos_ch7 !== false) {
        $body_before = substr($content, 0, $pos);
        $body_after = substr($content, $pos_ch7 + strlen("## CHƯƠNG 7: TÀI LIỆU THAM KHẢO"));
        $content = $body_before . "## CHƯƠNG 6: TÀI LIỆU THAM KHẢO" . $body_after;
        echo "Successfully reverted body text.\n";
    } else {
        echo "Could not find Chapter 7 header.\n";
    }
} else {
    echo "Could not find Chapter 6 contribution header.\n";
}

file_put_contents($main_report_path, $content);
echo "Revert complete.\n";
