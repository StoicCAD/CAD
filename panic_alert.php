<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log'); // Specify the path to your error log file

require_once 'config/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log('User not logged in: ' . json_encode($_SESSION)); // Log the session data
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['action']) || $data['action'] !== 'trigger_panic' || !isset($data['user_id'])) {
        error_log('Invalid request data: ' . json_encode($data)); // Log invalid request data
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit();
    }

    $user_id = intval($data['user_id']);

    // Fetch username based on user_id
    $username = getUsernameById($user_id);
    if (!$username) {
        error_log('User not found for user_id: ' . $user_id); // Log if the user is not found
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    try {
        // Insert the incident into the database
        $stmt = $conn->prepare("INSERT INTO incidents (title, description, status, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute(["Panic Alert", "A panic alert has been triggered by $username.", "Open"]);

        // Check if there are online users
        $onlineUsersCount = getOnlineUsersCount();
        
        // Create a single message
        $message = "Panic Button Pressed by $username. Alerting online users.";

        if ($onlineUsersCount > 0) {
            // Only send TTS message if there are online users
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Panic alert triggered successfully', 'messages' => [$message]]);
        } else {
            // No online users, do not send TTS
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Panic alert triggered successfully, but no online users to alert.']);
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage()); // Log database errors
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error triggering panic alert: ' . $e->getMessage()]);
    }
} else {
    error_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']); // Log invalid request methods
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Function to fetch username by user_id
function getUsernameById($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn(); // Fetch the username
}

// Function to count online users
function getOnlineUsersCount() {
    global $conn;
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE online = 1");
    return $stmt->fetchColumn(); // Return the count of online users
}
?>
