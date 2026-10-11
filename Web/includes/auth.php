<?php
// Session and Authentication Helper for SmartMove Transport

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    /**
     * Set user session upon successful database authentication
     */
    public static function login($user) {
        unset($user['password']); // Never store password in session
        $_SESSION['user'] = $user;
    }

    /**
     * Get active logged-in user or null
     */
    public static function user() {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is logged in
     */
    public static function check() {
        return isset($_SESSION['user']) && !empty($_SESSION['user']['email']);
    }

    /**
     * Require specific role(s) to access page
     */
    public static function requireRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!self::check()) {
            header('Location: signin.php?error=unauthorized');
            exit;
        }

        $userRole = strtoupper($_SESSION['user']['role'] ?? '');
        $allowed = array_map('strtoupper', $roles);

        if (!in_array($userRole, $allowed)) {
            header('Location: signin.php?error=forbidden');
            exit;
        }
    }

    /**
     * Logout and destroy session
     */
    public static function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
