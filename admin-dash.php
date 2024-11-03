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


// Handle POST requests for updates and deletes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        switch ($action) {
            case 'delete_user':
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                break;
            case 'edit_user':
                header("Location: edits/edit_user.php?user_id=" . $_POST['user_id']);
                break;
            case 'edit_incident':
                header("Location: edits/edit_incident.php?incident_id=" . $_POST['incident_id']);
                break;
            case 'edit_report':
                header("Location: edits/edit_report.php?report_id=" . $_POST['report_id']);
                break;
            case 'edit_ticket':
                header("Location: edits/edit_ticket.php?ticket_id=" . $_POST['ticket_id']);
                break;
            case 'edit_arrest':
                header("Location: edits/edit_arrest.php?arrest_id=" . $_POST['arrest_id']);
                break;
        }
        exit();
    }
}

// Fetch all data to display
$users = $conn->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
$incidents = $conn->query("SELECT * FROM incidents")->fetchAll(PDO::FETCH_ASSOC);
$reports = $conn->query("SELECT * FROM reports")->fetchAll(PDO::FETCH_ASSOC);
$tickets = $conn->query("SELECT * FROM tickets")->fetchAll(PDO::FETCH_ASSOC);
$arrests = $conn->query("SELECT * FROM arrests")->fetchAll(PDO::FETCH_ASSOC);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MDT</title>
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
</style>
        
</head>
<body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded">&#9776;</button>
        
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        <!-- Main Content -->
        <div class="flex-1 flex flex-col ml-64 p-10">
            <header class="mb-5">
                <h1>Admin Dashboard</h1>
            </header>

            <!-- Users Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold">Users</h2>
                <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    ID
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Username
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Avatar
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Department
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Rank
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Badge Number
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr class="bg-gray-800 hover:bg-gray-700">
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo $user['id']; ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($user['username'] ?? ''); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($user['email'] ?? ''); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <img src="<?php echo htmlspecialchars($user['avatar_url'] ?? ''); ?>" alt="Avatar" class="h-8 w-8 rounded-full">
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($user['dept'] ?? ''); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($user['rank'] ?? ''); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($user['badge_number'] ?? ''); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <a href="edits/edit_user.php?user_id=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                    <form method="post" action="" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="action" value="delete_user" class="text-red-500 hover:text-red-400 ml-4">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Incidents Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold">Active Calls</h2>
                <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    ID
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Title
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidents as $incident): ?>
                            <tr class="bg-gray-800 hover:bg-gray-700">
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo $incident['id']; ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($incident['title']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($incident['description']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($incident['status']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <a href="edits/edit_incident.php?incident_id=<?php echo $incident['id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                    <form method="post" action="" style="display:inline;">
                                        <input type="hidden" name="incident_id" value="<?php echo $incident['id']; ?>">
                                        <button type="submit" name="action" value="delete_incident" class="text-red-500 hover:text-red-400 ml-4">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
                <!-- Reports Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold">Reports</h2>
                    <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Author
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Content
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                <tr class="bg-gray-800 hover:bg-gray-700">
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo $report['report_id']; ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($report['author']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($report['report_content']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($report['status']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <a href="edits/edit_report.php?report_id=<?php echo $report['report_id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                            <button type="submit" name="action" value="delete_report" class="text-red-500 hover:text-red-400 ml-4">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tickets Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold">Tickets</h2>
                    <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Violation
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Fine Amount
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Issued By
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                <tr class="bg-gray-800 hover:bg-gray-700">
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo $ticket['ticket_id']; ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($ticket['violation']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($ticket['fine_amount']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($ticket['issued_by']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <a href="edits/edit_ticket.php?ticket_id=<?php echo $ticket['ticket_id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
                                            <button type="submit" name="action" value="delete_ticket" class="text-red-500 hover:text-red-400 ml-4">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    </div>
                <!-- Arrests Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold">Arrests</h2>
                    <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Charges
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Bail Amount
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Arrested By
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($arrests as $arrest): ?>
                                <tr class="bg-gray-800 hover:bg-gray-700">
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo $arrest['arrest_id']; ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($arrest['charges']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($arrest['bail_amount']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($arrest['officer_name']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <a href="edits/edit_arrest.php?arrest_id=<?php echo $arrest['arrest_id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="arrest_id" value="<?php echo $arrest['arrest_id']; ?>">
                                            <button type="submit" name="action" value="delete_arrest" class="text-red-500 hover:text-red-400 ml-4">Delete</button>
                                        </form>
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