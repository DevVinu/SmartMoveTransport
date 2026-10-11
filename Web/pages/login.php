<?php
// Permanent redirect from legacy login.php to signin.php
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: signin.php' . $queryString, true, 301);
exit;
