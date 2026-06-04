<?php
$html = file_get_contents('http://127.0.0.1:8000/san-pham/1');
preg_match_all('/<button[^>]+>/i', $html, $matches);
foreach ($matches[0] as $m) {
    if (strpos(strtolower($m), 'cart') !== false || strpos(strtolower($m), 'button') !== false || strpos(strtolower($m), 'submit') !== false || strpos(strtolower($m), 'btn') !== false) {
        echo $m . "\n";
    }
}
preg_match_all('/<a[^>]+>/i', $html, $matches2);
foreach ($matches2[0] as $m) {
    if (strpos(strtolower($m), 'cart') !== false || strpos(strtolower($m), 'pay') !== false || strpos(strtolower($m), 'buy') !== false) {
        echo $m . "\n";
    }
}
