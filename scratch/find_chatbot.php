<?php
$content = file_get_contents('BaoCao_DacTa_ChiTiet_ChucNang.md');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (stripos($line, 'chatbot') !== false) {
        echo "Line " . ($i + 1) . ": $line\n";
    }
}
