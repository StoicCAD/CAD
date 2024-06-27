<?php
session_start();
require_once '../config/db.php';  // Ensure this file contains your PDO connection logic

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_GET['user_id'];  // Get the userid from the data sent with the url

try {
    // Prepare and execute the statement to fetch user data
    $stmt = $conn->prepare("SELECT * FROM cadusers WHERE id = ?");
    $stmt->bindParam(1, $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found.");
    }

    // Setup data for the template
    $currentData = $user;
    $fields = [
        'username' => $user['username'],
        'email' => $user['email'],
        'dept' => $user['dept'] ?? '',
        'rank' => $user['rank'] ?? '',
        'badge_number' => $user['badge_number'] ?? ''
    ];
    $type = 'cadusers';
    $datatype = 'id';
    $id = $userId;

    // Include the generic edit template
    include 'edit_template.php';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}
?>
