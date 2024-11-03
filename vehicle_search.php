<?php

    require_once 'config/db.php'; // Ensure db.php provides a valid PDO instance `$conn`

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // User verification
    $stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number, super FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit;
    }


    $search_query = '';
    $results = [];

    $vehicleHashes = [
        // Super Cars
        '1886268224' => 'Adder',
        '633712403' => 'Bullet',
        '-1291952903' => 'Cheetah',
        '-2120700196' => 'Cyclone',
        '-1232836011' => 'Entity XF',
        '1426219628' => 'Entity XXR',
        '418536135' => 'FMJ',
        '-1934452204' => 'GP1',
        '-1558399629' => 'Infernus',
        '-1232836011' => 'Itali GTB',
        '-2048333973' => 'Itali GTB Custom',
        '1034187331' => 'Nero',
        '1093792632' => 'Nero Custom',
        '1987142870' => 'Osiris',
        '2123327359' => 'Penetrator',
        '-1758137366' => 'Reaper',
        '-674927303' => 'SC1',
        '2072687711' => 'T20',
        '-376434238' => 'Tempesta',
        '272929391' => 'Turismo R',
        '-982130927' => 'Tyrant',
        '2067820283' => 'Vacca',
        '-1758137366' => 'Vagner',
        '-998177792' => 'Visione',
        '-1622444098' => 'Voltic',
        '989294410' => 'XA-21',
        '917809321' => 'Zentorno',

        // Sports Cars
        '-214906006' => '9F',
        '-1995326987' => '9F Cabrio',
        '1032823388' => 'Alpha',
        '767087018' => 'Banshee',
        '-1041692462' => 'Bestia GTS',
        '1274868363' => 'Buffalo',
        '-304802106' => 'Buffalo S',
        '2072687711' => 'Carbonizzare',
        '941800958' => 'Comet',
        '-2022483795' => 'Comet SR',
        '1561920505' => 'Coquette',
        '784565758' => 'Drift Tampa',
        '196747873' => 'Elegy RH8',
        '272822606' => 'Elegy Retro Custom',
        '767087018' => 'Feltzer',
        '544021352' => 'Furore GT',
        '499169875' => 'Fusilade',
        '-1205801634' => 'Futo',
        '2016857647' => 'Itali GTO',
        '-1752116803' => 'Jester',
        '-1297672541' => 'Jester (Racecar)',
        '544021352' => 'Khamelion',
        '-1372848492' => 'Kuruma',
        '410882957' => 'Kuruma (Armored)',
        '482197771' => 'Lynx',
        '1032823388' => 'Massacro',
        '-631760477' => 'Massacro (Racecar)',
        '-1660945322' => 'Neon',
        '3663206819' => 'Omnis',
        '-377465520' => 'Pariah',
        '384071873' => 'Penumbra',
        '1830407356' => 'Penumbra FF',
        '-377465520' => 'Raiden',
        '-674927303' => 'Rapid GT',
        '1737773231' => 'Rapid GT Classic',
        '719660200' => 'Sultan',
        '970598228' => 'Sultan RS',
        '-1758137366' => 'Surano',
        '1887331236' => 'Tropos Rallye',
        '1102544804' => 'Verlierer',

        // Sports Classics
        '-2095439403' => '190z',
        '159274291' => 'Ardent',
        '-1207431159' => 'Casco',
        '941800958' => 'Cheetah Classic',
        '1011753235' => 'Coquette Classic',
        '784565758' => 'Deluxo',
        '1051415893' => 'Dynasty',
        '-1566741232' => 'Fagaloa',
        '-1563766864' => 'GT500',
        '1909141499' => 'Infernus Classic',
        '886934177' => 'JB 700',
        '-1066334226' => 'JB 700W',
        '1051415893' => 'Mamba',
        '1107404867' => 'Manana',
        '-1660945322' => 'Michelli GT',
        '1830407356' => 'Monroe',
        '-433375717' => 'Nebula Turbo',
        '1046206681' => 'Peyote',
        '-2119578145' => 'Pigalle',
        '1078682497' => 'Rapid GT Classic',
        '1841130506' => 'Retinue',
        '-1255452397' => 'Retinue Mk II',
        '-2040426790' => 'Roosevelt',
        '1445631933' => 'Roosevelt Valor',
        '464687292' => 'Savestra',
        '903794909' => 'Stinger',
        '1545842587' => 'Stinger GT',
        '-2098947590' => 'Stirling GT',
        '970598228' => 'Swinger',
        '1862507111' => 'Torero',
        '1504306544' => 'Tornado',
        '-2033222435' => 'Tornado Custom',
        '-1797613329' => 'Tornado Rat Rod',
        '-982130927' => 'Viseris',
        '838982985' => 'Z-Type',
        '1284356689' => 'Z190',

        // Muscle Cars
        '499169875' => 'Dominator',
        '-986944621' => 'Dominator GTX',
        '-1267543371' => 'Dominator GTT',
        '723973206' => 'Dukes',
        '-326143852' => 'Ellie',
        '-2119578145' => 'Faction',
        '-1790546981' => 'Gauntlet',
        '-1800170043' => 'Gauntlet Hellfire',
        '349315417' => 'Hermes',
        '37348240' => 'Hotknife',
        '525509695' => 'Hustler',
        '525509695' => 'Impaler',
        '444994115' => 'Imperator',
        '3001042683' => 'Lurcher',
        '-2119578145' => 'Moonbeam',
        '-1943285540' => 'Nightshade',
        '1507916787' => 'Phoenix',
        '741586030' => 'Picador',
        '-2095439403' => 'Rat-Loader',
        '-667151410' => 'Rat-Truck',
        '-2096818938' => 'Ruiner',
        '1507916787' => 'Ruiner 2000',
        '-1685021548' => 'Sabre Turbo',
        '223258115' => 'Sabre Turbo Custom',
        '729783779' => 'Slamvan',
        '833469436' => 'Stallion',
        '972671128' => 'Tampa',
        '-1210451983' => 'Tulip',
        '2134119907' => 'Vamos',
        '1871995513' => 'Vigero',
        '1737773231' => 'Virgo',
        '16646064' => 'Yosemite',

        // Off-road
        '1645267888' => 'BF Injection',
        '1162065741' => 'Bifta',
        '-1237253773' => 'Blazer',
        '-2128233223' => 'Brawler',
        '-1661854193' => 'Dune',
        '534258863' => 'Dune Buggy',
        '-349601129' => 'Kalahari',
        '92612664' => 'Kamacho',
        '914654722' => 'Liberator',
        '-808457413' => 'Marshall',
        '1221512915' => 'Mesa',
        '914654722' => 'Monster',
        '2139203625' => 'Rancher XL',
        '3087195462' => 'Rebel',
        '-1207771834' => 'Sandking',
        '989381445' => 'Sandking XL',
        '-1189015600' => 'Trophy Truck',

        // SUVs
        '3486509883' => 'Baller',
        '142944341' => 'Cavalcade',
        '2006918058' => 'Dubsta',
        '-1137532101' => 'FQ 2',
        '92612664' => 'Granger',
        '1221512915' => 'Gresley',
        '-1543762099' => 'Habanero',
        '884422927' => 'Huntley S',
        '486987393' => 'Landstalker',
        '1269098716' => 'Mesa',
        '-808831384' => 'Novak',
        '2136773105' => 'Patriot',
        '3486509883' => 'Radius',
        '2136773105' => 'Rebla GTS',
        '1203490606' => 'Rocoto',
        '1221512915' => 'Seminole',
        '1337041428' => 'Serrano',
        '788045382' => 'XLS',

        // Compacts
        '1126868326' => 'Blista',
        '3612755468' => 'Brioso R/A',
        '-1130810103' => 'Dilettante',
        '-1177863319' => 'Issi',
        '-431692672' => 'Issi Classic',
        '931280609' => 'Panto',
        '841808271' => 'Prairie',
        '1475773103' => 'Rhapsody',
    ];


// Assume database connection is already established in $conn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_query'])) {
    $search_query = trim($_POST['search_query']);

    // Update SQL statement to fetch vehicle, owner details, and related tickets
    $search_stmt = $conn->prepare("
        SELECT v.id, v.plate, JSON_UNQUOTE(JSON_EXTRACT(v.properties, '$.model')) AS model_hash, 
               c.id AS owner_id, c.first_name, c.last_name, c.dob, c.driverslicense,
               t.ticket_id, t.issued_by, t.issue_date, t.violation, t.fine_amount
        FROM vehicles v
        JOIN characters c ON v.owner = c.id
        LEFT JOIN tickets t ON c.id = t.char_id
        WHERE v.plate LIKE :query
    ");
    
    $search_stmt->execute([':query' => "%$search_query%"]);
    $results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert model hashes to names using a predefined hash-to-name mapping array
    foreach ($results as $key => $row) {
        // Assuming $vehicleHashes is defined elsewhere
        $results[$key]['model_name'] = isset($vehicleHashes[$row['model_hash']]) ? $vehicleHashes[$row['model_hash']] : 'Unknown Model';
    }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Search - MDT</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="scrollkit.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
        <button onclick="toggleSidebar()" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded">&#9776;</button>
        
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-5">
                <h1 class="font-bold text-3xl mb-4">Vehicle Search</h1>
                <form id="searchForm" class="space-y-4 bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div>
                        <label for="search_query" class="block">Search by Plate, Make, or Model:</label>
                        <input type="text" id="search_query" name="search_query" placeholder="Enter search terms" required class="w-full h-10 px-3 rounded bg-gray-700 focus:bg-gray-600 outline-none">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600 focus:outline-none">Search</button>
                </form>
                <div id="results" class="mt-4 bg-gray-800 p-6 rounded-lg shadow-lg space-y-4"></div>
            </header>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#searchForm').on('submit', function(event) {
                event.preventDefault(); // Prevent form submission
                var query = $('#search_query').val(); // Get the query value
                
                $.ajax({
                    url: 'search.php', // Separate PHP file for handling the search
                    type: 'POST',
                    data: { search_query: query },
                    success: function(data) {
                        $('#results').html(data); // Update results div with the response
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });

        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            var mainContent = document.getElementById("mainContent");
            sidebar.classList.toggle("hidden-sidebar");
            mainContent.classList.toggle("full-width");
        }
    </script>
</body>
</html>