<?php
// Database connection template
class Database {
    private static $host = 'localhost';
    private static $db   = 'smartmove';
    private static $user = 'root';
    private static $pass = '';

    public static function connect() {
        try {
            $pdo = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=utf8mb4", self::$user, self::$pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            return null;
        }
    }
}
