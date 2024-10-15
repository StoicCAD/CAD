<?php

    require_once '../config/db.php';

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
    if ($user['active_department'] === 'CIV') {
        header("Location: general_dashboard.php"); // Redirect to general_dashboard.php if department is CIV
        exit();
    }
    

    // Get char_id from URL or POST
    $char_id = $_GET['char_id'] ?? ($_POST['char_id'] ?? null);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $char_id) {
        $officer_name = $_POST['officer_name'];
        $arrest_date = $_POST['arrest_date'];
        $charges = $_POST['charges'];
        $bail_amount = $_POST['bail_amount'];

        $stmt = $conn->prepare("INSERT INTO arrests (character_id, officer_name, arrest_date, charges, bail_amount) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$char_id, $officer_name, $arrest_date, $charges, $bail_amount])) {
            header("Location: ../arrests.php");
            exit;
        } else {
            $error = "Failed to add arrest.";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Arrest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
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
            transition: margin-left 0.3s ease-out;
            margin-left: 16rem; /* match sidebar width when visible */
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
        <?php include '../sidebar.php'; ?>
        <!-- Content -->
        <div id="mainContent" class="flex-1 flex flex-col p-10 content">
            <header class="mb-5">
                <h1 class="font-bold text-3xl mb-4">Add a New Arrest</h1>
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <form action="" method="post" class="space-y-4">
                    <div>
                        <label for="officer_name" class="block text-sm font-medium">Officer Name:</label>
                        <input type="text" id="officer_name" name="officer_name" required class="mt-1 block w-full p-2 rounded bg-gray-700">
                    </div>
                    <div>
                        <label for="arrest_date" class="block text-sm font-medium">Arrest Date:</label>
                        <input type="datetime-local" id="arrest_date" name="arrest_date" required class="mt-1 block w-full p-2 rounded bg-gray-700">
                    </div>
                    <div>
                        <label for="charges" class="block text-sm font-medium">Charges:</label>
                        <textarea id="charges" name="charges" rows="4" required class="mt-1 block w-full p-2 rounded bg-gray-700"></textarea>
                    </div>
                    <div>
                        <label for="bail_amount" class="block text-sm font-medium">Bail Amount ($):</label>
                        <input type="number" id="bail_amount" name="bail_amount" required class="mt-1 block w-full p-2 rounded bg-gray-700">
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded">Submit</button>

                    </form>
                    <?php if (isset($error)): ?>
                        <p class="mt-4 bg-red-600 p-4 rounded"><?= htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                </div>
            </header>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('mainContent');
            sidebar.classList.toggle('hidden-sidebar');
            content.classList.toggle('full-width');
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Sidebar hover effect for dropdown menu
            const dropdown = document.querySelector('.dropdown');
            dropdown.addEventListener('mouseover', function () {
                dropdown.querySelector('.dropdown-menu').style.display = 'block';
            });
            dropdown.addEventListener('mouseout', function () {
                dropdown.querySelector('.dropdown-menu').style.display = 'none';
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
    // Auto-fill the current date and time in the "Arrest Date" field
    const arrestDateInput = document.getElementById('arrest_date');
    const now = new Date();
    
    // Adjust the timezone offset
    const tzOffset = now.getTimezoneOffset() * 60000; // Offset in milliseconds
    const localTime = new Date(now - tzOffset).toISOString().slice(0, 16); // Correct format for datetime-local

    arrestDateInput.value = localTime;
});

    </script>
</body>
</html>
