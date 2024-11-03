<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request: User ID not provided']);
        exit();
    }

    $user_id = (int)$data['user_id'];
    try {
        // Fetch the current online status of the user
        $stmt = $conn->prepare("SELECT online FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit();
        }

        // Set new status based on the current online status
        $newStatus = $user['online'] ? 0 : 1;

        // Update the duty status in the database
        $stmt = $conn->prepare("UPDATE users SET online = ? WHERE id = ?");
        $stmt->execute([$newStatus, $user_id]);

        // Send a success response back
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'online' => $newStatus]);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error setting duty status: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
