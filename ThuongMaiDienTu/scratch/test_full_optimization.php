<?php
$root = __DIR__ . '/..';
require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Services\CrossSellService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// Xóa cache cũ để test
Cache::flush();

// Lấy iPhone 15 Pro Max (ID: 1)
$iphone = Product::find(1);
if (!$iphone) { echo "Không tìm thấy iPhone\n"; exit; }

echo "--- TEST 1: ADMIN PICKS ---\n";
echo "Giả lập Admin gán Apple Watch Ultra 2 (ID: 27) cho iPhone 15\n";
DB::table('product_cross_sells')->insertOrIgnore([
    ['product_id' => 1, 'cross_sell_id' => 27, 'sort_order' => 1]
]);

$svc = new CrossSellService();
$results = $svc->getFullCrossSellList($iphone, 8);
echo "Kết quả (Sản phẩm đầu tiên phải là ID 27):\n";
echo "  [{$results[0]->product_id}] {$results[0]->name}\n";

echo "\n--- TEST 2: CACHING ---\n";
$start = microtime(true);
$svc->getFullCrossSellList($iphone, 8);
$end = microtime(true);
echo "Thời gian lấy lần 2 (từ cache): " . round(($end - $start) * 1000, 2) . "ms\n";

echo "\n--- TEST 3: PERSONALIZATION (Giả lập) ---\n";
// Giả lập user 1 đã xem ốp lưng MagSafe (ID: 30)
DB::table('wishlists_recently_viewed')->insertOrIgnore([
    ['user_id' => 1, 'product_id' => 30, 'type' => 'Viewed']
]);
// Login user 1
auth()->loginUsingId(1);
Cache::flush();

$results = $svc->getFullCrossSellList($iphone, 8);
echo "Kết quả cho User 1 (Phải có ốp lưng ID 30):\n";
foreach($results as $item) {
    if($item->product_id == 30) echo "  => Đã tìm thấy Ốp lưng (ID 30) trong kết quả cá nhân hóa!\n";
}

echo "\n--- HOÀN TẤT KIỂM TRA ---\n";
