<?php
$content = file_get_contents('BaoCao_DacTa_ChiTiet_ChucNang.md');
if (preg_match_all('/flash/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    echo "Found " . count($matches[0]) . " matches:\n";
    foreach ($matches[0] as $m) {
        $line = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        echo "Line $line: " . substr($content, $m[1] - 20, 50) . "\n";
    }
} else {
    echo "No matches found for 'flash'.\n";
}
