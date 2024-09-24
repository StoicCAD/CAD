<?php


// Set headers and session configurations
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

$secure = true;
$httponly = true;
$samesite = 'None';
$lifetime = 600;

if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params($lifetime, '/; samesite='.$samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
} else {
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}

require 'config/db.php';  // This will use the connection setup from db.php
include_once 'config/config.php';
$clientId = CLIENT_ID;
$redirectUri = REDIRECTURI;

if (!isset($_SESSION['oauth2state'])) {
    $_SESSION['oauth2state'] = hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR']);
}

$authorizeURL = "https://discord.com/api/oauth2/authorize?client_id={$clientId}&response_type=code&redirect_uri=" . urlencode($redirectUri) . "&scope=identify%20email&state=" . $_SESSION['oauth2state'];

// Check token function
function check_token($token) {
    $url = 'https://stoiccad.com/check_token.php'; // Local PHP endpoint
    $data = json_encode(array('token' => $token));
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the database query
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Check if user exists and password is correct
    if (!$user) {
        $_SESSION['error'] = "Invalid user.";
    } elseif (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Wrong password.";
    } else {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['dept'] = $user['dept'];
        $_SESSION['rank'] = $user['rank'];
        $_SESSION['badge_number'] = $user['badge_number'];

        // Redirect to the dashboard
        header("Location: dashboard.php");
        exit;
    }
}

// Clear the error message after displaying
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Police Dashboard Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        /* Styles for notification */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px;
            color: white;
            border-radius: 6px;
            display: none; /* Hidden by default */
            animation: fade-in 0.5s ease-out forwards, slide-up 0.5s ease-out forwards;
        }

        @keyframes fade-in {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes slide-up {
            0% { transform: translateY(50px); }
            100% { transform: translateY(0); }
        }

        .notification.visible {
            display: block;
        }

        .bg-green-500 {
            background-color: #34D399; /* Update tailwind color to match Discord green */
        }

        .bg-red-500 {
            background-color: #EF4444; /* Update tailwind color to match Discord red */
        }

        .bg-gray-500 {
            background-color: #6B7280; /* Update tailwind color to match Discord gray */
        }
    </style>
    <script>
        // Function to check token on page load
        function checkTokenOnLoad(token) {
            var xhr = new XMLHttpRequest();
            var url = 'https://stoiccad.com/check_token.php'; // Use HTTPS endpoint
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response && response.valid !== undefined) {
                                if (response.valid) {
                                    showNotification('[StoicCAD©️] ✅ Token is valid. ', '', 'bg-green-500', true);
                                } else {
                                    showNotification('[StoicCAD©️] ⚠️ Token is invalid.', ' Please validate token: <a href="https://stoiccad.com/dashboard.php">Dashboard</a>', 'bg-red-500', false);
                                }
                            } else {
                                showNotification('[StoicCAD©️] ⚠️ Invalid response format.', '[StoicCAD©️] Please try again later.', 'bg-gray-500', false);
                            }
                        } catch (error) {
                            showNotification('[StoicCAD©️] ⚠️ Error parsing response: ' + error.message, '[StoicCAD©️] Please try again later.', 'bg-gray-500', false);
                        }
                    } else {
                        showNotification('[StoicCAD©️] ⚠️ Request failed with status: ' + xhr.status, '[StoicCAD©️] Please try again later.', 'bg-gray-500', false);
                    }
                }
            };
            var data = JSON.stringify({ token: token });
            xhr.send(data);
        }

        // Function to display notification
        function showNotification(message, action, bgClass, closable) {
            var notification = document.getElementById('notification');
            notification.innerHTML = '<div class="flex items-center justify-between"><div class="flex items-center"><span class="mr-2">' + message + '</span></div><div>' + action + '</div></div><div>' + (closable ? '<i class="fas fa-times cursor-pointer" onclick="hideNotification()"></i>' : '') + '</div>';
            notification.className = 'notification ' + bgClass + ' visible';
        }

        // Function to hide notification
        function hideNotification() {
            var notification = document.getElementById('notification');
            notification.className = 'notification'; // Hide notification
        }

        // Execute token check on page load
        window.onload = function () {
            var token = '<?php echo TOKEN; ?>'; // Use token from PHP config
            checkTokenOnLoad(token);
        };
    </script>
</head>
<body class="bg-gray-800 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8 space-y-6 bg-gray-700 rounded-lg shadow-lg text-white">
        <h1 class="text-center text-3xl font-bold">Police Portal Login</h1>
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-bold mb-2">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required class="w-full p-3 rounded bg-gray-600 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required class="w-full p-3 rounded bg-gray-600 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Sign In</button>
        </form>
        <div class="text-center mt-4">
            <a href="<?php echo $authorizeURL ?>" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">Login with Discord</a>
        </div>
        <!-- Notification area -->
        <div id="notification" class="notification"></div>
    </div>
</body>
</html>