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

// Fetch user's departments
$userDepartments = explode(',', $user['dept']); // Assuming 'dept' is a comma-separated list of departments

// Redirection logic based on the department
if ($user['dept'] === 'CIV') {
    header("Location: general_dashboard.php"); // Redirect to general_dashboard.php if department is CIV
    exit();
}

if (isset($_POST['update_password'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the new password

    // Update password in the database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$password, $_SESSION['user_id']]);

    $message = "Password updated successfully.";
}

if (isset($_POST['update_department'])) {
    $active_department = $_POST['active_department']; // Get the selected active department

    // Update active department in the database
    $stmt = $conn->prepare("UPDATE users SET active_department = ? WHERE id = ?");
    $stmt->execute([$active_department, $_SESSION['user_id']]);

    $message = "Active department updated successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - MDT</title>
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
        <div class="flex-1 flex flex-col ml-64 p-10">
            <h1 class="font-bold text-3xl mb-2">Dashboard</h1>
            <div class="bg-gray-900 p-6 rounded-lg shadow-md">
                <h2 class="text-2xl mb-4">User Settings</h2>
                <h2 class="text-2xl mb-2">Update your password and active department below.</h2>
                <?php if (!empty($message)): ?>
                    <div class="bg-green-500 p-3 rounded"><?php echo $message; ?></div>
                <?php endif; ?>

                <!-- Change Password Form -->
                <form action="settings.php" method="post" class="mb-6">
                    <div class="mb-4">
                        <label class="block">New Password:</label>
                        <input type="password" name="password" required class="w-full mt-2 p-3 rounded bg-gray-700 text-white">
                    </div>
                    <button type="submit" name="update_password" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Password
                    </button>
                </form>

                <!-- Change Active Department Form -->
                <form action="settings.php" method="post">
                    <div class="mb-4">
                        <label class="block">Active Department:</label>
                        <select name="active_department" required class="w-full mt-2 p-3 rounded bg-gray-700 text-white">
                            <option value="">Select an active department</option>
                            <?php foreach ($userDepartments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo ($dept === $user['active_department']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_department" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Department
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        var mainContent = document.getElementById("mainContent");
        sidebar.classList.toggle("hidden-sidebar");
        mainContent.classList.toggle("full-width");
    }
</script>
</html>
