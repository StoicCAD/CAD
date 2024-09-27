<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
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

// Check if the user is an admin
if ($user['rank'] !== 'Admin') {
    echo "Access denied.";
    exit;
}

// Handle POST requests for updates to configuration files
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'edit_config':
                $configFile = 'config/config.php'; // Corrected file path
                $configContent = $_POST['config_content'];
                file_put_contents($configFile, $configContent);
                echo "Configuration file updated successfully.";
                break;



            default:
                echo "Invalid action.";
                break;
        }
        exit();
    }
}

// Get the contents of the configuration files for editing
$configContent = file_get_contents('config/config.php');


// Fallback for background image
$backgroundImage = isset($backgroundImage) ? $backgroundImage : 'default_background.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Configuration Edit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="scrollkit.css">
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
textarea {
    background-color: #2d3748; /* Dark background for better contrast */
    color: #e2e8f0; /* Light text color */
    border: 1px solid #4a5568; /* Darker border for better definition */
    border-radius: 0.375rem; /* Rounded corners */
    padding: 1rem; /* Padding for better text readability */
    font-family: monospace; /* Monospace font for code readability */
    font-size: 0.875rem; /* Slightly smaller font size for better fit */
    resize: vertical; /* Allow resizing only vertically */
    width: 100%;
    margin-bottom: 1rem; /* Margin below the textarea */
}

textarea:focus {
    border-color: #3182ce; /* Blue border on focus for better visibility */
    outline: none; /* Remove default outline */
    box-shadow: 0 0 0 2px rgba(66, 153, 225, 0.5); /* Blue shadow on focus */
}

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
                    <?php echo htmlspecialchars($user['rank'] ?? 'No Rank'); ?><br>
                    Badge #<?php echo htmlspecialchars($user['badge_number'] ?? 'No Badge'); ?>
                </p>
            </div>

            <nav>
                <a href="dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-home mr-2"></i>Dashboard</a>
                <a href="incidents.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-exclamation-triangle mr-2"></i>Incidents</a>
                <a href="reports.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-file-alt mr-2"></i>Reports</a>
                <a href="map.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-map-marked-alt mr-2"></i>Map</a>
                <!-- Dropdown for Searches -->
                <div class="relative dropdown">
                    <a href="#" class="block py-2.5 px-4 rounded hover:bg-blue-600 cursor-pointer"><i class="fas fa-search mr-2"></i>Searches <i class="fa fa-caret-down"></i></a>
                    <div class="dropdown-menu">
                        <a href="people_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">People</a>
                        <a href="vehicle_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">Vehicles</a>
                    </div>
                </div>

                <a href="settings.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-cog mr-2"></i>Settings</a>
                <?php if ($user['rank'] == 'Admin'): ?>
                    <a href="a-dash.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a>
                <?php endif; ?>

                <?php if ($user['super'] == 1): ?>
                    <a href="super-dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Supervisor Dashboard</a>
                <?php endif; ?>
                
                <form method="post" action="logout.php" class="mt-5">
                    <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-5">
                <h1 class="font-bold text-3xl mb-2">Admin Configuration Edit</h1>

                <!-- Display success or error message -->
                <?php if (isset($message)): ?>
                    <div class="bg-gray-900 p-4 rounded-lg shadow-md">
                        <div class="<?php echo $messageType; ?> p-4 rounded-lg mb-4"><?php echo htmlspecialchars($message); ?></div>
                    </div>
                <?php endif; ?>
            </header>

            <!-- Configuration File Edit Form -->
            <section>
                <h2 class="text-2xl font-semibold mb-4">Edit Configuration</h2>
                <form method="post">
                    <input type="hidden" name="action" value="edit_config">
                    <textarea name="config_content" rows="20" class="w-full p-4 border border-gray-600 rounded" required><?php echo htmlspecialchars($configContent); ?></textarea>
                    <button type="submit" class="mt-4 py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700">Save Configuration</button>
                </form>
            </section>

            <!-- Department Style Configuration Edit Form -->
            <section class="mt-10">
                <h2 class="text-2xl font-semibold mb-4">Edit Department Style Configuration</h2>
                <form method="post">
                    <input type="hidden" name="action" value="edit_dept_style">
                    <textarea name="dept_style_content" rows="20" class="w-full p-4 border border-gray-600 rounded" required><?php echo htmlspecialchars($deptStyleContent); ?></textarea>
                    <button type="submit" class="mt-4 py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700">Save Department Style Configuration</button>
                </form>
            </section>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('hidden-sidebar');
            mainContent.classList.toggle('full-width');
        }
        
        // Dropdown functionality
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.addEventListener('click', function() {
                this.querySelector('.dropdown-menu').classList.toggle('block');
            });
        });

        // Close dropdown when clicking outside
        window.addEventListener('click', function(event) {
            if (!event.target.matches('.dropdown a')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('block');
                });
            }
        });
    </script>
</body>
</html>