<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "Testing raw database connection...\n";
try {
    if (!file_exists(__DIR__.'/../.env')) {
        die(".env file not found!\n");
    }
    $env = file_get_contents(__DIR__.'/../.env');
    
    $conn = 'mysql';
    $host = '127.0.0.1';
    $port = '3306';
    $db = 'dienmay_pro';
    $user = 'root';
    $pass = '';

    foreach (explode("\n", $env) as $line) {
        $line = trim($line);
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if ($key === 'DB_CONNECTION') $conn = $val;
            if ($key === 'DB_HOST') $host = $val;
            if ($key === 'DB_PORT') $port = $val;
            if ($key === 'DB_DATABASE') $db = $val;
            if ($key === 'DB_USERNAME') $user = $val;
            if ($key === 'DB_PASSWORD') $pass = $val;
        }
    }
    
    echo "Parsed Config:\n";
    echo "Connection: $conn\n";
    echo "Host: $host\n";
    echo "Port: $port\n";
    echo "Database: $db\n";
    echo "User: $user\n";
    echo "Password: " . ($pass !== '' ? '********' : '(empty)') . "\n\n";
    
    if ($conn === 'sqlite') {
        $path = __DIR__.'/../' . $db;
        echo "SQLite database path: $path\n";
        if (file_exists($path)) {
            echo "SQLite database file exists.\n";
        } else {
            echo "SQLite database file does NOT exist!\n";
        }
        $pdo = new PDO("sqlite:$path");
    } else {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    }
    echo "Connection Status: SUCCESS!\n";
} catch (\Exception $e) {
    echo "Connection Status: FAILED!\n";
    echo "Error: " . $e->getMessage() . "\n";
}
