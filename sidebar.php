<!-- Sidebar -->
<div id="sidebar" class="bg-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 overflow-y-auto sidebar">
    <div class="text-center">
        <img src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'default_avatar.png'); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
        <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars($user['username'] ?? 'Unknown User'); ?></h2>
        <p>
            <?php 
            echo htmlspecialchars($user['active_department'] ?? 'No Active Department'); ?>, 
            <?php echo htmlspecialchars($user['rank'] ?? 'No Rank'); ?><br>
            Badge #<?php echo htmlspecialchars($user['badge_number'] ?? 'No Badge'); ?>
        </p>

        <!-- Fetch user's departments -->
        <?php 
          $userDepartments = explode(',', $user['dept']); // Assuming 'dept' is a comma-separated list of departments
        ?>

        <!-- Change Active Department Form, shown only if no active department -->
        <?php if (empty($user['active_department'])): ?>
        <form action="settings.php" method="post" class="mt-5">
            <div class="mb-4">
                <label class="block text-white">Active Department:</label>
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
        <?php endif; ?>
    </div>

    <nav>
    <div class="flex flex-col items-center mt-5 pb-4 border-b mb-3">
        <!-- On-Duty / Off-Duty Button -->
        <button id="toggleDutyButton" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 focus:outline-none">
            <?php echo $user['online'] ? 'Off-Duty' : 'On-Duty'; ?>
        </button>

        <!-- Panic Button -->
        <button id="panicButton" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 focus:outline-none mt-2">
            <i class="fas fa-exclamation-triangle mr-2"></i> Panic
        </button>
    </div>
    
    <a href="<?php echo DOMAIN ?>/dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-home mr-2"></i>Dashboard</a>
    <a href="<?php echo DOMAIN ?>/incidents.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-exclamation-triangle mr-2"></i>Active Calls</a>
    <a href="<?php echo DOMAIN ?>/reports.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-file-alt mr-2"></i>Reports</a>
    <a href="<?php echo DOMAIN ?>/map.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-map-marked-alt mr-2"></i>Map</a>

    <!-- Dropdown for Searches -->
    <div class="relative dropdown">
        <a href="#" class="block py-2.5 px-4 rounded hover:bg-blue-600 cursor-pointer dropdown-toggle"><i class="fas fa-search mr-2"></i>Searches <i class="fa fa-caret-down"></i></a>
        <div class="dropdown-menu hidden">
            <a href="<?php echo DOMAIN ?>/people_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">People</a>
            <a href="<?php echo DOMAIN ?>/vehicle_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">Vehicles</a>
        </div>
    </div>
    <a href="<?php echo DOMAIN ?>/settings.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-cog mr-2"></i>Settings</a>

    <?php 
    $departments = explode(',', $user['dept']);
    if (in_array('CIV', $departments)): ?>
        <a href="<?php echo DOMAIN ?>/civ/index.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-car mr-2"></i>Civilian Dashboard</a>
    <?php endif; ?>

    <?php if ($user['rank'] == 'Admin'): ?>
        <a href="<?php echo DOMAIN ?>/admin-dash.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a>
    <?php endif; ?>

    <?php if ($user['active_department'] == 'Dispatch'): ?>
        <a href="<?php echo DOMAIN ?>/dispatch.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Dispatch Dashboard</a>
    <?php endif; ?>

    <?php if ($user['super'] == 1): ?>
        <a href="<?php echo DOMAIN ?>/super-dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Supervisor Dashboard</a>
    <?php endif; ?>
    
    <form method="post" action="<?php echo DOMAIN ?>/logout.php" class="mt-5">
        <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </button>
    </form>
</nav>
</div>

<!-- Script for toggling the dropdown -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    const toggleDutyButton = document.getElementById('toggleDutyButton');

    // JavaScript for toggling duty status
    toggleDutyButton.addEventListener('click', function() {
        fetch('toggle_duty.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: '<?php echo htmlspecialchars($user['id']); ?>' })
        })
        .then(response => {
            // Log the raw response text for debugging
            return response.text(); // Change to text() to see the raw response
        })
        .then(text => {
            console.log('Response:', text); // Log the response text
            return JSON.parse(text); // Then parse it as JSON
        })
        .then(data => {
            if (data.success) {
                toggleDutyButton.textContent = data.online ? 'Off-Duty' : 'On-Duty';
                toggleDutyButton.classList.toggle('bg-green-600', !data.online);
                toggleDutyButton.classList.toggle('bg-red-600', data.online);
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('An error occurred while sending the panic alert: ' + error.message); // Optional: Notify the user
        });
    });

    // Assuming you have your panic button set up to send the POST request
    document.getElementById('panicButton').addEventListener('click', function() {
        fetch('panic_alert.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                user_id: '<?php echo htmlspecialchars($user['id']); ?>', // Include user_id
                username: '<?php echo htmlspecialchars($user['username']); ?>', // Include username
                action: 'trigger_panic' // Include action
            })
        })
        .then(response => {
            console.log('Raw response:', response); // Log the raw response
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Log the response data
            if (data.success) {
                console.log('Messages to speak:', data.messages); // Debugging output
                data.messages.forEach(function(message) {
                    speak(message);
                });
            } else {
                console.error('Error sending panic alert:', data.message);
                alert('Error sending panic alert: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // Function to use the Web Speech API for TTS
    function speak(text) {
        if (text && typeof text === 'string') {
            console.log('Speaking text:', text); // Debugging output
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'en-US'; // Set language as needed
            speechSynthesis.speak(utterance);
        } else {
            console.warn('No valid text to speak.');
        }
    }

    // Dropdown toggle
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    dropdownToggle.addEventListener('click', function() {
        dropdownMenu.classList.toggle('hidden');
    });

    // Hide the dropdown if clicked outside
    document.addEventListener('click', function(event) {
        if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
});
</script>
