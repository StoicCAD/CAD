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

// Retrieve all characters based on Discord ID
$characters = [];
if (isset($user['discord_id'])) {
    $discord_id = $user['discord_id'];
    
    // Fetch all character details
    $stmt = $conn->prepare("SELECT id, last_name, first_name FROM characters WHERE discord = ?");
    $stmt->execute([$discord_id]);
    $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initialize questions
$questions = [
    ['question' => 'What is the speed limit in a residential area?', 'options' => ['25 mph', '30 mph', '35 mph', '40 mph'], 'correct' => '25 mph'],
    ['question' => 'Who has the right of way at a four-way stop?', 'options' => ['The first car to stop', 'The car on the right', 'The car on the left', 'The fastest car'], 'correct' => 'The first car to stop'],
    ['question' => 'What should you do when approaching a school bus with its red lights flashing?', 'options' => ['Slow down and proceed with caution', 'Stop until the lights stop flashing', 'Speed up to pass quickly', 'Honk your horn to alert the bus driver'], 'correct' => 'Stop until the lights stop flashing'],
    ['question' => 'What does a yellow traffic light indicate?', 'options' => ['Prepare to stop', 'Go faster', 'Continue without change', 'Turn left'], 'correct' => 'Prepare to stop'],
    ['question' => 'When are you required to use your headlights?', 'options' => ['During daylight hours', 'In foggy conditions', 'When driving in a parking lot', 'Only at night'], 'correct' => 'In foggy conditions'],
    ['question' => 'What is the legal blood alcohol concentration (BAC) limit for drivers over 21?', 'options' => ['0.08%', '0.05%', '0.10%', '0.02%'], 'correct' => '0.08%'],
    ['question' => 'What should you do if you experience a tire blowout while driving?', 'options' => ['Steer firmly and gently apply the brakes', 'Accelerate to regain control', 'Immediately steer to the shoulder', 'Pump the brakes rapidly'], 'correct' => 'Steer firmly and gently apply the brakes'],
    ['question' => 'How far away from a crosswalk should you stop if there are pedestrians crossing?', 'options' => ['At least 10 feet', 'At least 20 feet', 'At least 30 feet', 'At least 5 feet'], 'correct' => 'At least 20 feet'],
    ['question' => 'What is the purpose of a roundabout?', 'options' => ['To improve traffic flow', 'To reduce traffic speed', 'To allow more lane changes', 'To provide additional lanes'], 'correct' => 'To improve traffic flow'],
    ['question' => 'When is it permissible to use a cell phone while driving?', 'options' => ['When using a hands-free device', 'When stopped at a red light', 'When driving on a highway', 'When the phone is secured in a holder'], 'correct' => 'When using a hands-free device'],
    ['question' => 'What should you do if an emergency vehicle with flashing lights approaches?', 'options' => ['Pull over to the right and stop', 'Continue driving at the same speed', 'Speed up to clear the lane', 'Move to the left side of the road'], 'correct' => 'Pull over to the right and stop'],
    ['question' => 'What does a solid white line on the roadway indicate?', 'options' => ['You may change lanes if it is safe', 'Lane changes are discouraged but allowed', 'You must not change lanes', 'It indicates a parking lane'], 'correct' => 'You must not change lanes'],
    ['question' => 'What is the proper way to make a left turn at an intersection?', 'options' => ['Turn from the leftmost lane', 'Turn from the rightmost lane', 'Turn from the center lane', 'Turn from any lane'], 'correct' => 'Turn from the leftmost lane'],
    ['question' => 'What is the minimum following distance you should maintain behind another vehicle?', 'options' => ['1 second', '2 seconds', '3 seconds', '4 seconds'], 'correct' => '3 seconds'],
    ['question' => 'What does a “No U-turn” sign mean?', 'options' => ['U-turns are prohibited', 'You must make a U-turn', 'U-turns are allowed with caution', 'U-turns are permitted only during daylight'], 'correct' => 'U-turns are prohibited']
];

// Handle DMV test form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correctAnswers = 0;
    $totalQuestions = count($questions); // Dynamically count the number of questions
    $passingScore = round($totalQuestions * 0.8);

    foreach ($questions as $index => $question) {
        $selectedAnswer = $_POST['question_' . $index] ?? '';
        if ($selectedAnswer === $question['correct']) {
            $correctAnswers++;
        }
    }

    $passed = $correctAnswers >= $passingScore;

    if ($passed) {
        // Update the driver's license status of the selected character
        $characterId = $_POST['character_id'] ?? null;
        if ($characterId) {
            $stmt = $conn->prepare("UPDATE characters SET driverslicense = 'valid' WHERE id = ?");
            $stmt->execute([$characterId]);
        }
        
        $_SESSION['dmv_status'] = 'valid';
    } else {
        $_SESSION['dmv_status'] = 'invalid';
    }

    $_SESSION['test_result'] = [
        'correct_answers' => $correctAnswers,
        'total_questions' => $totalQuestions,
        'passed' => $passed
    ];

    header("Location: dmv_test_result.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMV License Test - MDT</title>
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
            transition: margin-left 0.9s ease-out;
            margin-left: 140px; /* match sidebar width when visible */
        }
        .full-width {
            margin-left: 0; /* full width when sidebar is hidden */
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
        <button id="toggleSidebarMobile" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded hidden lg:block">&#9776;</button>
        
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar bg-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 overflow-y-auto lg:static lg:w-64 hidden sm:block">
            <div class="text-center">
                <img src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'default_avatar.png'); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
                <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars($user['username'] ?? 'Unknown User'); ?></h2>
                <p><?php echo htmlspecialchars($user['dept'] ?? 'No Department'); ?>, <?php echo htmlspecialchars($user['rank'] ?? 'No Rank'); ?></p>
            </div>
            <div class="mt-6 space-y-2">
                <a href="../civ/" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-600 rounded">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
                <a href="../new-character/" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-600 rounded">
                    <i class="fas fa-user-plus mr-2"></i>
                    New Character
                </a>
            </div>
            <form method="post" action="logout.php" class="mt-5">
                <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </button>
            </form>
        </div>

        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-0 sm:ml-64 p-4 sm:p-10">
            <header class="mb-5 flex justify-between items-center">
                <h1 class="font-bold text-2xl sm:text-3xl mb-2">DMV Drivers Test</h1>
                <div class="flex items-center">
                    <button id="toggleSidebarDesktop" class="text-gray-300 block lg:hidden">
                        <i class="fas fa-bars fa-2x"></i>
                    </button>
                </div>
            </header>
        <section>
            <form method="POST" class="space-y-4">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="mb-6">
                        <p class="mb-2 font-semibold text-gray-300"><?php echo htmlspecialchars($question['question']); ?></p>
                        <?php foreach ($question['options'] as $key => $option): ?>
                            <label class="block">
                                <input type="radio" name="question_<?php echo $index; ?>" value="<?php echo htmlspecialchars($option); ?>" class="mr-2">
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <div class="mb-6">
                    <label for="character_id" class="block mb-2 font-semibold text-gray-300">Select Character:</label>
                    <select id="character_id" name="character_id" class="block w-full p-2 bg-gray-800 border border-gray-700 rounded">
                    <?php foreach ($characters as $character): ?>
                        <option value="<?php echo htmlspecialchars($character['id']); ?>">
                            <?php echo htmlspecialchars($character['last_name'] . ', ' . $character['first_name']); ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <button type="submit" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Submit
                    </button>
                </div>
            </form>
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

        function openEditModal(id, lastName) {
            document.getElementById('character_id').value = id;
            document.getElementById('last_name').value = lastName;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        document.getElementById('toggleSidebarMobile').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('hidden-sidebar');
            document.getElementById('mainContent').classList.toggle('full-width');
        });

        document.getElementById('toggleSidebarDesktop').addEventListener('click', function () {
            var sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('hidden')) {
                sidebar.classList.remove('hidden');
                document.getElementById('mainContent').classList.add('ml-64');
            } else {
                sidebar.classList.add('hidden');
                document.getElementById('mainContent').classList.remove('ml-64');
            }
        });
    </script>
</body>
</html>
