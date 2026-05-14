<?php
$filePath = 'g:/ThuongMaiDienTu/ThuongMaiDienTu/database/seeders/ProductSeeder.php';
$content = file_get_contents($filePath);

$colsToRemove = ['ram', 'rom', 'cpu', 'gpu', 'screen', 'os', 'camera', 'battery', 'sim', 'connection'];

foreach ($colsToRemove as $col) {
    // Regex to remove the line: 'col' => '...',
    $content = preg_replace("/\s+'" . $col . "'\s+=>\s+['\"].*?['\"],\n/", "", $content);
}

file_put_contents($filePath, $content);
echo "Fixed ProductSeeder.php";
