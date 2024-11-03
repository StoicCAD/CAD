<?php
require_once 'config/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch incidents
$incidents_stmt = $conn->prepare("SELECT * FROM incidents ORDER BY created_at DESC");
$incidents_stmt->execute();
$incidents = $incidents_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch reports
$reports_stmt = $conn->prepare("SELECT * FROM reports ORDER BY report_date DESC");
$reports_stmt->execute();
$reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users and their duty status
$users_stmt = $conn->prepare("SELECT id, username, online FROM users");
$users_stmt->execute();
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare the response
$response = [
    'incidents' => $incidents,
    'reports' => $reports,
    'users' => $users, // Include users and their duty status
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
