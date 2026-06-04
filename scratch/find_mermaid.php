<?php
// Scan for mermaid.js locally
$dir = new RecursiveDirectoryIterator('.');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if (preg_match('/mermaid.*\.js$/i', $file->getFilename())) {
        echo $file->getPathname() . "\n";
    }
}
echo "Done scanning.\n";
