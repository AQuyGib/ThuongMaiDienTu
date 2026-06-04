<?php
$file = 'd:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md';
$content = file_get_contents($file);

$ui_layouts = preg_match_all('/images\/ui_layout_\d+\.png/is', $content, $m);
echo "Total UI layout image references in BaoCao_DacTa_ChiTiet_ChucNang.md: " . $ui_layouts . "\n";
