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
    // Redirection logic based on the department
    if ($user['dept'] === 'CIV') {
        header("Location: general_dashboard.php"); // Redirect to general_dashboard.php if department is CIV
        exit();
    }


    // Check if the user is a superuser
    if ($user['super'] != 1) {
        echo "Access denied. This area is restricted to super users only.";
        exit;
    }

    // Fetch all users in the same department as the logged-in super user
    $deptUsersStmt = $conn->prepare("SELECT * FROM users WHERE dept = ?");
    $deptUsersStmt->execute([$user['dept']]);
    $deptUsers = $deptUsersStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Dashboard - MDT</title>
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

        <!-- Main Content -->
        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-5">
                <h1>Super Admin Dashboard</h1>
            </header>

            <!-- Users Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold">Department Users</h2>
                <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">ID</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Username</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Email</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Avatar</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Rank</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Badge Number</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Super</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deptUsers as $deptUser): ?>
                            <tr class="bg-gray-800 hover:bg-gray-700">
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo $deptUser['id']; ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo htmlspecialchars($deptUser['username']); ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo htmlspecialchars($deptUser['email']); ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <img src="<?php echo htmlspecialchars($deptUser['avatar_url']); ?>" alt="Avatar" class="h-8 w-8 rounded-full">
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo htmlspecialchars($deptUser['rank']); ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo htmlspecialchars($deptUser['badge_number']); ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo $deptUser['super'] ? 'Yes' : 'No'; ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <a href="edits/edit_user.php?user_id=<?php echo $deptUser['id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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