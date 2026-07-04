<?php
// includes/db.php — MediaFind Database Connection
// Auto-detects XAMPP (local) vs UTeM server

$isUtem = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'utem.edu.my') !== false;

if ($isUtem) {
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'gw_04');
    define('DB_USER', 'GW04'); // same login as phpMyAdmin
    define('DB_PASS', 'GW04');
} else {
    // XAMPP local settings
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'gw_04');   // your local XAMPP database name
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        die('<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:32px;
            background:#fef0ed;border-radius:16px;border:1px solid #e8a090;">
            <h2 style="color:#b05540;">⚠ Database Connection Failed</h2>
            <p style="color:#555;">' . htmlspecialchars($e->getMessage()) . '</p>
            <hr style="margin:16px 0;border-color:#e8a090;">
            <p style="font-weight:600;color:#555;">Fix checklist:</p>
            <ol style="color:#555;margin-top:8px;padding-left:20px;line-height:2.2;">
              <li>Make sure XAMPP MySQL is running (green in Control Panel)</li>
              <li>Database name must be <strong>gw_04</strong> in phpMyAdmin</li>
              <li>Import <strong>GW04.sql</strong> into that database first</li>
              <li>Username: <strong>root</strong> · Password: <strong>(empty)</strong></li>
            </ol></div>');
    }
    return $pdo;
}
?>
