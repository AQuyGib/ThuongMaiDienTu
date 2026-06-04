<?php
$src_dir = 'C:/Users/ANH QUY/.gemini/antigravity/brain/43dbbf22-b150-4cdd-a986-72848dbfc915';
$dest_dir = 'd:/repogist/ThuongMaiDienTu/images';

$files = scandir($src_dir);
foreach ($files as $file) {
    if (strpos($file, 'use_case_auth_diagram') === 0 && pathinfo($file, PATHINFO_EXTENSION) === 'png') {
        $src_path = $src_dir . '/' . $file;
        $dest_path = $dest_dir . '/use_case_auth_diagram.png';
        if (copy($src_path, $dest_path)) {
            echo "Copied $file -> use_case_auth_diagram.png successfully.\n";
        } else {
            echo "Failed to copy.\n";
        }
        break;
    }
}
