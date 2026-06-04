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

$toc_items = [];
foreach ($matches as $match) {
    $num = $match[1];
    $title = trim($match[2]);
    // Create anchor link
    $anchor = strtolower($match[0]);
    $anchor = preg_replace('/[^a-z0-9\s\-]/', '', $anchor);
    $anchor = preg_replace('/[\s\-]+/', '-', $anchor);
    $anchor = trim($anchor, '-');
    
    // Custom anchor formatting based on the header text:
    // "## 7.1. ĐĂNG NHẬP / ĐĂNG KÝ ĐA KÊNH" -> "#71-dang-nhap-dang-ky-da-kenh"
    $raw_anchor = strtolower($match[0]);
    // remove "#" at the start
    $raw_anchor = ltrim($raw_anchor, '#');
    $raw_anchor = trim($raw_anchor);
    // Replace non-alphanumeric with space or strip depending on standard markdown
    // Vietnamese translation of slugify
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
        'Ú'=>'u','Ù'=>'u','Ủ'=>'u','Ũ'=>'u','Ụ'=>'u','Ư'=>'u','Ứ'=>'u','Ừ'=>'u','Ử'=>'u','Ự'=>'u',
        'Ý'=>'y','Ỳ'=>'y','Ỷ'=>'y','Ỹ'=>'y','Ỵ'=>'y',
        'Đ'=>'d',
        '/'=>' ', '('=>' ', ')'=>' ', ':'=>' ', ','=>' ', '.'=>' ', '&'=>' '
    ];
    $slug = strtr($raw_anchor, $vietnamese_map);
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    $toc_items[] = "    - [$num. $title](#$slug)";
}

// Prepare the Table of Contents replacement
// Let's locate the TOC in the main report and replace it.
// In the main report, the TOC section is under ## MỤC LỤC
// Let's print out the matches for debugging first.
echo "Found " . count($toc_items) . " functions in Appendix:\n";
foreach ($toc_items as $item) {
    echo $item . "\n";
}

// Let's reconstruct the Table of Contents in the main report.
// Look for the block starting with "7. [PHỤ LỤC: ĐẶC TẢ CHI TIẾT CHỨC NĂNG (SRS APPENDIX)](#phu-luc-dac-ta-chi-tiet-chuc-nang-srs-appendix)"
// and replace it with the new TOC list.
$toc_header = "8. [PHỤ LỤC: ĐẶC TẢ CHI TIẾT CHỨC NĂNG (SRS APPENDIX)](#phu-luc-dac-ta-chi-tiet-chuc-nang-srs-appendix)";
$new_toc_appendix = $toc_header . "\n" . implode("\n", $toc_items);

// Let's locate where "8. [PHỤ LỤC: ĐẶC TẢ CHI TIẾT CHỨC NĂNG (SRS APPENDIX)..." is in $main_content
$pattern = '/8\.\s+\[PHỤ LỤC:.*$/m';
if (preg_match($pattern, $main_content, $m, PREG_OFFSET_CAPTURE)) {
    $pos = $m[0][1];
    $remaining = substr($main_content, $pos);
    $end_pos = strpos($remaining, "---");
    if ($end_pos === false) {
        $end_pos = strpos($remaining, "##");
    }
    
    if ($end_pos !== false) {
        $main_content = substr_replace($main_content, $new_toc_appendix . "\n", $pos, $end_pos);
        echo "Table of Contents updated in main report content.\n";
    } else {
        echo "Could not find end of Table of Contents in main report.\n";
    }
} else {
    // If "8. [PHỤ LỤC..." is not found, let's insert it right after the Chapter 7 line in the TOC
    $ch7_pattern = '/7\.\s+\[CHƯƠNG 7: TÀI LIỆU THAM KHẢO\].*$/m';
    if (preg_match($ch7_pattern, $main_content, $m, PREG_OFFSET_CAPTURE)) {
        $pos = $m[0][1] + strlen($m[0][0]);
        $main_content = substr_replace($main_content, "\n" . $new_toc_appendix, $pos, 0);
        echo "Inserted Chapter 8 TOC after Chapter 7 TOC.\n";
    } else {
        echo "Could not find Chapter 7 TOC to insert Chapter 8 TOC.\n";
    }
}

// Combine the two files
// First, strip the top title and description of the appendix if needed, or keep it.
// The appendix starts with "# PHỤ LỤC: ĐẶC TẢ CHI TIẾT CHỨC NĂNG (SRS APPENDIX)"
// Let's strip the first header of the appendix since it's already in the TOC and we want a clean header hierarchy.
// Actually, keeping the `# PHỤ LỤC...` as the main divider is perfect because it starts Chapter 7.
// Let's make sure there is a page divider "---" between the main report and the appendix.
$combined_content = $main_content . "\n\n---\n\n" . $appendix_content;

file_put_contents($target_path, $combined_content);
echo "Successfully written combined report to $target_path. Total bytes: " . strlen($combined_content) . "\n";
