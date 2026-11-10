<?php
// PDO Database Connection Template
// Copy this file to 'db.php' and fill in your actual credentials.
require_once __DIR__ . '/constants.php';

class Database {
    private static ?PDO $pdo = null;

    private static string $host = '127.0.0.1';
    private static string $db   = 'ezsafar';
    private static string $user = 'root';
    private static string $pass = '';
    private static string $charset = 'utf8mb4';

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$pdo = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (PDOException $e) {
                try {
                    $dsnFallback = "mysql:host=localhost;dbname=" . self::$db . ";charset=" . self::$charset;
                    self::$pdo = new PDO($dsnFallback, self::$user, self::$pass, $options);
                } catch (PDOException $e2) {
                    die("Database connection error: " . htmlspecialchars($e2->getMessage()));
                }
            }
        }
        return self::$pdo;
    }
}

function get_db(): PDO {
    return Database::getConnection();
}

