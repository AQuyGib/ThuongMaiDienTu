<?php
$lines = explode("\n", file_get_contents('BaoCao_ChiTiet_DuAn.md'));
foreach ($lines as $i => $line) {
    if (strpos($line, '## CHƯƠNG 6') !== false || strpos($line, '## CHƯƠNG 7') !== false) {
        echo "Line " . ($i + 1) . ": $line\n";
        for ($j = max(0, $i - 5); $j <= min(count($lines) - 1, $i + 10); $j++) {
            echo "   [" . ($j + 1) . "]: " . $lines[$j] . "\n";
        }
        echo "\n";
    }
}
