<?php
// Read .env file to get MySQL parameters dynamically
$host = '127.0.0.1';
$dbname = 'myproject1';
$username = 'root';
$password = '';

// Find .env file in parent directories
$dir = __DIR__;
$envFile = null;
while ($dir !== dirname($dir)) {
    if (file_exists($dir . '/.env')) {
        $envFile = $dir . '/.env';
        break;
    }
    $dir = dirname($dir);
}

if ($envFile && file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2) + [NULL, NULL];
        if ($name !== NULL) {
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, '"\'');
            if ($name === 'DB_HOST') $host = $value;
            if ($name === 'DB_DATABASE') $dbname = $value;
            if ($name === 'DB_USERNAME') $username = $value;
            if ($name === 'DB_PASSWORD') $password = $value;
        }
    }
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
