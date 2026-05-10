<?php
return [
    // Định nghĩa thứ tự ưu tiên hiển thị các thông số kỹ thuật trong so sánh.
    // Các key ở đây tương ứng với các trường trong JSON specifications.
    'priority' => [
        'cpu',
        'cpu_chip',
        'ram',
        'ram_capacity',
        'rom',
        'storage',
        'screen',
        'screen_size',
        'gpu',
        'camera',
        'battery',
        'os',
        'sim',
        'connection',
        // Thêm các thông số tùy chỉnh ở đây nếu cần.
    ],
];
