<?php
header('Content-Type: text/html; charset=utf-8');

// URL of the external site
$url = 'https://hosting.thestoicbear.dev/login.php';

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Execute cURL request
$content = curl_exec($ch);
curl_close($ch);

// Optionally, process the content here if needed

// Output the content
echo $content;
?>
