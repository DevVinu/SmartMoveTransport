<?php
require_once __DIR__ . '/../includes/auth.php';
Auth::logout();
header('Location: login.php?logged_out=1');
exit;
