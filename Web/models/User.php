<?php
require_once __DIR__ . '/../config/db.php';

/**
 * User Model
 * Interacts with the Oracle 'users' table and handles authentication & registration.
 */
class User {
    public static function getRoles() {
        return ['PASSENGER', 'DRIVER', 'STAFF', 'ADMIN'];
    }

    /**
     * Authenticate a user by email and password.
     * Checks Oracle XE first, falls back to Demo accounts if DB is offline.
     */
    public static function authenticate($email, $password) {
        $email = trim($email);

        // Query Oracle Database directly
        if (Database::getOracle() !== null) {
            $sql = "SELECT userId, email, password, firstName, lastName, phone, nic, role, accountStatus 
                    FROM users 
                    WHERE LOWER(email) = LOWER(:email) AND accountStatus = 'ACTIVE'";

            $rows = Database::queryOracle($sql, [':email' => $email]);
            if (!empty($rows)) {
                $user = $rows[0];
                // Check plain text match or bcrypt hash
                if ($user['password'] === $password || (function_exists('password_verify') && @password_verify($password, $user['password']))) {
                    return $user;
                }
            }
        }

        return null;
    }

    /**
     * Check if email already exists in Oracle
     */
    public static function emailExists($email) {
        if (Database::getOracle() === null) return false;
        $sql = "SELECT userId FROM users WHERE LOWER(email) = LOWER(:email)";
        $rows = Database::queryOracle($sql, [':email' => trim($email)]);
        return !empty($rows);
    }

    /**
     * Check if NIC already exists in Oracle
     */
    public static function nicExists($nic) {
        if (Database::getOracle() === null) return false;
        $sql = "SELECT userId FROM users WHERE LOWER(nic) = LOWER(:nic)";
        $rows = Database::queryOracle($sql, [':nic' => trim($nic)]);
        return !empty($rows);
    }

    /**
     * Register a new Passenger
     */
    public static function createPassenger($data) {
        $hashedPass = password_hash($data['password'], PASSWORD_BCRYPT);
        
        // 1. Insert into base 'users' table
        $sqlUser = "INSERT INTO users (email, password, firstName, lastName, phone, nic, role, accountStatus)
                    VALUES (:email, :pass, :first, :last, :phone, :nic, 'PASSENGER', 'ACTIVE')";

        $paramsUser = [
            ':email' => trim($data['email']),
            ':pass'  => $hashedPass,
            ':first' => trim($data['firstName']),
            ':last'  => trim($data['lastName']),
            ':phone' => trim($data['phone']),
            ':nic'   => trim($data['nic'])
        ];

        $ok = Database::executeOracle($sqlUser, $paramsUser);
        if (!$ok) {
            return false;
        }

        // Fetch generated userId
        $fetchSql = "SELECT userId FROM users WHERE LOWER(email) = LOWER(:email)";
        $rows = Database::queryOracle($fetchSql, [':email' => trim($data['email'])]);
        if (empty($rows)) {
            return false;
        }

        $userId = $rows[0]['userid'] ?? $rows[0]['USERID'] ?? null;
        if (!$userId) return false;

        // 2. Insert into 'passenger' subtype table
        $sqlPass = "INSERT INTO passenger (passengerId, address, preferredPaymentMethod)
                    VALUES (:id, :addr, :pay)";

        $paramsPass = [
            ':id'   => $userId,
            ':addr' => trim($data['address'] ?? 'Colombo, Sri Lanka'),
            ':pay'  => trim($data['preferredPaymentMethod'] ?? 'CARD')
        ];

        return Database::executeOracle($sqlPass, $paramsPass);
    }

    /**
     * Register a new Driver Partner application
     */
    public static function createDriver($data) {
        $hashedPass = password_hash($data['password'], PASSWORD_BCRYPT);

        // 1. Insert into base 'users' table
        $sqlUser = "INSERT INTO users (email, password, firstName, lastName, phone, nic, role, accountStatus)
                    VALUES (:email, :pass, :first, :last, :phone, :nic, 'DRIVER', 'ACTIVE')";

        $paramsUser = [
            ':email' => trim($data['email']),
            ':pass'  => $hashedPass,
            ':first' => trim($data['firstName']),
            ':last'  => trim($data['lastName']),
            ':phone' => trim($data['phone']),
            ':nic'   => trim($data['nic'])
        ];

        $ok = Database::executeOracle($sqlUser, $paramsUser);
        if (!$ok) {
            return false;
        }

        // Fetch generated userId
        $fetchSql = "SELECT userId FROM users WHERE LOWER(email) = LOWER(:email)";
        $rows = Database::queryOracle($fetchSql, [':email' => trim($data['email'])]);
        if (empty($rows)) {
            return false;
        }

        $userId = $rows[0]['userid'] ?? $rows[0]['USERID'] ?? null;
        if (!$userId) return false;

        // 2. Insert into 'driver' subtype table
        $sqlDriver = "INSERT INTO driver (driverId, licenseNo, licenseType, licenseExpiryDate, driverStatus)
                      VALUES (:id, :lic, :type, TO_DATE(:expiry, 'YYYY-MM-DD'), 'AVAILABLE')";

        $paramsDriver = [
            ':id'     => $userId,
            ':lic'    => trim($data['licenseNo']),
            ':type'   => trim($data['licenseType'] ?? 'HEAVY_VEHICLE'),
            ':expiry' => $data['licenseExpiryDate'] ?? date('Y-m-d', strtotime('+3 years'))
        ];

        return Database::executeOracle($sqlDriver, $paramsDriver);
    }

    /**
     * Get user profile by userId.
     */
    public static function getById($userId) {
        $sql = "SELECT userId, email, firstName, lastName, phone, nic, role, accountStatus 
                FROM users 
                WHERE userId = :id";
        $rows = Database::queryOracle($sql, [':id' => $userId]);
        return !empty($rows) ? $rows[0] : null;
    }
}
