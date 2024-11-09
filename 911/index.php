<?php
// Check if the request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set CORS headers
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

    // Include the configuration file
    require_once '../config/config.php';

    // Session parameters
    $secure = true;
    $httponly = true;
    $samesite = 'none';
    $lifetime = 600;


    // Get the input data from the form
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    // Check if the form fields are not empty
    if (!empty($title) && !empty($description)) {
        // Prepare an SQL INSERT query using PDO from config.php
        try {
            $sql = "INSERT INTO incidents (title, description) VALUES (:title, :description)";
            $stmt = $conn->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);

            // Execute the statement and check for errors
            if ($stmt->execute()) {
                $message = "New incident reported successfully";
            } else {
                $message = "Error reporting incident";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>911 Incident Report Form</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-800 flex items-center justify-center min-h-screen" style="background-color: rgba(31, 41, 55, 0.0);">

    <div class="w-full max-w-2xl p-6 bg-gray-900 rounded-xl shadow-xl">
        <form id="incidentForm" method="POST" class="space-y-6 animate-fade-in-up">
            <h1 class="text-2xl font-bold text-center text-gray-100">911 Incident Report</h1>
            <?php if (!empty($message)): ?>
                <div class="p-3 bg-red-700 text-white text-sm rounded">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <div>
                <label for="title" class="block text-lg font-semibold text-gray-300">Incident Title</label>
                <input type="text" id="title" name="title" placeholder="Enter the incident title" required
                       class="mt-1 block w-full px-4 py-2 bg-gray-800 text-gray-300 border border-gray-700 rounded-md shadow-sm placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="description" class="block text-lg font-semibold text-gray-300">Description</label>
                <textarea id="description" name="description" rows="5" placeholder="Detailed description of the incident"
                          class="mt-1 block w-full px-4 py-2 bg-gray-800 text-gray-300 border border-gray-700 rounded-md shadow-sm placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <button type="submit" class="w-full px-4 py-2 text-lg font-bold text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Submit Report
            </button>
        </form>
    </div>

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 50px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.7s ease-out forwards;
        }
    </style>
</body>
</html>
