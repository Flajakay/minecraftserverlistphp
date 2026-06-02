<?php

use App\Core\System\Database;
use App\Core\System\MigrationRunner;

// Load Composer's autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables from .env
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}

// Manually load global helper functions
require_once dirname(__DIR__) . '/app/Core/Support/Helpers.php';

// Retrieve database configuration
$config = require dirname(__DIR__) . '/config/app.php';
$db = $config['db'];

$host = $db['host'];
$user = $db['username'];
$pass = $db['password'];
$databaseName = $db['database']; // This should resolve to serverlist_test due to phpunit.xml configuration

try {
    // 0. Clear pre-existing database query logs for this run
    $queryLogFile = dirname(__DIR__) . '/storage/logs/test_queries.log';
    if (file_exists($queryLogFile)) {
        @unlink($queryLogFile);
    }

    // 1. Connect to MySQL server without dbname to create it if it doesn't exist
    $dsnWithoutDb = "mysql:host={$host};charset=utf8mb4";
    $pdo = new PDO($dsnWithoutDb, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 2. Re-create the test database to ensure a pristine slate
    $pdo->exec("DROP DATABASE IF EXISTS `{$databaseName}`");
    $pdo->exec("CREATE DATABASE `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$databaseName}`");

    // 3. Import core database schema
    $schemaPath = dirname(__DIR__) . '/database/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new RuntimeException("Schema file not found at {$schemaPath}");
    }
    
    $schema = file_get_contents($schemaPath);
    // Split by semicolons to execute individual queries.
    // NOTE: This assumes standard statements that can be split by ';'
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            // Replace the admin password placeholder with a hashed one
            if (strpos($statement, "INSERT INTO `users`") !== false) {
                $adminPasswordHash = password_hash('admin', PASSWORD_ARGON2ID);
                $statement = str_replace(
                    "'password_here'",
                    "'" . $adminPasswordHash . "'",
                    $statement
                );
            }
            $pdo->exec($statement);
        }
    }

    // 4. Initialize the main Database connection class
    Database::connect();

    // 5. Run all pending migrations
    $migrationRunner = new MigrationRunner(Database::pdo(), dirname(__DIR__) . '/database/migrations');
    $migrationRunner->runAllPending();

    echo "Test database '{$databaseName}' initialized and migrated successfully.\n\n";

} catch (Exception $e) {
    fwrite(STDERR, "FATAL ERROR: Failed to initialize test database: " . $e->getMessage() . "\n");
    exit(1);
}
