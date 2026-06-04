<?php
$content = file_get_contents('BaoCao_ChiTiet_DuAn.md');
preg_match_all('/.*Anh Quý.*/iu', $content, $matches, PREG_OFFSET_CAPTURE);
foreach ($matches[0] as $m) {
    $line_num = substr_count(substr($content, 0, $m[1]), "\n") + 1;
    echo "Line $line_num: " . $m[0] . "\n";
}
