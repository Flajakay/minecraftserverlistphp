<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/bootstrap.php';

use App\Models\Server;
use App\Models\Payment;
use App\Models\PlayerHistory;
use App\Core\Integrations\GameServers\UniversalBatchPinger;
use App\Core\System\Database;

echo "Starting server status update...\n";

// We use a LIMIT to prevent the script from running forever if we have 10,000 servers.
// We order by 'last_check' so we always pick the "stalest" servers first.
// This creates a "Rolling Update" effect.

$batchSize = 10;
$servers = Database::fetchAll("SELECT * FROM servers WHERE active = 1 ORDER BY last_check ASC LIMIT $batchSize");

if (empty($servers)) {
    echo "No servers to update.\n";
    exit;
}

echo "Processing batch of " . count($servers) . " servers...\n";

// Initialize the universal batch pinger that handles all protocols
$pinger = new UniversalBatchPinger();

// Run the batch!
$results = $pinger->pingBatch($servers, 2, 10);

$updated = 0;
$errors = 0;

foreach ($servers as $server) {
    try {
        $result = $results[$server->id] ?? null;

        if (!$result) {
            $result = \App\Core\Integrations\GameServers\ServerStatusResult::offline();
        }

        Server::updateStatus(
            $server->id,
            $result->online ? 1 : 0,
            $result->players,
            $result->maxPlayers,
            $result->version
        );

        Server::updateProtocolStatus($server->id, [
            'map_name' => $result->map,
            'game_name' => $result->game,
            'password_protected' => $result->metadata['password_protected'] ?? null,
            'protocol_metadata' => $result->metadata,
        ]);

        if (PlayerHistory::shouldRecord($server->id, 15)) {
            PlayerHistory::record($server->id, $result->players, $result->maxPlayers, $result->online ? 1 : 0);
        }

        $updated++;
        echo "Updated {$server->name} ({$server->address}:{$server->port}) - " .
            ($result->online ? 'Online' : 'Offline') . "\n";

    } catch (Exception $e) {
        $errors++;
        echo "Error updating {$server->name}: " . $e->getMessage() . "\n";
    }
}

$expiredCount = Payment::expireHighlights();
echo "Expired highlights: {$expiredCount} server highlights removed\n";

echo "Update complete. Updated: {$updated}, Errors: {$errors}\n";
