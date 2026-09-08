// --- Load Environment Variables (Render PostgreSQL Env Vars) ---
$host = getenv('DIRECTORY_DB_HOST') ?: ($_ENV['DIRECTORY_DB_HOST'] ?? null);
$db   = getenv('DIRECTORY_DB_NAME') ?: ($_ENV['DIRECTORY_DB_NAME'] ?? null);
$user = getenv('DIRECTORY_DB_USER') ?: ($_ENV['DIRECTORY_DB_USER'] ?? null);
$pass = getenv('DIRECTORY_DB_PASS') ?: ($_ENV['DIRECTORY_DB_PASS'] ?? null);

// Fall back to reading local .env if direct environment variables aren't set
if (!$host) {
    $env_path = __DIR__ . '/.env';
    if (file_exists($env_path) && is_readable($env_path)) {
        $env = parse_ini_file($env_path);
        $host = $env['DIRECTORY_DB_HOST'] ?? '';
        $db   = $env['DIRECTORY_DB_NAME'] ?? '';
        $user = $env['DIRECTORY_DB_USER'] ?? '';
        $pass = $env['DIRECTORY_DB_PASS'] ?? '';
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
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        major VARCHAR(100) NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    // If connection fails, stop and show the error
    die("Database connection failed. Please verify your credentials. Error: " . $e->getMessage());
}
