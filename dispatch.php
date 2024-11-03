<?php
require_once "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare(
    "SELECT username, avatar_url, dept, rank, badge_number, super FROM users WHERE id = ?"
);
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit();
}

// Redirect based on user department
if ($user["dept"] === "CIV") {
    header("Location: /civ/");
    exit();
} elseif ($user["dept"] === "Dispatch") {
    header("Location: dispatch.php");
    exit();
}

// Check if the user is an admin
$isAdmin = $user["rank"] == "Admin";

// Update incident status if requested
if (
    isset(
        $_POST["update_incident_status"],
        $_POST["incident_id"],
        $_POST["status"]
    )
) {
    $allowedStatuses = ["Open", "Closed", "On Scene", "Enroute"];
    $status = $_POST["status"];
    $incident_id = (int) $_POST["incident_id"];

    if (in_array($status, $allowedStatuses)) {
        $stmt = $conn->prepare("UPDATE incidents SET status = ? WHERE id = ?");
        $stmt->execute([$status, $incident_id]);
    } else {
        echo "Invalid status update attempted.";
    }
}

// Remove incident if requested
if (isset($_POST["remove_incident"], $_POST["remove_incident_id"])) {
    $remove_incident_id = (int) $_POST["remove_incident_id"];
    $stmt = $conn->prepare("DELETE FROM incidents WHERE id = ?");
    $stmt->execute([$remove_incident_id]);
}

// Fetch all incidents and reports
$incidents_stmt = $conn->prepare(
    "SELECT * FROM incidents ORDER BY created_at DESC"
);
$incidents_stmt->execute();
$incidents = $incidents_stmt->fetchAll(PDO::FETCH_ASSOC);

$reports_stmt = $conn->prepare(
    "SELECT * FROM reports ORDER BY report_date DESC"
);
$reports_stmt->execute();
$reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle search query
if (isset($_GET["query"]) && !empty($_GET["query"])) {
    $query = trim($_GET["query"]);
    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE username LIKE ? OR dept LIKE ? OR badge_number LIKE ?"
    );
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Logout functionality
if (isset($_POST["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Search functionality for characters
$search_query = "";
$results = [];

// Test with just one condition
$search_stmt = $conn->prepare(
    "SELECT * FROM characters WHERE first_name LIKE :query"
);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search_query"])) {
    $search_query = trim($_POST["search_query"]);

    // Prepare the search statement with separate placeholders
    $search_stmt = $conn->prepare("
        SELECT * FROM characters 
        WHERE first_name LIKE :first_name 
        OR last_name LIKE :last_name 
        OR dob LIKE :dob 
        OR gender LIKE :gender
    ");

    // Create the search term
    $searchTerm = "%$search_query%";

    // Execute the search with separate parameters
    try {
        $search_stmt->execute([
            ":first_name" => $searchTerm,
            ":last_name" => $searchTerm,
            ":dob" => $searchTerm,
            ":gender" => $searchTerm,
        ]);
    } catch (PDOException $e) {
        echo "SQL Error: " . htmlspecialchars($e->getMessage());
        return; // Stop execution if there is an error
    }

    $results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);

    // If results are empty
    if (empty($results)) {
        echo "No results found.";
    }

    foreach ($results as $key => $character) {
        $char_id = $character["id"];

        // Fetch arrest records
        $arrest_stmt = $conn->prepare(
            "SELECT * FROM arrests WHERE character_id = ?"
        );
        $arrest_stmt->execute([$char_id]);
        $results[$key]["arrests"] = $arrest_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch ticket records
        $ticket_stmt = $conn->prepare(
            "SELECT * FROM tickets WHERE character_id = ?"
        );
        $ticket_stmt->execute([$char_id]);
        $results[$key]["tickets"] = $ticket_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch report records
        $report_stmt = $conn->prepare(
            "SELECT * FROM reports WHERE character_id = ?"
        );
        $report_stmt->execute([$char_id]);
        $results[$key]["reports"] = $report_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Default positions for the full name (if needed later in your HTML)
$fullNameTop = "180px"; // Change this in PHP as needed
$fullNameLeft = "45%"; // Change this in PHP as needed
$firstNameTop = "70px";
$firstNameLeft = "-10%";
$lastNameTop = "70px";
$lastNameLeft = "10%";
$dobTop = "127px";
$dobLeft = "-10%";
$genderTop = "127px";
$genderLeft = "5%";
$driversLicenseTop = "180px";
$driversLicenseLeft = "30%";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatcher Dashboard - MDT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="scrollkit.css">
    <link rel="stylesheet" href="dispatch.css">

</head>
<body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded">&#9776;</button>
        
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 overflow-y-auto sidebar">
            <div class="text-center">
                <!-- Ensure values are not null before using htmlspecialchars -->
                <img src="<?php echo htmlspecialchars(
                    $user["avatar_url"] ?? "default_avatar.png"
                ); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
                <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars(
                    $user["username"] ?? "Unknown User"
                ); ?></h2>
                <p>
                    <?php echo htmlspecialchars(
                        $user["dept"] ?? "No Department"
                    ); ?>, 
                    <?php echo htmlspecialchars(
                        $user["user_id"] ?? "No Department"
                    ); ?>, 
                    <?php echo htmlspecialchars(
                        $user["rank"] ?? "No Rank"
                    ); ?><br>
                    Badge #<?php echo htmlspecialchars(
                        $user["badge_number"] ?? "No Badge"
                    ); ?>
                </p>
            </div>

            <nav>
                <div class="flex justify-center mt-5">
                    <button id="panicButton" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 focus:outline-none">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Panic
                    </button>
                </div>
                <a href="dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-home mr-2"></i>Dashboard</a>
                <a href="incidents.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-exclamation-triangle mr-2"></i>Active Calls</a>
                <a href="reports.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-file-alt mr-2"></i>Reports</a>
                <?php if ($isAdmin): ?>
                    <a href="admin.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-cogs mr-2"></i>Admin Panel</a>
                <?php endif; ?>
            </nav>

            <form method="post" class="mt-5">
                <button type="submit" name="logout" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 focus:outline-none">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </button>
            </form>
        </div>

        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-10">
                <!-- Dispatch Search Database Section -->
<div class="collapsible" onclick="toggleCollapse('searchDatabaseContent')">Dispatch Search Database</div>
<div id="searchDatabaseContent" class="collapsible-content <?php echo !empty(
    $results
) || !empty($search_query)
    ? "expanded"
    : ""; ?>">
    <?php if ($isAdmin): ?>
        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
            <form method="post" class="space-y-4">
                <div>
                    <label for="search_query" class="block">Search for an individual:</label>
                    <input type="text" id="search_query" name="search_query" value="<?php echo htmlspecialchars(
                        $search_query
                    ); ?>" required class="w-full h-10 px-3 rounded bg-gray-700 focus:bg-gray-600 outline-none">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600 focus:outline-none">Search</button>
            </form>
        </div>
        <div id="idModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <img src="https://raw.githubusercontent.com/jonassvensson4/jsfour-idcard/master/html/assets/images/idcard.png" style="width: 100%; height: auto; position: relative;">
                <img id="mugshotImage" src="" alt="Mugshot">
                <div id="fullName" class="modal-data signature" style="top: <?php echo $fullNameTop; ?>; left: <?php echo $fullNameLeft; ?>;">Full Name:</div>
                <div id="firstName" class="modal-dataN" style="top: <?php echo $firstNameTop; ?>; left: <?php echo $firstNameLeft; ?>;">First Name:</div>
                <div id="lastName" class="modal-dataN" style="top: <?php echo $lastNameTop; ?>; left: <?php echo $lastNameLeft; ?>;">Last Name:</div>
                <div id="dob" class="modal-data" style="top: <?php echo $dobTop; ?>; left: <?php echo $dobLeft; ?>;">DOB:</div>
                <div id="gender" class="modal-data" style="top: <?php echo $genderTop; ?>; left: <?php echo $genderLeft; ?>;">Gender:</div>
                <div id="driverslicense" class="modal-data" style="top: <?php echo $driversLicenseTop; ?>; left: <?php echo $driversLicenseLeft; ?>;">DL Status:</div>
            </div>
        </div>
        <?php if (!empty($message)): ?>
            <div class="mt-4 bg-gray-800 p-4 rounded-lg shadow-lg"><?php echo htmlspecialchars(
                $message
            ); ?></div>
        <?php endif; ?>
        <!-- Search Results -->
        <?php if (!empty($results)): ?>
            <div class="bg-gray-800 mt-4 p-6 rounded-lg shadow-lg space-y-4">
                <h2 class="text-xl font-semibold">Search Results</h2>
                <?php foreach ($results as $row): ?>
                    <div class="bg-gray-700 p-4 rounded-lg flex items-center">
                        <div class="idcard-image" onclick="showModal('<?php echo htmlspecialchars(
                            $row["first_name"]
                        ); ?>', '<?php echo htmlspecialchars(
    $row["last_name"]
); ?>', '<?php echo htmlspecialchars(
    $row["dob"]
); ?>', '<?php echo htmlspecialchars(
    $row["gender"]
); ?>', '<?php echo htmlspecialchars(
    $row["driverslicense"]
); ?>', '<?php echo htmlspecialchars($row["mugshot"]); ?>')">
                            <img src="https://raw.githubusercontent.com/jonassvensson4/jsfour-idcard/master/html/assets/images/idcard.png" alt="ID Card" style="width: 100px;">
                        </div>
                        <div class="ml-4">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars(
                                $row["first_name"] . " " . $row["last_name"]
                            ); ?></p>
                            <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars(
                                $row["dob"]
                            ); ?></p>
                            <p><strong>Gender:</strong> <?php echo htmlspecialchars(
                                $row["gender"]
                            ); ?></p>
                            <p><strong>DL Status:</strong> <?php echo htmlspecialchars(
                                $row["driverslicense"]
                            ); ?></p>
                        </div>
                        <div>
                            <a href="tickets.php" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600">Tickets</a>
                            <a href="add/add_ticket.php?char_id=<?php echo htmlspecialchars(
                                $row["id"]
                            ); ?>" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600">Add Ticket</a>
                            <a href="add/add_arrest.php?char_id=<?php echo htmlspecialchars(
                                $row["id"]
                            ); ?>" class="px-4 py-2 bg-red-500 rounded hover:bg-red-600">Arrests</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>


                <!-- Live Map Section -->
                <div class="collapsible" onclick="toggleCollapse('liveMapContent')">Live Map</div>
                <div id="liveMapContent" class="collapsible-content">
                    <div class="bg-gray-800 p-4 rounded-lg shadow-lg">
                        <iframe src="<?php echo htmlspecialchars(
                            $iframeUrl
                        ); ?>" class="map-container"></iframe>
                    </div>
                </div>

                <!-- Active Incidents Section -->
                <div class="collapsible" onclick="toggleCollapse('activeIncidentsContent')">Active Incidents</div>
                <div id="activeIncidentsContent" class="collapsible-content">
                    <?php if (count($incidents) > 0): ?>
                        <?php foreach ($incidents as $incident): ?>
                            <div class="bg-gray-800 p-4 rounded mb-4">
                                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars(
                                    $incident["title"]
                                ); ?></h3>
                                <p>Status: <?php echo htmlspecialchars(
                                    $incident["status"]
                                ); ?></p>
                                <p>Created At: <?php echo htmlspecialchars(
                                    $incident["created_at"]
                                ); ?></p>
                                
                                <!-- Update Incident Status Form -->
                                <form method="post" class="mt-2">
                                    <input type="hidden" name="incident_id" value="<?php echo htmlspecialchars(
                                        $incident["id"]
                                    ); ?>" />
                                    <select name="status" class="bg-gray-700 text-white p-2 rounded">
                                        <option value="Open" <?php echo $incident[
                                            "status"
                                        ] === "Open"
                                            ? "selected"
                                            : ""; ?>>Open</option>
                                        <option value="Closed" <?php echo $incident[
                                            "status"
                                        ] === "Closed"
                                            ? "selected"
                                            : ""; ?>>Closed</option>
                                        <option value="On Scene" <?php echo $incident[
                                            "status"
                                        ] === "On Scene"
                                            ? "selected"
                                            : ""; ?>>On Scene</option>
                                        <option value="Enroute" <?php echo $incident[
                                            "status"
                                        ] === "Enroute"
                                            ? "selected"
                                            : ""; ?>>Enroute</option>
                                    </select>
                                    <button type="submit" name="update_incident_status" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">Update Status</button>
                                </form>
                                
                                <!-- Remove Incident Form -->
                                <form method="post" class="mt-2">
                                    <input type="hidden" name="remove_incident_id" value="<?php echo htmlspecialchars(
                                        $incident["id"]
                                    ); ?>" />
                                    <button type="submit" name="remove_incident" class="mt-2 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-700">Remove Incident</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-400">No active incidents.</p>
                    <?php endif; ?>
                </div>


                <!-- Reports Section -->
                <div class="collapsible" onclick="toggleCollapse('reportsContent')">Reports</div>
                <div id="reportsContent" class="collapsible-content">
                    <?php if (count($reports) > 0): ?>
                        <?php foreach ($reports as $report): ?>
                            <div class="bg-gray-800 p-4 rounded mb-4">
                                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars(
                                    $report["report_title"]
                                ); ?></h3>
                                <p>Status: <?php echo htmlspecialchars(
                                    $report["status"]
                                ); ?></p>
                                <p>Date: <?php echo htmlspecialchars(
                                    $report["report_date"]
                                ); ?></p>
                                <!-- Update Report Status Form -->
                                <form method="post" class="mt-2">
                                    <input type="hidden" name="report_id" value="<?php echo htmlspecialchars(
                                        $report["report_id"]
                                    ); ?>" />
                                    <select name="status" class="bg-gray-700 text-white p-2 rounded">
                                        <option value="Pending" <?php echo $report[
                                            "status"
                                        ] === "Pending"
                                            ? "selected"
                                            : ""; ?>>Pending</option>
                                        <option value="Reviewed" <?php echo $report[
                                            "status"
                                        ] === "Reviewed"
                                            ? "selected"
                                            : ""; ?>>Reviewed</option>
                                        <option value="Closed" <?php echo $report[
                                            "status"
                                        ] === "Closed"
                                            ? "selected"
                                            : ""; ?>>Closed</option>
                                    </select>
                                    <button type="submit" name="update_report_status" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">Update Status</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-400">No reports available.</p>
                    <?php endif; ?>
                </div>
            </header>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            var mainContent = document.getElementById("mainContent");
            sidebar.classList.toggle("hidden-sidebar");
            mainContent.classList.toggle("full-width");
        }

        function toggleCollapse(contentId) {
    var content = document.getElementById(contentId);
    
    // Check if the content is currently displayed
    if (content.style.display === 'block') {
        content.style.display = 'none';
    } else {
        content.style.display = 'block';
    }

    // Keep the content open if search results or query are present
    const searchQueryInput = document.getElementById('search_query');
    if (searchQueryInput && searchQueryInput.value) {
        content.style.display = 'block'; // Ensure it's open when there is a query
    }

    // Additionally, check if there are results displayed
    if (document.querySelectorAll('.bg-gray-800.mt-4.p-6').length > 0) {
        content.style.display = 'block'; // Ensure it's open when there are results
    }
}


        // Function to show the modal with user details
        function showModal(firstname, lastname, dob, gender, driverslicense, mugshot) {
            var modal = document.getElementById("idModal");
            modal.style.display = "block";
            document.getElementById("firstName").innerHTML = firstname;
            document.getElementById("lastName").innerHTML = lastname;
            document.getElementById("dob").innerHTML = dob;
            document.getElementById("gender").innerHTML = gender;
            document.getElementById("driverslicense").innerHTML = driverslicense;
            document.getElementById("mugshotImage").src = mugshot;
        }

        // Get the modal element and close button
        var modal = document.getElementById("idModal");
        var closeModal = document.getElementsByClassName("close")[0];

        // Close the modal when the close button is clicked
        closeModal.onclick = function() {
            modal.style.display = "none";
        }

        // Close the modal when clicking outside of the modal content
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
