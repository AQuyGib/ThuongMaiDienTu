<?php
$files = ['BaoCao_ChiTiet_DuAn.md', 'BaoCao_DacTa_ChiTiet_ChucNang.md', 'baocaotong.md'];
$patterns = ['/8 phân hệ/i', '/45 chức năng/i', '/45 CN/i', '/20 bảng/i'];
foreach ($files as $file) {
    if (!file_exists($file)) continue;
    echo "=== $file ===\n";
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line)) {
                echo "Line " . ($i + 1) . ": $line\n";
            }
        }
    }
    echo "\n";
}
