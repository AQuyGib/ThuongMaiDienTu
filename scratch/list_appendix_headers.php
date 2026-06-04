<?php
$content = file_get_contents('BaoCao_DacTa_ChiTiet_ChucNang.md');
preg_match_all('/^## 7\..*$/m', $content, $matches);
foreach ($matches[0] as $h) {
    echo $h . "\n";
}
