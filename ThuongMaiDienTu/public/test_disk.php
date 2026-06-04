<?php
header('Content-Type: text/plain');
echo "Free space on G: " . number_format(disk_free_space("G:") / (1024 * 1024), 2) . " MB\n";
echo "Free space on C: " . number_format(disk_free_space("C:") / (1024 * 1024), 2) . " MB\n";
echo "Temporary directory: " . sys_get_temp_dir() . "\n";
echo "Free space in temp dir: " . number_format(disk_free_space(sys_get_temp_dir()) / (1024 * 1024), 2) . " MB\n";
