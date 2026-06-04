<?php
$src_dir = 'd:/repogist/ThuongMaiDienTu/images';
$dest_dir = 'd:/HOC/Hoc4/pywword/images';

if (!is_dir($dest_dir)) {
    mkdir($dest_dir, 0777, true);
}

$files = scandir($src_dir);
$count = 0;
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $src_path = $src_dir . '/' . $file;
    $dest_path = $dest_dir . '/' . $file;
    if (is_file($src_path)) {
        if (copy($src_path, $dest_path)) {
            $count++;
        }
    }
}
echo "Successfully copied $count images from ThuongMaiDienTu/images to pywword/images.\n";
