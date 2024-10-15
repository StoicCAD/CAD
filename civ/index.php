<?php
require_once '../config/db.php';

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

// Retrieve all characters based on Discord ID
$characters = [];
if (isset($user['discord_id'])) {
    $discord_id = $user['discord_id'];
    
    // Fetch all character details
    $stmt = $conn->prepare("SELECT * FROM characters WHERE discord = ?");
    $stmt->execute([$discord_id]);
    $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle character update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $character_id = $_POST['character_id'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    
    $mugshot = '';

    if (isset($_FILES['mugshot']) && $_FILES['mugshot']['error'] === UPLOAD_ERR_OK) {
        // Get the uploaded file
        $file_tmp = $_FILES['mugshot']['tmp_name'];
        $file_type = mime_content_type($file_tmp);
        
        // Read the file content and encode it to Base64
        $file_data = file_get_contents($file_tmp);
        $mugshot = 'data:' . $file_type . ';base64,' . base64_encode($file_data);
    } else {
        // If no new mugshot was uploaded, keep the existing one
        $stmt = $conn->prepare("SELECT mugshot FROM characters WHERE id = ?");
        $stmt->execute([$character_id]);
        $mugshot = $stmt->fetchColumn();
    }

    if (!empty($character_id)) {
        try {
            // Update character data
            $stmt = $conn->prepare("UPDATE characters SET last_name = ?, mugshot = ? WHERE id = ?");
            $stmt->execute([$last_name, $mugshot, $character_id]);

            $message = "Character updated successfully!";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StoicCAD | CIV Dashboard | Computer Aided Dispatch / Mobile Data Terminal Character Center">
    <meta name="keywords" content="StoicCAD, CAD, MDT, Open Source, FiveM CAD, FiveM Open Source CAD, FiveM Open source MDT">
    <meta name="author" content="TheStoicBear | StoicCAD">
    <meta name="robots" content="index, follow">
    <meta name="copyright" content="Copyright 2024 StoicCAD">
    <meta property="og:title" content="StoicCAD | CIV Dashboard">
    <meta property="og:description" content="StoicCAD | CIV Dashboard">
    <meta property="og:image" content="URL to an image that represents your page">
    <meta property="og:url" content="https://mdt.stoiccad.com/civ/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="StoicCAD | CIV Dashboard">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="The title of your page">
    <meta name="twitter:description" content="StoicCAD | CIV Dashboard | Computer Aided Dispatch / Mobile Data Terminal Character Center">
    <meta name="twitter:image" content="URL to an image for Twitter">
    <meta name="theme-color" content="#3B82F6">
    <meta name="apple-mobile-web-app-title" content="StoicCAD | CIV Dashboard">
    <meta name="msapplication-TileColor" content="#3B82F6">
    <link rel="canonical" href="https://mdt.stoiccad.com/civ/">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <title>My Characters - MDT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
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
        
        <div id="sidebar" class="bg-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 overflow-y-auto lg:static lg:w-64 hidden sm:block">
    <div class="text-center">
        <img src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'default_avatar.png'); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
        <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars($user['username'] ?? 'Unknown User'); ?></h2>
        <p><?php echo htmlspecialchars($user['dept'] ?? 'No Department'); ?>, <?php echo htmlspecialchars($user['rank'] ?? 'No Rank'); ?></p>
    </div>
    <div class="mt-6 space-y-2">
    <a href="../dmv-test/" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-600 rounded">
        <i class="fas fa-id-card-alt mr-2"></i>
        DMV Test
    </a>
    <a href="../new-character/" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-600 rounded">
        <i class="fas fa-user-plus mr-2"></i>
        New Character
    </a>

    <?php
    // Define LEO departments
    $leoDepartments = ['LSPD', 'SASP', 'BCSO']; // Add other LEO departments as needed

    // Check if the user's department includes any LEO department
    $user_departments = explode(',', $user['dept']);
    if (array_intersect($user_departments, $leoDepartments)): ?>
        <a href="../dashboard.php" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-600 rounded">
            <i class="fas fa-shield-alt mr-2"></i>
            LEO Dashboard
        </a>
    <?php endif; ?>
</div>
    <form method="post" action="logout.php" class="mt-5">
        <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </button>
    </form>
</div>


        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-0 sm:ml-64 p-4 sm:p-10">
            <header class="mb-5 flex justify-between items-center">
                <h1 class="font-bold text-2xl sm:text-3xl mb-2">My Characters</h1>
                <div class="flex items-center">
                    <button id="toggleSidebarDesktop" class="text-gray-300 block lg:hidden">
                        <i class="fas fa-bars fa-2x"></i>
                    </button>
                </div>
            </header>

            <?php if (isset($message)): ?>
            <div class="bg-green-500 text-white px-4 py-2 rounded mb-5">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <!-- Characters Grid or Create New Character Button -->
            <section>
                <?php if (empty($characters)): ?>
                <div class="text-center">
                    <p class="text-lg mb-4">No characters found.</p>
                    <a href="../new-character/" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Create New Character
                    </a>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($characters as $character): ?>
                    <div class="bg-gray-800 rounded-lg p-4">
                        <img src="<?php echo $character['mugshot'] ?: 'default_mugshot.png'; ?>" alt="Mugshot" class="w-32 h-32 rounded-full mx-auto mb-4">
                        <h3 class="text-center text-xl font-semibold">
                            <?php echo htmlspecialchars($character['first_name'] . ' ' . $character['last_name']); ?>
                        </h3>
                        <button onclick="openEditModal(<?php echo $character['id']; ?>, '<?php echo htmlspecialchars($character['last_name']); ?>')"
                            class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded block mx-auto">
                            Edit Character
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden">
        <div class="bg-gray-800 p-8 rounded-lg w-full sm:w-1/2 lg:w-1/3">
            <h2 class="text-2xl font-bold mb-4">Edit Character</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" id="character_id" name="character_id">
                <div class="mb-4">
                    <label for="last_name" class="block text-sm font-medium text-gray-300">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="mugshot" class="block text-sm font-medium text-gray-300">Mugshot</label>
                    <input type="file" id="mugshot" name="mugshot" accept="image/*" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save Changes</button>
                </div>
            </form>
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
