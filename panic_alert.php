<?php
session_start();
require_once 'config/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve JSON data from the request
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate the data
    if (!isset($data['action']) || $data['action'] !== 'trigger_panic' || !isset($data['username'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit();
    }

    // Get the username from the request
    $username = htmlspecialchars($data['username']);

    // Insert a new panic alert into the incidents table
    try {
        $stmt = $conn->prepare("INSERT INTO incidents (title, description, status, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute(["Panic Alert", "A panic alert has been triggered by $username.", "Open"]);
        
        // Optionally: Notify all users about the panic alert
        // For example, you could use a messaging system or send an email to all users.

        echo json_encode(['success' => true, 'message' => 'Panic alert triggered successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error triggering panic alert: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
