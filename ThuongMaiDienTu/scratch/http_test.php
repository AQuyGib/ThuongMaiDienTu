<?php
// HTTP client to test real behavior
$baseUrl = 'http://127.0.0.1:8000';

// Step 1: Switch locale to EN and capture cookies
$ch = curl_init("$baseUrl/locale/en");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => false,
]);
$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
curl_close($ch);

// Extract cookies
preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headers, $matches);
$cookies = implode('; ', $matches[1]);
echo "Cookies: $cookies\n\n";

// Step 2: Fetch Home page to get CSRF token
$ch = curl_init("$baseUrl/Home");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIE => $cookies,
]);
$html = curl_exec($ch);
curl_close($ch);

preg_match('/<meta name="csrf-token" content="([^"]+)"/', $html, $csrfMatches);
$csrfToken = $csrfMatches[1] ?? '';
echo "CSRF Token: $csrfToken\n\n";

if (!$csrfToken) {
    echo "Failed to get CSRF token!\n";
    exit;
}

// Step 3: POST to /chatbot
$postData = json_encode([
    'prompt' => "The store's best portable laptops",
    'context' => ''
]);

$ch = curl_init("$baseUrl/chatbot");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_COOKIE => $cookies,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-CSRF-TOKEN: ' . $csrfToken,
        'Accept: application/json',
    ],
]);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $res\n";
