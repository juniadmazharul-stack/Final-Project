<?php
// Initialize variables
$errors = [];
$name = '';
$message = '';
$entries_to_display = [];

// --- Database Configuration via .env ---
$env_path = __DIR__ . '/.env';

if (!file_exists($env_path)) {
    die("Database connection failed: The .env file was not found in " . __DIR__);
}

$env = parse_ini_file($env_path);

$host    = $env['GUESTBOOK_DB_HOST'] ?? '';
$db      = $env['GUESTBOOK_DB_NAME'] ?? '';
$user    = $env['GUESTBOOK_DB_USER'] ?? '';
$pass    = $env['GUESTBOOK_DB_PASS'] ?? '';
$charset = 'utf8mb4';

// Explicitly include port=3306 to force TCP/IP over InfinityFree
$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Auto-create guestbook table if missing
    $pdo->exec("CREATE TABLE IF NOT EXISTS guestbook (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// --- 1. Handle Form Submission (POST) ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || strlen($name) > 100) {
        $errors[] = "Name is required and must be under 100 characters.";
    }
    if (empty($message) || strlen($message) > 500) {
        $errors[] = "Message is required and must be under 500 characters.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO guestbook (name, message) VALUES (?, ?)");
            $stmt->execute([$name, $message]); 

            header("Location: " . $_SERVER['PHP_SELF']); 
            exit();
        } catch (\PDOException $e) {
            $errors[] = "Database entry failed: " . $e->getMessage();
        }
    }
}

// --- 2. Read Entries for Display ---
try {
    $stmt = $pdo->query("SELECT id, name, message, submitted_at FROM guestbook ORDER BY submitted_at DESC");
    $entries_to_display = $stmt->fetchAll();
} catch (\PDOException $e) {
    $errors[] = "Could not retrieve guestbook entries: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guestbook | My First Web App</title>
    <link rel="stylesheet" href="style1.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

    <header>
        <h1>Guestbook</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="calculator.php">Calculator</a></li>
                <li><a href="directory.php">Student Directory</a></li>
                <li><a href="guestbook.php" class="active">Guestbook</a></li>
                <li><a href="books.php">Book Finder</a></li>
            </ul>
        </nav>
    </header>

    <main class="max-w-4xl mx-auto p-4 sm:p-6">
        <!-- Guestbook Form Section -->
        <div class="card bg-white p-6 md:p-8 rounded-xl mb-12 border-t-4 border-green-500 shadow-md">
            <h2 class="text-2xl font-semibold mb-6 text-gray-700">Sign the Guestbook</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <strong class="font-bold">Oops!</strong>
                    <span class="block sm:inline">Please correct the following errors:</span>
                    <ul class="list-disc list-inside mt-1">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>"
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500" 
                           maxlength="100" required>
                </div>
                
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Your Message</label>
                    <textarea id="message" name="message" rows="4" 
                              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500" 
                              maxlength="500" required><?php echo htmlspecialchars($message); ?></textarea>
                </div>

                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-300 ease-in-out shadow-md">
                    Submit Entry
                </button>
            </form>
        </div>
        
        <!-- Entries Display Section -->
        <div class="card bg-white p-6 md:p-8 rounded-xl border-t-4 border-gray-300 shadow-md">
            <h2 class="text-2xl font-semibold mb-6 text-gray-700">Recent Entries (<?php echo count($entries_to_display); ?>)</h2>
            
            <?php if (empty($entries_to_display)): ?>
                <p class="text-gray-500 italic text-center py-8">Be the first to sign the Guestbook!</p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($entries_to_display as $entry): ?>
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center border-b pb-2 mb-2">
                                <p class="font-bold text-lg text-green-700">
                                    <?php echo htmlspecialchars($entry['name']); ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-1 sm:mt-0">
                                    Signed on: <?php echo date("M d, Y", strtotime($entry['submitted_at'])); ?>
                                </p>
                            </div>
                            <p class="text-gray-800 whitespace-pre-wrap"><?php echo nl2br(htmlspecialchars($entry['message'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="text-center py-6 text-gray-500 text-sm">
        <p>&copy; <?php echo date("Y"); ?> Final Project Application | Designed by Mazharul Juniad</p>
    </footer>

</body>
</html>
