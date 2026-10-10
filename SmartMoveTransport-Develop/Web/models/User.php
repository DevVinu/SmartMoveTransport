<?php
// User model
class User {
    public static function getRoles() {
        return ['PASSENGER', 'DRIVER', 'STAFF', 'ADMIN'];
    }
}
