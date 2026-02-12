<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\System\Database;

try {
    // Manually initialize database connection since we can't use bootstrap.php
    // (bootstrap.php starts session which requires the table we are about to create)
    Database::connect();
    
    $sql = file_get_contents(__DIR__ . '/database/migrations/2026_02_12_create_sessions_table.up.sql');
    
    if (!$sql) {
        die("Error: Could not read migration file.\n");
    }
    
    Database::pdo()->exec($sql);
    
    echo "Migration executed successfully: sessions table created.\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
