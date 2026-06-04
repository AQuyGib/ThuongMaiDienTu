<?php
require 'd:/repogist/ThuongMaiDienTu/thuongmaidientu/vendor/autoload.php';
$app = require_once 'd:/repogist/ThuongMaiDienTu/thuongmaidientu/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$keywords = ['activity', 'log', 'role', 'permission', 'compare', 'wishlist', 'cart', 'checkout', 'pay', 'reward', 'spin', 'wheel', 'notification', 'article', 'import', 'export', 'supplier', 'product', 'category', 'attribute', 'flash', 'voucher', 'warehouse', 'transfer', 'audit', 'warning', 'movement', 'cashbook', 'invoice', 'ticket', 'repair', 'language', 'locale', 'customer', 'member', 'chatbot', 'search', 'videos'];

$routeCollection = Route::getRoutes();
$matched = [];
foreach ($routeCollection as $value) {
    $uri = $value->uri();
    $name = $value->getName();
    $methods = $value->methods();
    
    foreach ($keywords as $kw) {
        if (strpos(strtolower($uri), $kw) !== false || strpos(strtolower($name), $kw) !== false) {
            $matched[$kw][] = $methods[0] . " | " . $uri . " | " . $name;
        }
    }
}

foreach ($matched as $kw => $routes) {
    echo "=== Keyword: $kw ===\n";
    foreach (array_slice($routes, 0, 5) as $r) {
        echo "  $r\n";
    }
    if (count($routes) > 5) {
        echo "  ... and " . (count($routes) - 5) . " more\n";
    }
}
