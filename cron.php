<?php

require_once __DIR__ . '/bootstrap.php';

use App\Models\Server;
use App\Models\Payment;
use App\Core\MinecraftPing;
use App\Core\Database;

echo "Starting server status update...\n";

$servers = Database::fetchAll('SELECT * FROM servers WHERE active = 1');
$updated = 0;
$errors = 0;

foreach ($servers as $server) {
    try {
        $status = MinecraftPing::checkServer($server->address, $server->port);
        
        Server::updateStatus($server->id, 
            $status['online'] ? 1 : 0, 
            $status['players'], 
            $status['max_players'], 
            $status['version']
        );
        
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
