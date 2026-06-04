<?php
$f1 = 'd:/repogist/ThuongMaiDienTu/baocaotong.md';
$f2 = 'd:/HOC/Hoc4/pywword/baocaotong.md';

$c1 = file_get_contents($f1);
$c2 = file_get_contents($f2);

echo "Size of $f1: " . strlen($c1) . " bytes\n";
echo "Size of $f2: " . strlen($c2) . " bytes\n";

if ($c1 === $c2) {
    echo "Files are IDENTICAL.\n";
} else {
    echo "Files are DIFFERENT.\n";
    // Show first 100 chars of diff or check if there is a small difference
    $len = min(strlen($c1), strlen($c2));
    for ($i = 0; $i < $len; $i++) {
        if ($c1[$i] !== $c2[$i]) {
            echo "First difference at char $i:\n";
            echo "File 1: " . substr($c1, max(0, $i - 20), 40) . "\n";
            echo "File 2: " . substr($c2, max(0, $i - 20), 40) . "\n";
            break;
        }
    }
}
