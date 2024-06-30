<?php
$host = 'YOUR_SQL_IP';
$db_name = 'YOUR_SQL_DBSNAME';
$username = 'YOUR_SQL_USERNAME';
$password = 'YOUR_SQL_PASSWORD';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
    exit;
}
?>
