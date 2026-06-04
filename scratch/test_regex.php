<?php
$content = file_get_contents('d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md');
preg_match_all('/(```mermaid\s*(usecaseDiagram.*?)\s*```)/is', $content, $matches);
echo "Total matches: " . count($matches[0]) . "\n";
if (count($matches[0]) > 0) {
    echo "First match:\n" . $matches[0][0] . "\n";
}
