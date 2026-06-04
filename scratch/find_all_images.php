<?php
function find_images($dir) {
    $result = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = strtolower(pathinfo($file->getPathname(), PATHINFO_EXTENSION));
            if ($ext === 'png' || $ext === 'jpg' || $ext === 'jpeg') {
                // Ignore vendor, node_modules, .git, images
                $path = $file->getPathname();
                if (strpos($path, 'vendor') === false && 
                    strpos($path, 'node_modules') === false && 
                    strpos($path, '.git') === false && 
                    strpos($path, 'images') === false) {
                    $result[] = [
                        'path' => $path,
                        'size' => $file->getSize()
                    ];
                }
            }
        }
    }
    return $result;
}

$imgs = find_images('d:/repogist/ThuongMaiDienTu');
$imgs2 = find_images('d:/HOC/Hoc4/pywword');

echo "=== ThuongMaiDienTu ===\n";
foreach ($imgs as $img) {
    echo $img['path'] . " (" . $img['size'] . " bytes)\n";
}

echo "=== pywword ===\n";
foreach ($imgs2 as $img) {
    echo $img['path'] . " (" . $img['size'] . " bytes)\n";
}
