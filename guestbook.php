<?php
// Configuration and Initialization
$errors = [];
$name = '';
$message = '';
$success_message = '';
$entries_to_display = [];

// --- Database Configuration (FINAL LIVE HOST SETTINGS) ---
$host = 'sql111.infinityfree.com';  
$db   = 'if0_40395051_guessbook';  
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
} catch (\PDOException $e) {
     die("Database connection failed: " . $e->getMessage());
}

// --- 1. Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and Trim Input
    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    if (empty($name) || strlen($name) > 100) {
        $errors[] = "Name is required and must be under 100 characters.";
    }
    if (empty($message) || strlen($message) > 500) {
        $errors[] = "Message is required and must be under 500 characters.";
    }

    // Process Submission if no errors
    if (empty($errors)) {
        try {
            // Prepared statement for secure insertion (No SQL Injection risk)
            $stmt = $pdo->prepare("INSERT INTO guestbook_entries (name, message) VALUES (?, ?)");
            
            // Execute the statement
            $stmt->execute([$name, $message]); 

            // --- PRG FIX: REDIRECT AFTER SUCCESSFUL POST ---
            // Set a success flag in the session to display the message after redirect
            // Note: You would need session_start() at the very top for this, but for simplicity, 
            // we will just redirect and trust the entry is there.
            
            // We need to use header() to redirect to the current page.
            header("Location: " . $_SERVER['PHP_SELF']); 
            exit(); // Essential to stop script execution after redirect
            
        } catch (\PDOException $e) {
            $errors[] = "Database entry failed: " . $e->getMessage();
        }
    }
}

// --- 2. Read and Prepare Entries for Display ---
try {
    // Select all entries, ordering by submitted_at DESC (newest first)
    $stmt = $pdo->query("SELECT id, name, message, submitted_at FROM guestbook_entries ORDER BY submitted_at DESC");
    $entries_to_display = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    $errors[] = "Could not retrieve guestbook entries: " . $e->getMessage();
}

// --- 3. HTML Structure and Output (Styled with Tailwind CSS) ---
?>

</head>
 <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - My First Web App</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>

    <header>
        <h1>Guestbook</h1>
    </header>

    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="calculator.php">Calculator</a></li>
            <li><a href="directory.php">Student Directory</a></li>
            <li><a href="guestbook.php">Guestbook</a></li>
            <li><a href="books.php">Book Finder</a></li>
        </ul>
    </nav>
        </header>

        <!-- Guestbook Form Section -->
        <div class="card bg-white p-6 md:p-8 rounded-xl mb-12 border-t-4 border-green-500">
            <h2 class="text-2xl font-semibold mb-6 text-gray-700">Sign the GuestBook</h2>
            
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
            
            <!-- Note: The success message cannot be reliably displayed after a header redirect without using sessions. -->
            <!-- If you want the success message, let me know, and I can add sessions! -->

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
                        class="w-full sm:w-auto px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-300 ease-in-out transform hover:scale-[1.01] shadow-md">
                    Submit Entry
                </button>
            </form>
        </div>
        
        <!-- Entries Display Section -->
        <div class="card bg-white p-6 md:p-8 rounded-xl border-t-4 border-gray-300">
            <h2 class="text-2xl font-semibold mb-6 text-gray-700">Recent Entries (<?php echo count($entries_to_display); ?>)</h2>
            
            <?php if (empty($entries_to_display)): ?>
                <p class="text-gray-500 italic text-center py-8">Be the first to sign the GuestBook!</p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($entries_to_display as $entry): ?>
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <!-- Header (Name and Timestamp) -->
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center border-b pb-2 mb-2">
                                <p class="font-bold text-lg text-green-700">
                                    <!-- Use htmlspecialchars for safety when displaying fetched data -->
                                    <?php echo htmlspecialchars($entry['name']); ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-1 sm:mt-0">
                                    Signed on: <?php echo htmlspecialchars($entry['submitted_at']); ?>
                                </p>
                            </div>
                            <!-- Message Body -->
                            <!-- Use nl2br to convert stored newlines (\n) to HTML line breaks (<br>) for display -->
                            <p class="text-gray-800 whitespace-pre-wrap">
                                <?php echo nl2br(htmlspecialchars($entry['message'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
       <footer>
        <p>&copy; <?php echo date("Y"); ?> Final Project Application | Designed by Mazharul Juniad</p>
    </footer>
       </div>

</body>
    
</html>