<?php

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number, super, discord_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

// Fetch test result from session
$testResult = $_SESSION['test_result'] ?? null;

// Clear the session data after showing results
unset($_SESSION['test_result']);

// Fetch driver’s license status
$licenseStatus = "Not Issued";
if (isset($user['discord_id'])) {
    $discord_id = $user['discord_id'];
    
    // Fetch all character details
    $stmt = $conn->prepare("SELECT id, last_name, driverslicense FROM characters WHERE discord = ?");
    $stmt->execute([$discord_id]);
    $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check the license status of the selected character
    foreach ($characters as $character) {
        if ($character['driverslicense'] === 'valid') {
            $licenseStatus = 'Valid';
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMV Test Result - MDT</title>
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
            transition: margin-left 0.3s ease-out;
            margin-left: 16rem; /* Adjust to match sidebar width when visible */
        }
        .full-width {
            margin-left: 0; /* Full width when sidebar is hidden */
        }
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
                position: fixed;
            }
            .sidebar-button {
                display: none;
            }
            .content {
                margin-left: 16rem; /* Adjust to match sidebar width */
            }
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
    <button onclick="toggleSidebar()" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded lg:hidden">&#9776;</button>
    
    <div id="sidebar" class="bg-gray-800 text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 overflow-y-auto sidebar lg:static lg:transform-none">
        <div class="text-center">
            <img src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'default_avatar.png'); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
            <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars($user['username'] ?? 'Unknown User'); ?></h2>
            <p>
                <?php echo htmlspecialchars($user['dept'] ?? 'No Department'); ?>, 
                <?php echo htmlspecialchars($user['rank'] ?? 'No Rank'); ?><br>
            </p>
        </div>

        <nav>
            <a href="../civ/" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-home mr-2"></i>Dashboard</a>
            <a href="dmv_test.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-id-card-alt mr-2"></i>DMV Test</a>
            <!-- Add more links here as needed -->
            <form method="post" action="logout.php" class="mt-5">
                <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </button>
            </form>
        </nav>
    </div>
    <div id="mainContent" class="flex-1 flex flex-col p-10 content">
        <header class="mb-5">
            <h1 class="font-bold text-3xl mb-2">DMV Test Result</h1>
        </header>

        <section>
            <?php if ($testResult): ?>
                <div class="bg-gray-800 p-6 rounded-lg">
                    <h2 class="text-xl font-bold mb-4">Test Results</h2>
                    <p class="mb-2">Correct Answers: <?php echo htmlspecialchars($testResult['correct_answers']); ?></p>
                    <p class="mb-4">Total Questions: <?php echo htmlspecialchars($testResult['total_questions']); ?></p>
                    <p class="mb-4">Status: 
                        <?php if ($testResult['passed']): ?>
                            <span class="text-green-500 font-bold">Passed</span>
                        <?php else: ?>
                            <span class="text-red-500 font-bold">Failed</span>
                        <?php endif; ?>
                    </p>
                    <p class="mb-4">Driver's License Status: <?php echo htmlspecialchars($licenseStatus); ?></p>
                </div>
            <?php else: ?>
                <div class="bg-gray-800 p-6 rounded-lg">
                    <p>No test result found. Please take the test first.</p>
                </div>
            <?php endif; ?>
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
</script>
</body>
</html>
