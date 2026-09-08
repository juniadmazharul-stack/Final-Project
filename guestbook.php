<?php
/**
 * Guestbook - PostgreSQL Version for Render
 */

// Configuration and Initialization
$errors = [];
$success_message = '';
$entries_to_display = [];

// --- Load Environment Variables (Render Env Vars with .env Fallback) ---
$host = getenv('GUESTBOOK_DB_HOST') ?: ($_ENV['GUESTBOOK_DB_HOST'] ?? null);
$db   = getenv('GUESTBOOK_DB_NAME') ?: ($_ENV['GUESTBOOK_DB_NAME'] ?? null);
$user = getenv('GUESTBOOK_DB_USER') ?: ($_ENV['GUESTBOOK_DB_USER'] ?? null);
$pass = getenv('GUESTBOOK_DB_PASS') ?: ($_ENV['GUESTBOOK_DB_PASS'] ?? null);

// Fall back to reading local .env if direct environment variables aren't set
if (!$host) {
    $env_path = __DIR__ . '/.env';
    if (file_exists($env_path) && is_readable($env_path)) {
        $env = parse_ini_file($env_path);
        $host = $env['GUESTBOOK_DB_HOST'] ?? '';
        $db   = $env['GUESTBOOK_DB_NAME'] ?? '';
        $user = $env['GUESTBOOK_DB_USER'] ?? '';
        $pass = $env['GUESTBOOK_DB_PASS'] ?? '';
    } else {
        die("Database configuration error: Missing environment variables or readable .env file.");
    }
}

$dsn = "pgsql:host=$host;port=5432;dbname=$db;sslmode=require";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Establish the PostgreSQL connection
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Ensure the table exists using PostgreSQL syntax
    $pdo->exec("CREATE TABLE IF NOT EXISTS guestbook (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    // If connection fails, stop and show the error
    die("Database connection failed. Please verify your credentials. Error: " . $e->getMessage());
}

// --- 1. Handle Form Submission (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = trim($_POST['guestName'] ?? '');
    $message = trim($_POST['guestMessage'] ?? '');

    if (empty($name) || strlen($name) > 100) {
        $errors[] = "Name is required (max 100 chars).";
    }
    if (empty($message)) {
        $errors[] = "Message is required.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO guestbook (name, message) VALUES (?, ?)");
            $stmt->execute([$name, $message]); 

            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1"); 
            exit(); 
            
        } catch (PDOException $e) {
            $errors[] = "Save failed: " . $e->getMessage();
        }
    }
}

if (isset($_GET['success'])) {
    $success_message = "Guestbook entry added successfully!";
}

// --- 2. Read Entries for Display ---
try {
    $stmt = $pdo->query("SELECT name, message, submitted_at FROM guestbook ORDER BY submitted_at DESC");
    $entries_to_display = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Could not retrieve records: " . $e->getMessage();
}

$site_name = "Guestbook";
$page_title = "Guestbook";
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
                    <li><a href="directory.php">Student Directory</a></li>
                    <li><a href="guestbook.php" class="active">Guestbook</a></li>
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
            <h2>Sign the Guestbook</h2>
            <form method="POST" action="guestbook.php" class="data-form">
                <div class="form-group">
                    <label for="guestName">Your Name:</label>
                    <input type="text" id="guestName" name="guestName" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="guestMessage">Your Message:</label>
                    <textarea id="guestMessage" name="guestMessage" rows="4" required></textarea>
                </div>
                <button type="submit" class="button primary">Submit Entry</button>
            </form>
        </section>

        <section class="card">
            <h2>Guestbook Entries</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Message</th>
                            <th>Signed On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entries_to_display)): ?>
                            <tr><td colspan="3" class="text-center">The guestbook is currently empty. Be the first to sign!</td></tr>
                        <?php else: ?>
                            <?php foreach ($entries_to_display as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                                    <td><small><?php echo date("M d, Y H:i", strtotime($row['submitted_at'])); ?></small></td>
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
            <p>&copy; <?php echo date("Y"); ?> Final Project Application | Designed by Mazharul Juniad</p>
        </div>
    </footer>
</body>
</html>
