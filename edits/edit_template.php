<?php
session_start();
require_once '../config/db.php';  // Ensure this file contains your PDO connection logic

// Check if a valid ticket ID is provided
if (!isset($_GET['ticket_id']) || !is_numeric($_GET['ticket_id'])) {
    echo "Invalid ticket ID.";
    exit;
}

$ticket_id = $_GET['ticket_id'];

try {
    // Prepare and execute the statement to fetch ticket details
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
    $stmt->bindParam(1, $ticket_id, PDO::PARAM_INT);
    $stmt->execute();
    $currentData = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $currentData['issued_by'];
    // Check if the ticket data exists
    if (!$currentData) {
        throw new Exception("No ticket found with this ID.");
    }

    // Format the ticket data for display
    $type = 'tickets';
    $datatype = 'ticket_id';
    $id = $ticket_id;
    $fields = [
        'issued_by' => $currentData['issued_by'],
        'issue_date' => isset($currentData['issue_date']) ? (new DateTime($currentData['issue_date']))->format('Y-m-d H:i:s') : null,
        'violation' => $currentData['violation'],
        'fine_amount' => $currentData['fine_amount'],
    ];

    // Include the template to display the ticket details
    include 'edit_template.php';
} catch (PDOException $e) {
    // Handle database errors
    echo "Database error: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    // Handle other errors (e.g., no ticket found)
    echo "Error: " . $e->getMessage();
    exit;
}
?>
