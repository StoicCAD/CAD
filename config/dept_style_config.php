<?php



// dept_style_config.php
// Array holding the department-specific styles for backgrounds and logos
$deptStyles = [
    'LSPD' => [
        'backgroundImage' => 'https://img.thestoicbear.dev/images/Stoic-2024-05-01_23-45-27-6632d417f14f6.png',
        'logo' => 'https://img.thestoicbear.dev/images/Stoic-2024-04-24_08-30-12-6628c31481bbd.png'
    ],
    'BCSO' => [
        'backgroundImage' => 'https://img.thestoicbear.dev/images/sheriff_background.jpg',
        'logo' => 'https://img.thestoicbear.dev/images/sheriff_logo.png'
    ],
    'SASP' => [
        'backgroundImage' => 'https://img.thestoicbear.dev/images/Stoic-2024-05-01_23-45-55-6632d433f1f5c.png',
        'logo' => 'https://img.thestoicbear.dev/images/fbi_logo.png'
    ],
    'LSFD' => [
        'backgroundImage' => 'https://img.thestoicbear.dev/images/Stoic-2024-05-01_23-46-34-6632d45a71026.png',
        'logo' => 'https://img.thestoicbear.dev/images/fire_dept_logo.png'
    ],
    'SWAT' => [
        'backgroundImage' => 'https://img.thestoicbear.dev/images/Stoic-2024-05-01_23-46-19-6632d44bc1263.png',
        'logo' => 'https://img.thestoicbear.dev/images/fire_dept_logo.png'
    ],
    'CIV' => [
        'backgroundImage' => 'https://img.thestoicbear.dev/images/Stoic-2024-05-01_23-46-46-6632d466c4746.png',
        'logo' => 'https://img.thestoicbear.dev/images/default_logo.png'
    ]
];

// Determine the user's department and set styles
$dept = $user['dept'] ?? 'default';
$backgroundImage = $deptStyles[$dept]['backgroundImage'];
$logoImage = $deptStyles[$dept]['logo'];
// Set default style if department not matched
$deptKey = $user['dept'] ?? 'default';
$deptStyle = $deptStyles[$deptKey] ?? $deptStyles['default'];

?>
