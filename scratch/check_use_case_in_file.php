<?php
$file = 'd:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md';
$content = file_get_contents($file);
if (strpos($content, 'use_case') !== false) {
    echo "Found 'use_case' in file.\n";
    // Print all matches
    preg_match_all('/images\/[a-zA-Z0-9_\-\.]+/i', $content, $m);
    print_r($m[0]);
} else {
    echo "No 'use_case' found in file.\n";
}
