<?php
$c = file_get_contents('baocaotong.md');
$old = "6. [CHƯƠNG 6: TÀI LIỆU THAM KHẢO](#chuong-6-tai-lieu-tham-khao)\r\n\r\n---";
$new = "6. [CHƯƠNG 6: TÀI LIỆU THAM KHẢO](#chuong-6-tai-lieu-tham-khao)\r\n7. [PHỤ LỤC: ĐẶC TẢ CHI TIẾT 51 CHỨC NĂNG (SRS APPENDIX)](#phu-luc-dac-ta-chi-tiet-chuc-nang-srs-appendix)\r\n\r\n---";
$c = str_replace($old, $new, $c);
file_put_contents('baocaotong.md', $c);
echo "Done. Size: " . filesize('baocaotong.md') . " bytes\n";
