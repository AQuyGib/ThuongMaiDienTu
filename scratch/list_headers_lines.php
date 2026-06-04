<?php
$content = file_get_contents('BaoCao_DacTa_ChiTiet_ChucNang.md');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (preg_match('/^## 7\./', $line)) {
        echo "Line " . ($i + 1) . ": $line\n";
    }
}
