<?php
$scriptContent = file_get_contents(__DIR__ . '/../resources/views/partials/chatbot.blade.php');

$patterns = [
    '/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/u',
    '/\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/u',
    '/`([^`\\\\]*(?:\\\\.[^`\\\\]*)*)`/u'
];

foreach ($patterns as $pIdx => $pattern) {
    if (preg_match_all($pattern, $scriptContent, $matches)) {
        foreach ($matches[1] as $index => $strContent) {
            $fullMatch = $matches[0][$index];
            echo "Pattern $pIdx Match: \n";
            echo "  Full Match : " . $fullMatch . "\n";
            echo "  Str Content: " . $strContent . "\n";
        }
    }
}
