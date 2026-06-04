<?php
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Connection Diagnostic Tool</h1>";

// Try parent directory first (for nested setups) or current directory
$envFile = dirname(__DIR__) . '/.env';
if (!file_exists($envFile)) {
    $envFile = dirname(dirname(__DIR__)) . '/.env';
}
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../.env';
}

if (!file_exists($envFile)) {
    die("<p style='color:red;'>Error: .env file not found at " . htmlspecialchars($envFile) . "</p>");
}

echo "<p>Loading configuration from: <code>" . htmlspecialchars($envFile) . "</code></p>";

$config = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, '"\'');
        $config[$key] = $value;
    }
}

$dbConn = $config['DB_CONNECTION'] ?? 'mysql';
$dbHost = $config['DB_HOST'] ?? 'localhost';
$dbPort = $config['DB_PORT'] ?? '3306';
$dbName = $config['DB_DATABASE'] ?? '';
$dbUser = $config['DB_USERNAME'] ?? '';
$dbPass = $config['DB_PASSWORD'] ?? '';

echo "<h3>Configured Credentials:</h3>";
echo "<ul>";
echo "<li><strong>Connection:</strong> " . htmlspecialchars($dbConn) . "</li>";
echo "<li><strong>Host:</strong> " . htmlspecialchars($dbHost) . "</li>";
echo "<li><strong>Port:</strong> " . htmlspecialchars($dbPort) . "</li>";
echo "<li><strong>Database:</strong> " . htmlspecialchars($dbName) . "</li>";
echo "<li><strong>Username:</strong> " . htmlspecialchars($dbUser) . "</li>";
echo "<li><strong>Password:</strong> " . (empty($dbPass) ? "<i>(empty)</i>" : "********") . "</li>";
echo "</ul>";

try {
    echo "<p>Attempting to connect to database using PDO...</p>";
    $dsn = "$dbConn:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5, // 5 seconds timeout
    ];
    
    $start = microtime(true);
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
    $end = microtime(true);
    
    echo "<h2 style='color:green;'>✓ Connection Successful!</h2>";
    echo "<p>Time taken: " . round(($end - $start) * 1000, 2) . " ms</p>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Number of tables found in database: <strong>" . count($tables) . "</strong></p>";
    if (count($tables) > 0) {
        echo "<h4>Sample Tables:</h4><ul>";
        foreach (array_slice($tables, 0, 5) as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange;'>Warning: The database is connected but contains 0 tables. Did you forget to import the database or run migrations?</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2 style='color:red;'>✗ Connection Failed!</h2>";
    echo "<p><strong>Error Message:</strong> <code>" . htmlspecialchars($e->getMessage()) . "</code></p>";
    echo "<h3>Diagnostic Suggestions:</h3>";
    echo "<ol>";
    
    if ($dbHost !== 'localhost' && $dbHost !== '127.0.0.1') {
        echo "<li><strong>Change Database Host:</strong> Your host is set to <code>" . htmlspecialchars($dbHost) . "</code>. ";
        echo "On most hosting providers, when both the website files and MySQL database are on the same server, you must use <code>localhost</code> or <code>127.0.0.1</code>. ";
        echo "Please edit your <code>.env</code> file and set <code>DB_HOST=localhost</code>.</li>";
    }
    
    echo "<li><strong>Check Database Permissions (Very Common):</strong> Ensure you have not only created the database and the user in your hosting panel (cPanel, 1Panel, DirectAdmin, etc.), but also <strong>assigned/added the user to the database</strong> with <strong>ALL PRIVILEGES</strong>.</li>";
    echo "<li><strong>Check Credentials:</strong> Verify that the database name, username, and password are exactly correct and contain no leading/trailing spaces.</li>";
    echo "</ol>";
=======
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
