<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Middleware\TranslateHtmlResponse;

$middleware = new TranslateHtmlResponse($app->make(\App\Services\TranslationService::class));

// Reflect translateHtml method
$reflector = new ReflectionClass(TranslateHtmlResponse::class);
$method = $reflector->getMethod('translateHtml');
$method->setAccessible(true);

$chatbotView = file_get_contents(__DIR__ . '/../resources/views/partials/chatbot.blade.php');

$html = "<!DOCTYPE html><html><body>" . $chatbotView . "</body></html>";

$output = $method->invoke($middleware, $html);

// Find the regex block in output and print it
$lines = explode("\n", $output);
$start = false;
foreach ($lines as $i => $line) {
    if (strpos($line, 'cleanResponse = cleanResponse') !== false) {
        $start = true;
    }
    if ($start) {
        echo $line . "\n";
        if (strpos($line, 'appendMsg(cleanResponse') !== false) {
            break;
        }
    }
}
