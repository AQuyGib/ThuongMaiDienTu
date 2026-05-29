<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ChatbotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

// Set the locale to English
App::setLocale('en');
echo "Current Locale: " . App::getLocale() . "\n";

$controller = new ChatbotController();
$request = new Request();
$request->replace([
    'prompt' => 'Recommend some cheap phones under 5 million',
    'context' => ''
]);

$response = $controller->chat($request);
echo "Chatbot Response Status: " . ($response->getData()->success ? "SUCCESS" : "FAILED") . "\n";
if ($response->getData()->success) {
    echo "AI Response Text:\n";
    echo $response->getData()->response . "\n";
} else {
    echo "Error Message: " . $response->getData()->message . "\n";
}
