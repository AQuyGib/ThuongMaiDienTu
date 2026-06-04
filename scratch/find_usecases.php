<?php
$content = file_get_contents('d:/repogist/ThuongMaiDienTu/baocaotong.md');
$count = substr_count($content, 'usecaseDiagram');
echo "Total occurrences of 'usecaseDiagram': " . $count . "\n";
// Find positions
$offset = 0;
$i = 1;
while (($pos = strpos($content, 'usecaseDiagram', $offset)) !== false) {
    echo "Match $i at position $pos\n";
    $offset = $pos + 1;
    $i++;
}
