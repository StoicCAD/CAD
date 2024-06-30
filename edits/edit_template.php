<?php
session_start();

// Ensure $currentData is loaded properly or handle the condition appropriately
if (!isset($currentData)) {
    die("Data not loaded properly.");
}

// Include your database connection
require_once '../config/db.php';

// Fetch detailed user information including dept, rank, and badge number
$stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number, super FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include db.php for database connection and setup
    require_once '../db.php';  // Ensure your database connection file is correct

    $errors = [];
    $updateValues = [];

    // Assuming $fields is defined somewhere earlier in your code
    foreach ($fields as $field => $oldValue) {
        if (isset($_POST[$field])) {
            $updateValues[$field] = $_POST[$field];
        } else {
            $errors[] = "Missing value for field $field";
        }
    }

    if (empty($errors)) {
        try {
            // Prepare and execute SQL update statement
            $sql = "UPDATE {$type} SET " . join(', ', array_map(fn($field) => "$field = :$field", array_keys($updateValues))) . " WHERE id = :id";
            $stmt = $conn->prepare($sql);
            
            foreach ($updateValues as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            
            $stmt->bindValue(":id", $currentData['id']);
            $stmt->execute();
            echo "<p>Record updated successfully.</p>";
        } catch (PDOException $e) {
            echo "<p>Error updating record: " . $e->getMessage() . "</p>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<p>Error: $error</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Record</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-image: url('<?php echo $backgroundImage; ?>');
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
        .dropdown-menu {
            display: none;
            position: absolute;
            left: 0;
            z-index: 1000;
            width: 100%;
            background: #4b5563; /* Matching Tailwind's gray-700 */
            border-radius: 0 0 0.5rem 0.5rem;
        }
        form { 
            max-width: 600px; 
            margin: 20px auto; 
            padding: 20px; 
            background: #333; 
            border-radius: 8px;
        }
        label { display: block; margin-top: 10px; }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; }
        button { padding: 10px 20px; color: #fff; background-color: #007BFF; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded">&#9776;</button>
        
        <!-- Include Sidebar -->
        <?php include '../sidebar.php'; ?>
        
        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-5">
                <h1 class="font-bold text-3xl mb-2">Dashboard</h1>
            </header>
            <form action="" method="post">
                <h2>Edit <?php echo $type ?? 'Record'; ?></h2>
                <?php foreach ($fields as $field => $value): ?>
                    <label for="<?php echo $field; ?>"><?php echo ucfirst($field); ?>:</label>
                    <?php if ($field === 'description' || $field === 'content'): ?>
                        <textarea id="<?php echo $field; ?>" name="<?php echo $field; ?>"><?php echo htmlspecialchars($value); ?></textarea>
                    <?php else: ?>
                        <input type="<?php echo $field === 'email' ? 'email' : 'text'; ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo htmlspecialchars($value); ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
                <button type="submit">Update</button>
            </form>
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
