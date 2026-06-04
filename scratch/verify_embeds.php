<?php
$file = 'd:/HOC/Hoc4/pywword/baocaotong.md';
$content = file_get_contents($file);

$usecases = preg_match_all('/images\/use_case_\d+\.png/is', $content, $m1);
$activities = preg_match_all('/images\/activity_\d+\.png/is', $content, $m2);
$ui_layouts = preg_match_all('/images\/ui_layout_\d+\.png/is', $content, $m3);

echo "Total usecase image references in pywword/baocaotong.md: " . $usecases . "\n";
echo "Total activity image references in pywword/baocaotong.md: " . $activities . "\n";
echo "Total UI layout image references in pywword/baocaotong.md: " . $ui_layouts . "\n";
