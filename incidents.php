<?php

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch detailed user information including dept, rank, and badge number
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $reported_by = $user['id'];

    // Here you can specify attached users, e.g., from a checkbox selection or other input
    $attached_users = isset($_POST['attached_users']) ? implode(',', $_POST['attached_users']) : null;

    $stmt = $conn->prepare("INSERT INTO incidents (title, description, reported_by, attached_users) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $reported_by, $attached_users]);
    
    header("Location: incidents.php");
    exit();
}

// Fetch incidents with attached user details
$incidents_stmt = $conn->prepare("
    SELECT i.*, GROUP_CONCAT(u.username) AS attached_usernames
    FROM incidents i
    LEFT JOIN users u ON FIND_IN_SET(u.id, i.attached_users)
    GROUP BY i.id
    ORDER BY i.created_at DESC
");
$incidents_stmt->execute();
$incidents = $incidents_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidents</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="scrollkit.css">
    <style>
        body {
            background-color: #0d121c; /* Set the background color */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
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
        .content {
            transition: margin-left 0.9s ease-out;
            margin-right: 120px; /* match sidebar width when visible */
        }
        .full-width {
            margin-left: 0; /* full width when sidebar is hidden */
        }
        select[multiple] {
            height: auto;
            max-height: 300px; /* Increased height for better visibility */
            overflow-y: auto; /* Scroll when content exceeds the dropdown */
            background-color: #1f2937;
        }
    </style>
</head>
<body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded">&#9776;</button>
        
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        <!-- Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-5">
            <h1 class="font-bold text-3xl mb-6">Incidents</h1>
            <div class="bg-gray-900 p-6 rounded-lg shadow-md" style="max-width: 40dvw">
                <h2 class="text-xl font-semibold mb-4">Report New Incident</h2>
                <form method="post" class="space-y-4">
                    <div>
                        <label class="block mb-1" for="title">Title:</label>
                        <input type="text" id="title" name="title" required class="w-full h-10 px-3 rounded bg-gray-700 focus:bg-gray-600 outline-none">
                    </div>
                    <div>
                        <label class="block mb-1" for="description">Description:</label>
                        <textarea id="description" name="description" rows="4" required class="w-full px-3 py-2 rounded bg-gray-700 focus:bg-gray-600 outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block mb-1">Attach Users:</label>
                        <select name="attached_users[]" multiple class="w-full h-full px- rounded bg-gray-700 focus:bg-gray-600 outline-none">
                            <?php
                            // Fetch all users to populate the dropdown
                            $users_stmt = $conn->query("SELECT id, username FROM users");
                            $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($users as $attached_user) {
                                echo "<option value='" . htmlspecialchars($attached_user['id']) . "'>" . htmlspecialchars($attached_user['username']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-500 rounded hover:bg-blue-600 focus:outline-none">Report Incident</button>

                    </form>
                </div>
            <div class="bg-gray-900 mt-6 p-6 rounded-lg shadow-md" style="max-width: 40dvw">
                <h2 class="text-xl font-semibold mb-4">Existing Incidents</h2>
                <ul>
                    <?php foreach ($incidents as $incident) {
                        $attached_usernames = !empty($incident['attached_usernames']) ? htmlspecialchars($incident['attached_usernames']) : 'No users attached';
                        echo "<li class='py-2 border-b border-gray-700'>" . htmlspecialchars($incident['title']) . " - " . htmlspecialchars($incident['description']) . " (Attached: $attached_usernames)</li>";
                    } ?>
                </ul>
                <?php if(count($incidents) < 1) { echo "<p class='text-gray-300 text-center'>No existing incidents...</p>"; } ?>
            </div>
        </div>
    </div>
    <script>
        if (window.history.replaceState) {
          window.history.replaceState(null, null, window.location.href);
        }

        // Autofill the current date and time for the incident report
        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.getElementById('report_date');
            const now = new Date();
            const isoString = now.toISOString().slice(0, 16); // Get the current date and time in ISO format
            dateInput.value = isoString;
        });
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
