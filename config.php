<?php
/**
 * config.php - Master Database Configuration
 * IMPORTANT: This version has the password removed for GitHub safety.
 */

// --- Database Configuration ---
$host = 'sql111.infinityfree.com';  
$user = 'if0_40395051';            
$pass = ''; // REMOVED FOR SECURITY - Add your password only on your live server
$charset = 'utf8mb4';

// Database names
$db_directory = 'if0_40395051_student_directory'; 
$db_guestbook = 'if0_40395051_guessbook';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $dsn_dir = "mysql:host=$host;dbname=$db_directory;charset=$charset";
    $pdo = new PDO($dsn_dir, $user, $pass, $options);

    $dsn_gb = "mysql:host=$host;dbname=$db_guestbook;charset=$charset";
    $pdo_gb = new PDO($dsn_gb, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed. (Check your config.php password)");
}
?>