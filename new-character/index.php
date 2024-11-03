<?php

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number, super FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

// Handle character creation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discord = $_POST['discord'] ?? '';
    $steamid = $_POST['steamid'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $twitter_name = $_POST['twitter_name'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $dept = $_POST['dept'] ?? '';
    $driverslicense = $_POST['driverslicense'] ?? 'invalid'; // Default to 'invalid'

    // Insert character data
    $stmt = $conn->prepare("INSERT INTO characters (discord, steamid, first_name, last_name, twitter_name, dob, gender, dept, driverslicense) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$discord, $steamid, $first_name, $last_name, $twitter_name, $dob, $gender, $dept, $driverslicense]);

    $message = "Character created successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Character - MDT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .tooltip-content {
            display: none;
            position: absolute;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            background-color: #1a202c; /* Tailwind's bg-gray-900 */
            border: 1px solid #2d3748; /* Tailwind's border-gray-700 */
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 30; /* Ensure tooltip is above other elements */
            padding: 1rem;
            width: 250px;
            border-radius: 0.5rem;
            color: #e2e8f0; /* Tailwind's text-gray-300 */
        }
        .tooltip-container:hover .tooltip-content {
            display: block;
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
            margin-left: 14px; /* match sidebar width when visible */
        }
        .full-width {
            margin-left: 0; /* full width when sidebar is hidden */
        }
        /* Custom scrollbar styles for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 8px; /* Width of the scrollbar */
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: #4b5563; /* Thumb color */
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-track {
            background-color: #1f2937; /* Track color */
        }

        /* Custom scrollbar styles for content */
        .content {
            overflow-y: auto; /* Ensure vertical scroll */
        }

        .content::-webkit-scrollbar {
            width: 8px; /* Width of the scrollbar */
        }

        .content::-webkit-scrollbar-thumb {
            background-color: #4b5563; /* Thumb color */
            border-radius: 10px;
        }

        .content::-webkit-scrollbar-track {
            background-color: #1f2937; /* Track color */
        }

        /* Firefox */
        .sidebar, .content {
            scrollbar-width: thin; /* Scrollbar width */
            scrollbar-color: #4b5563 #1f2937; /* Thumb and track colors */
        }
        /* Custom scrollbar styles for the entire body */
        body::-webkit-scrollbar {
            width: 8px; /* Width of the scrollbar */
        }

        body::-webkit-scrollbar-thumb {
            background-color: #4b5563; /* Thumb color */
            border-radius: 10px;
        }

        body::-webkit-scrollbar-track {
            background-color: #1f2937; /* Track color */
        }
    </style>
</head>
<body class="font-sans antialiased text-white bg-gray-900">
    
    <div class="flex min-h-screen">
        <!-- Toggle Button for Mobile -->
        <button id="toggleSidebarMobile" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded hidden lg:block">&#9776;</button>
        
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar bg-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 overflow-y-auto lg:static lg:w-64 hidden sm:block">
            <div class="text-center">
                <img src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'default_avatar.png'); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
                <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars($user['username'] ?? 'Unknown User'); ?></h2>
                <p><?php echo htmlspecialchars($user['dept'] ?? 'No Department'); ?>, <?php echo htmlspecialchars($user['rank'] ?? 'No Rank'); ?></p>
            </div>
            <div class="mt-6 space-y-2">

                <a href="../civ/" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-600 rounded">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
                <a href="../dmv-test/" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-600 rounded">
                    <i class="fas fa-id-card-alt mr-2"></i>
                    DMV Test
                </a>
            </div>
            <form method="post" action="logout.php" class="mt-5">
                <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </button>
            </form>
        </div>

        <!-- Main Content -->
        <div id="mainContent" class="content flex-1 flex flex-col p-4 sm:p-10">
            <header class="mb-5 flex justify-between items-center">
                <h1 class="font-bold text-2xl sm:text-3xl mb-2">Create Characters</h1>
                <div class="flex items-center">
                    <button id="toggleSidebarDesktop" class="text-gray-300 block lg:hidden">
                        <i class="fas fa-bars fa-2x"></i>
                    </button>
                </div>
            </header>

            <?php if (isset($message)): ?>
                <div class="bg-green-500 text-white px-4 py-2 rounded mb-5">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <section>
                <form method="POST" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="relative">
                            <label for="discord" class="block text-sm font-medium text-gray-300">Discord ID</label>
                            <input type="text" id="discord" name="discord" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div class="relative tooltip-container">
                            <div class="flex items-center justify-between">
                                <label for="steamid" class="block text-sm font-medium text-gray-300">Steam ID</label>
                                <i class="fas fa-question-circle text-blue-500 cursor-pointer ml-2"></i>
                            </div>
                            <input type="text" id="steamid" name="steamid" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>

                            <!-- Tooltip content -->
                            <div class="tooltip-content">
                                <h3 class="font-semibold mb-2">To view your Steam ID:</h3>
                                <ul class="list-disc pl-4 text-sm text-gray-300">
                                    <li>In the Steam desktop application, select your Steam username in the top right corner of the screen.</li>
                                    <li>Select <strong>Account details</strong>.</li>
                                    <li>Your Steam ID can be found below your Steam username.</li>
                                </ul>
                                <img src="https://img.thestoicbear.dev/images/Stoic-2024-09-04_00-52-41-66d7af590f9b3.png" alt="Steam ID Location" class="mt-2 rounded">
                                <img src="https://img.thestoicbear.dev/images/Stoic-2024-09-04_00-52-54-66d7af66c8aa0.png" alt="Steam ID Location" class="mt-2 rounded">
                            </div>
                        </div>

                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-300">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-300">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label for="twitter_name" class="block text-sm font-medium text-gray-300">Twitter Name</label>
                            <input type="text" id="twitter_name" name="twitter_name" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="dob" class="block text-sm font-medium text-gray-300">Date of Birth</label>
                            <input type="date" id="dob" name="dob" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-300">Gender</label>
                            <select id="gender" name="gender" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="dept" class="block text-sm font-medium text-gray-300">Department</label>
                            <input type="text" id="dept" name="dept" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="driverslicense" class="block text-sm font-medium text-gray-300">Driver's License</label>
                            <input type="text" id="driverslicense" name="driverslicense" value="invalid" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" readonly>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Character
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            var mainContent = document.getElementById("mainContent");
            sidebar.classList.toggle("hidden-sidebar");
            mainContent.classList.toggle("full-width");
        }

        function openEditModal(id, lastName) {
            document.getElementById('character_id').value = id;
            document.getElementById('last_name').value = lastName;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        document.getElementById('toggleSidebarMobile').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('hidden-sidebar');
            document.getElementById('mainContent').classList.toggle('full-width');
        });

        document.getElementById('toggleSidebarDesktop').addEventListener('click', function () {
            var sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('hidden')) {
                sidebar.classList.remove('hidden');
                document.getElementById('mainContent').classList.add('ml-64');
            } else {
                sidebar.classList.add('hidden');
                document.getElementById('mainContent').classList.remove('ml-64');
            }
        });
    </script>
</body>
</html>
