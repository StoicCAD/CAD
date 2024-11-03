<?php

require_once 'config/db.php'; // Ensure db.php provides a valid PDO instance `$conn`

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_query'])) {
    $search_query = trim($_POST['search_query']);
    
    // Vehicle hash mapping (same as in your original code)
    $vehicleHashes = [
        // ... (your vehicle hash array)
    ];

    // SQL query to fetch characters based on the search query
    $stmt = $conn->prepare("SELECT id, first_name, last_name, gender FROM characters WHERE first_name LIKE :search_query OR last_name LIKE :search_query LIMIT 10");
    
    // Debugging: Check the parameters before execution
    $params = [':search_query' => "%$search_query%"];
    var_dump($params); // Debugging line

    // Check if prepare was successful
    if ($stmt === false) {
        die('Error preparing statement: ' . implode(',', $conn->errorInfo()));
    }

    // Execute the prepared statement
    $executed = $stmt->execute($params);
    
    // Check if execution was successful
    if ($executed === false) {
        die('Error executing statement: ' . implode(',', $stmt->errorInfo()));
    }
    
    $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($characters as $character) {
        $charId = $character['id'];
    
        // Fetch associated vehicles for the character
        $vehicleStmt = $conn->prepare("SELECT id AS vehicle_id, plate, properties FROM vehicles WHERE owner = :character_id");
        
        // Debugging: Check the parameters before execution
        $vehicleParams = [':character_id' => $charId];
        var_dump($vehicleParams); // Debugging line

        // Check if prepare was successful
        if ($vehicleStmt === false) {
            die('Error preparing vehicle statement: ' . implode(',', $conn->errorInfo()));
        }

        // Execute the vehicle statement
        $vehicleExecuted = $vehicleStmt->execute($vehicleParams);
        
        // Check if execution was successful
        if ($vehicleExecuted === false) {
            die('Error executing vehicle statement: ' . implode(',', $vehicleStmt->errorInfo()));
        }

        $vehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Prepare vehicle data with friendly names
        $vehiclesData = [];
        foreach ($vehicles as $vehicle) {
            $vehicleProps = json_decode($vehicle['properties'], true);
            $vehicleModel = $vehicleProps['model'] ?? 'Unknown Model';
            $vehicleName = $vehicleHashes[$vehicleModel] ?? 'Unknown Vehicle';
            $vehiclesData[] = [
                'id' => $vehicle['vehicle_id'],
                'name' => $vehicleName,
                'plate' => $vehicle['plate'],
                'color' => $vehicleProps['color'] ?? 'Unknown Color',
            ];
        }
    
        $result[] = [
            'id' => $charId,
            'name' => $character['first_name'] . ' ' . $character['last_name'],
            'gender' => $character['gender'],
            'vehicles' => $vehiclesData,
        ];
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
