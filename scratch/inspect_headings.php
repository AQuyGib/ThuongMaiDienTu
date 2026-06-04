<?php
$content = file_get_contents('d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md');
// Print lines from 7.1.1 to 7.1.6
$lines = explode("\n", $content);
for ($i = 0; $i < 100; $i++) {
    echo $i + 1 . ": " . $lines[$i] . "\n";
}
