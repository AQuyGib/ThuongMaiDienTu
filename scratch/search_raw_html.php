<?php
$html = file_get_contents('http://127.0.0.1:8000/');

preg_match_all('/id="([^"]*chat[^"]*|[^"]*bot[^"]*)"/i', $html, $matches1);
preg_match_all('/class="([^"]*chat[^"]*|[^"]*bot[^"]*)"/i', $html, $matches2);
preg_match_all('/href="([^"]*locale[^"]*)"/i', $html, $matches3);

echo "IDs matched:\n";
print_r(array_unique($matches1[0]));
echo "Classes matched:\n";
print_r(array_unique($matches2[0]));
echo "Hrefs matched:\n";
print_r(array_unique($matches3[0]));
