<?php

require_once '../config/db.php'; // Ensure this file contains your PDO connection logic
require_once '../config/config.php'; // Ensure this file contains your PDO connection logic

if (!isset($_GET['arrest_id']) || !is_numeric($_GET['arrest_id'])) {
    echo "Invalid arrest ID.";
    exit;
}

    // Fetch detailed user information including dept, rank, and badge number
    $stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number, super FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

$arrest_id = $_GET['arrest_id'];
$stmt = $conn->prepare("SELECT * FROM arrests WHERE arrest_id = ?");
$stmt->bindParam(1, $arrest_id, PDO::PARAM_INT);
$stmt->execute();
$currentData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($currentData) {
    $type = 'arrest';
    $arrestDate = new DateTime($currentData['arrest_date']);
    $fields = [
        'officer_name' => $currentData['officer_name'],
        'arrest_date' => $arrestDate->format('Y-m-d H:i:s'),  // Correctly format the date
        'charges' => $currentData['charges'],
        'bail_amount' => $currentData['bail_amount']
    ];

    include 'edit_template.php';
} else {
    echo "No arrest found with this ID.";
    exit;
}
?>