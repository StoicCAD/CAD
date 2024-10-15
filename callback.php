<?php
require 'config/db.php';  // This will use the connection setup from db.php
include_once 'config/config.php';

session_start();

$clientId = CLIENT_ID;
$clientSecret = CLIENT_SECRET; // Add CLIENT_SECRET to your config
$redirectUri = REDIRECTURI;
$state = $_GET['state'];
$code = $_GET['code'];

if ($state !== $_SESSION['oauth2state']) {
    die('Invalid state');
}

$tokenUrl = 'https://discord.com/api/oauth2/token';
$data = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirectUri,
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ],
];

$context  = stream_context_create($options);
$response = file_get_contents($tokenUrl, false, $context);
$tokenData = json_decode($response, true);

if (isset($tokenData['access_token'])) {
    $accessToken = $tokenData['access_token'];

    // Fetch user info
    $userUrl = 'https://discord.com/api/v10/users/@me';
    $options = [
        'http' => [
            'header'  => "Authorization: Bearer $accessToken\r\n",
            'method'  => 'GET',
        ],
    ];

    $context = stream_context_create($options);
    $userResponse = file_get_contents($userUrl, false, $context);
    $userData = json_decode($userResponse, true);

    // Handle user data (e.g., store in session, check against database)
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['username'] = $userData['username'];
    
    // Redirect to the dashboard
    header("Location: dashboard.php");
    exit();
} else {
    die('Error fetching access token');
}
?>
