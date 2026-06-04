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

$html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
    <script>
        let cleanResponse = "test";
        cleanResponse = cleanResponse
            .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
            .replace(/^[\s]*[-•*]\s/gm, '👉 ')
            .replace(/^[\s]*#{1,4}\s*(.*)/gm, '<b>$1</b>')
            .replace(/\r\n/g, '\n')
            .replace(/\n{2,}/g, '\n\n')
            .replace(/\n/g, '<br>')
            .replace(/(<br>\s*){3,}/gi, '<br><br>')
            .replace(/^(<br>\s*)+/i, '')
            .replace(/(<br>\s*)+$/i, '');
        console.log("Phân tích ưu nhược điểm");
    </script>
</body>
</html>
HTML;

$output = $method->invoke($middleware, $html);

echo "--- OUTPUT ---\n";
echo $output . "\n";
