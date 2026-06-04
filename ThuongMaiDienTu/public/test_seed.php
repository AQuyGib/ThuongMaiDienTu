<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Tự động kiểm tra và tạo Database nếu chưa tồn tại
try {
    $host = config('database.connections.mysql.host', '127.0.0.1');
    $port = config('database.connections.mysql.port', '3306');
    $username = config('database.connections.mysql.username', 'root');
    $password = config('database.connections.mysql.password', '');
    $database = config('database.connections.mysql.database', 'dienmay_pro');

    echo "Checking and creating database if not exists: $database...\n";
    $pdo = new \PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database '$database' is ready!\n\n";
} catch (\Throwable $e) {
    echo "Database creation check failed: " . $e->getMessage() . "\n";
    echo "Trying to continue with migration...\n\n";
}

echo "Running migrate:fresh --seed with Throwable catch...\n";
try {
    $status = \Artisan::call('migrate:fresh', ['--seed' => true]);
    echo "Exit status: " . $status . "\n";
    echo "Artisan Output:\n" . \Artisan::output() . "\n";
} catch (\Throwable $e) {
    echo "Caught Throwable: " . get_class($e) . " - " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
