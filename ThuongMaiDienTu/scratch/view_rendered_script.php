<?php
$baseUrl = 'http://127.0.0.1:8000';

// Switch locale to EN
$ch = curl_init("$baseUrl/locale/en");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
]);
$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
curl_close($ch);

preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headers, $matches);
$cookies = implode('; ', $matches[1]);

// Fetch Home
$ch = curl_init("$baseUrl/Home");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIE => $cookies,
]);
$html = curl_exec($ch);
curl_close($ch);

// Find the chatbot script tag
// Look for chatbotToggle or chatbotSend inside script
preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $html, $scriptMatches);

foreach ($scriptMatches[1] as $script) {
    if (strpos($script, 'chatbotToggle') !== false) {
        echo "--- FOUND CHATBOT SCRIPT ---\n";
        echo $script . "\n";
        break;
    }
}
