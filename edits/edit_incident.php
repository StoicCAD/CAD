<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config/db.php';


$stmt = $conn->prepare("SELECT title, description, reported_by, status FROM incidents WHERE id = ?");
$stmt->execute([$_GET['incident_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Prepare an INSERT statement
$sql = "INSERT INTO incidents (title, description, reported_by, status) VALUES (?, ?, ?, ?, 'Open')";

// Bind parameters
$title = $_GET['title'];
$description = $_GET['desc'];
$reported_by = $_GET['repby'];
$currentData = "";

$fields = [
    'reported_by' => $user["reported_by"],
    'title' => $user["title"],  
    'description' => $user["description"],
];
$type = 'incidents';
$datatype = 'id';
$id = $_GET['incident_id'];


include 'edit_template.php';
?>
