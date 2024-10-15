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



$char_id = $_GET['char_id'] ?? null; // Get char_id from URL

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $char_id) {
    $issued_by = $_POST['issued_by'];
    $issue_date = $_POST['issue_date'];
    $violation = $_POST['violation'];
    $fine_amount = $_POST['fine_amount'];

    // Update query to match the new database structure
    $stmt = $conn->prepare("INSERT INTO tickets (character_id, issued_by, issue_date, violation, fine_amount) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$char_id, $issued_by, $issue_date, $violation, $fine_amount])) {
        header("Location: ../tickets.php");
        exit;
    } else {
        $error = "Failed to add ticket.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Ticket</title>
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
        <?php include '../sidebar.php'; ?>
        <!-- Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-5">
            <h1 class="font-bold text-3xl mb-4">Add a New Ticket</h1>
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <form action="" method="post" class="space-y-4">
                    <div>
                        <label for="issued_by" class="block text-sm font-medium">Issued By:</label>
                        <input type="text" id="issued_by" name="issued_by" required class="mt-1 block w-full p-2 rounded bg-gray-700">
                    </div>
                    <div>
                        <label for="issue_date" class="block text-sm font-medium">Issue Date:</label>
                        <input type="datetime-local" id="issue_date" name="issue_date" required class="mt-1 block w-full p-2 rounded bg-gray-700">
                    </div>
                    <div>
                        <label for="violation" class="block text-sm font-medium">Violation:</label>
                        <textarea id="violation" name="violation" rows="4" required class="mt-1 block w-full p-2 rounded bg-gray-700"></textarea>
                    </div>
                    <div>
                        <label for="fine_amount" class="block text-sm font-medium">Fine Amount ($):</label>
                        <input type="number" id="fine_amount" name="fine_amount" required class="mt-1 block w-full p-2 rounded bg-gray-700">
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded">Submit</button>
                </form>
                <?php if (!empty($error)): ?>
                    <div class="mt-4 bg-red-600 p-4 rounded"><?php echo $error; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            var content = document.getElementById('mainContent');
            if (sidebar.classList.contains('hidden-sidebar')) {
                sidebar.classList.remove('hidden-sidebar');
                content.classList.remove('full-width');
            } else {
                sidebar.classList.add('hidden-sidebar');
                content.classList.add('full-width');
            }
        }
        document.addEventListener('DOMContentLoaded', function () {
            const issueDateInput = document.getElementById('issue_date');
            const now = new Date();

            // Adjust to local time and format it as required for input type="datetime-local"
            const tzOffset = now.getTimezoneOffset() * 60000; // Offset in milliseconds
            const localTime = new Date(now - tzOffset).toISOString().slice(0, 16); // Format for datetime-local

            issueDateInput.value = localTime; // Set the input's value to current time
        });
    </script>
</body>
</html>
