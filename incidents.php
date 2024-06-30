<?php
    session_start();
    require_once 'config/db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Fetch detailed user information including dept, rank, and badge number
    $stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number, super FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit;
    }

    require_once 'config/dept_style_config.php'; // Include the department style configurations



    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $reported_by = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO incidents (title, description, reported_by) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $reported_by]);
    }

    $incidents_stmt = $conn->prepare("SELECT * FROM incidents ORDER BY created_at DESC");
    $incidents_stmt->execute();
    $incidents = $incidents_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidents</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-image: url('<?php echo $backgroundImage; ?>');
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
    </style>
</head>
<body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded">&#9776;</button>
        
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-5">
            <h1 class="font-bold text-3xl mb-6">Incidents</h1>
            <div class="bg-gray-900 p-6 rounded-lg shadow-md">
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
                    <button type="submit" class="w-full py-2 bg-blue-500 rounded hover:bg-blue-600 focus:outline-none">Report Incident</button>
                </form>
            </div>
            <div class="bg-gray-900 mt-6 p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Existing Incidents</h2>
                <ul>
                    <?php foreach ($incidents as $incident) {
                        echo "<li class='py-2 border-b border-gray-700'>" . htmlspecialchars($incident['title']) . " - " . htmlspecialchars($incident['description']) . "</li>";
                    } ?>
                </ul>
            </div>
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
