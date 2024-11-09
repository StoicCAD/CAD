<?php

require_once '../config/db.php'; // Ensure this file contains your PDO connection logic

if (!isset($_GET['ticket_id']) || !is_numeric($_GET['ticket_id'])) {
    echo "Invalid ticket ID.";
    exit;
}

$ticket_id = $_GET['ticket_id'];
$stmt = $conn->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
$stmt->bindParam(1, $ticket_id, PDO::PARAM_INT);
$stmt->execute();
$currentData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentData) {
    echo "No ticket found with this ID.";
    exit;
}

$type = 'ticket';

// Convert issue_date to DateTime object
$issue_date = new DateTime($currentData['issue_date']); // Convert string to DateTime

$fields = [
    'issued_by' => $currentData['issued_by'],
    'issue_date' => $issue_date->format('Y-m-d H:i:s'), // Use format after converting to DateTime
    'violation' => $currentData['violation'],
    'fine_amount' => $currentData['fine_amount']
];

include 'edit_template.php';
?>
