<?php
require_once __DIR__ . '/../config/db.php';

/**
 * User Model
 * Interacts with the Oracle 'users' table and handles authentication.
 */
class User {
    public static function getRoles() {
        return ['PASSENGER', 'DRIVER', 'STAFF', 'ADMIN'];
    }

    /**
     * Authenticate a user by email and password.
     * @param string $email
     * @param string $password
     * @return array|null User record or null if invalid
     */
    public static function authenticate($email, $password) {
        $sql = "SELECT userId, email, password, firstName, lastName, phone, role, accountStatus 
                FROM users 
                WHERE LOWER(email) = LOWER(:email) AND accountStatus = 'ACTIVE'";

        $rows = Database::queryOracle($sql, [':email' => trim($email)]);
        if (!empty($rows)) {
            $user = $rows[0];
            // Support both plain-text match and password_verify hash
            if ($user['password'] === $password || (function_exists('password_verify') && @password_verify($password, $user['password']))) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Get user profile by userId.
     * @param int $userId
     * @return array|null
     */
    public static function getById($userId) {
        $sql = "SELECT userId, email, firstName, lastName, phone, nic, role, accountStatus 
                FROM users 
                WHERE userId = :id";
        $rows = Database::queryOracle($sql, [':id' => $userId]);
        return !empty($rows) ? $rows[0] : null;
    }
}

