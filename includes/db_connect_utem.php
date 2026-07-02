<?php
define('DB_HOST', '127.0.0.1');   // matches "Server: 127.0.0.1" shown in phpMyAdmin
define('DB_PORT', '3306');        // MySQL default port (not 5432/5433 - that's Postgres)
define('DB_NAME', 'gw04');
define('DB_USER', 'GW04');   // same login you use for phpMyAdmin
define('DB_PASS', 'GW04');   // same password you use for phpMyAdmin

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        die('<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:32px;background:#fef0ed;border-radius:16px;border:1px solid #e8a090;">
        <h2 style="color:#b05540;margin-bottom:12px;">⚠ Database Connection Failed</h2>
        <p style="color:#555;">'.htmlspecialchars($e->getMessage()).'</p>
        <hr style="margin:16px 0;border-color:#e8a090;">
        <p style="font-weight:600;color:#555;">Fix checklist:</p>
        <ol style="color:#555;margin-top:8px;padding-left:20px;line-height:2.2;">
          <li>Enable <strong>pdo_mysql</strong> extension on the server</li>
          <li>Double check <strong>DB_USER</strong> / <strong>DB_PASS</strong> match your UTeM phpMyAdmin login</li>
          <li>Make sure database <strong>gw04</strong> exists and the tables were imported</li>
          <li>Confirm <strong>DB_HOST</strong> matches what phpMyAdmin shows (currently 127.0.0.1)</li>
        </ol></div>');
    }
    return $pdo;
}
