<?php

require_once '../config/db.php'; // Ensure this file contains your PDO connection logic

// Only allow access if the user is an admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get the incident ID from the query string
$incidentId = $_GET['incident_id'] ?? null;
if (!$incidentId) {
    die("Incident ID not specified.");
}

// Prepare and execute the statement to fetch incident data
$stmt = $conn->prepare("SELECT * FROM incidents WHERE id = ?");
$stmt->bindParam(1, $incidentId);
$stmt->execute();
$incident = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$incident) {
    die("Incident not found.");
}

// Setup data for the template
$currentData = $incident;
$fields = [
    'title' => $incident['title'],
    'description' => $incident['description'],
    'reported_by' => $incident['reported_by'],
    'status' => $incident['status']
];
$type = 'Incident';

// Include the generic edit template
include 'edit_template.php';

?>
