<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Page;

$targetLocale = config('translatable.default_target_locale', 'en');

$models = [
    'Products' => Product::class,
    'Categories' => Category::class,
    'Attributes' => Attribute::class,
    'Pages' => Page::class,
];

echo "=== TRANSLATION DASHBOARD STATS ===" . PHP_EOL;
foreach ($models as $label => $class) {
    $total = $class::count();
    $translated = $class::whereHas('translations', fn ($q) => $q->where('locale', $targetLocale))->count();
    $missing = $total - $translated;
    $percent = $total > 0 ? round(($translated / $total) * 100) : 0;
    echo sprintf("  %-12s: %d total | %d translated | %d missing | %d%%", $label, $total, $translated, $missing, $percent) . PHP_EOL;
}

echo PHP_EOL . "=== LOCALE SWITCHING TEST ===" . PHP_EOL;
app()->setLocale('en');
$product = Product::first();
echo "Locale=en → Product name: " . $product->name . PHP_EOL;

app()->setLocale('vi');
// Need to clear relation cache
$product = Product::first();
echo "Locale=vi → Product name: " . $product->name . PHP_EOL;

echo PHP_EOL . "=== ROUTE CHECK ===" . PHP_EOL;
echo "admin.translations.index: " . route('admin.translations.index') . PHP_EOL;
echo "admin.translations.sync:  " . route('admin.translations.sync') . PHP_EOL;
echo "locale.switch(vi):        " . route('locale.switch', 'vi') . PHP_EOL;
echo "locale.switch(en):        " . route('locale.switch', 'en') . PHP_EOL;
