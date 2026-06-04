<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\User;
use App\Services\CrossSellService;
use Illuminate\Support\Facades\Cache;

echo "=============================================\n";
echo "TESTING AI RECOMMENDATION & DYNAMIC PRICING\n";
echo "=============================================\n";

$product = Product::first();
if (!$product) {
    echo "Lỗi: Không tìm thấy sản phẩm nào trong DB.\n";
    exit(1);
}

echo "Sản phẩm chính đang xem:\n";
echo "- ID: {$product->product_id}\n";
echo "- Tên: {$product->name}\n";
echo "- Giá gốc: " . number_format($product->base_price, 0, ',', '.') . "đ\n";
echo "- Danh mục ID: {$product->category_id}\n";

$crossSellService = app(CrossSellService::class);

// Mẫu 1: User hạng Vàng (Gold) hoặc tạo mock user
$mockGoldUser = User::first() ?: new User();
$mockGoldUser->member_tier = 'Vang';
$mockGoldUser->full_name = 'Nguyễn Văn Vàng';

// Xóa cache trước
$userKeyGold = $mockGoldUser->user_id ?? 'guest';
Cache::forget("ai_combo_recs_{$userKeyGold}_{$product->product_id}");

echo "\n--- ĐANG GỌI AI GỢI Ý COMBO CHO USER HẠNG VÀNG ---\n";
$startTime = microtime(true);
$goldCombos = $crossSellService->getAICrossSellCombos($product, $mockGoldUser, []);
$duration = round(microtime(true) - $startTime, 2);

echo "Thời gian xử lý: {$duration}s\n";
echo "Danh sách Combo đề xuất cho hạng Vàng:\n";
foreach ($goldCombos as $idx => $item) {
    $pivot = $item->pivot;
    echo ($idx + 1) . ". {$item->name}\n";
    echo "   - Giá gốc: " . number_format($item->base_price, 0, ',', '.') . "đ\n";
    echo "   - Loại giảm giá: {$pivot->discount_type}\n";
    echo "   - Giá trị giảm: {$pivot->discount_value}" . ($pivot->discount_type === 'percentage' ? '%' : 'đ') . "\n";
    echo "   - AI Optimized: " . ($pivot->is_ai_optimized ? 'Đúng' : 'Không') . "\n";
    echo "   - Lý do đề xuất: {$pivot->ai_reason}\n";
}

// Mẫu 2: User hạng Đồng (Bronze)
$mockBronzeUser = new User();
$mockBronzeUser->member_tier = 'Dong';
$mockBronzeUser->full_name = 'Trần Văn Đồng';

$userKeyBronze = 'guest'; // force guest/dong
Cache::forget("ai_combo_recs_guest_{$product->product_id}");

echo "\n--- ĐANG GỌI AI GỢI Ý COMBO CHO KHÁCH HÀNG HẠNG ĐỒNG/VÃNG LAI ---\n";
$startTime = microtime(true);
$bronzeCombos = $crossSellService->getAICrossSellCombos($product, null, []);
$duration = round(microtime(true) - $startTime, 2);

echo "Thời gian xử lý: {$duration}s\n";
echo "Danh sách Combo đề xuất cho hạng Đồng:\n";
foreach ($bronzeCombos as $idx => $item) {
    $pivot = $item->pivot;
    echo ($idx + 1) . ". {$item->name}\n";
    echo "   - Giá gốc: " . number_format($item->base_price, 0, ',', '.') . "đ\n";
    echo "   - Loại giảm giá: {$pivot->discount_type}\n";
    echo "   - Giá trị giảm: {$pivot->discount_value}" . ($pivot->discount_type === 'percentage' ? '%' : 'đ') . "\n";
    echo "   - AI Optimized: " . ($pivot->is_ai_optimized ? 'Đúng' : 'Không') . "\n";
    echo "   - Lý do đề xuất: {$pivot->ai_reason}\n";
}
