<?php
$src_md = 'd:/repogist/ThuongMaiDienTu/baocaotong.md';
$dest_md = 'd:/HOC/Hoc4/pywword/baocaotong.md';

if (copy($src_md, $dest_md)) {
    echo "Successfully synced baocaotong.md to pywword workspace.\n";
} else {
    echo "Failed to copy baocaotong.md.\n";
}
