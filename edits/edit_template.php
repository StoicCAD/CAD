<?php
// Start the session only if it hasn't already been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($currentData)) {
    die("Data not loaded properly.");
}

require_once '../config/config.php'; // Ensure this file contains your PDO connection logic

// Fetch detailed user information including dept, rank, and badge number
$stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number, super FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    $updateValues = [];

    // Prepare values for updating
    foreach ($fields as $field => $oldValue) {
        if (isset($_POST[$field])) {
            $updateValues[$field] = $_POST[$field];
        } else {
            $errors[] = "Missing value for field $field";
        }
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE {$type} SET " . join(', ', array_map(fn($field) => "$field = :$field", array_keys($updateValues))) . " WHERE id = :id";
            $stmt = $conn->prepare($sql);
            foreach ($updateValues as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            $stmt->bindValue(":id", $currentData['id']);
            $stmt->execute();
            echo "<p class='text-green-500'>Record updated successfully.</p>";
        } catch (PDOException $e) {
            echo "<p class='text-red-500'>Error updating record: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<p class='text-red-500'>Error: $error</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Record</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../scrollkit.css">
    <style>
        body {
            background-color: #0d121c; /* Custom background color */
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            left: 0;
            z-index: 1000;
            width: 100%;
            background: #4b5563; /* Matching Tailwind's gray-700 */
            border-radius: 0 0 0.5rem 0.5rem;
        }
        .sidebar {
            transition: transform 0.3s ease-out;
            transform: translateX(0);
            z-index: 10;
        }
        .hidden-sidebar {
            transform: translateX(-100%);
        }
        .sidebar-button {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 20;
        }
</style>
    </style>
</head>
<body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded">&#9776;</button>
        
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 overflow-y-auto sidebar">
            <div class="text-center">
                <!-- Ensure values are not null before using htmlspecialchars -->
                <img src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'default_avatar.png'); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
                <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars($user['username'] ?? 'Unknown User'); ?></h2>
                <p>
                    <?php echo htmlspecialchars($user['dept'] ?? 'No Department'); ?>, 
                    <?php echo htmlspecialchars($user['user_id'] ?? 'No Department'); ?>, 
                    <?php echo htmlspecialchars($user['rank'] ?? 'No Rank'); ?><br>
                    Badge #<?php echo htmlspecialchars($user['badge_number'] ?? 'No Badge'); ?>
                </p>
            </div>

            <nav>
                <div class="flex justify-center mt-5">

                </div>
                <a href="../dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-home mr-2"></i>Dashboard</a>
                <a href="../incidents.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-exclamation-triangle mr-2"></i>Active Calls</a>
                <a href="../reports.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-file-alt mr-2"></i>Reports</a>
                <a href="../map.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-map-marked-alt mr-2"></i>Map</a>

                <!-- Dropdown for Searches -->
                <div class="relative dropdown">
                    <a href="#" class="block py-2.5 px-4 rounded hover:bg-blue-600 cursor-pointer"><i class="fas fa-search mr-2"></i>Searches <i class="fa fa-caret-down"></i></a>
                    <div class="dropdown-menu">
                        <a href="people_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">People</a>
                        <a href="vehicle_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">Vehicles</a>
                    </div>
                </div>

                <a href="../settings.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-cog mr-2"></i>Settings</a>

                <?php 
                // Handle multiple departments
                $departments = explode(',', $user['dept']); // Split the departments string into an array

                // Check if user is in 'CIV' department
                if (in_array('CIV', $departments)): ?>
                    <a href="../civ/" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-car mr-2"></i>Civilian Dashboard</a>
                <?php endif; ?>

                <?php if ($user['rank'] == 'Admin'): ?>
                    <a href="../admin-dash.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a>
                <?php endif; ?>

                <?php if ($user['super'] == 1): ?>
                    <a href="../super-dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Supervisor Dashboard</a>
                <?php endif; ?>
                
                <form method="post" action="../logout.php" class="mt-5">
                    <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </nav>
        </div>


        <div class="mb-8 flex-grow">
            <h2 class="text-xl font-semibold text-center">Edit <?php echo htmlspecialchars($type ?? 'Record'); ?></h2>
            <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6 mx-auto p-6 w-full max-w-md">
                <form action="" method="post">
                    <?php foreach ($fields as $field => $value): ?>
                        <label for="<?php echo $field; ?>" class="block text-sm font-medium text-gray-300 mt-2"><?php echo ucfirst($field); ?>:</label>
                        <?php if ($field === 'description' || $field === 'content'): ?>
                            <textarea id="<?php echo $field; ?>" name="<?php echo $field; ?>" class="mt-1 block w-full border border-gray-600 bg-gray-800 text-white p-2 rounded-md" rows="4"><?php echo htmlspecialchars($value); ?></textarea>
                        <?php else: ?>
                            <input type="<?php echo $field === 'email' ? 'email' : 'text'; ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo htmlspecialchars($value); ?>" class="mt-1 block w-full border border-gray-600 bg-gray-800 text-white p-2 rounded-md">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <button type="submit" class="mt-4 w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2 rounded-md transition duration-200">Update</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            var mainContent = document.getElementById("mainContent");
            sidebar.classList.toggle("hidden-sidebar");
            mainContent.classList.toggle("full-width");
        }
        // JavaScript to handle dropdown behavior
        document.addEventListener('DOMContentLoaded', function () {
            const dropdown = document.querySelector('.dropdown');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            dropdown.addEventListener('click', function (event) {
                event.stopPropagation();
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            });

            window.addEventListener('click', function () {
                if (dropdownMenu.style.display === 'block') {
                    dropdownMenu.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
