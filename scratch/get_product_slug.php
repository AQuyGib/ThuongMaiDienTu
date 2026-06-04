<?php
require 'd:/repogist/ThuongMaiDienTu/thuongmaidientu/vendor/autoload.php';
$app = require_once 'd:/repogist/ThuongMaiDienTu/thuongmaidientu/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = \App\Models\Product::first();
if ($product) {
    echo "product_id: " . $product->product_id . "\n";
    echo "slug: " . $product->slug . "\n";
} else {
    echo "No product found\n";
}
