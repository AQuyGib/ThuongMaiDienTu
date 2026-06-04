<?php
$files = ['BaoCao_ChiTiet_DuAn.md', 'baocaotong.md', 'BaoCao_DacTa_ChiTiet_ChucNang.md'];
foreach ($files as $file) {
    echo "=== $file ===\n";
    if (!file_exists($file)) {
        echo "File not found!\n\n";
        continue;
    }
    $content = file_get_contents($file);
    // Find all headings of level 1, 2, 3 starting with CHƯƠNG or PHỤ LỤC or similar
    preg_match_all('/^(#+)\s+(.*)$/m', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $level = strlen($match[1]);
        $text = trim($match[2]);
        if (preg_match('/(CHƯƠNG|PHỤ LỤC|MỤC LỤC)/ui', $text) || ($level == 2 && preg_match('/^\d+\./', $text))) {
            echo str_repeat("  ", $level) . "$text\n";
        }
    }
    echo "\n";
}
