<?php
    // // Set headers and session configurations
    // header("Access-Control-Allow-Origin: *");
    // header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    // header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

    // $secure = true;
    // $httponly = true;
    // $samesite = 'None';
    // $lifetime = 600;

    // if (PHP_VERSION_ID < 70300) {
    //     session_set_cookie_params($lifetime, '/; samesite='.$samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
    // } else {
    //     session_set_cookie_params([
    //         'lifetime' => $lifetime,
    //         'path' => '/',
    //         'domain' => $_SERVER['HTTP_HOST'],
    //         'secure' => $secure,
    //         'httponly' => $httponly,
    //         'samesite' => $samesite
    //     ]);
    // }

    require_once 'config/db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit;
    }



    $search_query = '';
    $results = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_query'])) {
        $search_query = trim($_POST['search_query']);
    
        // Search characters based on the new schema
        $search_stmt = $conn->prepare("
            SELECT * FROM characters 
            WHERE first_name LIKE ? 
            OR last_name LIKE ? 
            OR dob LIKE ? 
            OR gender LIKE ? 
            OR mugshot LIKE ?
        ");

        $search_stmt->execute([
            "%$search_query%",
            "%$search_query%",
            "%$search_query%",
            "%$search_query%",
            "%$search_query%"
        ]);
        $results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);
    
        foreach ($results as $key => $character) {
            $char_id = $character['id'];
    
            // Fetch arrest records
            $arrest_stmt = $conn->prepare("SELECT * FROM arrests WHERE character_id = ?");
            $arrest_stmt->execute([$char_id]);
            $results[$key]['arrests'] = $arrest_stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Fetch ticket records
            $ticket_stmt = $conn->prepare("SELECT * FROM tickets WHERE character_id = ?");
            $ticket_stmt->execute([$char_id]);
            $results[$key]['tickets'] = $ticket_stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Fetch report records
            $report_stmt = $conn->prepare("SELECT * FROM reports WHERE character_id = ?");
            $report_stmt->execute([$char_id]);
            $results[$key]['reports'] = $report_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Search and fetch results
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_query'])) {
        $search_query = trim($_POST['search_query']);
        $search_stmt = $conn->prepare("
            SELECT * FROM characters 
            WHERE first_name LIKE ? 
            OR last_name LIKE ? 
            OR dob LIKE ? 
            OR gender LIKE ? 
            OR mugshot LIKE ?
        ");

        $search_stmt->execute([
            "%$search_query%",
            "%$search_query%",
            "%$search_query%",
            "%$search_query%",
            "%$search_query%"
        ]);
        $results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Default positions for the full name
    $fullNameTop = '180px';  // Change this in PHP as needed
    $fullNameLeft = '45%';  // Change this in PHP as needed
    $firstNameTop = '70px';
    $firstNameLeft = '-10%';

    $lastNameTop = '70px';
    $lastNameLeft = '10%';

    $dobTop = '127px';
    $dobLeft = '-10%';

    $genderTop = '127px';
    $genderLeft = '5%';

    $driversLicenseTop = '180px';
    $driversLicenseLeft = '30%';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Searches - MDT</title>
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
        .content {
            transition: margin-left 0.3s ease-out;
            margin-left: 256px; /* Initial margin to accommodate sidebar */
        }
        .full-width {
            margin-left: 0; /* Full width when sidebar is hidden */
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
        }
        .modal-content {
            position: relative;
            background-color: #fefefe00;
            margin: 15% auto;
            padding: 20px;
            width: 80%;
            max-width: 500px;
            height: 250px; /* Adjust based on your background image size */
        }
        .close {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 25px;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .idcard-image {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .idcard-image:hover {
            transform: scale(1.05);
        }
        .content-container {
            padding-top: 100px; /* Added top padding to move content down */
        }
        .modal-data {
            position: absolute;
            width: 100%;
            text-align: center;
            font-size: 14px; /* Adjusted font size for better fit */
            font-weight: bold;
            color: #fff; /* Black color for visibility on a light background */
        }
        .modal-dataN {
            position: absolute;
            width: 100%;
            text-align: center;
            font-size: 22px; /* Adjusted font size for better fit */
            font-weight: bold;
            color: #fff; /* Black color for visibility on a light background */
        }
        #mugshotImage {
            position: absolute;
            top: 55px;      /* Adjusted for vertical alignment */
            left: 40px;     /* Closer to the left edge of the modal */
            width: 100px;   /* Size as per your specification */
            height: 100px;  /* Size as per your specification */
            border-radius: 50%;
            transform: translate(0, 0); /* Removed the centering transform if not needed */
        }
        .signature {
            font-family: 'Great Vibes', cursive;
            font-size: 24px;
            color: #fff; /* Adjust as needed */
            position: absolute;
            left: 50%; /* Center horizontally */
            top: 150px; /* Position where it fits on your background */
            transform: translateX(-50%); /* Center align */
        }
        /* Close button */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
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
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
        <header class="mb-5">
                <h1 class="font-bold text-3xl mb-4">Police Search Database</h1>
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <form method="post" class="space-y-4">
                        <div>
                            <label for="search_query" class="block">Search for an individual:</label>
                            <input type="text" id="search_query" name="search_query" value="<?php echo htmlspecialchars($search_query); ?>" required class="w-full h-10 px-3 rounded bg-gray-700 focus:bg-gray-600 outline-none">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600 focus:outline-none">Search</button>
                    </form>
                </div>
                <div id="idModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <img src="https://raw.githubusercontent.com/jonassvensson4/jsfour-idcard/master/html/assets/images/idcard.png" style="width: 100%; height: auto;">
                        <img id="mugshotImage" src="" alt="Mugshot">
                        <div id="fullName" class="modal-data signature" style="top: <?php echo $fullNameTop; ?>; left: <?php echo $fullNameLeft; ?>;">Full Name:</div>
                        <div id="firstName" class="modal-dataN" style="top: <?php echo $firstNameTop; ?>; left: <?php echo $firstNameLeft; ?>;">First Name:</div>
                        <div id="lastName" class="modal-dataN" style="top: <?php echo $lastNameTop; ?>; left: <?php echo $lastNameLeft; ?>;">Last Name:</div>
                        <div id="dob" class="modal-data" style="top: <?php echo $dobTop; ?>; left: <?php echo $dobLeft; ?>;">DOB:</div>
                        <div id="gender" class="modal-data" style="top: <?php echo $genderTop; ?>; left: <?php echo $genderLeft; ?>;">Gender:</div>
                        <div id="driverslicense" class="modal-data" style="top: <?php echo $driversLicenseTop; ?>; left: <?php echo $driversLicenseLeft; ?>;">DL Status:</div>
                    </div>
                </div>

                <!-- Display Search Results -->
                <?php if (!empty($results)): ?>
                <div class="bg-gray-800 mt-4 p-6 rounded-lg shadow-lg space-y-4">
                    <h2 class="text-xl font-semibold">Search Results</h2>
                    <?php foreach ($results as $row): ?>
                        <div class="bg-gray-700 p-4 rounded-lg flex flex-col md:flex-row items-center">
                            <div class="idcard-image cursor-pointer" onclick="showModal('<?php echo htmlspecialchars($row['first_name']); ?>', '<?php echo htmlspecialchars($row['last_name']); ?>', '<?php echo htmlspecialchars($row['dob']); ?>', '<?php echo htmlspecialchars($row['gender']); ?>', '<?php echo htmlspecialchars($row['mugshot'] ?? 'config/NOMUG.png'); ?>', 'valid')">
                                <i class="fas fa-id-card fa-2x"></i>
                            </div>
                            <div class="ml-4 flex-1">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
<p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($row['dob'] ?? 'N/A'); ?></p>
<p><strong>Gender:</strong> <?php echo htmlspecialchars($row['gender'] ?? 'N/A'); ?></p>

                                <div class="flex flex-col md:flex-row space-x-0 md:space-x-4 mt-2">
                                    <a href="tickets.php" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600">Tickets</a>
                                    <a href="add/add_ticket.php?char_id=<?php echo htmlspecialchars($row['id']); ?>" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600">Add Ticket</a>
                                    <a href="add/add_arrest.php?char_id=<?php echo htmlspecialchars($row['id']); ?>" class="px-4 py-2 bg-red-500 rounded hover:bg-red-600">Add Arrest</a>
                                    <a href="arrests.php" class="px-4 py-2 bg-red-500 rounded hover:bg-red-600">Arrests</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
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
        function showModal(firstName, lastName, dob, gender, mugshot, dlstatus) {
            var modal = document.getElementById('idModal');
            var closeBtn = document.querySelector('.close');

            document.getElementById('fullName').textContent = firstName + ' ' + lastName;
            document.getElementById('firstName').textContent = firstName;
            document.getElementById('lastName').textContent = lastName;
            document.getElementById('dob').textContent = dob;
            document.getElementById('gender').textContent = gender;
            document.getElementById('driverslicense').textContent = dlstatus;

            var mugshotImage = document.getElementById('mugshotImage');
            mugshotImage.src = mugshot ? mugshot : 'path_to_default_image.jpg';

            modal.style.display = 'block';

            closeBtn.onclick = function () {
                modal.style.display = 'none';
            };

            window.onclick = function (event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            };
        }


    </script>
</body>
</html>