<?php
$f1 = 'd:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md';
$f2 = 'd:/HOC/Hoc4/pywword/baocaotong.md';

function show_samples($file, $name) {
    $content = file_get_contents($file);
    preg_match_all('/```mermaid\s*(flowchart.*?)\s*```/is', $content, $matches);
    echo "=== $name (total: " . count($matches[0]) . ") ===\n";
    for ($i = 0; $i < min(3, count($matches[0])); $i++) {
        echo "Match " . ($i+1) . ":\n";
        echo $matches[0][$i] . "\n\n";
    }
}

show_samples($f1, "ThuongMaiDienTu Appendix");
show_samples($f2, "pywword baocaotong");
