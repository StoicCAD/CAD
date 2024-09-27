<?php
// Set headers and session configurations
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Configure secure session cookies
$secure = true;
$httponly = true;
$samesite = 'None';
$lifetime = 600;

if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params($lifetime, '/; samesite='.$samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
} else {
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}

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
    header("Location: general_dashboard.php");
    exit();
}



// Check for char_id in the URL or POST and handle appropriately
$char_id = $_GET['char_id'] ?? $_POST['char_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $char_id) {
    $issued_by = $_POST['issued_by'];
    $issue_date = $_POST['issue_date'];
    $violation = $_POST['violation'];
    $fine_amount = $_POST['fine_amount'];

    $stmt = $conn->prepare("INSERT INTO tickets (char_id, issued_by, issue_date, violation, fine_amount) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$char_id, $issued_by, $issue_date, $violation, $fine_amount])) {
        header("Location: tickets.php");
        exit;
    } else {
        $error = "Failed to add ticket.";
    }
}

// Fetch tickets
$stmt = $conn->prepare("SELECT * FROM tickets ORDER BY issue_date DESC");
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tickets</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css">
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
        <h1 class="font-bold text-3xl mb-4">Tickets Overview</h1>
        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Ticket ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Character ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Issued By
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Issue Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Violation
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Fine Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-800 divide-y divide-gray-700">
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-300"><?php echo htmlspecialchars($ticket['ticket_id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?php echo htmlspecialchars($ticket['character_id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?php echo htmlspecialchars($ticket['issued_by']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?php echo htmlspecialchars($ticket['issue_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?php echo htmlspecialchars($ticket['violation']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?php echo htmlspecialchars($ticket['fine_amount']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </header>
        </div>
    </div>
    
    <!-- Script for Sidebar Toggle -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            if (sidebar.classList.contains('hidden-sidebar')) {
                sidebar.classList.remove('hidden-sidebar');
                mainContent.classList.remove('full-width');
            } else {
                sidebar.classList.add('hidden-sidebar');
                mainContent.classList.add('full-width');
            }
        }
        
        // Dropdown menu functionality
        document.querySelectorAll('.dropdown').forEach(function(dropdown) {
            dropdown.addEventListener('click', function() {
                const menu = dropdown.querySelector('.dropdown-menu');
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });
    </script>
</body>
</html>
