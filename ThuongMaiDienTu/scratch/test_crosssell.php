<?php
$root = __DIR__ . '/..';
require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Services\CrossSellService;

// Test with iPhone 15 Pro Max (likely ID 1 or near)
$p = Product::where('name', 'like', '%iPhone%')->first();
if (!$p) $p = Product::first();

echo "Testing Cross-Sell for: [{$p->product_id}] {$p->name}\n";

$svc = new CrossSellService();
$results = $svc->getFullCrossSellList($p, 8);

echo "Cross-Sell results (" . $results->count() . "):\n";
foreach ($results as $item) {
    echo "  - [{$item->product_id}] {$item->name} (Category: {$item->category_id})\n";
}
