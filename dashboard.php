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
    header("Location: /civ/");
    exit();
}



$versionUrl = 'https://github.com/StoicCAD/CAD/blob/nat/version.txt';
$currentVersion = '1.0.0';

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
        ? "A new version ($latestVersion) is available. Please <a href=\"https://github.com/yourusername/yourrepo/releases\" class=\"text-blue-700 underline\" target=\"_blank\">update now</a>!"
        : "Your version ($currentVersion) is up-to-date.";
}

$isAdmin = $user['rank'] == 'Admin';

// Update incident status
if (isset($_POST['update_incident_status'], $_POST['incident_id'], $_POST['status'])) {
    $allowedStatuses = ['Open', 'Closed', 'On Scene', 'Enroute'];
    $status = $_POST['status'];
    $incident_id = (int)$_POST['incident_id'];
    
    if (in_array($status, $allowedStatuses)) {
        $stmt = $conn->prepare("UPDATE incidents SET status = ? WHERE id = ?");
        $stmt->execute([$status, $incident_id]);
    } else {
        echo "Invalid status update attempted.";
    }
}

// Update report status
if (isset($_POST['update_report_status'])) {
    $stmt = $conn->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
    $stmt->execute([$_POST['status'], $_POST['report_id']]);
}

// Fetch incidents
$incidents_stmt = $conn->prepare("SELECT * FROM incidents ORDER BY created_at DESC");
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

                            <!-- Add the content element here -->
                            <div id="content">
                        <div class="bg-gray-900 p-6 rounded-lg shadow-md">
                            <h2 class="text-xl mb-2">Active Calls</h2>
                            <div id="activeCalls">
                              <?php foreach($incidents as $incident) : ?>
                                <div class="bg-gray-800 p-4 rounded mb-2">
                                    <p><strong><?php echo $incident['title'] ?></strong>: <?php echo $incident['description'] ?> - <em>Status: <?php echo $incident['status'] ?></em></p>
                                    <form method="post">
                                        <input type="hidden" name="incident_id" value="<?php echo $incident['id'] ?>">
                                        <select name="status" class="bg-gray-700 text-white p-2 rounded">
                                            <option value="Open" <?php echo $incident['status'] === "Open" ? "selected" : "" ?>>Open</option>
                                            <option value="Closed" <?php echo $incident['status'] === "Closed" ? "selected" : "" ?>>Closed</option>
                                            <option value="On Scene" <?php echo $incident['status'] === "On Scene" ? "selected" : "" ?>>On Scene</option>
                                            <option value="Enroute" <?php echo $incident['status'] === "Enroute" ? "selected" : "" ?>>Enroute</option>
                                        </select>
                                        <button type="submit" name="update_incident_status" class="ml-2 px-3 py-1 bg-blue-500 rounded hover:bg-blue-700">Update Status</button>
                                    </form>
                                </div>
                              <?php endforeach; ?>
                            </div> <!-- Placeholder for incidents -->
                        </div>

                        <div class="bg-gray-900 mt-5 p-5 rounded-lg shadow-lg">
                            <h2 class="text-xl mb-2">Reports</h2>
                            <div id="reportsList"></div> <!-- Placeholder for reports -->
                        </div>
                    </div>
                </div>
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

    document.getElementById('panicButton').addEventListener('click', function() {
        if (confirm('Are you sure you want to send a PANIC alert? This will notify all users.')) {
            fetch('panic_alert.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'trigger_panic', username: '<?php echo $user['username']; ?>' })
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert('Panic alert sent successfully.');
                    } else {
                        alert('Error sending panic alert.');
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    alert('Error sending panic alert. Invalid response format.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending panic alert.');
            });
        }
    });

    let storedData = JSON.parse(`<?php echo json_encode($response) ?>`);
    function fetchUpdates() {
        fetch('fetch_updates.php')
            .then(response => response.json())
            .then(data => {
                // Update incidents
                if(JSON.stringify(data) === JSON.stringify(storedData)) return;
                storedData = data;
                const activeCallsElement = document.getElementById('activeCalls');
                activeCallsElement.innerHTML = ''; // Clear previous content
                data.incidents.forEach(incident => {
                    activeCallsElement.innerHTML += `
                        <div class="bg-gray-800 p-4 rounded mb-2">
                            <p><strong>${incident.title}</strong>: ${incident.description} - <em>Status: ${incident.status}</em></p>
                            <form method="post">
                                <input type="hidden" name="incident_id" value="${incident.id}">
                                <select name="status" class="bg-gray-700 text-white p-2 rounded">
                                    <option value="Open" ${incident.status == 'Open' ? 'selected' : ''}>Open</option>
                                    <option value="Closed" ${incident.status == 'Closed' ? 'selected' : ''}>Closed</option>
                                    <option value="On Scene" ${incident.status == 'On Scene' ? 'selected' : ''}>On Scene</option>
                                    <option value="Enroute" ${incident.status == 'Enroute' ? 'selected' : ''}>Enroute</option>
                                </select>
                                <button type="submit" name="update_incident_status" class="ml-2 px-3 py-1 bg-blue-500 rounded hover:bg-blue-700">Update Status</button>
                            </form>
                        </div>
                    `;
                });

                // Update reports
                const reportsListElement = document.getElementById('reportsList');
                reportsListElement.innerHTML = ''; // Clear previous content
                data.reports.forEach(report => {
                    reportsListElement.innerHTML += `
                        <div class="bg-gray-800 p-4 rounded mb-2">
                            <p><strong>${report.author}</strong>: ${report.report_content} - <em>Status: ${report.status}</em></p>
                            <form method="post">
                                <input type="hidden" name="report_id" value="${report.report_id}">
                                <select name="status" class="bg-gray-700 text-white p-2 rounded">
                                    <option value="Open" ${report.status == 'Open' ? 'selected' : ''}>Open</option>
                                    <option value="Closed" ${report.status == 'Closed' ? 'selected' : ''}>Closed</option>
                                    <option value="On Scene" ${report.status == 'On Scene' ? 'selected' : ''}>On Scene</option>
                                    <option value="Enroute" ${report.status == 'Enroute' ? 'selected' : ''}>Enroute</option>
                                </select>
                                <button type="submit" name="update_report_status" class="ml-2 px-3 py-1 bg-blue-500 rounded hover:bg-blue-700">Update Status</button>
                            </form>
                        </div>
                    `;
                });
            })
            .catch(error => console.error('Error fetching updates:', error));
    }

    // Fetch updates every 5 seconds
    setInterval(fetchUpdates, 5000);

</script>

</body>
</html>
