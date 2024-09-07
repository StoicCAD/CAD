<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database credentials
// Define the host and port of your MySQL database. This is typically 'localhost' or an IP address followed by the port number if applicable (e.g., '127.0.0.1:3308').
define('DB_HOST', '127.0.0.1');

// Define the username used to connect to your MySQL database. Update this with the username for your database.
define('DB_USERNAME', '');

// Define the password for the MySQL database user. Update this with the password associated with your database username.
define('DB_PASSWORD', '');

// Define the name of the database you are connecting to. Update this with the name of your database.
define('DB_NAME', '');

// OAuth client credentials
// To get your OAuth client credentials, you typically need to register your application with the OAuth service provider (e.g., Google, Discord).
// The client ID and client secret are provided during this registration process.
// Replace the placeholders below with the credentials obtained from the OAuth service.
define('CLIENT_ID', ''); // The client ID provided by the OAuth service.
define('CLIENT_SECRET', ''); // The client secret provided by the OAuth service.

// Define the URL to which the OAuth service will redirect after successful authentication. Update this to match your actual redirect URI.
define('REDIRECTURI', 'https://your-domain/process-oauth.php');

// Discord Token
// To generate a token, follow these steps:
// 1. Go to https://stoiccad.com/login.php and log in with Discord.
// 2. After logging in, you'll be redirected to https://stoiccad.com/dashboard.php.
// 3. Click on 'Generate Random Token' to get your token.
// 4. Replace the empty string below with the token you obtain.
define('TOKEN', '');


//Access the map at http://<server IP>:<server port>/webmap/
// or 
//https://<owner>-<server ID>.users.cfx.re/webmap/ (Note: The trailing slash is necessary).
$iframeUrl = "https://owner-serverID.users.cfx.re/webmap/";


// Establish PDO database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $conn = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
} catch (PDOException $e) {
    die("Could not connect to the database " . DB_NAME . ": " . $e->getMessage());
}


// Function to check if user is logged in
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}
?>
