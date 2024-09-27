<!-- Sidebar -->
<div id="sidebar" class="bg-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 overflow-y-auto sidebar">
    <div class="text-center">
        <!-- Ensure values are not null before using htmlspecialchars -->
        <img src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'default_avatar.png'); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
        <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars($user['username'] ?? 'Unknown User'); ?></h2>
        <p>
            <?php echo htmlspecialchars($user['active_department'] ?? 'No Active Department'); ?>, 
            <?php echo htmlspecialchars($user['user_id'] ?? 'No Department'); ?>, 
            <?php echo htmlspecialchars($user['rank'] ?? 'No Rank'); ?><br>
            Badge #<?php echo htmlspecialchars($user['badge_number'] ?? 'No Badge'); ?>
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
        <a href="map.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-map-marked-alt mr-2"></i>Map</a>

        <!-- Dropdown for Searches -->
        <div class="relative dropdown">
            <a href="#" class="block py-2.5 px-4 rounded hover:bg-blue-600 cursor-pointer"><i class="fas fa-search mr-2"></i>Searches <i class="fa fa-caret-down"></i></a>
            <div class="dropdown-menu">
                <a href="people_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">People</a>
                <a href="vehicle_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">Vehicles</a>
            </div>
        </div>

        <a href="settings.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-cog mr-2"></i>Settings</a>

        <?php 
        // Handle multiple departments
        $departments = explode(',', $user['dept']); // Split the departments string into an array

        // Check if user is in 'CIV' department
        if (in_array('CIV', $departments)): ?>
            <a href="civ/" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-car mr-2"></i>Civilian Dashboard</a>
        <?php endif; ?>

        <?php if ($user['rank'] == 'Admin'): ?>
            <a href="admin-dash.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a>
        <?php endif; ?>

        <?php if ($user['active_department'] == 'Dispatch'): ?>
            <a href="dispatch.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Dispatch Dashboard</a>
        <?php endif; ?>


        <?php if ($user['super'] == 1): ?>
            <a href="super-dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Supervisor Dashboard</a>
        <?php endif; ?>
        
        
        <form method="post" action="logout.php" class="mt-5">
            <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </button>
        </form>
    </nav>
</div>
