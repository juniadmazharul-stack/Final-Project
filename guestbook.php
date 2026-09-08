// --- Database Configuration (Render PostgreSQL Env Vars) ---
$host = getenv('GUESTBOOK_DB_HOST') ?: ($_ENV['GUESTBOOK_DB_HOST'] ?? null);
$db   = getenv('GUESTBOOK_DB_NAME') ?: ($_ENV['GUESTBOOK_DB_NAME'] ?? null);
$user = getenv('GUESTBOOK_DB_USER') ?: ($_ENV['GUESTBOOK_DB_USER'] ?? null);
$pass = getenv('GUESTBOOK_DB_PASS') ?: ($_ENV['GUESTBOOK_DB_PASS'] ?? null);

// Fall back to reading a local .env file if environment variables aren't set directly
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

// Use PostgreSQL TCP/IP connection over port 5432 with SSL required
$dsn = "pgsql:host=$host;port=5432;dbname=$db;sslmode=require";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Auto-create guestbook table if missing (PostgreSQL syntax using SERIAL)
    $pdo->exec("CREATE TABLE IF NOT EXISTS guestbook (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
