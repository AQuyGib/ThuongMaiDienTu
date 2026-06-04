<?php
$file = 'd:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md';
$content = file_get_contents($file);

$usecases = preg_match_all('/images\/use_case_\d+\.png/is', $content, $m1);
$activities = preg_match_all('/images\/activity_\d+\.png/is', $content, $m2);

echo "Total usecase image references in BaoCao_DacTa_ChiTiet_ChucNang.md: " . $usecases . "\n";
echo "Total activity image references in BaoCao_DacTa_ChiTiet_ChucNang.md: " . $activities . "\n";
