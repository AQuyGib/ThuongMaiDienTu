<?php

use App\Http\Controllers\CompareController;
use Illuminate\Http\Request;
use App\Models\Product;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Request::capture());

// Lấy 2 sản phẩm bất kỳ để test
$products = Product::limit(2)->get();
if ($products->isEmpty()) {
    echo "No products found in DB\n";
    exit;
}

$ids = $products->pluck('product_id')->toArray();
echo "Testing with IDs: " . implode(', ', $ids) . "\n";

$controller = new CompareController();
$request = new Request(['ids' => implode(',', $ids)]);
$response = $controller->data($request);

echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response content: " . $response->getContent() . "\n";
