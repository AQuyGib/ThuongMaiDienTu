<?php
$root = __DIR__ . '/..';
require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$accessoryIds = [4, 5, 6, 18, 19, 20, 21, 22];
$count = Product::whereIn('category_id', $accessoryIds)->count();
echo "Total accessories in DB: " . $count . "\n";

if ($count > 0) {
    $accessories = Product::whereIn('category_id', $accessoryIds)->get(['product_id', 'name', 'category_id']);
    foreach ($accessories as $a) {
        echo "  [{$a->product_id}] {$a->name} (cat: {$a->category_id})\n";
    }
} else {
    echo "NO ACCESSORIES FOUND! I should probably seed some or check category data.\n";
}
