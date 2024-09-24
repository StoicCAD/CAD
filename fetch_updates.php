<?php
require_once 'config/db.php';

// Fetch incidents
$incidents_stmt = $conn->prepare("SELECT * FROM incidents ORDER BY created_at DESC");
$incidents_stmt->execute();
$incidents = $incidents_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch reports
$reports_stmt = $conn->prepare("SELECT * FROM reports ORDER BY report_date DESC");
$reports_stmt->execute();
$reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare the response
$response = [
    'incidents' => $incidents,
    'reports' => $reports,
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>