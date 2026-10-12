<?php
// Logout handler
require_once __DIR__ . '/../includes/auth.php';

Auth::logout();
header('Location: signin.php?logged_out=1');
exit;
