<?php
$content = file_get_contents('BaoCao_DacTa_ChiTiet_ChucNang.md');
if (preg_match_all('/chatbot/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    echo "Found " . count($matches[0]) . " matches:\n";
    foreach ($matches[0] as $m) {
        $line = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        echo "Line $line\n";
    }
} else {
    echo "No matches found for 'chatbot'.\n";
}
