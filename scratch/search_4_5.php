<?php
$content = file_get_contents('BaoCao_ChiTiet_DuAn.md');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, '4.5.') !== false) {
        echo "Line " . ($i + 1) . ": $line\n";
        for ($j = max(0, $i - 2); $j <= min(count($lines) - 1, $i + 15); $j++) {
            echo "   [" . ($j + 1) . "]: " . $lines[$j] . "\n";
        }
        echo "\n";
    }
}
