<?php

// Tự động tạo thư mục và các file kiểm thử nếu chưa tồn tại
$baseDir = dirname(__DIR__, 2) . '/tai_lieu_kiem_thu';
if (!file_exists($baseDir . '/images/avatar_test.png')) {
    @mkdir($baseDir, 0777, true);
    @mkdir($baseDir . '/images', 0777, true);
    @mkdir($baseDir . '/videos', 0777, true);
    @mkdir($baseDir . '/audio', 0777, true);
    @mkdir($baseDir . '/documents', 0777, true);
    @mkdir($baseDir . '/spreadsheets', 0777, true);
    @mkdir($baseDir . '/archives', 0777, true);
    @mkdir($baseDir . '/web_data', 0777, true);

    $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
    $jpgData = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
    $webpData = base64_decode('UklGRkoAAABXRUJQVlA4WAoAAAAQAAAAAAAAAAAAQUxQSAwAAAARBxAR/Q9ERP8DAABWUDggGAAAABQBAJ0BKgEAAQAAAPgAgYwE5ALiAAD1AfgA');
    $gifData = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

    @file_put_contents($baseDir . '/images/avatar_test.png', $pngData);
    @file_put_contents($baseDir . '/images/thumbnail_test.jpg', $jpgData);
    @file_put_contents($baseDir . '/images/theme_test.webp', $webpData);
    @file_put_contents($baseDir . '/images/animation_test.gif', $gifData);

    $avatarSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">'
               . '<rect width="100%" height="100%" fill="#3b82f6" rx="20"/>'
               . '<circle cx="100" cy="90" r="40" fill="#ffffff"/>'
               . '<path d="M 60,160 A 40,40 0 0,1 140,160 Z" fill="#ffffff"/>'
               . '<text x="100" y="190" font-size="14" font-family="sans-serif" fill="#ffffff" text-anchor="middle" font-weight="bold">AVATAR TEST</text>'
               . '</svg>';
    @file_put_contents($baseDir . '/images/avatar_test.svg', $avatarSvg);

    $themeSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="400" viewBox="0 0 800 400">'
              . '<defs><linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">'
              . '<stop offset="0%" style="stop-color:#1e3a8a;stop-opacity:1" />'
              . '<stop offset="100%" style="stop-color:#3b82f6;stop-opacity:1" />'
              . '</linearGradient></defs>'
              . '<rect width="100%" height="100%" fill="url(#grad)"/>'
              . '<text x="400" y="180" font-size="48" font-family="sans-serif" fill="#ffffff" text-anchor="middle" font-weight="bold" letter-spacing="2">DIEN MAY PRO</text>'
              . '<text x="400" y="240" font-size="20" font-family="sans-serif" fill="#93c5fd" text-anchor="middle" letter-spacing="4">THEME BANNER TEST</text>'
              . '</svg>';
    @file_put_contents($baseDir . '/images/theme_test.svg', $themeSvg);

    $mp4Hex = '00000018667479706d7034320000000069736f6d6d7034320000000866726565000002ac6d646174';
    $mp4Data = hex2bin($mp4Hex);
    @file_put_contents($baseDir . '/videos/video_review.mp4', $mp4Data);
    @file_put_contents($baseDir . '/videos/video_mkv.mkv', $mp4Data);

    $mp3Hex = '49443303000000000000';
    $wavHex = '524946462408000057415645666d74201000000001000100401f0000401f0000010008006461746100080000';
    $oggHex = '4f676753000200000000000000000000000000000000';
    @file_put_contents($baseDir . '/audio/audio_test.mp3', hex2bin($mp3Hex));
    @file_put_contents($baseDir . '/audio/audio_test.wav', hex2bin($wavHex));
    @file_put_contents($baseDir . '/audio/audio_test.ogg', hex2bin($oggHex));

    $pdfData = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000052 00000 n\n0000000096 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n149\n%%EOF";
    @file_put_contents($baseDir . '/documents/hoadon_test.pdf', $pdfData);

    $docxHex = '504b03040a000000000055745432000000000000000000000000080000005b436f6e74656e745f54797065735d2e786d6c504b01020a000a00000000005574543200000000000000000000000008000000000000000000000020000000000000005b436f6e74656e745f54797065735d2e786d6c504b0506000000000100010036000000280000000000';
    @file_put_contents($baseDir . '/documents/tailieu_test.docx', hex2bin($docxHex));
    @file_put_contents($baseDir . '/documents/presentation_test.pptx', hex2bin($docxHex));

    $txtContent = "=== THƯ MỤC TÀI LIỆU KIỂM THỬ (TEST RESOURCES) ===\n\n"
                . "Thư mục này chứa đầy đủ các loại tệp tin để bạn trình chiếu và kiểm thử các tính năng của trang web trước hội đồng kiểm tra:\n\n"
                . "1. Thư mục images/: Chứa ảnh PNG, JPG, WebP, GIF, SVG thực tế dùng để test upload hình ảnh.\n"
                . "2. Thư mục videos/: Chứa tệp MP4, MKV để test video review.\n"
                . "3. Thư mục audio/: Chứa tệp MP3, WAV, OGG để test phát nhạc, âm thanh.\n"
                . "4. Thư mục documents/: Chứa tệp PDF, DOCX, PPTX để test đính kèm tài liệu văn bản.\n"
                . "5. Thư mục spreadsheets/: Chứa tệp CSV và XLSX để test Import sản phẩm.\n"
                . "6. Thư mục archives/: Chứa tệp ZIP, RAR, 7Z để test nén dữ liệu.\n"
                . "7. Thư mục web_data/: Chứa tệp JSON, XML, HTML để test cấu trúc dữ liệu web.\n";
    @file_put_contents($baseDir . '/documents/huongdan_test.txt', $txtContent);

    $csvContent = "sku,name,brand,category,base_price,old_price,status,seo_description,description,thumbnail\n"
                . "IPHONE15PM,Điện thoại iPhone 15 Pro Max 256GB,Apple,Điện thoại,29990000,34990000,1,Siêu phẩm iPhone 15 Pro Max titan tự nhiên,Mô tả chi tiết sản phẩm iPhone 15 Pro Max chính hãng VN/A,uploads/products/iphone15.jpg\n"
                . "SAMS24ULTRA,Điện thoại Samsung Galaxy S24 Ultra 512GB,Samsung,Điện thoại,27990000,31990000,1,Samsung Galaxy S24 Ultra tích hợp Galaxy AI,Mô tả chi tiết sản phẩm Galaxy S24 Ultra chip Snapdragon 8 Gen 3,uploads/products/s24u.jpg\n"
                . "MACBOOKM3,Laptop Apple Macbook Air M3 13 inch,Apple,Laptop,27990000,,1,Macbook Air chip M3 siêu mạnh mẽ,,uploads/products/macm3.jpg\n";
    @file_put_contents($baseDir . '/spreadsheets/products_import_template.csv', $csvContent);
    @file_put_contents($baseDir . '/spreadsheets/products_import_excel.xlsx', hex2bin($docxHex));

    $zipHex = '504b0506000000000000000000000000000000000000';
    $rarHex = '526172211a0700';
    $sevenZHex = '377abcaf271c0003';
    @file_put_contents($baseDir . '/archives/archive_test.zip', hex2bin($zipHex));
    @file_put_contents($baseDir . '/archives/archive_test.rar', hex2bin($rarHex));
    @file_put_contents($baseDir . '/archives/archive_test.7z', hex2bin($sevenZHex));

    $jsonContent = '{"status": "test", "name": "Dien May Pro", "version": "1.0"}';
    $xmlContent = '<?xml version="1.0" encoding="UTF-8"?><root><name>Dien May Pro</name><purpose>Testing</purpose></root>';
    $htmlContent = '<!DOCTYPE html><html><head><title>Test</title></head><body><h1>Dien May Pro Test</h1></body></html>';
    @file_put_contents($baseDir . '/web_data/data_test.json', $jsonContent);
    @file_put_contents($baseDir . '/web_data/data_test.xml', $xmlContent);
    @file_put_contents($baseDir . '/web_data/page_test.html', $htmlContent);
}

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
