<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/bootstrap.php';

use App\Models\Server;
use App\Models\Payment;
use App\Models\PlayerHistory;
use App\Core\Integrations\AsyncBatchPinger;
use App\Core\System\Database;

echo "Starting server status update...\n";

// We use a LIMIT to prevent the script from running forever if we have 10,000 servers.
// We order by 'last_updated' so we always pick the "stalest" servers first.
// This creates a "Rolling Update" effect.

$batchSize = 10;
$servers = Database::fetchAll("SELECT * FROM servers WHERE active = 1 ORDER BY last_check ASC LIMIT $batchSize");

if (empty($servers)) {
    echo "No servers to update.\n";
    exit;
}

echo "Processing batch of " . count($servers) . " servers...\n";

// Initialize our new Async Pinger
$pinger = new AsyncBatchPinger();

// Run the batch!
// This will take approx 2 seconds TOTAL, even if all 10 servers are offline.
$results = $pinger->pingBatch($servers, 2, 10);

$updated = 0;
$errors = 0;

foreach ($servers as $server) {
    try {
        $status = $results[$server->id] ?? null;

        if (!$status) {
            // Should not happen if pinger works correctly, but safe fallback
            $status = ['online' => false, 'players' => 0, 'max_players' => 0, 'version' => 'offline'];
        }

        Server::updateStatus(
            $server->id,
            $status['online'] ? 1 : 0,
            $status['players'],
            $status['max_players'],
            $status['version']
        );

        if (PlayerHistory::shouldRecord($server->id, 15)) {
            PlayerHistory::record($server->id, $status['players'], $status['max_players'], $status['online'] ? 1 : 0);
        }

        $updated++;
        echo "Updated {$server->name} ({$server->address}:{$server->port}) - " .
            ($status['online'] ? 'Online' : 'Offline') . "\n";

    } catch (Exception $e) {
        $errors++;
        echo "Error updating {$server->name}: " . $e->getMessage() . "\n";
    }
}

$expiredCount = Payment::expireHighlights();
echo "Expired highlights: {$expiredCount} server highlights removed\n";

echo "Update complete. Updated: {$updated}, Errors: {$errors}\n";
