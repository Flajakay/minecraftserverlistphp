<?php

use App\Core\System\Database;
use App\Core\System\MigrationRunner;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script can only be run from the command line.\n");
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

Database::connect();

$command = $argv[1] ?? 'status';
$runner = new MigrationRunner();

switch ($command) {
    case 'status':
        printStatus($runner->status());
        exit(0);

    case 'run':
    case 'up':
        $result = $runner->runAllPending();
        printRunResult($result);
        exit(empty($result['failed']) ? 0 : 1);

    default:
        fwrite(STDERR, "Usage: php migrate.php [status|run]\n");
        exit(1);
}

function printStatus(array $status): void
{
    echo "Migrations\n";
    echo "----------\n";
    echo "Total:   " . (int)($status['total'] ?? 0) . "\n";
    echo "Applied: " . (int)($status['applied'] ?? 0) . "\n";
    echo "Pending: " . (int)($status['pending'] ?? 0) . "\n";
    echo "Ignored: " . (int)($status['ignored'] ?? 0) . "\n\n";

    foreach (($status['items'] ?? []) as $item) {
        $state = !empty($item['applied']) ? 'applied' : 'pending';
        echo sprintf("[%s] %s\n", $state, $item['name']);
    }

    if (!empty($status['ignored_items'])) {
        echo "\nIgnored files\n";
        echo "-------------\n";
        foreach ($status['ignored_items'] as $item) {
            echo sprintf("[ignored] %s (%s)\n", $item['name'], $item['reason'] ?? 'not supported');
        }
    }
}

function printRunResult(array $result): void
{
    if (!empty($result['executed'])) {
        foreach ($result['executed'] as $migration) {
            echo sprintf("%s: %s\n", $migration['name'], $migration['status']);

            foreach (($migration['statements'] ?? []) as $statement) {
                echo sprintf(
                    "  #%d %s: %s\n",
                    $statement['index'],
                    $statement['status'],
                    $statement['statement']
                );
            }
        }
    } else {
        echo "No pending migrations.\n";
    }

    if (!empty($result['failed'])) {
        $failed = $result['failed'];
        fwrite(STDERR, "\nMigration failed: " . ($failed['name'] ?? '') . "\n");
        fwrite(STDERR, "Message: " . ($failed['message'] ?? '') . "\n");

        if (!empty($failed['statement_index'])) {
            fwrite(STDERR, "Statement: #" . $failed['statement_index'] . "\n");
        }

        if (!empty($failed['sqlstate'])) {
            fwrite(STDERR, "SQLSTATE: " . $failed['sqlstate'] . "\n");
        }

        if (!empty($failed['driver_code'])) {
            fwrite(STDERR, "Driver code: " . $failed['driver_code'] . "\n");
        }

        if (!empty($failed['statement'])) {
            fwrite(STDERR, "SQL:\n" . $failed['statement'] . "\n");
        }
    }
}
