<?php
$content = file_get_contents('d:/repogist/ThuongMaiDienTu/baocaotong.md');
preg_match_all('/```mermaid\s*(usecaseDiagram.*?)\s*```/s', $content, $matches);
echo "Matches found: " . count($matches[0]) . "\n\n";
for ($i = 0; $i < min(5, count($matches[0])); $i++) {
    echo "--- MATCH " . ($i + 1) . " ---\n";
    echo $matches[0][$i] . "\n\n";
}
