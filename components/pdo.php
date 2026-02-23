<?php
// components/pdo.php
// Database connection using PDO

define('DB_HOST', 'localhost');
define('DB_NAME', 'usermgmt');
define('DB_USER', 'root');      // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:monospace;background:#fee;border:1px solid red;padding:20px;margin:20px;">
                <strong>Database Connection Error:</strong><br>' . htmlspecialchars($e->getMessage()) . '<br><br>
                Please check your database credentials in <code>components/pdo.php</code>
            </div>');
        }
    }
    return $pdo;
}
