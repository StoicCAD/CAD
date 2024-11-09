<?php

require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

// Redirect based on department
if ($user['dept'] === 'CIV') {
    header("Location: civ/index.php");
    exit();
}

// Define status constants
const STATUS_OPEN = 'Open';
const STATUS_CLOSED = 'Closed';
const STATUS_ON_SCENE = 'On Scene';
const STATUS_ENROUTE = 'Enroute';

$allowedStatuses = [STATUS_OPEN, STATUS_CLOSED, STATUS_ON_SCENE, STATUS_ENROUTE];

$versionUrl = 'https://raw.githubusercontent.com/StoicCAD/CAD/version.txt'; // Use the raw content URL
$currentVersion = '1.2.7';

function getLatestVersion($url) {
    $version = @file_get_contents($url);
    if ($version === FALSE) {
        return false; // Error fetching version
    }
    return trim($version);
}

$latestVersion = getLatestVersion($versionUrl);
if ($latestVersion === false) {
    $versionMessage = 'Error fetching version information.';
} else {
    $isUpdateAvailable = version_compare($latestVersion, $currentVersion, '>');
    $versionMessage = $isUpdateAvailable 
        ? "A new version ($latestVersion) is available. Please <a href=\"https://github.com/StoicCAD/CAD/tree/standalone\" class=\"text-blue-700 underline\" target=\"_blank\">update now</a>!"
        : "Your version ($currentVersion) is up-to-date.";
}

$isAdmin = $user['rank'] == 'Admin';

// Update incident status
if (isset($_POST['update_incident_status'], $_POST['incident_id'], $_POST['status'])) {
    $status = $_POST['status'];
    $incident_id = (int)$_POST['incident_id'];
    
    if (in_array($status, $allowedStatuses)) {
        $stmt = $conn->prepare("UPDATE incidents SET status = ? WHERE id = ?");
        if (!$stmt->execute([$status, $incident_id])) {
            echo "Failed to update incident status.";
        }
    } else {
        echo "Invalid status update attempted.";
    }
}

// Update report status
if (isset($_POST['update_report_status'])) {
    $stmt = $conn->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
    $stmt->execute([$_POST['status'], $_POST['report_id']]);
}

// Fetch incidents with attached users
$incidents_stmt = $conn->prepare("
    SELECT incidents.*, GROUP_CONCAT(users.username) AS attached_usernames 
    FROM incidents 
    LEFT JOIN users ON FIND_IN_SET(users.id, incidents.attached_users) 
    GROUP BY incidents.id 
    ORDER BY incidents.created_at DESC
");
$incidents_stmt->execute();
$incidents = $incidents_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch reports
$reports_stmt = $conn->prepare("SELECT * FROM reports ORDER BY report_date DESC");
$reports_stmt->execute();
$reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    'incidents' => $incidents,
    'reports' => $reports,
];

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
// Attach user to incident
if (isset($_POST['attach_self'], $_POST['incident_id'])) {
    $incident_id = (int)$_POST['incident_id'];

    // Fetch the current attached users
    $stmt = $conn->prepare("SELECT attached_users FROM incidents WHERE id = ?");
    $stmt->execute([$incident_id]);
    $currentAttached = $stmt->fetchColumn();

    // If no users attached, start with an empty string
    $currentAttached = $currentAttached ? $currentAttached . ',' : '';

    // Append the current user ID
    $currentAttached .= $user_id;

    // Update the incident with the new list of attached users
    $stmt = $conn->prepare("UPDATE incidents SET attached_users = ? WHERE id = ?");
    if (!$stmt->execute([$currentAttached, $incident_id])) {
        echo "Failed to attach user.";
    }
}

// Fetch incidents with attached users
$incidents_stmt = $conn->prepare("
    SELECT incidents.*, GROUP_CONCAT(users.username) AS attached_usernames 
    FROM incidents 
    LEFT JOIN users ON FIND_IN_SET(users.id, incidents.attached_users) 
    GROUP BY incidents.id 
    ORDER BY incidents.created_at DESC
");
$incidents_stmt->execute();
$incidents = $incidents_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Dashboard - MDT</title>
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
            transition: transform 1.3s ease-out;
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
        /* Firefox */
        .sidebar, .content {
            scrollbar-width: thin; /* Scrollbar width */
            scrollbar-color: #4b5563 #1f2937; /* Thumb and track colors */
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
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10">
            <h1 class="font-bold text-3xl mb-2">Dashboard</h1>

            <!-- Display version update message -->
            <?php if ($isAdmin): ?>
                <div class="mb-5 bg-gray-900 p-4 rounded-lg shadow-md">
                    <div class="<?= $latestVersion === false ? 'bg-red-500' : ($isUpdateAvailable ? 'bg-yellow-500' : 'bg-green-500'); ?> p-4 rounded-lg text-center">
                        <p class="text-black font-semibold"><?= $versionMessage; ?></p>
                    </div>
                </div>
            <?php endif; ?>
<!-- Display incidents -->
<div id="content">
    <div class="bg-gray-900 p-6 rounded-lg shadow-md">
        <h2 class="text-xl mb-2">Active Calls</h2>
        <div id="activeCalls">
            <?php foreach ($incidents as $incident): ?>
                <div class="bg-gray-800 p-4 rounded mb-2">
                    <div class="flex flex-col gap"><div class="flex flex-row gap-2"><strong>Title: </strong><?php echo htmlspecialchars($incident['title']); ?></div><div class="flex flex-col gap"><div class="flex flex-row gap-2"><strong>Description:</strong> <?php echo htmlspecialchars($incident['description']); ?></div><em><strong>Status:</strong> <?php echo htmlspecialchars($incident['status']); ?></em></div></div>
                    <p><strong>Attached Users:</strong> <?php echo !empty($incident['attached_usernames']) ? htmlspecialchars($incident['attached_usernames']) : 'NONE ATTACHED'; ?></p>
                    <div class="flex flex-row gap-3 items-center mt-2">
                      <form method="post" class="">
                        <input type="hidden" name="incident_id" value="<?php echo $incident['id']; ?>">
                        <button type="submit" name="attach_self" class="px-3 py-1.5 bg-green-500 rounded hover:bg-green-700">Attach Self</button>
                      </form>
                      <form method="post" class="">
                          <input type="hidden" name="incident_id" value="<?php echo $incident['id']; ?>">
                          <select name="status" class="bg-gray-700 text-white p-2 rounded h-full">
                              <option value="Open" <?php echo $incident['status'] === "Open" ? "selected" : ""; ?>>Open</option>
                              <option value="Closed" <?php echo $incident['status'] === "Closed" ? "selected" : ""; ?>>Closed</option>
                              <option value="On Scene" <?php echo $incident['status'] === "On Scene" ? "selected" : ""; ?>>On Scene</option>
                              <option value="Enroute" <?php echo $incident['status'] === "Enroute" ? "selected" : ""; ?>>Enroute</option>
                          </select>
                          <button type="submit" name="update_incident_status" class="ml-2 px-3 py-1.5 bg-blue-500 rounded hover:bg-blue-700">Update Status</button>
                      </form>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if(count($incidents) < 1): ?>
              <p class="text-gray-400">No Active calls...</p>
            <?php endif; ?>
        </div>
    </div>
</div>
                <div class="bg-gray-900 mt-5 p-5 rounded-lg shadow-lg">
                    <h2 class="text-xl mb-2">Reports</h2>
                    <div id="reports">
                      <?php foreach ($reports as $report): ?>
                          <div class="bg-gray-800 p-4 rounded mb-2">
                              <div class="flex flex-col gap">
                                <p><strong>Title:</strong> <?php echo htmlspecialchars($report['report_title'] ?? 'Untitled Report'); ?></p>
                                <p><strong>Content:</strong> <?php echo htmlspecialchars($report['report_content'] ?? 'No content available.'); ?></p>
                                <p><em><strong>Status:</strong> <?php echo htmlspecialchars($report['status'] ?? 'Unknown'); ?></em></p>
                              </div>
                              <form method="post" class="mt-2">
                                  <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                  <select name="status" class="bg-gray-700 text-white p-2 rounded">
                                      <option value="Pending" <?php echo ($report['status'] ?? '') === "Pending" ? "selected" : ""; ?>>Pending</option>
                                      <option value="Reviewed" <?php echo ($report['status'] ?? '') === "Reviewed" ? "selected" : ""; ?>>Reviewed</option>
                                      <option value="Closed" <?php echo ($report['status'] ?? '') === "Closed" ? "selected" : ""; ?>>Closed</option>
                                  </select>
                                  <button type="submit" name="update_report_status" class="ml-2 px-3 py-1.5 bg-blue-500 rounded hover:bg-blue-700">Update Status</button>
                              </form>
                          </div>
                      <?php endforeach; ?>
                    </div> <!-- Placeholder for reports -->
                    <?php if(count($reports) < 1): ?>
                      <p class="text-gray-400">No reports...</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
