<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Start session
session_start();
// Include the centralized database connection
include 'database.php';



// https://discord.com/developers/applications
// OAuth2 Use Discord as an authorization system or use our API on behalf of your users. Add a redirect URI, pick your scopes, roll a D20 for good luck, and go!
define('CLIENT_ID', 'YOUR_DISCORD_CLIENTID');
define('CLIENT_SECRET', 'YOUR_DISCORD_CLIENT_SECRET');

//Redirect URI. If you are using your own Domain (KSRP.COM) than you'd use https://KSRP.COM/process-oauth.php
//Redirect URI. If you are using HTMLBear. You'd use https://html.thestoicbear.dev/YourUserName/YourWebsiteName/process-oauth.php
define('REDIRECTURI', 'https://html.thestoicbear.dev/Stoic/stoiccad/process-oauth.php');


// Function to check if user is logged in
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}
?>
