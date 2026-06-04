<?php
$src_dir = 'C:/Users/ANH QUY/.gemini/antigravity/brain/43dbbf22-b150-4cdd-a986-72848dbfc915';
$dest_dir = 'd:/repogist/ThuongMaiDienTu/images';

if (!is_dir($dest_dir)) {
    mkdir($dest_dir, 0777, true);
}

$mappings = [
    'login_2fa_ui' => 'login_2fa_ui.png',
    'cart_payment_ui' => 'cart_payment_ui.png',
    'repair_portal_ui' => 'repair_portal_ui.png',
    'lucky_wheel_ui' => 'lucky_wheel_ui.png',
    'ai_chatbot_ui' => 'ai_chatbot_ui.png',
    'admin_dashboard_ui' => 'admin_dashboard_ui.png'
];

$files = scandir($src_dir);
foreach ($mappings as $prefix => $newName) {
    foreach ($files as $file) {
        if (strpos($file, $prefix) === 0 && pathinfo($file, PATHINFO_EXTENSION) === 'png') {
            $src_path = $src_dir . '/' . $file;
            $dest_path = $dest_dir . '/' . $newName;
            if (copy($src_path, $dest_path)) {
                echo "Copied $file -> $newName successfully.\n";
            } else {
                echo "Failed to copy $file -> $newName.\n";
            }
            break; // found it, move to next mapping
        }
    }
}
