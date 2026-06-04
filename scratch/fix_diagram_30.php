<?php
$f1 = 'd:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md';
$f2 = 'd:/HOC/Hoc4/pywword/baocaotong.md';

function fix_file($file) {
    if (!file_exists($file)) {
        echo "File $file does not exist.\n";
        return;
    }
    $content = file_get_contents($file);
    if (strpos($content, '-- >') !== false) {
        $content = str_replace('-- >', '-->', $content);
        file_put_contents($file, $content);
        echo "Fixed -- > to --> in $file.\n";
    } else {
        echo "No -- > found in $file.\n";
    }
}

fix_file($f1);
fix_file($f2);
