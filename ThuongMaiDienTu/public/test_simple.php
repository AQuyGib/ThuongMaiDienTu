<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "Step 1: autoload\n";
require __DIR__.'/../vendor/autoload.php';
echo "Step 2: bootstrap app\n";
$app = require_once __DIR__.'/../bootstrap/app.php';
echo "Step 3: make kernel\n";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
echo "Step 4: bootstrap kernel\n";
$kernel->bootstrap();
echo "Step 5: done\n";
