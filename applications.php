<?php

require_once 'config/db.php'; // Ensure this file contains the correct database connection setup
require_once 'config/config.php'; // Ensure this file contains the correct configuration settings
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}



// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
if (isset($_POST['submitApplication'])) {
    // Collecting form data
    $department = $_POST['department'];
    $fullName = $_POST['fullName'];
    $emailAddress = $_POST['emailAddress'];
    $discordName = $_POST['discordName'];
    $previousExperience = $_POST['previousExperience'];
    $motivation = $_POST['motivation'];


    // Prepare the data to send to Discord webhook
    $webhookurl = DISCORD_WEBHOOK_URL;  // Use the webhook URL from the config file

    $json_data = json_encode([
        "content" => "",
        "embeds" => [
            [
                "title" => "New Application for $department",
                "description" => "**$fullName** has applied to join **$department**.",
                "color" => hexdec("3366ff"),
                "fields" => [
                    ["name" => "Full Name", "value" => $fullName, "inline" => true],
                    ["name" => "Email Address", "value" => $emailAddress, "inline" => true],
                    ["name" => "Discord Name", "value" => $discordName, "inline" => true],
                    ["name" => "Previous Experience", "value" => $previousExperience, "inline" => false],
                    ["name" => "Motivation", "value" => $motivation, "inline" => false]
                ]
            ]
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

    // Sending data to Discord via cURL
    $ch = curl_init($webhookurl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    // Check for errors
    if(curl_error($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        echo 'Application submitted successfully.';
    }
    curl_close($ch);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIV Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="scrollkit.css">
    <style>
      .dropdown-menu {
          display: none;
          position: absolute;
          left: 0;
          z-index: 1000;
          width: 100%;
          background: #4b5563; /* Matching Tailwind's gray-700 */
          border-radius: 0 0 0.5rem 0.5rem;
      }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-gray-800 w-64 p-5 fixed inset-y-0 left-0">
            <div class="text-center">
                <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
                <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars($user['username']); ?></h2>
                <p>Dept: <?php echo htmlspecialchars($user['dept']); ?></p>
            </div>
            <nav class="mt-10">
                <a href="general_dashboard.php" class="block py-2.5 px-4 rounded bg-blue-600 hover:bg-blue-700"><i class="fas fa-home mr-2"></i>Home</a>
                <?php if($user['super'] === 1 && $user['rank'] === "Admin") { ?>
                    <a href="dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-home mr-2"></i>Dashboard</a>
                    <a href="incidents.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-exclamation-triangle mr-2"></i>Active Calls</a>
                    <a href="reports.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-file-alt mr-2"></i>Reports</a>
                    <a href="map.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-map-marked-alt mr-2"></i>Map</a>
                    <!-- Dropdown for Searches -->
                    <div class="relative dropdown">
                        <a href="#" class="block py-2.5 px-4 rounded hover:bg-blue-600 cursor-pointer"><i class="fas fa-search mr-2"></i>Searches <i class="fa fa-caret-down"></i></a>
                        <div class="dropdown-menu">
                            <a href="people_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">People</a>
                            <a href="vehicle_search.php" class="block py-2 px-4 text-sm text-white rounded-[0_0_0.5rem_0.5rem] hover:bg-gray-600">Vehicles</a>
                        </div>
                    </div>

                    <a href="settings.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-cog mr-2"></i>Settings</a>
                    <?php if ($user['rank'] == 'Admin'): ?>
                        <a href="admin-dash.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a>
                    <?php endif; ?>

                    <?php if ($user['super'] == 1): ?>
                        <a href="super-dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Supervisor Dashboard</a>
                    <?php endif; ?>
                <?php } ?>
            </nav>
                <form method="post" action="logout.php" class="mt-5">
                    <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
        </div>


            <!-- Content Area in the HTML File -->
            <div class="flex-1 ml-64 p-10">
                <h1 class="text-3xl font-bold">Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="mt-3">Apply to join one of our dedicated departments and serve your community.</p>

                <form action="" method="post" class="mt-8 bg-gray-800 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold mb-4 text-white">Department Application Form</h2>

                    <div class="mb-4">
                        <label for="department" class="block text-sm font-medium text-gray-300">Department:</label>
                        <select name="department" id="department" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none">
                            <?php foreach ($departments as $value => $label) : ?>
                                <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="fullName" class="block text-sm font-medium text-gray-300">Full Name:</label>
                        <input type="text" id="fullName" name="fullName" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none" placeholder="John Doe">
                    </div>

                    <div class="mb-4">
                        <label for="emailAddress" class="block text-sm font-medium text-gray-300">Email Address:</label>
                        <input type="email" id="emailAddress" name="emailAddress" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none" placeholder="johndoe@example.com">
                    </div>

                    <div class="mb-4">
                        <label for="discordName" class="block text-sm font-medium text-gray-300">Discord Name:</label>
                        <input type="text" id="discordName" name="discordName" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none" placeholder="Your Discord username#0000">
                    </div>

                    <div class="mb-4">
                        <label for="previousExperience" class="block text-sm font-medium text-gray-300">Previous Experience:</label>
                        <textarea id="previousExperience" name="previousExperience" rows="4" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none" placeholder="Describe any previous related experience..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="motivation" class="block text-sm font-medium text-gray-300">Why do you want to join?</label>
                        <textarea id="motivation" name="motivation" rows="4" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none" placeholder="Your motivation for joining the department..."></textarea>
                    </div>

                    <button type="submit" name="submitApplication" class="py-2 px-4 bg-blue-700 hover:bg-blue-800 rounded text-white font-bold">Submit Application</button>
                </form>
            </div>
        </div>
    <script>
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
