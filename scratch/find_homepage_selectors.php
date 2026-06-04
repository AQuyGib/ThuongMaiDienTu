<?php
$html = file_get_contents('C:/Users/ANH QUY/.gemini/antigravity/brain/43dbbf22-b150-4cdd-a986-72848dbfc915/.system_generated/steps/2287/content.md');

// Find chatbot-related elements
preg_match_all('/id="[^"]*chat[^"]*"/i', $html, $matches1);
preg_match_all('/class="[^"]*chat[^"]*"/i', $html, $matches2);
preg_match_all('/id="[^"]*bot[^"]*"/i', $html, $matches3);
preg_match_all('/class="[^"]*bot[^"]*"/i', $html, $matches4);

echo "Chatbot ID Matches:\n";
print_r($matches1[0]);
echo "Chatbot Class Matches:\n";
print_r($matches2[0]);
echo "Bot ID Matches:\n";
print_r($matches3[0]);
echo "Bot Class Matches:\n";
print_r($matches4[0]);

// Find language dropdown
preg_match_all('/locale/i', $html, $matches_locale);
echo "Locale mentions: " . count($matches_locale[0]) . "\n";
