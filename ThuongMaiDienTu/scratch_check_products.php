<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $count = \App\Models\Product::count();
    echo "Total products in database: " . $count . PHP_EOL;
    if ($count > 0) {
        echo "Sample products:" . PHP_EOL;
        foreach (\App\Models\Product::limit(5)->get() as $p) {
            echo "- ID: {$p->product_id} | Name: {$p->name} | Price: " . number_format($p->base_price, 0, ',', '.') . "đ" . PHP_EOL;
        }
    } else {
        echo "Warning: No products found in database!" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
