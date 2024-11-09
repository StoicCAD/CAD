<?php

require_once '../config/db.php'; // Ensure this file contains your PDO connection logic

if (!isset($_GET['arrest_id']) || !is_numeric($_GET['arrest_id'])) {
    echo "Invalid arrest ID.";
    exit;
}

$arrest_id = $_GET['arrest_id'];
$stmt = $conn->prepare("SELECT * FROM arrests WHERE arrest_id = ?");
$stmt->bindParam(1, $arrest_id, PDO::PARAM_INT);
$stmt->execute();
$currentData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentData) {
    echo "No arrest found with this ID.";
    exit;
}

$type = 'arrest';

// Convert arrest_date to DateTime object
$arrest_date = new DateTime($currentData['arrest_date']); // Convert string to DateTime

$fields = [
    'officer_name' => $currentData['officer_name'],
    'arrest_date' => $arrest_date->format('Y-m-d H:i:s'), // Use format after converting to DateTime
    'charges' => $currentData['charges'],
    'bail_amount' => $currentData['bail_amount']
];

include 'edit_template.php';
?>
