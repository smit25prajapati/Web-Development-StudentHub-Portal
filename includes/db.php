<?php
// Update these settings to match your local XAMPP MySQL setup.
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'studenthub_portal';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    http_response_code(500);
    exit('Database connection failed. Check includes/db.php and import database.sql.');
}
