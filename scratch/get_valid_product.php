<?php
require 'd:/repogist/ThuongMaiDienTu/thuongmaidientu/vendor/autoload.php';
$app = require_once 'd:/repogist/ThuongMaiDienTu/thuongmaidientu/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
echo "User: " . ($user ? $user->email : "None") . "\n";

$product = \App\Models\Product::first();
if ($product) {
    echo "Product ID: " . $product->id . "\n";
    echo "Product Name: " . $product->name . "\n";
    // Check if slug or slug-like attributes exist
    echo "Product attributes: " . json_encode(array_keys($product->getAttributes())) . "\n";
} else {
    echo "Product: None\n";
}
