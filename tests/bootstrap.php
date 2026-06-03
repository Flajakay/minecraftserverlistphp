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

    // 3. Initialize the main Database connection class
    Database::connect();

    // 4. Run all migrations, including the initial schema migration.
    $migrationRunner = new MigrationRunner(Database::pdo(), dirname(__DIR__) . '/database/migrations');
    $migrationRunner->runAllPending();

    // 5. Seed the default admin user used by tests and fresh installs.
    seedDefaultAdminUser(Database::pdo());

    echo "Test database '{$databaseName}' initialized and migrated successfully.\n\n";

} catch (Exception $e) {
    fwrite(STDERR, "FATAL ERROR: Failed to initialize test database: " . $e->getMessage() . "\n");
    exit(1);
}

function seedDefaultAdminUser(PDO $pdo): void
{
    $passwordHash = password_hash('admin', PASSWORD_ARGON2ID);

    $stmt = $pdo->prepare(
        "INSERT INTO `users` (`username`, `password`, `email`, `name`, `type`, `active`, `created_at`)
         SELECT ?, ?, ?, ?, ?, ?, NOW()
         WHERE NOT EXISTS (
             SELECT 1 FROM `users` WHERE `username` = ? OR `email` = ?
         )"
    );

    $stmt->execute([
        'admin',
        $passwordHash,
        'admin@admin.com',
        'Admin',
        2,
        1,
        'admin',
        'admin@admin.com'
    ]);
}
