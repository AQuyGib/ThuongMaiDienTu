<?php
$file = 'BaoCao_ChiTiet_DuAn.md';
if (!file_exists($file)) {
    die("File not found: $file\n");
}
$content = file_get_contents($file);

// Replace "8 phân hệ (45 chức năng)" with "9 phân hệ (51 chức năng)"
$content = str_replace(
    '2.2. Danh mục mô tả chi tiết nghiệp vụ 8 phân hệ (45 chức năng)',
    '2.2. Danh mục mô tả chi tiết nghiệp vụ 9 phân hệ (51 chức năng)',
    $content
);
$content = str_replace(
    '### 2.2. Danh mục mô tả chi tiết nghiệp vụ 8 phân hệ (45 chức năng)',
    '### 2.2. Danh mục mô tả chi tiết nghiệp vụ 9 phân hệ (51 chức năng)',
    $content
);

// Replace "20 bảng cơ sở dữ liệu" with "61 bảng cơ sở dữ liệu"
$content = str_replace(
    '3.1. Đặc tả chi tiết 20 bảng cơ sở dữ liệu',
    '3.1. Đặc tả chi tiết 61 bảng cơ sở dữ liệu trong phpMyAdmin',
    $content
);

file_put_contents($file, $content);
echo "Successfully updated $file\n";
