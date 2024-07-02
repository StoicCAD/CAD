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

$host = '76.59';
$db_name = 'qbtest';
$username = 'discord';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}
?>
