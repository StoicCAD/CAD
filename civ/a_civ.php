<?php

require_once '../config/db.php';

// Get user details
$stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, discord_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['rank'] !== 'Admin') {
    echo "Access denied.";
    exit;
}

// Initialize variables
$search = $_GET['search'] ?? '';
$filter_dept = $_GET['filter_dept'] ?? '';
$query = "SELECT * FROM characters WHERE 1=1";

// Add search filter
if (!empty($search)) {
    $query .= " AND (first_name LIKE :search OR last_name LIKE :search)";
}

// Add department filter
if (!empty($filter_dept)) {
    $query .= " AND dept = :dept";
}

$stmt = $conn->prepare($query);

// Bind parameters
if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
if (!empty($filter_dept)) {
    $stmt->bindValue(':dept', $filter_dept);
}

$stmt->execute();
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle character update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $character_id = $_POST['character_id'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    
    $mugshot = '';

    if (isset($_FILES['mugshot']) && $_FILES['mugshot']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['mugshot']['tmp_name'];
        $file_type = mime_content_type($file_tmp);
        
        $file_data = file_get_contents($file_tmp);
        $mugshot = 'data:' . $file_type . ';base64,' . base64_encode($file_data);
    } else {
        $stmt = $conn->prepare("SELECT mugshot FROM characters WHERE id = ?");
        $stmt->execute([$character_id]);
        $mugshot = $stmt->fetchColumn();
    }

    if (!empty($character_id)) {
        try {
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
    <meta name="description" content="StoicCAD | Admin Dashboard">
    <meta name="keywords" content="StoicCAD, CAD, MDT, Admin Dashboard">
    <meta name="author" content="TheStoicBear | StoicCAD">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#3B82F6">
    <title>Admin Dashboard - All Characters</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body class="font-sans antialiased text-white bg-gray-900">
    
    <div class="flex min-h-screen">
        <!-- Toggle Button for Mobile -->
        <button id="toggleSidebarMobile" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded lg:hidden">&#9776;</button>
        
        <!-- Sidebar -->
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
                <h1 class="font-bold text-2xl sm:text-3xl mb-2">All Characters</h1>
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

            <!-- Search and Filter -->
            <form method="get" class="mb-5">
                <div class="flex flex-col sm:flex-row gap-4">
                    <input type="text" name="search" placeholder="Search by Name" value="<?php echo htmlspecialchars($search); ?>" class="px-4 py-2 border border-gray-600 rounded-md bg-gray-800 text-white w-full sm:w-1/2">
                    <select name="filter_dept" class="px-4 py-2 border border-gray-600 rounded-md bg-gray-800 text-white w-full sm:w-1/4">
                        <option value="">All Departments</option>
                        <option value="CIV" <?php echo $filter_dept == 'CIV' ? 'selected' : ''; ?>>CIV</option>
                        <option value="LSPD" <?php echo $filter_dept == 'LSPD' ? 'selected' : ''; ?>>LSPD</option>
                        <option value="SAHP" <?php echo $filter_dept == 'SAHP' ? 'selected' : ''; ?>>SAHP</option>
                        <option value="LSFD" <?php echo $filter_dept == 'LSFD' ? 'selected' : ''; ?>>LSFD</option>
                        <option value="BCSO" <?php echo $filter_dept == 'BCSO' ? 'selected' : ''; ?>>BCSO</option>
                        <option value="ADMIN" <?php echo $filter_dept == 'ADMIN' ? 'selected' : ''; ?>>ADMIN</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md">Filter</button>
                </div>
            </form>

            <!-- Characters Grid -->
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
                        <button onclick="openUpdateForm(<?php echo $character['id']; ?>)" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 mt-4 rounded-md">Update</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>

            <!-- Character Update Modal -->
            <div id="updateForm" class="fixed inset-0 hidden bg-gray-900 bg-opacity-75 flex items-center justify-center">
                <div class="bg-gray-800 p-8 rounded-lg max-w-md w-full">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="character_id" id="characterId" value="">
                        <h2 class="text-2xl font-bold mb-4">Update Character</h2>
                        <label for="last_name" class="block mb-2">Last Name:</label>
                        <input type="text" name="last_name" id="last_name" class="w-full mb-4 px-4 py-2 border border-gray-600 rounded-md bg-gray-700 text-white" required>
                        <label for="mugshot" class="block mb-2">Mugshot:</label>
                        <input type="file" name="mugshot" id="mugshot" class="w-full mb-4 bg-gray-700 text-white px-4 py-2 border border-gray-600 rounded-md">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md">Save Changes</button>
                        <button type="button" class="w-full mt-4 bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md" onclick="closeUpdateForm()">Cancel</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- JavaScript to handle sidebar and modal -->
    <script>
        const sidebar = document.getElementById("sidebar");
        const toggleSidebarDesktop = document.getElementById("toggleSidebarDesktop");
        const toggleSidebarMobile = document.getElementById("toggleSidebarMobile");
        const mainContent = document.getElementById("mainContent");
        const updateForm = document.getElementById("updateForm");
        const characterIdInput = document.getElementById("characterId");

        toggleSidebarDesktop.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
        });

        toggleSidebarMobile.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
        });

        function openUpdateForm(characterId) {
            characterIdInput.value = characterId;
            updateForm.classList.remove("hidden");
        }

        function closeUpdateForm() {
            updateForm.classList.add("hidden");
        }
    </script>
</body>
</html>
