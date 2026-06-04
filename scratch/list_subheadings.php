<?php
$content = file_get_contents('d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md');
preg_match_all('/### 7\.\d+\.\d+\.[^\n]+/i', $content, $matches);
foreach ($matches[0] as $m) {
    echo $m . "\n";
}
