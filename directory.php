<?php
/**
 * Student Directory - SQL Version for InfinityFree
 */

// Configuration and Initialization
$errors = [];
$success_message = '';
$entries_to_display = [];

// --- Database Configuration ---
$host = 'sql111.infinityfree.com';  
$db   = 'if0_40395051_student_directory';  
$user = 'if0_40395051';            
$pass = '3PWJ54EUazO'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Establish the connection
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Ensure the table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        major VARCHAR(100) NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    // If connection fails, stop and show the error
    die("Database connection failed. Please verify your credentials in phpMyAdmin. Error: " . $e->getMessage());
}

// --- 1. Handle Form Submission (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = trim($_POST['studentName'] ?? '');
    $major = trim($_POST['studentMajor'] ?? '');

    if (empty($name) || strlen($name) > 100) {
        $errors[] = "Name is required (max 100 chars).";
    }
    if (empty($major) || strlen($major) > 100) {
        $errors[] = "Major is required (max 100 chars).";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (name, major) VALUES (?, ?)");
            $stmt->execute([$name, $major]); 

            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1"); 
            exit(); 
            
        } catch (PDOException $e) {
            $errors[] = "Save failed: " . $e->getMessage();
        }
    }
}

if (isset($_GET['success'])) {
    $success_message = "Student record added successfully!";
}

// --- 2. Read Entries for Display ---
try {
    $stmt = $pdo->query("SELECT name, major, submitted_at FROM students ORDER BY name ASC");
    $entries_to_display = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Could not retrieve records: " . $e->getMessage();
}

$site_name = "Student Directory";
$page_title = "Student Directory";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | <?php echo $site_name; ?></title>
    <link rel="stylesheet" href="style1.css">
    <style>
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo $site_name; ?></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="calculator.php">Calculator</a></li>
                    <li><a href="directory.php" class="active">Student Directory</a></li>
                    <li><a href="guestbook.php">Guestbook</a></li>
                    <li><a href="books.php">Book Finder</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach($errors as $err) echo "<div>" . htmlspecialchars($err) . "</div>"; ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h2>Add New Student</h2>
            <form method="POST" action="directory.php" class="data-form">
                <div class="form-group">
                    <label for="studentName">Student Name:</label>
                    <input type="text" id="studentName" name="studentName" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="studentMajor">Major/Program:</label>
                    <input type="text" id="studentMajor" name="studentMajor" required maxlength="100">
                </div>
                <button type="submit" class="button primary">Save to Database</button>
            </form>
        </section>

        <section class="card">
            <h2>Current Student Directory</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Major/Program</th>
                            <th>Registered On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entries_to_display)): ?>
                            <tr><td colspan="3" class="text-center">The directory is currently empty.</td></tr>
                        <?php else: ?>
                            <?php foreach ($entries_to_display as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['major']); ?></td>
                                    <td><small><?php echo date("M d, Y", strtotime($row['submitted_at'])); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?>  Final Project Application | Designed by Mazharul Juniad</p>
        </div>
    </footer>
</body>
</html>