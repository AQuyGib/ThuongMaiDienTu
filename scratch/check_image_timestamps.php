<?php
$dir = 'd:/repogist/ThuongMaiDienTu/images';
$files = glob("$dir/ui_layout_*.png");
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

echo "Total ui_layout files: " . count($files) . "\n";
echo "Top 5 most recently modified:\n";
for ($i = 0; $i < min(5, count($files)); $i++) {
    echo basename($files[$i]) . " - " . date("Y-m-d H:i:s", filemtime($files[$i])) . "\n";
}
