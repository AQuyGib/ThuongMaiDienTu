<?php
$content = file_get_contents('d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md');
echo "Occurrences in ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md: " . substr_count(strtolower($content), 'flowchart') . "\n";

if (file_exists('d:/HOC/Hoc4/pywword/baocaotong.md')) {
    $content2 = file_get_contents('d:/HOC/Hoc4/pywword/baocaotong.md');
    echo "Occurrences in pywword/baocaotong.md: " . substr_count(strtolower($content2), 'flowchart') . "\n";
} else {
    echo "pywword/baocaotong.md does not exist\n";
}
