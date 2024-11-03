<?php
$secure = true; 
$httponly = true;
$samesite = 'none';
$lifetime = 600;  // This is the variable that should be used for session cookie lifetime

if (PHP_VERSION_ID < 70300) {
    // Corrected to use $lifetime instead of $maxlifetime
    session_set_cookie_params($lifetime, '/; samesite='.$samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
} else {
    // Corrected to use $lifetime instead of $maxlifetime in the array
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}

// Include database connection details
require_once('config.php');

// Now you can use $conn PDO object for database operations
?>
