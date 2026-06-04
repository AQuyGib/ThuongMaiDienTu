<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\ChatbotController;
use App\Http\Middleware\TranslateHtmlResponse;
use Illuminate\Support\Facades\App;

// Set locale to Vietnamese
App::setLocale('vi');

$request = Request::create('/chatbot', 'POST', [
    'prompt' => "máy tính xách tay cấu hình cao tốt nhất",
    'context' => ''
]);

// Set session locale to 'vi'
$request->setLaravelSession($app['session']->driver());
$request->session()->put('locale', 'vi');

$controller = new ChatbotController();
$middleware = new TranslateHtmlResponse($app->make(\App\Services\TranslationService::class));

$response = $middleware->handle($request, function($req) use ($controller) {
    return $controller->chat($req);
});

echo "--- RESPONSE STATUS ---\n";
echo "HTTP Status: " . $response->getStatusCode() . "\n";
echo "--- RESPONSE CONTENT ---\n";
echo $response->getContent() . "\n";
