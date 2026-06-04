<?php
$diff = shell_exec('git diff d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md');
if (preg_match_all('/[+-].*?use_case_\d+/i', $diff, $matches)) {
    echo "Found use_case_ in diff:\n";
    foreach ($matches[0] as $match) {
        echo $match . "\n";
    }
} else {
    echo "No use_case_ found in git diff.\n";
}
