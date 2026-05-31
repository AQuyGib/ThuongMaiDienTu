<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\App;

App::setLocale('vi');

$prompt = 'tư vấn laptop văn phòng';
$keywords = explode(' ', mb_strtolower($prompt, 'UTF-8'));
$stopwords = [
    'là', 'gì', 'cho', 'tôi', 'hỏi', 'có', 'không', 'giá', 'bao', 'nhiêu',
    'tư', 'vấn', 'cái', 'này', 'xin', 'chào', 'mua', 'bán', 'nào', 'của',
    'và', 'hay', 'hoặc', 'thì', 'mà', 'với', 'được', 'các', 'một', 'những',
    'đây', 'đó', 'kia', 'ạ', 'nhé', 'nha', 'ơi', 'thế', 'sao',
];

$searchTerms = [];
foreach ($keywords as $word) {
    $word = trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $word));
    if (mb_strlen($word) >= 2 && !in_array($word, $stopwords)) {
        $searchTerms[] = $word;
    }
}

echo "Search terms: " . implode(', ', $searchTerms) . "\n";

$query = Product::whereNull('deleted_at');
$query->where(function ($q) use ($searchTerms) {
    foreach ($searchTerms as $term) {
        $q->where(function ($subQ) use ($term) {
            $subQ->where('name', 'LIKE', "%{$term}%")
                 ->orWhereHas('translations', function ($transQ) use ($term) {
                     $transQ->where('name', 'LIKE', "%{$term}%");
                 });
        });
    }
});

$products = $query->limit(10)->get();
echo "Found products count (AND): " . $products->count() . "\n";
foreach ($products as $p) {
    echo "- ID: {$p->product_id}, Name: {$p->name}\n";
}

if ($products->isEmpty()) {
    $query = Product::whereNull('deleted_at');
    $query->where(function ($q) use ($searchTerms) {
        foreach ($searchTerms as $term) {
            $q->orWhere('name', 'LIKE', "%{$term}%")
              ->orWhereHas('translations', function ($transQ) use ($term) {
                  $transQ->where('name', 'LIKE', "%{$term}%");
              });
        }
    });
    
    $products = $query->limit(10)->get();
    echo "Found products count (OR): " . $products->count() . "\n";
    foreach ($products as $p) {
        echo "- ID: {$p->product_id}, Name: {$p->name}\n";
    }
}
