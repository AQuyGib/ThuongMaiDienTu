<?php
$html = file_get_contents('http://127.0.0.1:8000/');
preg_match_all('/<input[^>]+>/i', $html, $matches);
foreach ($matches[0] as $m) {
    echo $m . "\n";
}
