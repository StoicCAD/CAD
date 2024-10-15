<?php
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch detailed user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

// Initialize the reports variable
$reports = []; // Set a default value for $reports

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        $author = trim($_POST['author'] ?? ''); // Changed 'subject' to 'author'
        $content = trim($_POST['content'] ?? '');
        $perpetrator = trim($_POST['perpetrator'] ?? '');
        $report_date = date("Y-m-d H:i:s");
        $status = 'Open';
        $user_id = $_SESSION['user_id']; // Assuming the user_id is stored in session

        // Insert the report
        $stmt = $conn->prepare("INSERT INTO reports (author, perpetrator, report_date, report_content, status, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$author, $perpetrator, $report_date, $content, $status, $user_id])) {
          header("Location: reports.php");
          exit();
        } else {
            echo "<p class='text-red-500'>Error submitting report. Please try again.</p>";
        }
    } elseif (isset($_POST['search'])) {
        $search_term = trim($_POST['search_term'] ?? '');
        $reports_stmt = $conn->prepare("SELECT * FROM reports WHERE author LIKE ? OR perpetrator LIKE ? OR report_content LIKE ?");
        $reports_stmt->execute(["%$search_term%", "%$search_term%", "%$search_term%"]);
        $reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Fetch all reports if no form is submitted
if (empty($reports)) {
    $reports_stmt = $conn->prepare("SELECT * FROM reports ORDER BY report_date DESC");
    $reports_stmt->execute();
    $reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
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
                <h1 class="font-bold text-3xl mb-4">Reports</h1>
                <div class="bg-gray-900 p-6 rounded-lg shadow-md" style="max-width: 40dvw">
                    <h2 class="text-xl font-semibold mb-4">Create New Report</h2>
                    <form method="post" class="space-y-4">
                        <div>
                            <label for="author" class="block mb-1">Reporter (Author):</label>
                            <input type="text" id="author" name="author" required class="w-full h-10 px-3 rounded bg-gray-700 focus:bg-gray-600 outline-none">
                        </div>
                        <div>
                            <label for="perpetrator" class="block mb-1">Perpetrator:</label>
                            <input type="text" id="perpetrator" name="perpetrator" required class="w-full h-10 px-3 rounded bg-gray-700 focus:bg-gray-600 outline-none">
                        </div>
                        <div>
                            <label for="content" class="block mb-1">Content:</label>
                            <textarea id="content" name="content" rows="4" required class="w-full px-3 py-2 rounded bg-gray-700 focus:bg-gray-600 outline-none"></textarea>
                        </div>
                        <button type="submit" name="submit" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600 focus:outline-none">Submit Report</button>
                    </form>
                </div>
                <!-- Search Form -->
                <div class="mt-6">
                    <h2 class="text-xl font-semibold">Search Reports</h2>
                    <form method="post" class="mb-4 flex flex-row">
                        <input type="text" name="search_term" placeholder="Search by author, perpetrator, or content" class="px-3 py-2 rounded bg-gray-700 focus:bg-gray-600 w-full rounded-r-none">
                        <button type="submit" name="search" class="px-4 py-2 bg-blue-500 rounded w-max hover:bg-blue-600 rounded-l-none">Search</button>
                    </form>
                </div>
                <!-- Reports Table -->
                <div class="bg-gray-900 p-6 rounded-lg shadow-lg">
                    <h2 class="text-xl font-semibold mb-4">Existing Reports</h2>
                    <table class="w-full text-sm text-left text-gray-500 rounded-xl">
                        <thead class="text-xs text-gray-400 uppercase bg-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-3">Reporter</th>
                                <th scope="col" class="px-6 py-3">Perpetrator</th>
                                <th scope="col" class="px-6 py-3">Date</th>
                                <th scope="col" class="px-6 py-3">Content</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($reports)) : ?>
                                <?php foreach ($reports as $report) : ?>
                                    <tr class="bg-gray-700 border-b border-gray-900 text-white">
                                        <td class="px-6 py-4"><?= htmlspecialchars($report['author']) ?></td>
                                        <td class="px-6 py-4"><?= htmlspecialchars($report['perpetrator']) ?></td>
                                        <td class="px-6 py-4"><?= htmlspecialchars($report['report_date']) ?></td>
                                        <td class="px-6 py-4"><?= htmlspecialchars($report['report_content']) ?></td>
                                        <td class="px-6 py-4"><?= htmlspecialchars($report['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center">No reports found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </header>
        </div>
    </div>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('hidden-sidebar');
            document.getElementById('mainContent').classList.toggle('full-width');
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
