<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$scriptContent = file_get_contents(__DIR__ . '/../resources/views/partials/chatbot.blade.php');

$viRegex = '/[\x{00C0}-\x{00C3}\x{00C8}-\x{00CA}\x{00CC}-\x{00CD}\x{00D2}-\x{00D5}\x{00D9}-\x{00DA}\x{00DD}\x{00E0}-\x{00E3}\x{00E8}-\x{00EA}\x{00EC}-\x{00ED}\x{00F2}-\x{00F5}\x{00F9}-\x{00FA}\x{00FD}\x{0102}-\x{0103}\x{0110}-\x{0111}\x{0128}-\x{0129}\x{0168}-\x{0169}\x{01A0}-\x{01A1}\x{01AF}-\x{01B0}\x{1EA0}-\x{1EF9}]/u';

$patterns = [
    '/"([^"\\\\\r\n]*(?:\\\\.[^"\\\\\r\n]*)*)"/u',
    '/\'([^\'\\\\\r\n]*(?:\\\\.[^\'\\\\\r\n]*)*)\'/u',
    '/`([^`\\\\]*(?:\\\\.[^`\\\\]*)*)`/u'
];

$replacements = [];

foreach ($patterns as $pattern) {
    if (preg_match_all($pattern, $scriptContent, $matches)) {
        foreach ($matches[1] as $index => $strContent) {
            $fullMatch = $matches[0][$index];
            if (preg_match($viRegex, $strContent)) {
                // Mock translation
                $translatedStr = $strContent . " (translated)";
                $quoteChar = $fullMatch[0];
                $escapedTrans = addcslashes($translatedStr, $quoteChar . "\\");
                $newStr = $quoteChar . $escapedTrans . $quoteChar;
                $replacements[$fullMatch] = $newStr;
            }
        }
    }
}

echo "--- REPLACEMENTS ---\n";
print_r($replacements);

$output = strtr($scriptContent, $replacements);

// Check if backslashes are doubled
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
